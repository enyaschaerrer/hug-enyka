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

## Correction de baseline Cooper Hewitt

Cooper Hewitt rend visuellement les textes un peu trop hauts dans certains composants, surtout dans les boutons, badges, bulles et inputs. Le probleme vient des metriques verticales de la police : le texte parait centre mathematiquement, mais pas centre optiquement.

Pour eviter des corrections au cas par cas du type `translate-y-[2px]`, le projet utilise des classes de baseline reutilisables.

### Texte inline

Pour les textes courts dans des boutons, badges, labels, nav items ou compteurs :

```css
.cooper-baseline {
    display: inline-block;
    transform: translateY(0.12em);
}
```

`0.12em` s'adapte automatiquement a la taille du texte :

- petit texte : correction legere ;
- texte standard : correction proche de 2px ;
- gros titre ou tampon : correction plus forte.

Exemple :

```html
<span class="cooper-baseline">Admin</span>
```

### Texte bloc

Pour un paragraphe, un titre ou une ligne de texte qui doit garder son comportement de bloc :

```css
.cooper-text-baseline {
    transform: translateY(0.12em);
}
```

Exemple :

```html
<h2 class="cooper-text-baseline text-2xl font-bold">Titre</h2>
```

### Inputs

Un `input` ne permet pas de wrapper son texte dans un `span`. Pour ce cas, on corrige la baseline avec un padding vertical asymetrique, sans changer la hauteur totale du champ :

```css
.cooper-input-baseline {
    line-height: 1;
    padding-top: calc(0.625rem + 0.16em);
    padding-bottom: calc(0.625rem - 0.16em);
}
```

Exemple :

```html
<input class="cooper-input-baseline text-sm font-medium" />
```

Regle pratique :

- utiliser `cooper-baseline` pour les petits textes inline ;
- utiliser `cooper-text-baseline` pour les titres, paragraphes ou textes de bulle ;
- utiliser `cooper-input-baseline` pour les champs de formulaire ;
- eviter les corrections Tailwind ponctuelles du type `translate-y-[2px]`, sauf test temporaire.

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
