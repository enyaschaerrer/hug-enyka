# Frontend

Le front-end doit utiliser Vue.js pour les parties reactives et DaisyUI/Tailwind CSS pour l'interface. Les vues doivent etre structurees avec une approche par composants, comme demande dans `md_files/guidelines/consigne.md`.

## Stack

- Vue `^3.5.34`
- Vite `^8.0.0`
- Tailwind CSS `^4.0.0`
- DaisyUI `^5.5.20`

## Organisation

- `resources/js/App.vue` contient le composant racine.
- `resources/js/pages/` contient les pages Vue.
- `resources/js/services/` contient les services front-end, par exemple les appels API.
- `resources/css/app.css` contient le CSS global.

## Composants Vue

- Un composant doit avoir une responsabilite claire.
- Extraire un composant quand un bloc devient gros, reutilisable ou difficile a lire.
- Utiliser Vue pour gerer l'etat, les evenements et les donnees.
- Ne pas manipuler directement le DOM si Vue peut le faire.
- Garder les noms de composants explicites.

## UI avec DaisyUI

- Utiliser les composants DaisyUI selon `md_files/guidelines/daisyui.md`.
- Ne pas inventer de classes DaisyUI.
- Ne pas ajouter une autre bibliotheque UI sans demande explicite.
- Utiliser Tailwind CSS pour la mise en page, le responsive et les ajustements.
- Tester le rendu desktop et mobile.

## Formulaires et erreurs

- Afficher des messages d'erreur propres.
- Garder les formulaires accessibles et lisibles.
- Relier les erreurs front-end aux erreurs de validation Laravel quand c'est applicable.
- Ne pas masquer une erreur technique avec un message trompeur.

## Responsive

- Les pages doivent rester utilisables sur mobile et desktop.
- Les tableaux, formulaires et cartes doivent rester lisibles.
- Eviter les textes qui debordent ou les boutons trop petits.

## IA

Une IA doit respecter Vue 3, DaisyUI v5 et Tailwind CSS v4. Elle ne doit pas proposer du code Vue 2, une ancienne configuration Tailwind ou une autre bibliotheque UI par habitude.
