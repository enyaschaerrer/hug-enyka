# Tests et verification

Le projet doit etre fonctionnel de bout en bout. Les tests, seeders et verifications manuelles doivent servir cet objectif.

## Tests PHP

Le projet utilise PHPUnit, configure avec `phpunit.xml`.

Les tests doivent etre places dans :

- `tests/Unit/` pour les tests unitaires ;
- `tests/Feature/` pour les tests de fonctionnalite.

## Ce qu'il faut tester

Tester en priorite :

- validation des formulaires ;
- routes importantes ;
- creation, modification et suppression des donnees ;
- acces autorises et interdits ;
- messages d'erreur importants ;
- comportements critiques de l'application.

## Seeders

Le professeur demande un jeu de donnees de test.

Les seeders doivent permettre de tester rapidement l'application en local avec des donnees coherentes.

Ils ne doivent jamais contenir :

- vrais mots de passe personnels ;
- tokens reels ;
- donnees sensibles ;
- informations privees.

## Verification front-end

Verifier au minimum :

- les pages principales ;
- les formulaires ;
- les erreurs affichees ;
- le responsive mobile et desktop ;
- les composants DaisyUI modifies.

## IA

Une IA qui ajoute une fonctionnalite importante doit proposer ou ajouter une verification adaptee.

Elle ne doit pas pretendre qu'un changement est valide si aucun test ou verification n'a ete effectue.
