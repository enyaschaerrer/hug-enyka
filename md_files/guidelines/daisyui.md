# Utilisation des composants DaisyUI

Ce projet utilise DaisyUI `^5.5.20` avec Tailwind CSS `^4.0.0`.

DaisyUI doit etre utilise comme une bibliotheque de classes CSS, pas comme une bibliotheque de composants JavaScript a importer.

## Documentation officielle

La documentation a utiliser en priorite est la documentation officielle :

- composants : `https://daisyui.com/components/`
- documentation DaisyUI v5 : `https://daisyui.com/docs/v5/`
- themes : `https://daisyui.com/docs/themes/`

Il faut eviter les exemples trouves sur des blogs, anciens tutoriels, videos ou forums si leur version DaisyUI n'est pas clairement compatible avec DaisyUI v5.

## Comment chercher un composant

Pour trouver un composant, commencer par la page officielle des composants DaisyUI.

Exemples de recherches utiles :

- `daisyUI button`
- `daisyUI modal`
- `daisyUI navbar`
- `daisyUI card`
- `daisyUI table`
- `daisyUI form`
- `daisyUI drawer`
- `daisyUI dropdown`
- `daisyUI alert`

Verifier ensuite que l'exemple vient bien du site `daisyui.com`.

## Comment utiliser un composant

Un composant DaisyUI s'utilise avec des classes HTML.

Exemple :

```html
<button class="btn btn-primary">
    Enregistrer
</button>
```

DaisyUI fournit les classes de base comme `btn`, `card`, `modal`, `alert`, `navbar`, `input`, `select`, `table`, etc.

Tailwind CSS peut ensuite etre ajoute pour adapter l'espacement, la grille, la taille ou le responsive.

Exemple :

```html
<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h2 class="card-title">Titre</h2>
        <p>Contenu de la carte.</p>
        <div class="card-actions justify-end">
            <button class="btn btn-primary">Action</button>
        </div>
    </div>
</div>
```

## Regles pour l'utilisateur

- Chercher d'abord dans la documentation officielle DaisyUI.
- Copier seulement le HTML utile, puis l'adapter au projet.
- Garder les classes DaisyUI principales du composant.
- Ajouter des classes Tailwind seulement pour la mise en page ou les ajustements visuels.
- Tester le rendu dans l'application apres integration.
- Ne pas installer une autre bibliotheque UI si DaisyUI suffit.

## Regles strictes pour l'IA

Toute IA qui modifie ce projet doit respecter ces regles :

- utiliser DaisyUI v5, pas DaisyUI v3 ou v4 ;
- utiliser Tailwind CSS v4, pas une configuration Tailwind v3 ;
- ne pas inventer de composant DaisyUI qui n'existe pas ;
- ne pas utiliser de syntaxe ou de classes obsoletes sans verification ;
- ne pas ajouter d'import JavaScript pour un composant DaisyUI standard ;
- ne pas remplacer DaisyUI par Bootstrap, Flowbite, shadcn/ui, Headless UI ou une autre bibliotheque sans demande explicite ;
- ne pas ajouter de dependance pour un composant qui existe deja dans DaisyUI ;
- verifier la documentation officielle si le composant, la classe ou le comportement n'est pas certain.

Si une IA propose un composant, elle doit pouvoir expliquer de quel composant DaisyUI officiel il vient.

## Adaptation dans Vue

Dans les fichiers Vue, DaisyUI s'utilise directement dans les templates.

Exemple :

```vue
<template>
    <button class="btn btn-primary" @click="save">
        Enregistrer
    </button>
</template>
```

DaisyUI ne remplace pas la logique Vue. Vue gere les etats, les evenements et les donnees. DaisyUI gere principalement l'apparence.

## Points d'attention

Certains composants DaisyUI sont purement CSS et HTML. D'autres peuvent demander un peu de logique Vue pour gerer leur etat.

Exemples :

- un bouton simple ne demande pas de logique speciale ;
- un modal peut demander une variable Vue pour ouvrir ou fermer la fenetre ;
- un dropdown peut fonctionner avec les classes DaisyUI, mais son comportement doit etre teste ;
- un tableau doit rester lisible sur mobile ;
- un formulaire doit rester relie a la validation Laravel ou Vue existante.

## Avant d'ajouter un composant

Verifier :

- le composant existe dans DaisyUI v5 ;
- le composant correspond au besoin reel ;
- le HTML reste lisible ;
- les classes ne contredisent pas le design existant ;
- le rendu fonctionne sur desktop et mobile ;
- aucune nouvelle dependance inutile n'est ajoutee.
