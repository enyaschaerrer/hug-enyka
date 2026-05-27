# Contraste des couleurs co-brandees

Ce document decrit la regle appliquee aux couleurs d'entreprise dans l'interface co-brandee.

## Probleme

La couleur `primaryColor` vient de l'entreprise et peut etre tres claire, par exemple une couleur pastel.

Si cette couleur est utilisee comme fond de bouton avec un texte blanc force, le contraste peut devenir insuffisant et le texte peut etre difficile a lire.

## Decision

Pour le moment, le projet ne bloque pas les couleurs pastel.

La couleur `primaryColor` reste libre, mais les textes places sur un fond `primaryColor` doivent choisir automatiquement la couleur lisible :

- texte blanc si le contraste avec le fond est meilleur ;
- texte fonce si le fond est trop clair.

Cette regle permet de respecter la couleur de marque sans rendre les boutons illisibles.

## Implementation

Le calcul est centralise dans :

```text
resources/js/utils/contrast.ts
```

Fonction principale :

```ts
readableTextColor(backgroundColor)
```

Elle compare le contraste de la couleur de fond avec :

```text
#ffffff
#111827
```

La fonction retourne la couleur qui offre le meilleur contraste.

## Usage actuel

La regle est appliquee au bouton principal de la popup QR co-brandee :

```text
resources/js/components/modals/CoBrandedPhoneModal.vue
```

Le fond utilise `primaryColor`.
Le texte utilise `readableTextColor(primaryColor)`.

## Regle pour les futurs composants

Chaque fois que `primaryColor` est utilisee comme background avec du texte ou une icone importante, ne pas forcer `text-white`.

Utiliser la couleur de texte calculee via `readableTextColor`.

Pour les usages decoratifs de `primaryColor`, comme des bandeaux, pastilles ou traits sans texte, aucun calcul de contraste n'est necessaire.
