# RAPPORT TECHNIQUE ET FONCTIONNEL
## Système de Gestion de Pharmacie — PharmaSanté
### TP Final — DAW — L2 INF G 04 — UHBC FSEI

---

> **Projet :** PharmaSanté  
> **Slogan :** Votre santé, notre priorité  
> **Université :** UHBC — Faculté des Sciences Exactes et Informatique (FSEI)  
> **Niveau :** L2 Informatique — Groupe 04  
> **Matière :** Développement d'Applications Web (DAW)  
> **Année universitaire :** 2025–2026  

---

## TABLE DES MATIÈRES

1. Présentation du projet  
2. Stack technique  
3. Architecture des fichiers  
4. Schéma de la base de données  
5. Comptes de démonstration  
6. Rôles et permissions  
7. Système d'authentification  
8. Module Administrateur  
9. Module Vendeur  
10. Module Patient  
11. Espace Visiteur  
12. Rapport des ventes (nouveau module)  
13. Sécurité  
14. Comportements JavaScript  
15. Installation sur XAMPP  
16. Résumé des pages PHP  

---

## 1. PRÉSENTATION DU PROJET

PharmaSanté est un système complet de gestion de pharmacie développé en PHP procédural pur avec MySQL. Il permet de gérer l'ensemble des opérations d'une pharmacie : stock de médicaments, caisse enregistreuse, enregistrement des ventes, gestion des patients et des vendeurs, ainsi qu'un espace personnel pour chaque patient.

L'application est entièrement en français, les montants sont affichés en Dinars Algériens (DA), et elle est conçue pour fonctionner sur XAMPP sans aucune dépendance externe côté serveur.

---

## 2. STACK TECHNIQUE

| Composant        | Technologie                          |
|------------------|--------------------------------------|
| Langage serveur  | PHP 7+ (procédural, sans framework)  |
| Base de données  | MySQL / MariaDB 5.7+                 |
| Frontend         | HTML5 + CSS3 pur (sans framework)    |
| JavaScript       | Vanilla JS (sans jQuery ni framework)|
| Icônes           | FontAwesome 6 (CDN)                  |
| Serveur local    | XAMPP (Apache + MySQL)               |
| Architecture     | Page-based routing (pas de MVC)      |
| Encodage         | UTF-8 / utf8mb4 (support arabe/fr)   |

---

## 3. ARCHITECTURE DES FICHIERS

```
pharmacare/
│
├── config/
│   └── database.php              ← Connexion MySQLi (host, user, password, db)
│
├── includes/
│   └── functions.php             ← Fonctions utilitaires et gardes d'accès
│
├── sql/
│   └── pharmacare_db.sql         ← Schéma complet + données de démonstration
│
├── index.php                     ← Page d'accueil publique (catalogue médicaments)
├── login.php                     ← Connexion unifiée (tous les rôles)
├── logout.php                    ← Destruction de session
├── register_patient.php          ← Auto-inscription des patients
├── forgot_password.php           ← Demande de réinitialisation du mot de passe
├── reset_password.php            ← Réinitialisation via token sécurisé
│
├── admin_dashboard.php           ← Tableau de bord administrateur
├── gestion_vendeur.php           ← CRUD complet des vendeurs
├── gestion_medicament.php        ← CRUD complet des médicaments + stock
├── gestion_caisse.php            ← CRUD des caisses enregistreuses
├── liste_vente.php               ← Historique global de toutes les ventes
├── admin_patients.php            ← Vue lecture seule de tous les patients
├── rapport_ventes.php            ← Rapport imprimable des ventes (nouveau)
│
├── vendeur_dashboard.php         ← Tableau de bord vendeur
├── caisse_operation.php          ← Ouverture / fermeture de caisse
├── ajouter_vente.php             ← Enregistrement d'une vente
├── mes_ventes.php                ← Historique personnel du vendeur
├── gestion_patient.php           ← Ajout/modification des patients par le vendeur
├── recherche_medicament.php      ← Recherche rapide de médicaments
│
├── patient_dashboard.php         ← Tableau de bord patient
├── patient_profile.php           ← Modifier profil et mot de passe
├── patient_historique.php        ← Historique complet des achats du patient
│
├── header.php                    ← En-tête de navigation partagé (include)
├── footer.php                    ← Pied de page partagé (include)
├── style.css                     ← Feuille de style complète (1 seul fichier CSS)
└── script.js                     ← JavaScript global (modals, animations, etc.)
```

**Total : 27 fichiers PHP + 1 CSS + 1 JS + 1 SQL**

---

## 4. SCHÉMA DE LA BASE DE DONNÉES

Base de données : `pharmacare_db` — Encodage : `utf8mb4_unicode_ci`

### Table `vendeur`
Stocke les comptes administrateur et vendeur.

| Colonne         | Type                        | Description                        |
|-----------------|-----------------------------|------------------------------------|
| id              | INT AUTO_INCREMENT PK       | Identifiant unique                 |
| nom             | VARCHAR(100) NOT NULL       | Nom complet                        |
| email           | VARCHAR(100) UNIQUE NOT NULL| Adresse email (identifiant)        |
| password        | VARCHAR(255) NOT NULL       | Mot de passe hashé (bcrypt)        |
| role            | ENUM('admin','vendeur')     | Rôle dans le système               |
| date_creation   | TIMESTAMP DEFAULT NOW()     | Date d'inscription                 |

### Table `patient`
Stocke les comptes patients (auto-inscrits ou ajoutés par un vendeur).

| Colonne         | Type                        | Description                        |
|-----------------|-----------------------------|------------------------------------|
| id              | INT AUTO_INCREMENT PK       | Identifiant unique                 |
| nom             | VARCHAR(100) NOT NULL       | Nom de famille                     |
| prenom          | VARCHAR(100) NOT NULL       | Prénom                             |
| telephone       | VARCHAR(20) NOT NULL        | Numéro de téléphone                |
| email           | VARCHAR(100) UNIQUE NOT NULL| Adresse email (identifiant)        |
| password        | VARCHAR(255) NOT NULL       | Mot de passe hashé (bcrypt)        |
| adresse         | TEXT                        | Adresse postale                    |
| date_naissance  | DATE                        | Date de naissance                  |
| date_creation   | TIMESTAMP DEFAULT NOW()     | Date d'inscription                 |

### Table `medicament`
Catalogue complet des médicaments avec stock.

| Colonne         | Type                        | Description                        |
|-----------------|-----------------------------|------------------------------------|
| id              | INT AUTO_INCREMENT PK       | Identifiant unique                 |
| nom             | VARCHAR(150) NOT NULL       | Nom du médicament                  |
| description     | TEXT                        | Description / indication           |
| prix_dinar      | DECIMAL(10,2) NOT NULL      | Prix unitaire en DA                |
| quantite_stock  | INT DEFAULT 0               | Quantité en stock                  |
| categorie       | VARCHAR(100)                | Catégorie thérapeutique            |
| date_ajout      | TIMESTAMP DEFAULT NOW()     | Date d'ajout au catalogue          |

### Table `caisse`
Représente les caisses enregistreuses de la pharmacie.

| Colonne           | Type                        | Description                      |
|-------------------|-----------------------------|----------------------------------|
| id                | INT AUTO_INCREMENT PK       | Identifiant unique               |
| nom               | VARCHAR(100) NOT NULL       | Nom de la caisse                 |
| montant_ouverture | DECIMAL(10,2) DEFAULT 0     | Fonds de caisse à l'ouverture    |
| montant_fermeture | DECIMAL(10,2)               | Montant enregistré à la fermeture|
| heure_ouverture   | DATETIME                    | Horodatage d'ouverture           |
| heure_fermeture   | DATETIME                    | Horodatage de fermeture          |
| statut            | ENUM('ouverte','fermee')    | État actuel de la caisse         |
| vendeur_id        | INT FK → vendeur(id)        | Vendeur responsable              |

### Table `vente`
Enregistre chaque transaction de vente. Clé centrale du système.

| Colonne       | Type                        | Description                        |
|---------------|-----------------------------|------------------------------------|
| id            | INT AUTO_INCREMENT PK       | Identifiant de la vente            |
| date_vente    | TIMESTAMP DEFAULT NOW()     | Horodatage de la vente             |
| medicament_id | INT FK → medicament(id)     | Médicament vendu                   |
| patient_id    | INT FK → patient(id)        | Patient acheteur                   |
| vendeur_id    | INT FK → vendeur(id)        | Vendeur réalisateur                |
| caisse_id     | INT FK → caisse(id)         | Caisse utilisée                    |
| quantite      | INT NOT NULL                | Nombre d'unités vendues            |
| prix_unitaire | DECIMAL(10,2) NOT NULL      | Prix au moment de la vente         |
| prix_total    | DECIMAL(10,2) NOT NULL      | prix_unitaire × quantite           |
| notes         | TEXT                        | Remarques optionnelles             |

> **Relations :** vente → medicament (N:1), vente → patient (N:1), vente → vendeur (N:1), vente → caisse (N:1), caisse → vendeur (N:1)

### Table `password_resets`
Gère les tokens de réinitialisation de mot de passe.

| Colonne      | Type                          | Description                      |
|--------------|-------------------------------|----------------------------------|
| id           | INT AUTO_INCREMENT PK         | Identifiant                      |
| email        | VARCHAR(100) NOT NULL         | Email de l'utilisateur           |
| token        | VARCHAR(64) NOT NULL (INDEX)  | Token hexadécimal (bin2hex 32B)  |
| table_source | ENUM('vendeur','patient')     | Table d'origine de l'utilisateur |
| expires_at   | DATETIME NOT NULL             | Expiration (1 heure)             |

---

## 5. COMPTES DE DÉMONSTRATION

Ces comptes sont insérés automatiquement par le fichier SQL.

| Rôle        | Email                    | Mot de passe  | Accès                            |
|-------------|--------------------------|---------------|----------------------------------|
| Administrateur | admin@pharmacare.dz   | admin123      | Contrôle total du système        |
| Vendeur     | karim@pharmacare.dz      | vendeur123    | Ventes, patients, caisse         |
| Vendeur     | samira@pharmacare.dz     | vendeur456    | Ventes, patients, caisse         |
| Patient     | youcef@gmail.com         | patient123    | Espace personnel uniquement      |
| Patient     | lina@gmail.com           | patient123    | Espace personnel uniquement      |
| Patient     | karima@gmail.com         | patient123    | Espace personnel uniquement      |

---

## 6. RÔLES ET PERMISSIONS

| Action                              | Visiteur | Patient | Vendeur | Admin |
|-------------------------------------|:--------:|:-------:|:-------:|:-----:|
| Voir le catalogue médicaments       | ✓        | ✓       | ✓       | ✓     |
| Créer un compte patient             | ✓        | –       | –       | –     |
| Se connecter                        | –        | ✓       | ✓       | ✓     |
| Voir son historique d'achats        | –        | ✓       | –       | –     |
| Modifier son profil                 | –        | ✓       | –       | –     |
| Ouvrir / fermer une caisse          | –        | –       | ✓       | ✓     |
| Enregistrer une vente               | –        | –       | ✓       | ✓     |
| Ajouter / modifier des patients     | –        | –       | ✓       | ✓     |
| Consulter ses propres ventes        | –        | –       | ✓       | ✓     |
| Gérer les médicaments (CRUD)        | –        | –       | –       | ✓     |
| Gérer les vendeurs (CRUD)           | –        | –       | –       | ✓     |
| Gérer les caisses (CRUD)            | –        | –       | –       | ✓     |
| Voir toutes les ventes              | –        | –       | –       | ✓     |
| Voir tous les patients              | –        | –       | –       | ✓     |
| Générer un rapport des ventes       | –        | –       | –       | ✓     |

---

## 7. SYSTÈME D'AUTHENTIFICATION

### Connexion (login.php)

1. L'utilisateur soumet son email et mot de passe via formulaire POST.
2. PHP interroge d'abord la table `vendeur` via une requête préparée.
3. Si trouvé et que `password_verify()` réussit → session créée avec `user_id`, `user_name`, `user_role` → redirection vers le tableau de bord correspondant.
4. Si non trouvé dans vendeur, PHP interroge la table `patient` → même logique.
5. `session_regenerate_id(true)` est appelé à chaque connexion réussie (protection contre la fixation de session).
6. En cas d'échec : message d'erreur générique sans préciser si c'est l'email ou le mot de passe qui est incorrect.

### Garde d'accès (functions.php)

Chaque page protégée appelle une fonction en tout début de script :
- `requireAdmin()` → refuse l'accès si `$_SESSION['user_role'] !== 'admin'`
- `requireVendeur()` → autorise `admin` et `vendeur`
- `requirePatient()` → autorise uniquement `patient`

Toute tentative d'accès non autorisé redirige immédiatement vers `login.php`.

### Réinitialisation de mot de passe

1. `forgot_password.php` : l'utilisateur entre son email → le système génère un token de 64 caractères (`bin2hex(random_bytes(32))`), le stocke dans `password_resets` avec une expiration dans 1 heure, et affiche le lien de réinitialisation.
2. `reset_password.php` : validation du token (existence + non-expiré) → formulaire de nouveau mot de passe (min. 6 caractères) → `password_hash()` → mise à jour en base → suppression du token.

### Déconnexion (logout.php)

`session_unset()` + `session_destroy()` + redirection vers `login.php`.

---

## 8. MODULE ADMINISTRATEUR

### 8.1 Tableau de bord (admin_dashboard.php)

Statistiques affichées en temps réel :
- Nombre total de vendeurs actifs
- Nombre total de médicaments dans le catalogue
- Nombre total de patients inscrits
- Nombre de ventes du jour (depuis minuit)
- Chiffre d'affaires du jour en DA
- Nombre de médicaments épuisés (stock = 0)

Les nombres s'animent avec un effet "count-up" au chargement de la page (JavaScript).

Les 5 dernières ventes de la pharmacie sont affichées en tableau.

### 8.2 Gestion des Vendeurs (gestion_vendeur.php)

- **Ajouter :** formulaire avec nom, email, mot de passe (haché avant stockage), rôle (admin/vendeur)
- **Modifier :** modal popup pré-rempli avec les données actuelles
- **Supprimer :** confirmation via modal, suppression définitive de la base
- Toutes les opérations utilisent des requêtes préparées avec `bind_param`

### 8.3 Gestion des Médicaments (gestion_medicament.php)

- **Ajouter :** nom, catégorie (avec suggestions via `<datalist>`), prix en DA, stock initial, description
- **Modifier :** modal popup
- **Supprimer :** bloqué si le médicament a des ventes liées (contrainte de clé étrangère)
- **Recherche :** filtre par nom ou catégorie via paramètre GET
- **Pagination :** 10 éléments par page
- Badge de stock coloré : vert (en stock), orange (stock faible < 10), rouge (épuisé)

### 8.4 Gestion des Caisses (gestion_caisse.php)

- Créer des caisses nommées (ex : "Caisse Principale", "Caisse 2")
- Voir le statut (ouverte/fermée), les horodatages, le vendeur assigné
- Modifier le nom d'une caisse
- Supprimer uniquement les caisses fermées (les ouvertes sont protégées)

### 8.5 Toutes les Ventes (liste_vente.php)

- Tableau complet de toutes les ventes de la pharmacie
- Filtres combinables : vendeur, date de début, date de fin, recherche par patient ou médicament
- Total général calculé dynamiquement selon les filtres actifs
- Bouton "Imprimer" (masqué dans la version imprimée)

### 8.6 Voir les Patients (admin_patients.php)

- Vue lecture seule de tous les patients inscrits
- Données affichées : nom, prénom, téléphone, email, adresse, date de naissance, date d'inscription

### 8.7 Rapport des Ventes — NOUVEAU (rapport_ventes.php)

Rapport complet et imprimable. Accessible via la sidebar admin.

**Filtres de période :**
- Aujourd'hui
- Cette semaine
- Ce mois
- Cette année
- Personnalisé (sélecteur de dates de début et fin)

**Indicateurs clés (KPIs) :**
- Nombre de ventes réalisées
- Chiffre d'affaires total (DA)
- Panier moyen par vente (DA)
- Nombre d'unités vendues
- Nombre de patients servis (distincts)
- Nombre de vendeurs actifs

**Sections du rapport :**
1. Top 5 médicaments (par quantité vendue)
2. Performance des vendeurs (par chiffre d'affaires)
3. Ventes par catégorie thérapeutique (barres de progression)
4. Récapitulatif journalier (tableau par jour)
5. Détail complet de toutes les ventes (tableau avec numérotation)
6. Bloc de signature (Administrateur / Directeur / Date)

**Impression :** le bouton "Imprimer / Exporter PDF" déclenche `window.print()`. Les éléments de navigation (sidebar, filtres, header du site) sont masqués via CSS `@media print`. L'en-tête du document imprimé affiche le logo PharmaSanté, l'adresse, et la période du rapport.

---

## 9. MODULE VENDEUR

### 9.1 Tableau de bord (vendeur_dashboard.php)

- Bannière de statut de caisse : si une caisse est ouverte, affiche son nom, l'heure d'ouverture, et un **compteur en temps réel** (HH:MM:SS mis à jour chaque seconde via `setInterval`). Si fermée, bouton pour en ouvrir une.
- Statistiques : patients inscrits, ses propres ventes du jour, son propre chiffre d'affaires du jour
- Tableau de ses 5 dernières ventes personnelles

### 9.2 Gestion Caisse (caisse_operation.php)

**Ouvrir une caisse :**
- Sélectionner parmi les caisses disponibles (fermées)
- Saisir le montant d'ouverture
- Le système enregistre `heure_ouverture`, `montant_ouverture`, `vendeur_id`, `statut = 'ouverte'`

**Fermer une caisse :**
- Saisir le montant de fermeture (comptage physique)
- Le système enregistre `heure_fermeture`, `montant_fermeture`, `statut = 'fermee'`
- La caisse est libérée pour un autre vendeur

> Un vendeur ne peut enregistrer des ventes que si une caisse est ouverte à son nom.

### 9.3 Nouvelle Vente (ajouter_vente.php)

**Pré-requis :** caisse ouverte obligatoire (sinon message d'alerte avec lien vers caisse_operation.php).

**Formulaire :**
1. Sélectionner un patient (dropdown avec nom + téléphone)
2. Sélectionner un médicament (dropdown avec stock disponible et prix)
3. Saisir la quantité (le max est limité au stock disponible côté JavaScript)
4. Notes optionnelles

**Calculateur de prix en temps réel :**
Dès qu'un médicament est sélectionné, JavaScript affiche instantanément :
- Prix unitaire (DA)
- Quantité saisie
- Total = prix unitaire × quantité (DA)

**Au soumettre :**
1. Vérification côté serveur que le stock est suffisant
2. Insertion dans `vente` (medicament_id, patient_id, vendeur_id, caisse_id, quantite, prix_unitaire, prix_total)
3. Décrémentation de `quantite_stock` dans `medicament`
4. Affichage d'un **reçu de vente** récapitulatif (patient, médicament, quantité, prix, caisse, date)

### 9.4 Mes Ventes (mes_ventes.php)

Historique personnel filtrable par date, affichant uniquement les ventes du vendeur connecté.

### 9.5 Gérer les Patients (gestion_patient.php)

- Ajouter un nouveau patient manuellement (sans qu'il s'inscrive lui-même)
- Modifier les informations d'un patient existant
- Supprimer un patient

### 9.6 Recherche Médicament (recherche_medicament.php)

Recherche rapide par nom ou catégorie, affichant le stock actuel, le prix, et la description.

---

## 10. MODULE PATIENT

### 10.1 Tableau de bord (patient_dashboard.php)

Après connexion, le patient voit :
- Total de ses achats (nombre de transactions)
- Montant total dépensé en DA
- Nombre de médicaments distincts achetés
- Date de son dernier achat
- Tableau de ses 5 achats les plus récents (médicament, quantité, montant, vendeur)

### 10.2 Mon Profil (patient_profile.php)

- Modifier : nom, prénom, téléphone, adresse, date de naissance
- Changer son mot de passe (vérification de l'ancien mot de passe avant d'accepter le nouveau)

### 10.3 Mes Médicaments (patient_historique.php)

- Historique complet et paginé de tous ses achats
- Date, médicament, quantité, montant payé, nom du vendeur qui a réalisé la vente

---

## 11. ESPACE VISITEUR

La page d'accueil `index.php` est publique et accessible sans connexion.

**Catalogue de médicaments :**
- Grille de cartes (une carte par médicament)
- Chaque carte affiche : nom, catégorie, badge de stock (En Stock / Épuisé), prix en DA, description
- **Filtre par catégorie :** onglets cliquables (Tous, Analgésique, Antibiotique, Anti-inflammatoire, Antidiabétique, Antihistaminique, Cardiovasculaire, Gastro-entérologie, Respiratoire, Vitamines)
- **Recherche en temps réel :** une barre de recherche filtre les cartes instantanément via JavaScript sans rechargement de page
- 15 médicaments de démonstration pré-chargés

---

## 12. RAPPORT DES VENTES (NOUVEAU MODULE)

Voir section 8.7 pour le détail complet.

**Accès :** Admin → sidebar → "Rapport des Ventes"  
**URL :** `rapport_ventes.php`

Ce module génère un document professionnel et imprimable directement depuis le navigateur. Via `Ctrl+P` ou le bouton "Imprimer / Exporter PDF", l'utilisateur peut sauvegarder le rapport en PDF depuis les options d'impression du navigateur (option "Enregistrer en PDF" dans Chrome/Firefox).

---

## 13. SÉCURITÉ

| Mesure                          | Implémentation                                              |
|---------------------------------|-------------------------------------------------------------|
| Hachage des mots de passe       | `password_hash()` (bcrypt) + vérification `password_verify()` |
| Protection SQL Injection        | Requêtes préparées (`prepare` + `bind_param`) sur toutes les entrées utilisateur |
| Protection XSS                  | Fonction `h()` = `htmlspecialchars()` sur toutes les sorties HTML |
| Contrôle d'accès par rôle       | Fonctions `requireAdmin()`, `requireVendeur()`, `requirePatient()` en tête de chaque page |
| Protection session fixation     | `session_regenerate_id(true)` à chaque connexion            |
| Tokens de réinitialisation      | 64 caractères hex cryptographiquement sûrs, expiration 1h, usage unique |
| Validation côté serveur         | Toutes les entrées POST/GET validées avant traitement        |
| Suppression protégée            | Médicaments avec ventes = non supprimables / Caisses ouvertes = non supprimables |

---

## 14. COMPORTEMENTS JAVASCRIPT (script.js)

Tout le JavaScript de l'application tient dans un seul fichier `script.js`.

| Fonction                    | Description                                                     |
|-----------------------------|-----------------------------------------------------------------|
| `togglePasswordVisibility`  | Bascule type="password" / type="text" sur les champs de mdp     |
| `validateForm`              | Validation côté client avant soumission (champs requis, email)   |
| `validatePasswordStrength`  | Barre de force du mot de passe (rouge < 6 chars, vert ≥ 6)      |
| `countUp`                   | Animation des statistiques de 0 → valeur réelle au chargement   |
| `openModal` / `closeModal`  | Affichage/masquage des modals (overlay + fenêtre)               |
| `deleteConfirmation`        | Affiche le modal de confirmation avant suppression               |
| `confirmDelete`             | Soumet le formulaire de suppression caché après confirmation     |
| `validateStock`             | Avertit en rouge si la quantité dépasse le stock disponible      |
| `calculateTotal`            | Met à jour le total DA en temps réel dans le formulaire de vente |
| `onMedChange`               | Met à jour les infos de stock et prix au choix du médicament     |
| `updateTimer`               | Affiche un compteur HH:MM:SS de durée d'ouverture de caisse      |
| Recherche live (index.php)  | Filtre les cartes médicaments par nom sans rechargement          |
| Filtre catégorie (index.php)| Masque/affiche les cartes selon la catégorie cliquée             |
| `setPeriod` (rapport)       | Change la période du rapport et soumet le formulaire             |

---

## 15. INSTALLATION SUR XAMPP

### Prérequis
- XAMPP installé (Windows, macOS ou Linux)
- Apache et MySQL démarrés dans le panneau XAMPP

### Étapes

**1.** Extraire le dossier `pharmasante/` dans :
```
C:\xampp\htdocs\pharmasante\    (Windows)
/Applications/XAMPP/htdocs/pharmasante/    (macOS)
/opt/lampp/htdocs/pharmasante/    (Linux)
```

**2.** Ouvrir **phpMyAdmin** à l'adresse : `http://localhost/phpmyadmin`

**3.** Créer une nouvelle base de données :
- Nom : `pharmacare_db`
- Encodage : `utf8mb4_unicode_ci`

**4.** Importer le schéma :
- Onglet "Importer" → choisir le fichier `pharmasante/sql/pharmacare_db.sql`
- Cliquer "Exécuter"
- Résultat attendu : 6 tables créées + données de démonstration insérées

**5.** Vérifier la configuration `config/database.php` (déjà configuré pour XAMPP) :
```php
$host     = 'localhost';
$user     = 'root';
$password = '';          // Vide par défaut sur XAMPP
$database = 'pharmacare_db';
```

**6.** Accéder à l'application :
```
http://localhost/pharmasante/
```

**7.** Se connecter avec un des comptes de démonstration (voir section 5).

---

## 16. RÉSUMÉ DES PAGES PHP

| Fichier                    | Rôle requis         | Description courte                              |
|----------------------------|---------------------|-------------------------------------------------|
| index.php                  | Aucun (public)      | Catalogue public des médicaments                |
| login.php                  | Aucun               | Connexion unifiée tous rôles                    |
| logout.php                 | Connecté            | Déconnexion et destruction de session           |
| register_patient.php       | Aucun               | Inscription d'un nouveau patient                |
| forgot_password.php        | Aucun               | Demande de réinitialisation de mdp              |
| reset_password.php         | Aucun               | Réinitialisation via token sécurisé             |
| admin_dashboard.php        | Admin               | Tableau de bord avec stats globales             |
| gestion_vendeur.php        | Admin               | CRUD vendeurs                                   |
| gestion_medicament.php     | Admin               | CRUD médicaments + gestion stock                |
| gestion_caisse.php         | Admin               | CRUD caisses enregistreuses                     |
| liste_vente.php            | Admin               | Historique filtrable de toutes les ventes       |
| admin_patients.php         | Admin               | Vue lecture seule de tous les patients          |
| rapport_ventes.php         | Admin               | Rapport imprimable avec KPIs et détails         |
| vendeur_dashboard.php      | Vendeur + Admin     | Tableau de bord avec statut caisse et timer     |
| caisse_operation.php       | Vendeur + Admin     | Ouverture et fermeture de caisse                |
| ajouter_vente.php          | Vendeur + Admin     | Formulaire de vente avec calcul temps réel      |
| mes_ventes.php             | Vendeur + Admin     | Historique personnel des ventes                 |
| gestion_patient.php        | Vendeur + Admin     | Ajout / modification de patients                |
| recherche_medicament.php   | Vendeur + Admin     | Recherche rapide dans le catalogue              |
| patient_dashboard.php      | Patient             | Espace personnel avec statistiques d'achats     |
| patient_profile.php        | Patient             | Modification du profil et du mot de passe       |
| patient_historique.php     | Patient             | Historique complet et paginé des achats         |

---

## DONNÉES DE DÉMONSTRATION INCLUSES

### 15 Médicaments (répartis en 9 catégories)

| Médicament             | Catégorie          | Prix (DA) | Stock |
|------------------------|--------------------|-----------|-------|
| Paracétamol 500mg      | Analgésique        | 150,00    | 200   |
| Doliprane 1000mg       | Analgésique        | 165,00    | 130   |
| Ibuprofène 400mg       | Anti-inflammatoire | 180,00    | 150   |
| Diclofénac 50mg        | Anti-inflammatoire | 195,00    | 85    |
| Amoxicilline 500mg     | Antibiotique       | 320,00    | 80    |
| Doxycycline 100mg      | Antibiotique       | 290,00    | 45    |
| Metformine 850mg       | Antidiabétique     | 210,00    | 60    |
| Cétirizine 10mg        | Antihistaminique   | 160,00    | 100   |
| Loratadine 10mg        | Antihistaminique   | 175,00    | 75    |
| Amlodipine 5mg         | Cardiovasculaire   | 240,00    | 90    |
| Simvastatine 20mg      | Cardiovasculaire   | 260,00    | 55    |
| Oméprazole 20mg        | Gastro-entérologie | 280,00    | 120   |
| Salbutamol Spray       | Respiratoire       | 420,00    | 40    |
| Vitamine C 1000mg      | Vitamines          | 120,00    | 250   |
| Zinc 15mg              | Vitamines          | 140,00    | 180   |

### 1 Caisse pré-configurée
- Caisse Principale — Montant d'ouverture : 5 000,00 DA — Statut : Fermée

---

*Rapport généré pour le TP Final DAW — L2 INF G 04 — UHBC FSEI — 2025/2026*
