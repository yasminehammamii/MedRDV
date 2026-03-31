<?php
// models/Avis.php

require_once __DIR__ . '/../config/db.php';

class Avis {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByMedecin(int $medecinId): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.nom AS patient_nom, u.prenom AS patient_prenom
             FROM avis a
             JOIN patients p ON a.patient_id = p.id
             JOIN users u ON p.user_id = u.id
             WHERE a.medecin_id = ? AND a.visible = 1
             ORDER BY a.date_avis DESC"
        );
        $stmt->execute([$medecinId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        // Un seul avis par RDV
        if ($data['rdv_id'] && $this->existsForRdv($data['rdv_id'])) {
            throw new RuntimeException("Vous avez déjà laissé un avis pour ce rendez-vous.");
        }
        $stmt = $this->db->prepare(
            "INSERT INTO avis (patient_id, medecin_id, rdv_id, note, commentaire)
             VALUES (:patient_id, :medecin_id, :rdv_id, :note, :commentaire)"
        );
        $stmt->execute([
            ':patient_id'  => $data['patient_id'],
            ':medecin_id'  => $data['medecin_id'],
            ':rdv_id'      => $data['rdv_id'] ?? null,
            ':note'        => $data['note'],
            ':commentaire' => $data['commentaire'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function existsForRdv(int $rdvId): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM avis WHERE rdv_id = ?");
        $stmt->execute([$rdvId]);
        return (bool)$stmt->fetchColumn();
    }

    public function getStats(int $medecinId): array {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(AVG(note), 0) AS moyenne, COUNT(*) AS total
             FROM avis WHERE medecin_id = ? AND visible = 1"
        );
        $stmt->execute([$medecinId]);
        return $stmt->fetch();
    }
}