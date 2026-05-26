# hug-enyka
Projet d'intégration 2026, Ingénierie des Médias - problématique de la collecte du don du sang en entreprise, pour les HUG

## Technologies utilisees

- PHP 8.4.17
- Laravel 13.9.0
- MySQL 8.0.40
- Node.js 22.20.0
- npm 11.10.0
- Vite 8.0.13
- Vue.js 3.5.34
- Tailwind CSS 4.3.0
- daisyUI 5.5.20
- GSAP 3.15.0

## Installation locale

### Prérequis

- PHP 8.4 ou supérieur
- Composer
- Node.js et npm
- MAMP avec MySQL démarré ou Docker

### Récupérer le projet

```bash
git clone <https://github.com/enyaschaerrer/hug-enyka>
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

La configuration MySQL locale attendue est celle de MAMP :

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

L'interclassement et l'encodage () peuvent rester sur la valeur par defaut proposee par MySQL/MAMP.

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

### Lancer le projet

Activer le serveur local avec MAMP, puis ouvrir l'URL locale du projet.

Si le projet n'est pas servi par MAMP, utiliser le serveur Laravel integré :

```bash
php artisan serve
```

Dans ce cas, le site est accessible sur l'URL affichée par Laravel, généralement :

```text
http://127.0.0.1:8000
```

### Développement frontend

Si vous modifiez les fichiers CSS, JavaScript ou Vue, lancer Vite dans un autre terminal :

```bash
npm run dev
```

Sinon, cette commande n'est pas nécessaire pour simplement ouvrir le projet en local.


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


## Structure actuelle

```text
app/
  Enums/
    UserRole.php                 Roles utilisateur actuels.
  Http/
    Controllers/
      Admin/                     Controllers du back-office.
      PublicSiteController.php   Point d'entree des pages publiques.
    Middleware/
      EnsureUserHasRole.php      Protection des routes par role.
    Requests/
      Admin/                     Validation des formulaires admin.
  Models/
    User.php                     Modele utilisateur Laravel.

bootstrap/
  app.php                        Configuration Laravel, dont alias middleware.

database/
  factories/                     Factories de test.
  migrations/                    Schema de base de donnees.
  seeders/                       Donnees de test, dont superadmin.

md_architecture/
  auth-admin-cobranding.md       Decisions d'architecture auth/admin/co-branding.

resources/
  css/
    app.css                      CSS global Tailwind/DaisyUI.
  js/
    App.vue                      Selectionne la page Vue selon l'URL.
    app.ts                       Point d'entree Vue.
    components/
      tinder-cards/              Prototype swipe actuel.
    pages/
      public/                    Pages publiques: home, collecte, trophee, label, contact.
      admin/                     Pages admin: login, dashboard.
  views/
    app.blade.php                Vue Blade qui monte l'application Vue.

routes/
  web.php                        Routes publiques et admin.

tests/
  Feature/                       Tests fonctionnels Laravel.
  Unit/                          Tests unitaires.
```

Routes utiles actuellement :

```text
/              Accueil public.
/collecte      Page publique Collecte.
/trophee       Page publique Trophee.
/label         Page publique Label.
/contact       Page publique Contact.
/admin/login   Connexion admin.
/admin         Dashboard admin protege.
```
