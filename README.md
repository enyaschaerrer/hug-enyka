# Enyka — Plateforme HUG de collecte de sang en entreprise
Enyka est une plateforme web conçue pour promouvoir et faciliter la collecte de sang en entreprise pour les HUG.
Le projet est scindé en trois espaces : un site public de présentation, un site co-brandé propre à chaque entreprise partenaire, utilisé pour communiquer la collecte en interne et rediriger les collaborateurs vers la prise de rendez-vous, et un espace `/admin` permettant au CTS de gérer les campagnes, les entreprises et de suivre les indicateurs de performance.

Ce projet a été réalisé dans le cadre d’un mandat confié par les HUG à des étudiants de la HEIG-VD.

## Stack technique

### Stack globale

- Backend : PHP, Laravel, MySQL
- Frontend : Vue 3, TypeScript, Vite
- UI : Tailwind CSS, daisyUI

### Dépendances Composer utilisées

- `laravel/framework` : framework principal backend

### Dépendances Node utilisées

- `d3-geo` : rendu des cartes
- `topojson-client` : lecture des données cartographiques
- `world-atlas` : données monde pour les cartes
- `flag-icons` : affichage des drapeaux
- `qr-code-styling` : génération du QR code co-brandé
- `vue3-flashcards` : interactions de type swipe
- `vue3-lottie` : animations Lottie

## Installation locale

### Prérequis

- PHP 8.3 ou supérieur
- Composer
- Node.js et npm
- Un serveur local type MAMP ou équivalent, avec MySQL démarré

### Récupérer le projet

```bash
git clone https://github.com/enyaschaerrer/hug-enyka
cd hug-enyka
```

### Installer les dépendances

```bash
composer install
npm install
```

### Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Configurer ensuite les variables de base de données dans le fichier `.env` selon votre environnement local.

Exemple de configuration MySQL locale avec MAMP :

#### Mac

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=hug_enyka_local
DB_USERNAME=root
DB_PASSWORD=root
```

#### Windows

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hug_enyka_local
DB_USERNAME=root
DB_PASSWORD=root
```

### Créer la base de donnees

Dans phpMyAdmin, créer une base de données nommée :

```text
hug_enyka_local
```

L’interclassement et l’encodage peuvent rester sur les valeurs par défaut proposées par phpMyAdmin.

### Initialiser la base de données

```bash
php artisan migrate
php artisan db:seed
```

Compte de test pour le back-office :

```text
Email: superadmin@example.com
Mot de passe: password
Role: superadmin
```

Vous avez également la possibilité d’utiliser un snapshot de la base déjà peuplée :

```bash
php artisan migrate:fresh
php artisan db:seed --class=Database\\Seeders\\EnykaLocalSeeder
```

### Lancer le projet en local

Si le projet est déjà servi via votre environnement local habituel, ouvrez simplement son URL locale dans le navigateur.

Sinon, lancer le serveur Laravel :

```bash
php artisan serve
```

Dans ce cas, le site est accessible sur l'URL affichée par Laravel, généralement :

```text
http://127.0.0.1:8000
```

Puis, dans un autre terminal, lancer Vite pour compiler les assets frontend :

```bash
npm run dev
```


## Déploiement (production)

Le déploiement est automatisé via un **Git Hook push-to-deploy**. À chaque `git push prod main`, le serveur met à jour le code et rebuild le projet automatiquement, sans passer par GitHub.

### Configuration initiale du serveur (à faire une seule fois)

#### 1. Créer le site dans le Manager Infomaniak

- Créer un nouveau site dans le Manager Infomaniak
- Cloner le repo dans le dossier du site créé via SSH
- Une fois le projet en place, configurer le **Document Root** pour pointer sur le dossier `/public` du projet

#### 2. Se connecter au serveur

```bash
ssh 9i1pnb_enyaschaerrer@9i1pnb.ftp.infomaniak.com
```

#### 3. Installer Node.js via NVM

```bash
touch ~/.bashrc
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
source ~/.bashrc
nvm install --lts

# Vérifier l'installation
node -v && npm -v
```

#### 4. Préparer le dépôt Git bare

```bash
mkdir -p ~/git/hug-enyka.git
cd ~/git/hug-enyka.git
git init --bare
```

#### 5. Créer le Git Hook

```bash
nano ~/git/hug-enyka.git/hooks/post-receive
```

Contenu du hook :

```bash
#!/bin/bash

GIT_WORK_TREE=/home/clients/1aa1db0e5a3858eeace68203d5ed3b7c/sites/coeur-dhonneur.ch/hug-enyka git checkout -f main

cd /home/clients/1aa1db0e5a3858eeace68203d5ed3b7c/sites/coeur-dhonneur.ch/hug-enyka

composer install --no-dev --optimize-autoloader

export NVM_DIR="/home/clients/1aa1db0e5a3858eeace68203d5ed3b7c/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

npm ci
npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Rendre le hook exécutable :

```bash
chmod +x ~/git/hug-enyka.git/hooks/post-receive
exit
```

#### 6. Ajouter le remote prod en local

Dans ton dossier projet local :

```bash
git remote add prod 9i1pnb_enyaschaerrer@9i1pnb.ftp.infomaniak.com:git/hug-enyka.git
```

### Déployer

S'assurer d'avoir la dernière version en local, puis :

```bash
git push prod main
```

Le serveur checkout le code, installe les dépendances, build les assets et migre la base de données automatiquement.

### Commandes utiles

```bash
# Se connecter au serveur
ssh 9i1pnb_enyaschaerrer@9i1pnb.ftp.infomaniak.com

# Quitter le serveur
exit
```


## Structure du projet

```text
app/
  Enums/
    UserRole.php                 Roles utilisateur actuels.
  Http/
    Controllers/
      Admin/                     Controllers du back-office et des endpoints JSON admin.
      Public/                    Controllers des formulaires publics.
      CoBranded*.php             Controllers des pages, accès et tracking co-brandés.
      PublicSiteController.php   Point d'entrée des pages publiques.
    Middleware/
      EnsureUserHasRole.php      Protection des routes par rôle.
      TrackPageVisit.php         Tracking des visites publiques avec consentement.
    Requests/
      Admin/                     Validation des formulaires admin.
  Mail/                          Mailables Laravel.
  Models/
    Company.php                  Modèle entreprise.
    Collection.php               Modèle collecte.
    Form.php                     Modèle formulaire public.
    User.php                     Modèle utilisateur Laravel.
  Support/                       Helpers métier, notamment sur les domaines email.

bootstrap/
  app.php                        Configuration Laravel, middleware et bootstrap global.

database/
  factories/                     Factories de test.
  migrations/                    Schéma de base de données.
  seeders/                       Seeders de démo, de KPI et snapshot local.

md_architecture/
  *.md                           Documentation d'architecture et décisions projet.

resources/
  css/
    app.css                      CSS global Tailwind / daisyUI.
  js/
    App.vue                      Shell SPA pour l'admin et les pages co-brandées.
    app.ts                       Point d'entrée frontend global.
    components/
      admin/                     Composants du back-office.
      co-branded/                Composants de l'expérience co-brandée.
      interactive-map/           Carte monde interactive.
      modals/                    Modales globales, dont consentement cookies.
      public/                    Îlots Vue des pages publiques.
      sms-chat/                  Prototype de conversation SMS.
      tinder-cards/              Prototype swipe / questionnaire.
    composables/                 Logique réutilisable Vue.
    data/                        Données statiques JSON du frontend.
    pages/
      admin/                     Pages SPA du back-office.
      co-branded/                Pages SPA des campagnes co-brandées.
    services/                    Services frontend (tracking, cookies, etc.).
    types/                       Types TypeScript.
    utils/                       Utilitaires frontend.
  views/
    app.blade.php                Vue Blade qui monte la SPA admin / co-brandée.
    layouts/                     Layouts Blade publics.
    partials/                    Partiels Blade du site public.
    public/                      Pages publiques Blade.
    emails/                      Templates d'e-mails.

routes/
  web.php                        Routes publiques, co-brandées et admin.

tests/
  Feature/                       Tests fonctionnels Laravel.
  Unit/                          Tests unitaires.
```
