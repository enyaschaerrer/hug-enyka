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

### Prerequis

- PHP 8.4 ou superieur
- Composer
- Node.js et npm
- MAMP avec MySQL demarre

### Recuperer le projet

```bash
git clone <https://github.com/enyaschaerrer/hug-enyka>
cd hug-enyka
```

### Installer les dependances

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

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=hug_enyka_local
DB_USERNAME=root
DB_PASSWORD=root
```

### Creer la base de donnees

Dans phpMyAdmin, creer une base de donnees nommee :

```text
hug_enyka_local
```

L'interclassement et l'encodage () peuvent rester sur la valeur par defaut proposee par MySQL/MAMP.

### Initialiser la base de donnees

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

Si le projet n'est pas servi par MAMP, utiliser le serveur Laravel integre :

```bash
php artisan serve
```

Dans ce cas, le site est accessible sur l'URL affichee par Laravel, generalement :

```text
http://127.0.0.1:8000
```

### Developpement frontend

Si vous modifiez les fichiers CSS, JavaScript ou Vue, lancer Vite dans un autre terminal :

```bash
npm run dev
```

Sinon, cette commande n'est pas necessaire pour simplement ouvrir le projet en local.

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
