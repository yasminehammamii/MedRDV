<?php
// models/Disponibilite.php

require_once __DIR__ . '/../config/db.php';

class Disponibilite {
    private PDO $db;

    const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    public function __construct() {
        $this->db = getDB();
    }

    public function findByMedecin(int $medecinId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM disponibilites WHERE medecin_id = ? ORDER BY jour_semaine, heure_debut"
        );
        $stmt->execute([$medecinId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO disponibilites (medecin_id, jour_semaine, heure_debut, heure_fin, duree_rdv)
             VALUES (:medecin_id, :jour_semaine, :heure_debut, :heure_fin, :duree_rdv)"
        );
        $stmt->execute([
            ':medecin_id'   => $data['medecin_id'],
            ':jour_semaine' => $data['jour_semaine'],
            ':heure_debut'  => $data['heure_debut'],
            ':heure_fin'    => $data['heure_fin'],
            ':duree_rdv'    => $data['duree_rdv'] ?? 30,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id, int $medecinId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM disponibilites WHERE id = ? AND medecin_id = ?"
        );
        return $stmt->execute([$id, $medecinId]);
    }

    public function toggleActif(int $id, int $medecinId): bool {
        $stmt = $this->db->prepare(
            "UPDATE disponibilites SET actif = NOT actif WHERE id = ? AND medecin_id = ?"
        );
        return $stmt->execute([$id, $medecinId]);
    }
}