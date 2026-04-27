<?php
// models/User.php

require_once __DIR__ . '/../config/db.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND actif = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (nom, prenom, email, mot_de_passe, role, telephone)
             VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :telephone)"
        );
        $stmt->execute([
            ':nom'          => $data['nom'],
            ':prenom'       => $data['prenom'],
            ':email'        => $data['email'],
            ':mot_de_passe' => password_hash($data['mot_de_passe'], PASSWORD_BCRYPT),
            ':role'         => $data['role'],
            ':telephone'    => $data['telephone'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return (bool)$stmt->fetchColumn();
    }

    public function verifyPassword(string $plain, string $hash): bool {
        return password_verify($plain, $hash);
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        foreach (['nom', 'prenom', 'telephone'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id");
        return $stmt->execute($params);
    }
}