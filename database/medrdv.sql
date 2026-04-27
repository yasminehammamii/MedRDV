-- =============================================
-- MedRDV - Système de Gestion de Rendez-vous
-- Base de données : medrdv
-- =============================================

CREATE DATABASE IF NOT EXISTS medrdv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medrdv;

-- Table des utilisateurs (base commune)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('patient', 'medecin', 'admin') NOT NULL DEFAULT 'patient',
    telephone VARCHAR(20),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Table des médecins
CREATE TABLE medecins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialite VARCHAR(100) NOT NULL,
    num_ordre VARCHAR(50),
    adresse VARCHAR(255),
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    bio TEXT,
    photo VARCHAR(255),
    tarif DECIMAL(8,2),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des patients
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_naissance DATE,
    sexe ENUM('M', 'F') DEFAULT 'M',
    adresse VARCHAR(255),
    ville VARCHAR(100),
    mutuelle VARCHAR(100),
    antecedents TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des disponibilités des médecins
CREATE TABLE disponibilites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medecin_id INT NOT NULL,
    jour_semaine TINYINT NOT NULL COMMENT '0=Lundi ... 6=Dimanche',
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    duree_rdv INT DEFAULT 30 COMMENT 'Durée en minutes',
    actif TINYINT(1) DEFAULT 1,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des rendez-vous
CREATE TABLE rendezvous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    medecin_id INT NOT NULL,
    date_rdv DATE NOT NULL,
    heure_rdv TIME NOT NULL,
    motif TEXT,
    statut ENUM('en_attente', 'confirme', 'annule', 'termine') DEFAULT 'en_attente',
    notes_medecin TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des avis / évaluations
CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    medecin_id INT NOT NULL,
    rdv_id INT,
    note TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
    visible TINYINT(1) DEFAULT 1,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    FOREIGN KEY (rdv_id) REFERENCES rendezvous(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- Données de démonstration
-- =============================================

-- Utilisateurs (mot de passe: Password123!)
INSERT INTO users (nom, prenom, email, mot_de_passe, role, telephone) VALUES
('Admin', 'Système', 'admin@medrdv.tn', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uEutrius2', 'admin', '71000000'),
('Ben Ali', 'Khalil', 'khalil.benali@medrdv.tn', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uEutrius2', 'medecin', '98123456'),
('Trabelsi', 'Sonia', 'sonia.trabelsi@medrdv.tn', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uEutrius2', 'medecin', '22987654'),
('Bouazizi', 'Mehdi', 'mehdi.bouazizi@medrdv.tn', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uEutrius2', 'patient', '55001122'),
('Mansouri', 'Lina', 'lina.mansouri@medrdv.tn', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uEutrius2', 'patient', '54334455');

INSERT INTO medecins (user_id, specialite, num_ordre, adresse, ville, code_postal, bio, tarif) VALUES
(2, 'Cardiologie', 'MED-2024-001', '12 Rue de la République', 'Tunis', '1000', 'Cardiologue avec 15 ans d\'expérience, spécialisé en cardiologie interventionnelle.', 80.00),
(3, 'Dermatologie', 'MED-2024-002', '45 Avenue Habib Bourguiba', 'Sfax', '3000', 'Dermatologue experte en maladies de la peau et esthétique dermatologique.', 60.00);

INSERT INTO patients (user_id, date_naissance, sexe, adresse, ville, mutuelle) VALUES
(4, '1990-05-15', 'M', '8 Rue Ibn Khaldoun', 'Tunis', 'CNAM'),
(5, '1985-11-20', 'F', '33 Avenue des Jasmins', 'Sousse', 'CNAM');

INSERT INTO disponibilites (medecin_id, jour_semaine, heure_debut, heure_fin, duree_rdv) VALUES
(1, 1, '09:00', '13:00', 30), -- Lundi
(1, 1, '14:00', '18:00', 30),
(1, 3, '09:00', '13:00', 30), -- Mercredi
(1, 4, '09:00', '13:00', 30), -- Jeudi
(2, 2, '10:00', '14:00', 20), -- Mardi
(2, 2, '15:00', '18:00', 20),
(2, 5, '09:00', '12:00', 20); -- Vendredi

INSERT INTO rendezvous (patient_id, medecin_id, date_rdv, heure_rdv, motif, statut) VALUES
(1, 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00', 'Consultation de routine', 'confirme'),
(2, 2, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '11:00', 'Problème de peau', 'en_attente'),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 10 DAY), '09:30', 'Suivi cardiaque', 'termine');

INSERT INTO avis (patient_id, medecin_id, rdv_id, note, commentaire) VALUES
(1, 1, 3, 5, 'Excellent médecin, très professionnel et à l\'écoute. Je recommande vivement.');