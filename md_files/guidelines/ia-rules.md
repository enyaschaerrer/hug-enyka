# Regles generales pour toute IA

Ce fichier est prioritaire pour toute IA qui modifie ce projet.

Les consignes du professeur dans `md_files/guidelines/consigne.md` sont obligatoires. Elles passent avant les preferences de style, les habitudes de l'IA ou les exemples generiques trouves ailleurs.

## Objectif principal

Le projet doit rester fonctionnel de bout en bout, lisible, structure, securise et coherent avec la stack imposee :

- Laravel pour le back-end ;
- Vue.js pour les parties reactives front-end ;
- MySQL pour la base de donnees ;
- Git pour la gestion du code source ;
- Infomaniak pour l'hebergement et le deploiement ;
- DaisyUI comme framework UI du projet.

## Avant de coder

Toute IA doit d'abord lire les fichiers utiles au changement demande.

Fichiers de reference importants :

- `md_files/guidelines/consigne.md`
- `md_files/guidelines/versions.md`
- `md_files/guidelines/daisyui.md`
- `composer.json`
- `package.json`
- `vite.config.js`
- `routes/web.php`
- les fichiers deja existants dans `app/`, `resources/`, `database/` et `tests/`

Une IA ne doit pas inventer une structure, une commande ou une convention sans verifier le projet.

## Regles strictes

- respecter les versions imposees dans `md_files/guidelines/versions.md` ;
- respecter les composants et conventions DaisyUI de `md_files/guidelines/daisyui.md` ;
- produire du code lisible, structure et modulaire ;
- eviter les generations IA non maitrisees ;
- eviter le code redondant ou les gros fichiers fourre-tout ;
- garder une architecture coherente ;
- respecter les principes de securite Web de base ;
- fournir des messages d'erreur propres ;
- ne pas ajouter de dependance sans justification claire ;
- ne pas modifier des fichiers sans rapport avec la demande ;
- ne pas supprimer du code existant sans raison explicite ;
- ne pas commiter de donnees sensibles.

## Verification apres modification

Apres un changement, une IA doit verifier au minimum :

- que le projet compile ou que le fichier modifie est syntaxiquement coherent ;
- que les routes, vues, composants et services appeles existent ;
- que les donnees utilisateur sont validees ;
- que les erreurs sont gerees proprement ;
- que le changement respecte les consignes du professeur.

Si une verification ne peut pas etre executee, l'IA doit le signaler clairement.
