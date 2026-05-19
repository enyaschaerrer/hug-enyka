# Base de donnees

Le projet doit utiliser MySQL et fournir un jeu de donnees de test avec des seeders. C'est une demande explicite du professeur dans `md_files/guidelines/consigne.md`.

## Technologies

- Base de donnees cible : MySQL.
- ORM principal : Eloquent.
- Migrations : `database/migrations/`.
- Seeders : `database/seeders/`.
- Factories : `database/factories/`.

## Migrations

- Une migration doit decrire clairement une modification de schema.
- Utiliser des noms de tables et colonnes explicites.
- Ajouter les contraintes utiles : `nullable`, `unique`, `foreignId`, `constrained`, `cascadeOnDelete`, etc.
- Ne pas modifier une ancienne migration deja partagee ou executee sans raison claire.
- Preferer une nouvelle migration pour faire evoluer le schema.

## Models et relations

- Les models Eloquent doivent representer les tables importantes.
- Definir les relations Eloquent quand elles existent : `hasMany`, `belongsTo`, `belongsToMany`, etc.
- Proteger les champs assignables avec `$fillable` ou une strategie equivalente.
- Eviter de mettre de la logique de presentation dans les models.

## Seeders et donnees de test

- Le projet doit contenir un jeu de donnees de test utilisable.
- Les seeders doivent creer des donnees coherentes pour tester l'application localement.
- Les donnees de test ne doivent contenir aucun secret reel.
- Les factories doivent etre utilisees quand elles simplifient la creation de donnees.

## Validation des donnees

- Les contraintes de base de donnees ne remplacent pas la validation Laravel.
- Les donnees doivent etre validees avant insertion ou mise a jour.
- Les erreurs de validation doivent etre propres et comprehensibles.

## IA

Une IA doit verifier les migrations, models et seeders existants avant d'ajouter une table ou une relation. Elle ne doit pas inventer un schema sans tenir compte de l'existant.
