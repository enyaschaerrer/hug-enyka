# Index des guidelines du projet

Toute IA qui code dans ce projet doit lire et respecter ces documents avant de modifier le code. Ces regles sont obligatoires : elles servent a respecter les demandes du professeur, la stack technique du projet et les conventions locales. Si une demande utilisateur contredit ces guidelines, l'IA doit signaler le conflit avant de coder.

Les guidelines sont rangees dans le dossier `md_files/guidelines/`.

## Documents prioritaires

- `guidelines/consigne.md` : demandes du professeur. Ce fichier est la source principale des contraintes du projet.
- `guidelines/ia-rules.md` : regles generales que toute IA doit suivre avant, pendant et apres une modification.
- `guidelines/versions.md` : versions imposees de Laravel, PHP, Vue, Vite, Tailwind CSS, DaisyUI et des outils du projet.

## Architecture et code

- `guidelines/architecture.md` : organisation attendue du projet Laravel/Vue, emplacement des fichiers et interdictions d'architecture.
- `guidelines/coding-rules.md` : regles de lisibilite, structure du code, responsabilites des fichiers et limites pour les dependances.
- `guidelines/frontend.md` : regles front-end pour Vue, Tailwind CSS, DaisyUI, responsive et composants.
- `guidelines/daisyui.md` : comment chercher, verifier et utiliser les composants DaisyUI correctement.

## Backend, donnees et securite

- `guidelines/database.md` : regles pour MySQL, migrations, models, relations, factories et seeders.
- `guidelines/security.md` : validation, acces, CSRF, secrets, messages d'erreur et protections Laravel.
- `guidelines/testing.md` : tests PHPUnit, verification des fonctionnalites, seeders et controles front-end.

## Projet, Git et livraison

- `guidelines/git.md` : bonnes pratiques Git, fichiers a ne pas commiter et commandes interdites sans accord.
- `guidelines/local-setup.md` : exigences pour documenter et maintenir un environnement local installable.
- `guidelines/deployment.md` : exigences pour la procedure de mise en production, notamment sur Infomaniak.

## Ordre conseille pour une IA

Avant une modification, lire au minimum :

1. `guidelines/consigne.md`
2. `guidelines/ia-rules.md`
3. `guidelines/versions.md`
4. le ou les fichiers specialises lies a la demande

Exemples :

- modification UI : lire aussi `guidelines/frontend.md` et `guidelines/daisyui.md` ;
- modification base de donnees : lire aussi `guidelines/database.md` et `guidelines/security.md` ;
- ajout de tests : lire aussi `guidelines/testing.md` ;
- documentation d'installation : lire aussi `guidelines/local-setup.md` ;
- deploiement : lire aussi `guidelines/deployment.md`.
