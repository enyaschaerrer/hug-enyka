# Regles de code

Le code doit etre lisible, structure et maitrise. Le professeur demande explicitement d'eviter les generations IA non maitrisees.

## Lisibilite

- Utiliser des noms clairs pour les variables, fonctions, classes et composants.
- Garder les fonctions courtes quand c'est possible.
- Eviter les blocs de code trop longs et difficiles a relire.
- Ajouter un commentaire seulement quand il explique une intention non evidente.
- Ne pas laisser de code mort, de `console.log` inutile ou de debug temporaire.

## Structure

- Une classe, un controller, un composant ou un service doit avoir une responsabilite claire.
- Ne pas dupliquer la meme logique dans plusieurs fichiers.
- Extraire une fonction, un composant ou un service seulement si cela rend le code plus clair.
- Respecter les dossiers existants du projet.

## Laravel

- Utiliser les conventions Laravel du projet.
- Valider les entrees utilisateur avant de les utiliser.
- Utiliser Eloquent proprement au lieu de SQL brut, sauf besoin justifie.
- Retourner des erreurs propres et comprehensibles.
- Ne pas exposer d'information sensible dans les messages d'erreur.

## Vue

- Utiliser Vue 3.
- Garder les composants lisibles.
- Eviter les composants qui font trop de choses.
- Ne pas manipuler directement le DOM si Vue peut gerer l'etat.
- Garder les donnees, evenements et props explicites.

## Dependances

- Ne pas ajouter de dependance sans besoin reel.
- Verifier la compatibilite avec les versions du projet.
- Preferer les outils deja installes : Laravel, Vue, Vite, Tailwind CSS, DaisyUI.

## Attitude attendue d'une IA

Une IA doit proposer du code simple, verifiable et adapte au projet. Elle ne doit pas produire un gros bloc de code sans rapport direct avec la demande.
