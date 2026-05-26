# Typographies HUG

Ce document reference les polices HUG importees dans le projet.
Les fichiers sont disponibles mais ne sont pas encore actives dans l'application.

## Emplacement des fichiers

Les polices sont stockees comme assets statiques dans :

```text
public/fonts/hug/
```

Structure :

```text
public/fonts/hug/cooper-hewitt/
public/fonts/hug/univers/
```

Ces fichiers peuvent ensuite etre appeles depuis le CSS avec des URLs publiques du type :

```css
url('/fonts/hug/cooper-hewitt/CooperHewitt-Book.otf')
```

## Police principale du site

La police principale a utiliser pour ce site est :

```text
Cooper Hewitt
```

Elle doit etre privilegiee pour :

- les titres ;
- les paragraphes ;
- les boutons ;
- les formulaires ;
- les interfaces publiques ;
- les interfaces admin, sauf decision UI contraire.

Univers est conservee comme police HUG secondaire. Elle peut etre utile pour certains elements de marque, des compositions proches de la charte HUG ou des cas precis valides par l'UI.

## Variantes Cooper Hewitt

Famille CSS recommandee :

```css
font-family: 'Cooper Hewitt', sans-serif;
```

Variantes disponibles :

| Fichier | Graisse CSS | Style CSS | Usage recommande |
| --- | ---: | --- | --- |
| `CooperHewitt-Thin.otf` | `100` | `normal` | Tres grands titres ou effets typographiques ponctuels. |
| `CooperHewitt-ThinItalic.otf` | `100` | `italic` | Variante italique tres fine, usage rare. |
| `CooperHewitt-Light.otf` | `300` | `normal` | Titres legers, textes secondaires a fort contraste. |
| `CooperHewitt-LightItalic.otf` | `300` | `italic` | Mise en emphase legere. |
| `CooperHewitt-Book.otf` | `400` | `normal` | Texte courant principal. |
| `CooperHewitt-BookItalic.otf` | `400` | `italic` | Italique standard du texte courant. |
| `CooperHewitt-Medium.otf` | `500` | `normal` | Labels, navigation, boutons secondaires. |
| `CooperHewitt-MediumItalic.otf` | `500` | `italic` | Emphase moyenne. |
| `CooperHewitt-Semibold.otf` | `600` | `normal` | Titres de sections, boutons importants. |
| `CooperHewitt-SemiboldItalic.otf` | `600` | `italic` | Emphase forte en semi-bold. |
| `CooperHewitt-Bold.otf` | `700` | `normal` | Titres, accents forts, CTA. |
| `CooperHewitt-BoldItalic.otf` | `700` | `italic` | Emphase forte italique. |
| `CooperHewitt-Heavy.otf` | `900` | `normal` | Titres tres impactants, a utiliser avec retenue. |
| `CooperHewitt-HeavyItalic.otf` | `900` | `italic` | Variante heavy italique, usage exceptionnel. |

### Declaration CSS Cooper Hewitt

Quand l'equipe decide d'activer la police, ajouter les declarations dans le CSS global du projet, par exemple dans :

```text
resources/css/app.css
```

Exemple complet :

```css
@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-Thin.otf') format('opentype');
    font-weight: 100;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-ThinItalic.otf') format('opentype');
    font-weight: 100;
    font-style: italic;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-Light.otf') format('opentype');
    font-weight: 300;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-LightItalic.otf') format('opentype');
    font-weight: 300;
    font-style: italic;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-Book.otf') format('opentype');
    font-weight: 400;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-BookItalic.otf') format('opentype');
    font-weight: 400;
    font-style: italic;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-Medium.otf') format('opentype');
    font-weight: 500;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-MediumItalic.otf') format('opentype');
    font-weight: 500;
    font-style: italic;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-Semibold.otf') format('opentype');
    font-weight: 600;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-SemiboldItalic.otf') format('opentype');
    font-weight: 600;
    font-style: italic;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-Bold.otf') format('opentype');
    font-weight: 700;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-BoldItalic.otf') format('opentype');
    font-weight: 700;
    font-style: italic;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-Heavy.otf') format('opentype');
    font-weight: 900;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Cooper Hewitt';
    src: url('/fonts/hug/cooper-hewitt/CooperHewitt-HeavyItalic.otf') format('opentype');
    font-weight: 900;
    font-style: italic;
    font-display: swap;
}
```

Exemples d'utilisation apres activation :

```css
body {
    font-family: 'Cooper Hewitt', sans-serif;
}

.title {
    font-family: 'Cooper Hewitt', sans-serif;
    font-weight: 700;
}

.small-label {
    font-family: 'Cooper Hewitt', sans-serif;
    font-weight: 500;
}
```

## Baseline Cooper Hewitt

Cooper Hewitt peut avoir une baseline visuellement differente selon le composant, mais le rendu actuel du projet ne justifie pas une correction globale par defaut.

La correction precedente avec `transform: translateY(0.12em)` s'est revelee trop agressive : elle descendait trop les textes dans plusieurs composants. Elle ne doit donc pas etre appliquee globalement a tous les textes.

Le principe actuel est :

- garder les textes Cooper sans decalage par defaut ;
- verifier visuellement les composants un par un ;
- appliquer une correction locale seulement si un bouton, badge, label, bulle ou input est clairement mal centre ;
- eviter de modifier `line-height` globalement, car cela peut casser la hauteur des paragraphes, boutons, bulles, inputs et titres.

### Texte inline neutre

Classe neutre utilisable pour wrapper un texte court sans changer sa baseline :

```css
.cooper-baseline {
    display: inline-block;
}
```

Exemple :

```html
<span class="cooper-baseline">Admin</span>
```

### Correction locale si necessaire

Si un composant precis a besoin d'un ajustement apres verification visuelle, creer une classe locale au composant ou une classe dediee au type de composant, avec une valeur faible et testee.

Exemple possible pour un cas ponctuel :

```css
.cooper-button-baseline-adjust {
    display: inline-block;
    transform: translateY(0.04em);
}
```

Cette correction ne doit pas remplacer `cooper-baseline` globalement.

### Texte bloc neutre

Pour un paragraphe, un titre ou une ligne de texte qui doit garder son comportement de bloc sans decalage :

```css
.cooper-text-baseline {
}
```

Exemple :

```html
<h2 class="cooper-text-baseline text-2xl font-bold">Titre</h2>
```

### Inputs

Ne pas appliquer de padding vertical asymetrique global sur les inputs Cooper. Cela peut casser l'alignement avec les hauteurs DaisyUI/Tailwind.

La classe peut rester neutre si elle est deja utilisee dans le code :

```css
.cooper-input-baseline {}
```

Exemple :

```html
<input class="cooper-input-baseline text-sm font-medium" />
```

Regle pratique :

- utiliser `cooper-baseline`, `cooper-text-baseline` et `cooper-input-baseline` comme classes neutres par defaut ;
- ne pas mettre de `translateY` ou de `line-height` global dans ces classes ;
- appliquer une correction locale seulement sur un composant identifie comme mal aligne ;
- documenter la raison si une correction locale est ajoutee.

## Variantes Univers

Famille CSS recommandee :

```css
font-family: 'Univers', sans-serif;
```

Variantes disponibles :

| Fichier | Graisse CSS | Style CSS | Largeur CSS | Usage recommande |
| --- | ---: | --- | --- | --- |
| `UniversLight.ttf` | `300` | `normal` | `normal` | Texte ou titres legers dans des compositions HUG specifiques. |
| `UniversRegular.ttf` | `400` | `normal` | `normal` | Texte secondaire si Univers est requis par la charte. |
| `UniversBold.ttf` | `700` | `normal` | `normal` | Accents forts ou titres courts en Univers. |
| `UniversCnRg.ttf` | `400` | `normal` | `condensed` | Texte condense, labels ou elements de marque. |
| `UniversCnBold.ttf` | `700` | `normal` | `condensed` | Titres courts ou elements de marque condenses. |

### Declaration CSS Univers

Univers ne doit pas remplacer Cooper Hewitt comme police principale du site.
Si une maquette ou la charte demande Univers, ajouter les declarations utiles seulement.

Exemple complet :

```css
@font-face {
    font-family: 'Univers';
    src: url('/fonts/hug/univers/UniversLight.ttf') format('truetype');
    font-weight: 300;
    font-style: normal;
    font-stretch: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Univers';
    src: url('/fonts/hug/univers/UniversRegular.ttf') format('truetype');
    font-weight: 400;
    font-style: normal;
    font-stretch: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Univers';
    src: url('/fonts/hug/univers/UniversBold.ttf') format('truetype');
    font-weight: 700;
    font-style: normal;
    font-stretch: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Univers';
    src: url('/fonts/hug/univers/UniversCnRg.ttf') format('truetype');
    font-weight: 400;
    font-style: normal;
    font-stretch: condensed;
    font-display: swap;
}

@font-face {
    font-family: 'Univers';
    src: url('/fonts/hug/univers/UniversCnBold.ttf') format('truetype');
    font-weight: 700;
    font-style: normal;
    font-stretch: condensed;
    font-display: swap;
}
```

Exemples d'utilisation apres activation :

```css
.hug-mark {
    font-family: 'Univers', sans-serif;
    font-weight: 700;
}

.hug-condensed-label {
    font-family: 'Univers', sans-serif;
    font-weight: 700;
    font-stretch: condensed;
}
```

## Regles d'utilisation

- Ne pas charger toutes les variantes si seules quelques graisses sont utilisees en production.
- Activer d'abord Cooper Hewitt en `400`, `500`, `600` et `700` si l'interface n'a pas besoin de toutes les variantes.
- Garder `font-display: swap` pour eviter un blocage du rendu texte.
- Ne pas utiliser Univers comme police globale sans validation UI.
- Ne pas modifier les fichiers de police sources.
- Ne pas utiliser les polices tant que leur licence et leur usage pour le projet ont ete valides par l'equipe.
