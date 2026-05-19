# Deploiement

Le projet doit fournir une procedure de mise en production. C'est une demande explicite du professeur dans `md_files/guidelines/consigne.md`.

La cible d'hebergement mentionnee dans la consigne est Infomaniak.

## Objectif

La procedure de deploiement doit expliquer comment passer d'un projet local fonctionnel a une version disponible en production.

## Points a documenter

- environnement serveur requis ;
- version PHP attendue ;
- configuration du domaine ou sous-domaine ;
- configuration du dossier public Laravel ;
- installation des dependances Composer ;
- installation ou compilation des assets Vite ;
- configuration du fichier `.env` de production ;
- configuration MySQL ;
- lancement des migrations ;
- gestion des permissions de `storage/` et `bootstrap/cache/` ;
- activation du cache Laravel si necessaire ;
- verification finale du site.

## Securite en production

- `APP_ENV` doit etre configure pour la production.
- `APP_DEBUG` doit etre desactive.
- Les secrets doivent rester dans l'environnement serveur, pas dans Git.
- Le dossier public expose doit etre `public/`, pas la racine Laravel complete.

## IA

Une IA ne doit pas proposer une procedure de deploiement generique qui ignore Laravel, Vite, MySQL ou Infomaniak.

Si un detail depend de l'interface Infomaniak actuelle, l'IA doit le signaler et verifier la documentation officielle ou demander confirmation.
