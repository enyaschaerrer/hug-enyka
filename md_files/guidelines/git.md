# Git et gestion du code source

Git doit etre utilise pour la gestion du code source. C'est une demande explicite du professeur dans `md_files/guidelines/consigne.md`.

## Fichiers a ne pas commiter

Ne jamais commiter :

- `.env`
- mots de passe, tokens, cles API ou secrets ;
- `node_modules/`
- `vendor/`
- fichiers temporaires ;
- logs inutiles ;
- fichiers de cache.

## Fichiers importants a garder

Ces fichiers doivent rester suivis s'ils existent :

- `.env.example`
- `composer.json`
- `composer.lock`
- `package.json`
- `package-lock.json`
- `vite.config.js`
- migrations, seeders et factories ;
- documentation dans `md_files/`.

## Bonnes pratiques

- Faire des commits clairs et limites a un sujet.
- Utiliser des branches si le travail est important.
- Ne pas melanger une correction, une nouvelle fonctionnalite et du nettoyage sans raison.
- Verifier l'etat Git avant une modification importante.
- Ne pas supprimer ou remplacer le travail d'une autre personne sans accord.

## Messages de commit

Un bon message de commit doit expliquer ce qui change.

Exemples :

- `Add DaisyUI usage guidelines`
- `Create database seed data`
- `Fix validation errors on contact form`
- `Document deployment procedure`

## IA

Une IA ne doit jamais utiliser de commande Git destructive sans demande explicite. Elle ne doit pas faire de reset, checkout force, suppression massive ou rebase sans validation claire de l'utilisateur.
