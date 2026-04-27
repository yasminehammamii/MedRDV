<?php
// models/Patient.php

require_once __DIR__ . '/../config/db.php';

class Patient {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByUserId(int $userId): array|false {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.nom, u.prenom, u.email, u.telephone
             FROM patients p
             JOIN users u ON p.user_id = u.id
             WHERE p.user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.nom, u.prenom, u.email, u.telephone
             FROM patients p
             JOIN users u ON p.user_id = u.id
             WHERE p.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO patients (user_id, date_naissance, sexe, adresse, ville, mutuelle, antecedents)
             VALUES (:user_id, :date_naissance, :sexe, :adresse, :ville, :mutuelle, :antecedents)"
        );
        $stmt->execute([
            ':user_id'        => $userId,
            ':date_naissance' => $data['date_naissance'] ?? null,
            ':sexe'           => $data['sexe'] ?? 'M',
            ':adresse'        => $data['adresse'] ?? null,
            ':ville'          => $data['ville'] ?? null,
            ':mutuelle'       => $data['mutuelle'] ?? null,
            ':antecedents'    => $data['antecedents'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE patients SET date_naissance=:date_naissance, sexe=:sexe,
             adresse=:adresse, ville=:ville, mutuelle=:mutuelle, antecedents=:antecedents
             WHERE id=:id"
        );
        return $stmt->execute([
            ':date_naissance' => $data['date_naissance'] ?? null,
            ':sexe'           => $data['sexe'] ?? 'M',
            ':adresse'        => $data['adresse'] ?? null,
            ':ville'          => $data['ville'] ?? null,
            ':mutuelle'       => $data['mutuelle'] ?? null,
            ':antecedents'    => $data['antecedents'] ?? null,
            ':id'             => $id,
        ]);
    }
}