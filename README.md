# ELVITA // ROYAUME COSMIQUE

**ELVITA** est une application web futuriste de type "portail cosmique" offrant une interface avancée avec chat IA, tableau de bord utilisateur, et panneau d'administration complet.

---

## 🌌 Fonctionnalités

### Portail Utilisateur (`index.php`)
- Interface holographique futuriste avec effets visuels avancés (scanlines, grille, aurores)
- Système d'authentification avec écran SAS (Security Access System)
- Chat IA intégré utilisant l'API Mistral avec rotation de 3 clés API
- Tableau de bord personnel avec KPIs (Bonheur, Santé, Finance, Karma, Amour, Travail, Confiance, Influence)
- Historique des conversations sauvegardé en base de données
- Design responsive mobile-first

### Panneau d'Administration (`admin/`)
- Interface "Grand Monarque" avec statistiques en temps réel
- Gestion des utilisateurs (certification, suppression, consultation)
- Visualisation des messages et conversations utilisateurs
- Gestion de la boutique (ajout, modification, suppression d'articles)
- Configuration des prompts IA
- Logs d'activité admin
- Graphiques Chart.js pour les visites et l'activité
- Effets Particles.js et animations GSAP

### API (`api/`)
- **auth.php** : Authentification (login/logout/inscription)
- **mistral.php** : Moteur IA avec rotation automatique des clés API
  - Clé 1 : Chat général & conseil
  - Clé 2 : Analyse profil & OPGA
  - Clé 3 : Admin IA & diagnostics
  - Support des modèles : `mistral-medium-2505`, `mistral-large-2411`, `magistral-medium-2509`

### Base de Données (`db/`)
- SQLite avec mode WAL pour la performance
- Tables : `users`, `messages`, `opga`, `visites`, `boutique`, `prompts_config`, `admin_log`
- Fichier principal : `elvita.db`

---

## 📁 Structure du Projet

```
/workspace
├── index.php          # Portail principal utilisateur
├── ajax.php           # Handler AJAX global
├── admin/
│   ├── index.php      # Interface d'administration
│   └── ajax.php       # Handler AJAX admin
├── api/
│   ├── auth.php       # API d'authentification
│   └── mistral.php    # API Mistral IA
└── db/
    ├── init.php       # Initialisation DB SQLite
    └── elvita.db      # Base de données SQLite
```

---

## ⚙️ Prérequis

- **PHP 7.4+** avec extensions :
  - `pdo_sqlite` ou `sqlite3`
  - `curl`
  - `session`
  - `json`
- **Serveur web** : Apache, Nginx, ou PHP built-in server
- **Navigateur moderne** avec support CSS Grid, Flexbox, Canvas

---

## 🚀 Installation

1. **Cloner le dépôt** (ou copier les fichiers) dans votre dossier web :
   ```bash
   cd /var/www/html/elvita
   ```

2. **Vérifier les permissions** :
   ```bash
   chmod 755 -R /var/www/html/elvita
   chmod 666 /var/www/html/elvita/db/elvita.db*
   ```

3. **Configurer le serveur web** (exemple Apache) :
   ```apache
   <Directory /var/www/html/elvita>
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

4. **Accéder à l'application** :
   - Portail utilisateur : `http://votre-domaine.com/index.php`
   - Administration : `http://votre-domaine.com/admin/`

---

## 🔐 Premier Accès Admin

La base de données doit contenir au moins un utilisateur avec le rôle `admin`. Pour créer un admin manuellement :

```php
<?php
require_once 'db/init.php';
$db = getDB();
$pass = password_hash('VotreMotDePasseSecret', PASSWORD_DEFAULT);
$db->exec("INSERT INTO users (email, password, role) VALUES ('admin@elvita.net', '$pass', 'admin')");
echo "Admin créé !";
?>
```

---

## 🎨 Technologies Utilisées

### Backend
- PHP 7.4+
- SQLite3 (mode WAL)
- cURL pour les appels API

### Frontend
- HTML5 / CSS3 (variables CSS, Grid, Flexbox)
- JavaScript (ES6+)
- Google Fonts : Orbitron, VT323, Rajdhani, Share Tech Mono, Exo 2
- Bootstrap 5.3.3
- Chart.js 4.4.4
- GSAP 3.12.5
- Particles.js 2.0.0
- Tippy.js (tooltips)
- Notyf (notifications toast)
- Animate.css

### APIs Externes
- Mistral AI API (chat, analyse, admin)

---

## 📊 Base de Données

### Tables Principales

| Table | Description |
|-------|-------------|
| `users` | Utilisateurs avec KPIs, rôles, certification |
| `messages` | Historique des conversations chat IA |
| `opga` | Objectifs Personnels de Grande Ambition |
| `visites` | Log des visites pages |
| `boutique` | Articles de la boutique |
| `prompts_config` | Configuration des prompts IA |
| `admin_log` | Journal des actions admin |

---

## 🔑 Configuration API Mistral

Les clés API sont définies dans `api/mistral.php` :

```php
define('API_KEYS', [
    1 => '5qaR8Rake',     // Chat général
    2 => 'o3rGXRShytu',   // Analyse profil
    3 => 'vEzQruXkF',     // Admin IA
]);
```

⚠️ **Remplacez ces clés par vos propres clés Mistral API en production.**

---

## 🛡️ Sécurité

- Sessions PHP sécurisées
- Mots de passe hashés avec `password_hash()`
- Protection CSRF basique via sessions
- Validation des entrées utilisateur
- Logs d'activité admin

---

## 📝 Notes

- L'application utilise un design "dark mode" avec néons cyan/or
- Le système de rotation des clés API permet de contourner les limites de rate-limiting
- La base de données SQLite est pré-initialisée avec un schéma complet
- Les effets visuels (scanlines, particules, aurores) créent une immersion "cosmique"

---

## 🧪 Test Rapide avec PHP Built-in Server

```bash
cd /workspace
php -S localhost:8000
```

Puis ouvrez :
- http://localhost:8000 (utilisateur)
- http://localhost:8000/admin/ (administration)

---

## 📄 Licence

Projet propriétaire — Tous droits réservés.

---

## 👨‍💻 Auteur

Développé pour **ELVITA NEXUS v4.0** — Royaume Cosmique

---

*✨ "Bienvenue dans le royaume cosmique Elvita"*
