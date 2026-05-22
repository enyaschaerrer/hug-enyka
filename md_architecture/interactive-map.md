# Carte monde interactive

Ce document definit le scope de la future fonctionnalite de carte monde interactive.
Il sert de reference avant implementation pour garder un composant limite, maintenable et coherent avec l'architecture actuelle.

## Prerequis avant implementation

Avant de coder cette fonctionnalite, lire les documents Markdown existants du projet, notamment :

```text
README.md
md_files/index.md
md_files/guidelines/versions.md
md_files/guidelines/architecture.md
md_files/guidelines/frontend.md
md_files/guidelines/daisyui.md
md_files/guidelines/coding-rules.md
md_architecture/auth-admin-cobranding.md
md_architecture/fonctionnalites.md
```

L'implementation doit respecter l'infrastructure actuelle :

- Laravel 13 ;
- Vue 3 ;
- Vite ;
- Tailwind CSS 4 ;
- DaisyUI 5 ;
- structure existante `resources/js/components`, `resources/js/data`, `resources/js/types` ;
- pages publiques montees via l'application Vue existante.

Pour cette premiere version, il ne faut rien ajouter en base de donnees :

- pas de migration ;
- pas de model Laravel ;
- pas de seeder ;
- pas de controller specifique ;
- pas de route Laravel dediee.

La fonctionnalite est un prototype front-end base sur des donnees JSON locales.

## Objectif

La fonctionnalite doit permettre a un utilisateur de selectionner un pays visite afin de voir si ce pays peut imposer une attente avant un don du sang.

La carte doit prendre la forme d'une heatmap mondiale :

- tous les pays du monde doivent etre presents ;
- l'Antarctique doit etre exclu ;
- les pays sont colores selon un niveau de contrainte ;
- plus le pays est blanc, plus il est considere comme safe ;
- plus le pays est rouge sature, plus la contrainte est forte.

Les donnees seront d'abord des donnees de test dans un fichier JSON local.
Elles ne doivent pas etre considerees comme des recommandations medicales officielles.

## Placement temporaire

Pour la phase de test, la carte doit etre ajoutee sur la page d'accueil, sous les prototypes existants :

```text
1. Prototype Tinder
2. Prototype SMS
3. Carte monde interactive
```

La carte n'est pas encore liee au Tinder ni au SMS.

Le seul fichier existant a modifier pour l'affichage initial doit etre :

```text
resources/js/pages/public/HomePage.vue
```

Les composants Tinder et SMS existants ne doivent pas etre modifies pour cette integration.

## Architecture attendue

Le composant principal doit suivre l'organisation deja utilisee dans le projet :

```text
resources/js/components/interactive-map/InteractiveWorldMap.vue
```

Les donnees front statiques doivent rester centralisees dans :

```text
resources/js/data/
```

Nom recommande pour les donnees pays :

```text
resources/js/data/country-donation-rules.json
```

Si des types TypeScript deviennent utiles, les placer dans :

```text
resources/js/types/
```

Exemple :

```text
resources/js/types/interactive-map.ts
```

## Section et layout

La carte doit etre affichee dans une section separee :

```text
width: 100vw
height: 100svh
```

Le comportement doit etre le meme sur desktop, tablette et mobile :

- section pleine largeur ;
- section pleine hauteur ;
- fond blanc neutre ;
- recherche en haut ;
- carte visible et lisible ;
- petite legende flottante en bas a droite ;
- pas de page marketing ;
- pas de titres ou textes decoratifs ajoutes hors scope.

## Source geographique

La carte ne doit pas etre dessinee a la main.

Interdit :

```text
SVG monde bricole manuellement
carte approximative
paths inventes ou simplistes
```

La carte doit utiliser une source fiable et maintenable, par exemple :

- Natural Earth ;
- world-atlas ;
- GeoJSON ou TopoJSON monde maintenu.

L'implementation peut utiliser une librairie raisonnable si elle facilite un rendu SVG propre.

Recommendation technique :

```text
d3-geo + GeoJSON/TopoJSON local ou source maintenue
```

Autres librairies possibles seulement si elles restent legeres et adaptees a une heatmap de pays.
Il ne faut pas introduire une solution lourde de type carte a tuiles si elle n'est pas necessaire.

## Correspondance carte / JSON

Point critique : la carte et le JSON doivent couvrir le meme ensemble de pays.

Regles :

- tous les pays presents sur la carte doivent avoir une entree JSON ;
- tous les pays presents dans le JSON doivent exister sur la carte ;
- l'Antarctique doit etre retire des deux cotes ;
- les cas ambigus doivent suivre la source geographique choisie ;
- ne pas ajouter ou retirer des pays manuellement sans justification.

Si la source geographique contient des territoires particuliers, le JSON doit suivre cette source.
Le but est d'eviter les trous de donnees et les mappings fragiles.

## Donnees JSON

Le JSON doit etre une liste d'objets, pas un objet indexe par code.
C'est plus pratique pour :

- l'autocomplete ;
- le tri ;
- la verification d'exhaustivite ;
- la recherche sur plusieurs champs.

Structure recommandee :

```json
[
    {
        "name": "Suisse",
        "iso2": "CH",
        "iso3": "CHE",
        "aliases": ["Switzerland", "Confederation suisse"],
        "riskScore": 0,
        "waitTime": null,
        "description": null
    },
    {
        "name": "Bresil",
        "iso2": "BR",
        "iso3": "BRA",
        "aliases": ["Brazil"],
        "riskScore": 85,
        "waitTime": "1 mois",
        "description": "Attente conseillee apres certaines zones a risque."
    }
]
```

Champs obligatoires :

```text
name
iso2
iso3
aliases
riskScore
waitTime
description
```

Regles de contenu :

- `name` doit etre en francais ;
- `iso2` sert notamment aux drapeaux ;
- `iso3` sert souvent au matching avec les donnees geographiques ;
- `aliases` sert a la recherche ;
- `riskScore` va de 0 a 100 ;
- `waitTime` vaut `null` si aucun delai ;
- `description` vaut `null` si aucun delai.

Les donnees peuvent etre approximativement realistes pour le prototype, mais elles restent des donnees de test non medicales.
La validation finale des delais devra etre faite plus tard avec une source officielle CTS/HUG ou par l'equipe projet.

## Heatmap

La heatmap doit utiliser une echelle monochrome rouge coherente avec le prototype SMS.

Principe :

```text
riskScore 0   -> blanc, bord noir, aucune contrainte
riskScore 100 -> rouge tres sature, contrainte forte
```

Les valeurs intermediaires doivent interpoler entre blanc et rouge.

La carte doit rester lisible :

- pays safe blancs avec bordure noire ou foncee ;
- pays contraignants en rouges progressifs ;
- hover/clic clairement perceptible ;
- pays selectionne visuellement identifiable.

## Interaction carte

L'interaction principale est le clic/tap.

Comportement attendu :

1. L'utilisateur clique ou tape sur un pays.
2. Le pays devient selectionne.
3. Une tooltip DaisyUI s'affiche autour du pays selectionne.
4. La tooltip reste visible tant qu'un autre pays n'est pas selectionne.
5. La tooltip ne disparait pas automatiquement.

Le hover peut etre utilise en desktop comme aide visuelle, mais il ne doit pas etre le seul moyen d'obtenir l'information.
Le mobile doit fonctionner au tap.

Pas de zoom requis.
Pas de recentrage automatique requis.

## Tooltip

La tooltip doit utiliser DaisyUI autant que possible.

Contenu attendu :

- drapeau du pays ;
- nom du pays ;
- statut safe ou temps d'attente ;
- description courte uniquement s'il y a un temps d'attente.

Exemples :

```text
Suisse
Safe
```

```text
Bresil
Attendre 1 mois
Attente conseillee apres certaines zones a risque.
```

La tooltip peut avoir un element visuel lie au niveau de risque :

- bordure gauche ;
- bordure superieure ;
- pastille ;
- accent dans la couleur heatmap du pays.

La tooltip doit rester sobre.
Ne pas ajouter de titre global, card marketing, texte explicatif long ou decoration hors scope.

## Recherche

La recherche doit etre placee en haut de la section.
Elle doit utiliser des composants/classes DaisyUI autant que possible.

Comportement :

- recherche active a partir de 1 caractere ;
- maximum 5 suggestions visibles ;
- menu deroulant sous le champ ;
- clic sur une suggestion selectionne le pays ;
- la selection via recherche affiche la meme tooltip que le clic sur la carte ;
- si aucun resultat, afficher un etat `0 resultat`.

La recherche doit matcher au minimum :

- `name` ;
- `iso2` ;
- `iso3` ;
- `aliases`.

Exemples :

```text
recherche "ch" -> propose Suisse grace a iso2 CH
recherche "uae" -> propose Emirats arabes unis grace a aliases
```

La recherche doit etre case-insensitive.
Si possible, ignorer les accents.

## Suggestions de recherche

Chaque ligne de suggestion doit afficher :

- nom du pays a gauche ;
- drapeau du pays a droite.

Les drapeaux ne doivent pas etre geres manuellement avec des fichiers locaux.

Solution recommandee :

```text
flag-icons
```

ou une librairie equivalente de drapeaux SVG professionnelle et maintenue.

Les emojis drapeaux ne sont pas souhaites.

## Legende heatmap

Ajouter une petite legende flottante en bas a droite de la section.

Elle doit rester discrete.

Contenu recommande :

```text
0 contrainte  [degrade blanc -> rouge]  contrainte forte
```

La legende ne doit pas prendre trop de place et ne doit pas gener l'usage mobile.

## Contraintes de design

La carte doit etre fonctionnelle et sobre.

Contraintes :

- fond blanc neutre ;
- pas de hero ;
- pas de section marketing ;
- pas de texte decoratif ;
- pas de titres inutiles ;
- pas d'illustrations supplementaires ;
- pas de modification des composants Tinder/SMS ;
- pas d'initiative design hors scope.

L'objectif est de tester la fonctionnalite, pas de refaire la page d'accueil.

## Dependances possibles

Dependances envisageables :

```text
d3-geo
topojson-client
flag-icons
```

Ces dependances ne sont pas obligatoires si une solution plus simple et propre est trouvee.
Elles sont autorisees si elles evitent une implementation fragile.

Toute dependance ajoutee doit avoir une utilite claire :

- rendre une vraie carte monde ;
- transformer GeoJSON/TopoJSON en SVG ;
- afficher des drapeaux SVG propres.

## Non-objectifs pour la premiere version

Ne pas implementer pour la premiere version :

- zoom carte ;
- pan carte ;
- tuiles cartographiques ;
- Mapbox ou service externe lourd ;
- geolocalisation ;
- selection multi-pays ;
- calcul de delai global ;
- lien avec le chat SMS ;
- lien avec le Tinder ;
- sauvegarde en base ;
- donnees medicales finales ;
- edition admin.

## Verification attendue

Avant de considerer la fonctionnalite terminee :

- la carte s'affiche sur desktop ;
- la carte s'affiche sur mobile ;
- l'Antarctique n'est pas visible ;
- les pays sont colories selon `riskScore` ;
- le clic sur un pays affiche la tooltip ;
- la recherche propose jusqu'a 5 resultats ;
- la recherche par nom fonctionne ;
- la recherche par ISO ou alias fonctionne ;
- le cas `0 resultat` fonctionne ;
- les drapeaux s'affichent dans la recherche ;
- les drapeaux s'affichent dans la tooltip ;
- la legende heatmap est visible mais discrete ;
- `npm run type-check` passe ;
- `npm run build` passe.
