# Securite

Les principes de securite Web de base doivent etre respectes. C'est une demande explicite du professeur dans `md_files/guidelines/consigne.md`.

## Donnees utilisateur

- Valider toutes les entrees utilisateur.
- Utiliser des Form Requests Laravel quand la validation devient importante.
- Ne jamais faire confiance aux donnees envoyees par le navigateur.
- Nettoyer et contraindre les donnees avant de les stocker.

## Base de donnees

- Utiliser Eloquent ou le query builder Laravel.
- Eviter les requetes SQL brutes.
- Si une requete brute est necessaire, utiliser des parametres lies.
- Ne pas construire une requete SQL avec une concatenation de donnees utilisateur.

## Authentification et acces

- Proteger les routes qui doivent l'etre avec middleware, policies ou gates.
- Verifier qu'un utilisateur a le droit d'acceder, modifier ou supprimer une ressource.
- Ne pas cacher une action seulement dans l'interface : le back-end doit aussi verifier les droits.

## CSRF et formulaires

- Garder la protection CSRF Laravel pour les formulaires web.
- Ne pas desactiver CSRF sans raison explicite et documentee.
- Gerer proprement les erreurs de validation.

## Secrets et configuration

- Ne jamais commiter `.env`.
- Ne jamais mettre de mot de passe, token, cle API ou acces base de donnees dans le code.
- Utiliser `.env.example` pour documenter les variables necessaires sans valeur sensible.

## Messages d'erreur

- Les messages doivent etre propres et utiles pour l'utilisateur.
- Ne pas afficher de stack trace ou d'information interne en production.
- Ne pas reveler si un email, identifiant ou compte existe, sauf si le contexte le justifie.

## IA

Une IA ne doit jamais proposer de code qui contourne la validation, l'authentification, CSRF ou les protections Laravel pour aller plus vite.
