# Design System co-brandé — ENYKA / HUG
---

## Typographie

Police principale : **Cooper Hewitt**

| Rôle | Police | Graisse | Taille |
|------|--------|---------|--------|
| Display | Cooper Hewitt | Bold | 38px |
| Heading t1 | Cooper Hewitt | Semi-bold | 25px |
| Heading t2 | Cooper Hewitt | Semi-bold | 21px |
| Heading t3 | Cooper Hewitt | Medium | 18px |
| Body | Cooper Hewitt | Book | 16px |
| Caption | Cooper Hewitt | Book | 13px |

---

## Iconographie

Police d'icônes utilisée : **Google Material Symbols Outlined** (variable font)

### Setup
Fichier : `resources/fonts/material-symbols/material-symbols-outlined.woff2`
Déclarée dans `app.css` via `@font-face`, exposée par la classe utilitaire `.material-symbols-outlined`.

### Utilisation
```html
<span class="material-symbols-outlined">favorite</span>
<span class="material-symbols-outlined">account_circle</span>
<span class="material-symbols-outlined">filter_list</span>
```
Le mot à l'intérieur du `<span>` est le **ligature name** de l'icône. Catalogue complet : https://fonts.google.com/icons

### Personnalisation
La police est une variable font, donc tu peux ajuster :

| Axe | Valeurs | Effet |
|-----|---------|-------|
| `FILL` | 0 → 1 | Outlined → rempli |
| `wght` | 100 → 700 | Épaisseur du trait |
| `GRAD` | -50 → 200 | Accentuation/contraste |
| `opsz` | 20 → 48 | Taille optique (rendu adapté à la taille) |

Via inline style :
```html
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">favorite</span>
```

### Taille et couleur
Style inline ou Tailwind :
```html
<!-- Taille spécifique -->
<span class="material-symbols-outlined" style="font-size: 32px;">person</span>

<!-- Couleur (hérite de currentColor) -->
<span class="material-symbols-outlined text-fuzzywuzzybrown-700">favorite</span>
```

### Note prod
Le fichier `.woff2` complet fait ~3.7 MB (tous les icônes). En production, on pourra le subset avec `fontTools` pour ne garder que les glyphes utilisés (généralement <100 KB final).

---


## Couleurs principales HUG
Martinique 500 - `#757ABC` 
Martinique 700 - `#5A579E` 
Martinique 950 - `#2F2D46` 
Fuzzy Wuzzy Brown 600 - `#D1444A` 
Fuzzy Wuzzy Brown 400 - `#EC8380` 


## Couleurs principales site
CatskillWhite 100 - `#ebf3f3`
CatskillWhite 300 - `#abcecb`
CatskillWhite 700 - `#3c6866`
CatskillWhite 900 - `#304a49`
MonteCarlo 800 - `#23494c`
PictonBlue 950 - `#0b3146`
Razzmatazz 50 - `#fff0f3`
Razzmatazz 100 - `#ffe1e8`
Razzmatazz 800 - `#ae034c`
Razzmatazz 950 - `#539924`

---

## Couleurs — Swatches principaux HUG

| Nom | Hex |
|-----|-----|
| VistaBlue (HUG) | `#9fd0ab` |
| PictonBlue (HUG) | `#3abff0` |
| MonteCarlo (HUG) | `#70c4c3` |
| CatskillWhite (HUG) | `#f1f7f7` |
| Chicago (HUG) | `#575656` |
| Razzmatazz (HUG) | `#e5005a` |

---

## Palettes des alertes

### Red
| Nuance | Hex |
|--------|-----|
| 50 | `#fef2f2` |
| 100 | `#ffe2e2` |
| 200 | `#ffc9c9` |
| 300 | `#ffa2a2` |
| 400 | `#ff6467` |
| 500 | `#fb2c36` |
| 600 | `#e7000b` |
| 700 | `#c10007` |
| 800 | `#9f0712` |
| 900 | `#82181a` |
| 950 | `#460809` |

### Yellow
| Nuance | Hex |
|--------|-----|
| 50 | `#fefce8` |
| 100 | `#fef9c2` |
| 200 | `#fff085` |
| 300 | `#ffdf20` |
| 400 | `#f5c100` |
| 500 | `#f0b100` |
| 600 | `#d08700` |
| 700 | `#a65f00` |
| 800 | `#894b00` |
| 900 | `#733e0a` |
| 950 | `#432004` |

### Green
| Nuance | Hex |
|--------|-----|
| 50 | `#f0fdf4` |
| 100 | `#dbfce7` |
| 200 | `#b9f8cf` |
| 300 | `#7bf1a8` |
| 400 | `#05df72` |
| 500 | `#00c950` |
| 600 | `#00a63e` |
| 700 | `#008236` |
| 800 | `#016630` |
| 900 | `#0d542b` |
| 950 | `#032e15` |

## Palettes des contrastes

### VistaBlue
| Nuance | Hex |
|--------|-----|
| 50 | `#f0f9f2` |
| 100 | `#dcefde` |
| 200 | `#bbdfc2` |
| 300 | `#9fd0ab` |
| 400 | `#5daa72` |
| 500 | `#3c8d55` |
| 600 | `#2b7042` |
| 700 | `#225a36` |
| 800 | `#1d482d` |
| 900 | `#193b26` |
| 950 | `#0d2115` |

### PictonBlue
| Nuance | Hex |
|--------|-----|
| 50 | `#f1f9fe` |
| 100 | `#e1f3fd` |
| 200 | `#bde7fa` |
| 300 | `#82d6f7` |
| 400 | `#3abff0` |
| 500 | `#17a9e0` |
| 600 | `#0a88bf` |
| 700 | `#096d9b` |
| 800 | `#0c5c80` |
| 900 | `#104c6a` |
| 950 | `#0b3146` |

### MonteCarlo
| Nuance | Hex |
|--------|-----|
| 50 | `#f3faf9` |
| 100 | `#d7f0ee` |
| 200 | `#aee1df` |
| 300 | `#70c4c3` |
| 400 | `#53acae` |
| 500 | `#3a8f92` |
| 600 | `#2c7175` |
| 700 | `#275a5e` |
| 800 | `#23494c` |
| 900 | `#213e40` |
| 950 | `#0e2225` |

### CatskillWhite
| Nuance | Hex |
|--------|-----|
| 50 | `#f1f7f7` |
| 100 | `#ebf3f3` |
| 200 | `#d2e5e4` |
| 300 | `#abcecb` |
| 400 | `#7eb2ad` |
| 500 | `#5e9994` |
| 600 | `#4a7f7c` |
| 700 | `#3c6866` |
| 800 | `#355755` |
| 900 | `#304a49` |
| 950 | `#203131` |

### Chicago
| Nuance | Hex |
|--------|-----|
| 50 | `#faf9f9` |
| 100 | `#f5f4f4` |
| 200 | `#e6e5e5` |
| 300 | `#d4d3d3` |
| 400 | `#a4a2a2` |
| 500 | `#737171` |
| 600 | `#575656` |
| 700 | `#404040` |
| 800 | `#272626` |
| 900 | `#1a1919` |
| 950 | `#0b0a0a` |

### Razzmatazz
| Nuance | Hex |
|--------|-----|
| 50 | `#fff0f3` |
| 100 | `#ffe1e8` |
| 200 | `#ffc8d7` |
| 300 | `#ff9bb6` |
| 400 | `#ff6391` |
| 500 | `#ff2c70` |
| 600 | `#f60860` |
| 700 | `#e5005a` |
| 800 | `#ae034c` |
| 900 | `#940747` |
| 950 | `#530024` |