<?php
// models/Medecin.php

require_once __DIR__ . '/../config/db.php';

class Medecin {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByUserId(int $userId): array|false {
        $stmt = $this->db->prepare(
            "SELECT m.*, u.nom, u.prenom, u.email, u.telephone
             FROM medecins m
             JOIN users u ON m.user_id = u.id
             WHERE m.user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT m.*, u.nom, u.prenom, u.email, u.telephone
             FROM medecins m
             JOIN users u ON m.user_id = u.id
             WHERE m.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO medecins (user_id, specialite, num_ordre, adresse, ville, code_postal, bio, tarif)
             VALUES (:user_id, :specialite, :num_ordre, :adresse, :ville, :code_postal, :bio, :tarif)"
        );
        $stmt->execute([
            ':user_id'     => $userId,
            ':specialite'  => $data['specialite'],
            ':num_ordre'   => $data['num_ordre'] ?? null,
            ':adresse'     => $data['adresse'] ?? null,
            ':ville'       => $data['ville'] ?? null,
            ':code_postal' => $data['code_postal'] ?? null,
            ':bio'         => $data['bio'] ?? null,
            ':tarif'       => $data['tarif'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE medecins SET specialite=:specialite, adresse=:adresse, ville=:ville,
             code_postal=:code_postal, bio=:bio, tarif=:tarif
             WHERE id=:id"
        );
        return $stmt->execute([
            ':specialite'  => $data['specialite'],
            ':adresse'     => $data['adresse'] ?? null,
            ':ville'       => $data['ville'] ?? null,
            ':code_postal' => $data['code_postal'] ?? null,
            ':bio'         => $data['bio'] ?? null,
            ':tarif'       => $data['tarif'] ?? null,
            ':id'          => $id,
        ]);
    }

    public function search(string $query = '', string $specialite = '', string $ville = ''): array {
        $sql = "SELECT m.*, u.nom, u.prenom, u.telephone,
                       COALESCE(AVG(a.note), 0) AS note_moyenne,
                       COUNT(a.id) AS nb_avis
                FROM medecins m
                JOIN users u ON m.user_id = u.id
                LEFT JOIN avis a ON a.medecin_id = m.id AND a.visible = 1
                WHERE u.actif = 1";
        $params = [];

        if ($query) {
            $sql .= " AND (u.nom LIKE :q OR u.prenom LIKE :q OR m.specialite LIKE :q)";
            $params[':q'] = "%$query%";
        }
        if ($specialite) {
            $sql .= " AND m.specialite = :specialite";
            $params[':specialite'] = $specialite;
        }
        if ($ville) {
            $sql .= " AND m.ville LIKE :ville";
            $params[':ville'] = "%$ville%";
        }

        $sql .= " GROUP BY m.id ORDER BY note_moyenne DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSpecialites(): array {
        $stmt = $this->db->query("SELECT DISTINCT specialite FROM medecins ORDER BY specialite");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getVilles(): array {
        $stmt = $this->db->query("SELECT DISTINCT ville FROM medecins WHERE ville IS NOT NULL ORDER BY ville");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}