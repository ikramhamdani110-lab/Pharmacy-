SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `pharmacare_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `pharmacare_db`;

DROP TABLE IF EXISTS `vente`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `caisse`;
DROP TABLE IF EXISTS `medicament`;
DROP TABLE IF EXISTS `patient`;
DROP TABLE IF EXISTS `vendeur`;

CREATE TABLE `vendeur` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','vendeur') DEFAULT 'vendeur',
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_vendeur_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `patient` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `telephone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `adresse` TEXT,
    `date_naissance` DATE,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_patient_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medicament` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `prix_dinar` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `quantite_stock` INT NOT NULL DEFAULT 0,
    `categorie` VARCHAR(100) DEFAULT NULL,
    `date_ajout` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `caisse` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `montant_ouverture` DECIMAL(10,2) DEFAULT 0.00,
    `montant_fermeture` DECIMAL(10,2) DEFAULT NULL,
    `heure_ouverture` DATETIME DEFAULT NULL,
    `heure_fermeture` DATETIME DEFAULT NULL,
    `statut` ENUM('ouverte','fermee') DEFAULT 'fermee',
    `vendeur_id` INT DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_caisse_vendeur`
        FOREIGN KEY (`vendeur_id`) REFERENCES `vendeur`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vente` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `date_vente` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `medicament_id` INT NOT NULL,
    `patient_id` INT NOT NULL,
    `vendeur_id` INT NOT NULL,
    `caisse_id` INT NOT NULL,
    `quantite` INT NOT NULL,
    `prix_unitaire` DECIMAL(10,2) NOT NULL,
    `prix_total` DECIMAL(10,2) NOT NULL,
    `notes` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_vente_medicament` FOREIGN KEY (`medicament_id`) REFERENCES `medicament`(`id`),
    CONSTRAINT `fk_vente_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient`(`id`),
    CONSTRAINT `fk_vente_vendeur` FOREIGN KEY (`vendeur_id`) REFERENCES `vendeur`(`id`),
    CONSTRAINT `fk_vente_caisse` FOREIGN KEY (`caisse_id`) REFERENCES `caisse`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `table_source` ENUM('vendeur','patient') NOT NULL,
    `expires_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_token` (`token`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `vendeur` (`nom`, `email`, `password`, `role`) VALUES
('Administrateur', 'admin@pharmacare.dz',  '$2y$10$TlUAxHPvZ0q4AupGKXz2P.2lHpck9a2g52wcKUHlZpgpH5wZ4xqmq', 'admin'),
('Karim Benali',   'karim@pharmacare.dz',  '$2y$10$Nc4s8jyhVdQPlXkp8vJS7eDLMdfTp54ZPgiObuImYT/UeVWRaRohG', 'vendeur'),
('Samira Hamidi',  'samira@pharmacare.dz', '$2y$10$gj8bIkBvEDIz8TaHOaXpw.2uhTxvrLj9ZTSHfckiGV6sRNOhrOWKm', 'vendeur');

INSERT INTO `patient` (`nom`, `prenom`, `telephone`, `email`, `password`, `adresse`, `date_naissance`) VALUES
('Mekki',  'Youcef', '0770123456', 'youcef@gmail.com',  '$2y$10$J2aFD7e.xXyBJs8ty2PoEOS5iDKUa20E8ZUkOwX55hQ37xqjEfDWe', '12 Rue Didouche, Alger',         '1995-06-15'),
('Aouad',  'Lina',   '0661234567', 'lina@gmail.com',    '$2y$10$J2aFD7e.xXyBJs8ty2PoEOS5iDKUa20E8ZUkOwX55hQ37xqjEfDWe', '5 Cité 1000 Log, Oran',          '1998-03-22'),
('Brahim', 'Karima', '0550987654', 'karima@gmail.com',  '$2y$10$J2aFD7e.xXyBJs8ty2PoEOS5iDKUa20E8ZUkOwX55hQ37xqjEfDWe', '8 Rue Bab Azoun, Constantine',   '1990-11-05');

INSERT INTO `medicament` (`nom`, `description`, `prix_dinar`, `quantite_stock`, `categorie`) VALUES
('Paracétamol 500mg',  'Analgésique et antipyrétique',               150.00, 200, 'Analgésique'),
('Doliprane 1000mg',   'Paracétamol haute dose adulte',              165.00, 130, 'Analgésique'),
('Ibuprofène 400mg',   'Anti-inflammatoire non stéroïdien',          180.00, 150, 'Anti-inflammatoire'),
('Diclofénac 50mg',    'Anti-inflammatoire et analgésique',          195.00,  85, 'Anti-inflammatoire'),
('Amoxicilline 500mg', 'Antibiotique à large spectre',               320.00,  80, 'Antibiotique'),
('Doxycycline 100mg',  'Antibiotique tétracycline',                  290.00,  45, 'Antibiotique'),
('Metformine 850mg',   'Traitement du diabète de type 2',            210.00,  60, 'Antidiabétique'),
('Cétirizine 10mg',    'Antihistaminique pour les allergies',        160.00, 100, 'Antihistaminique'),
('Loratadine 10mg',    'Antihistaminique de 2e génération',          175.00,  75, 'Antihistaminique'),
('Amlodipine 5mg',     'Traitement de l hypertension artérielle',    240.00,  90, 'Cardiovasculaire'),
('Simvastatine 20mg',  'Hypolipémiant pour réduire le cholestérol',  260.00,  55, 'Cardiovasculaire'),
('Oméprazole 20mg',    'Inhibiteur de la pompe à protons',           280.00, 120, 'Gastro-entérologie'),
('Salbutamol Spray',   'Bronchodilatateur pour l asthme',            420.00,  40, 'Respiratoire'),
('Vitamine C 1000mg',  'Supplément de vitamine C',                   120.00, 250, 'Vitamines'),
('Zinc 15mg',          'Complément alimentaire en zinc',             140.00, 180, 'Vitamines');

INSERT INTO `caisse` (`nom`, `montant_ouverture`, `statut`, `vendeur_id`, `heure_ouverture`) VALUES
('Caisse Principale', 5000.00, 'fermee', 2, NULL);
