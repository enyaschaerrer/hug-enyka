# Environnement local

Le projet doit fournir un environnement local disponible et une procedure claire pour le mettre en place. C'est une demande explicite du professeur dans `md_files/guidelines/consigne.md`.

## Objectif

Toute personne qui recupere le projet doit pouvoir l'installer, configurer la base de donnees, lancer le back-end Laravel et lancer le front-end Vite.

## Elements requis

- PHP compatible avec `md_files/guidelines/versions.md`
- Composer
- Node.js et npm
- MySQL
- Git

## Fichiers importants

- `.env.example` doit documenter les variables necessaires.
- `.env` ne doit jamais etre commite.
- `composer.json` et `composer.lock` doivent rester coherents.
- `package.json` et `package-lock.json` doivent rester coherents.

## Procedure attendue

Une procedure locale doit expliquer au minimum :

- comment installer les dependances PHP ;
- comment installer les dependances JavaScript ;
- comment creer le fichier `.env` ;
- comment configurer la connexion MySQL ;
- comment generer la cle Laravel ;
- comment lancer les migrations ;
- comment lancer les seeders ;
- comment demarrer le serveur Laravel ;
- comment demarrer Vite.

## IA

Une IA ne doit pas inventer une procedure d'installation sans verifier les scripts existants dans `composer.json` et `package.json`.

Si une commande ne correspond pas au projet actuel, elle ne doit pas etre proposee.
