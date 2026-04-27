<?php
// models/RendezVous.php

require_once __DIR__ . '/../config/db.php';

class RendezVous {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function create(array $data): int {
        // Vérifier unicité du créneau
        if ($this->creneauPris($data['medecin_id'], $data['date_rdv'], $data['heure_rdv'])) {
            throw new RuntimeException("Ce créneau est déjà réservé.");
        }
        $stmt = $this->db->prepare(
            "INSERT INTO rendezvous (patient_id, medecin_id, date_rdv, heure_rdv, motif, statut)
             VALUES (:patient_id, :medecin_id, :date_rdv, :heure_rdv, :motif, 'en_attente')"
        );
        $stmt->execute([
            ':patient_id' => $data['patient_id'],
            ':medecin_id' => $data['medecin_id'],
            ':date_rdv'   => $data['date_rdv'],
            ':heure_rdv'  => $data['heure_rdv'],
            ':motif'      => $data['motif'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function creneauPris(int $medecinId, string $date, string $heure): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM rendezvous
             WHERE medecin_id = ? AND date_rdv = ? AND heure_rdv = ?
             AND statut NOT IN ('annule')"
        );
        $stmt->execute([$medecinId, $date, $heure]);
        return (bool)$stmt->fetchColumn();
    }

    public function findByPatient(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.nom AS medecin_nom, u.prenom AS medecin_prenom,
                    m.specialite, m.adresse AS medecin_adresse
             FROM rendezvous r
             JOIN medecins m ON r.medecin_id = m.id
             JOIN users u ON m.user_id = u.id
             WHERE r.patient_id = ?
             ORDER BY r.date_rdv DESC, r.heure_rdv DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    public function findByMedecin(int $medecinId, ?string $dateDebut = null, ?string $dateFin = null): array {
        $sql = "SELECT r.*, u.nom AS patient_nom, u.prenom AS patient_prenom, u.telephone AS patient_tel
                FROM rendezvous r
                JOIN patients p ON r.patient_id = p.id
                JOIN users u ON p.user_id = u.id
                WHERE r.medecin_id = ?";
        $params = [$medecinId];

        if ($dateDebut) { $sql .= " AND r.date_rdv >= ?"; $params[] = $dateDebut; }
        if ($dateFin)   { $sql .= " AND r.date_rdv <= ?"; $params[] = $dateFin; }

        $sql .= " ORDER BY r.date_rdv ASC, r.heure_rdv ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT r.*,
                    u_p.nom AS patient_nom, u_p.prenom AS patient_prenom,
                    u_m.nom AS medecin_nom, u_m.prenom AS medecin_prenom,
                    m.specialite
             FROM rendezvous r
             JOIN patients p ON r.patient_id = p.id
             JOIN users u_p ON p.user_id = u_p.id
             JOIN medecins m ON r.medecin_id = m.id
             JOIN users u_m ON m.user_id = u_m.id
             WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatut(int $id, string $statut, ?string $notes = null): bool {
        $stmt = $this->db->prepare(
            "UPDATE rendezvous SET statut = :statut, notes_medecin = :notes WHERE id = :id"
        );
        return $stmt->execute([':statut' => $statut, ':notes' => $notes, ':id' => $id]);
    }

    public function annuler(int $id, int $userId, string $role): bool {
        // Vérifier que l'utilisateur est propriétaire
        $rdv = $this->findById($id);
        if (!$rdv) return false;
        return $this->updateStatut($id, 'annule');
    }

    public function getCreneauxDisponibles(int $medecinId, string $date): array {
        // Récupérer les disponibilités du jour
        $jourSemaine = (int)date('N', strtotime($date)) - 1; // 0=Lundi
        $stmt = $this->db->prepare(
            "SELECT * FROM disponibilites
             WHERE medecin_id = ? AND jour_semaine = ? AND actif = 1"
        );
        $stmt->execute([$medecinId, $jourSemaine]);
        $dispos = $stmt->fetchAll();

        // Générer tous les créneaux
        $creneaux = [];
        foreach ($dispos as $dispo) {
            $current = strtotime($dispo['heure_debut']);
            $fin     = strtotime($dispo['heure_fin']);
            $duree   = (int)$dispo['duree_rdv'] * 60;
            while ($current + $duree <= $fin) {
                $creneaux[] = date('H:i', $current);
                $current += $duree;
            }
        }

        // Enlever les créneaux déjà pris
        $stmt2 = $this->db->prepare(
            "SELECT heure_rdv FROM rendezvous
             WHERE medecin_id = ? AND date_rdv = ? AND statut NOT IN ('annule')"
        );
        $stmt2->execute([$medecinId, $date]);
        $pris = $stmt2->fetchAll(PDO::FETCH_COLUMN);

        return array_values(array_diff($creneaux, $pris));
    }

    public function statsPatient(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(statut = 'confirme') AS confirmes,
                SUM(statut = 'en_attente') AS en_attente,
                SUM(statut = 'annule') AS annules,
                SUM(statut = 'termine') AS termines
             FROM rendezvous WHERE patient_id = ?"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetch();
    }

    public function statsMedecin(int $medecinId): array {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(statut = 'confirme') AS confirmes,
                SUM(statut = 'en_attente') AS en_attente,
                SUM(statut = 'annule') AS annules,
                SUM(statut = 'termine') AS termines,
                SUM(date_rdv = CURDATE()) AS aujourd_hui
             FROM rendezvous WHERE medecin_id = ?"
        );
        $stmt->execute([$medecinId]);
        return $stmt->fetch();
    }
}