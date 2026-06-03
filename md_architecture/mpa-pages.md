# Pages publiques MPA et ilots Vue

Ce document decrit l'etat reel des pages publiques rendues par Blade, avec enrichissements Vue ponctuels.

## Scope

Ce document concerne uniquement les pages publiques du site principal.

Il ne concerne pas :

- le back-office `/admin`, qui passe par la SPA Vue servie via `resources/views/app.blade.php` ;
- les pages co-brandees `/collecte/{brand}/{token}`, qui passent aussi par la SPA Vue ;
- les endpoints JSON admin.

## Principe general

Le site public melange :

- des pages Blade purement server-rendered ;
- des pages Blade avec ilots Vue ;
- des pages publiques qui passent par la SPA quand le besoin fonctionnel est plus riche.

Le point d'entree front public reste :

```text
resources/js/app.ts
```

Ce fichier detecte les points de montage existants dans le DOM et monte uniquement les composants necessaires.

## Layout public

Le layout public partage est :

```text
resources/views/layouts/public.blade.php
```

Il inclut notamment :

- les assets Vite ;
- le header public ;
- le footer public ;
- le point de montage `#cookie-consent-root`.

## Pages publiques actuelles

### `/`

Route :

```text
App\Http\Controllers\PublicSiteController@home
```

Vue Blade :

```text
resources/views/public/home.blade.php
```

Cette page est une page Blade enrichie par plusieurs ilots Vue.

Points de montage actuellement utilises via `resources/js/app.ts` :

- `#podium`
- `#companies`
- `#cookie-consent-root`

## `/collecte`

Route :

```text
resources/views/public/collection.blade.php
```

Cette page reste une page Blade publique.

Le formulaire Vue de demande de collecte est monte sur :

```html
<div id="formulaire"></div>
```

Le composant monte est :

```text
resources/js/components/public/CollectionForm.vue
```

Important : l'ancien point de montage `#collecte-form` n'est plus la reference.

## `/prix`

Route :

```text
resources/views/public/prize.blade.php
```

Le formulaire Vue est monte via :

```html
<div id="prize-form"></div>
```

Composant :

```text
resources/js/components/public/PrizeForm.vue
```

## `/label`

Route :

```text
App\Http\Controllers\PublicSiteController@label
```

Vue Blade :

```text
resources/views/public/label.blade.php
```

Cette page est publique et ne depend pas du shell SPA admin.

## `/contact`

La route existe dans `routes/web.php`, mais la vue correspondante n'est pas presente dans `resources/views/public/`.

Conclusion :

- la route est actuellement incoherente avec le code disponible ;
- il ne faut pas considerer `/contact` comme une page publique fonctionnelle tant que la vue n'existe pas.

## Pages co-brandees

La route :

```text
/collecte/{brand}/{token}
```

ne suit pas le modele MPA documente ici.

Elle passe par :

```text
App\Http\Controllers\CoBrandedCollecteController@show
resources/views/app.blade.php
resources/js/App.vue
```

et injecte ses donnees dans `window.__APP__`.

## Admin

Toutes les pages `/admin/...` hors login passent par le shell SPA :

```text
resources/views/app.blade.php
resources/js/App.vue
```

Le login admin reste separe :

```text
resources/views/admin/login.blade.php
```

## Points de montage Vue actuellement utilises

Dans `resources/js/app.ts`, les points de montage publics reels sont :

- `#formulaire`
- `#prize-form`
- `#podium`
- `#companies`
- `#login-form`
- `#cookie-consent-root`
- `#app` pour les zones SPA

## Regle de contribution

Pour ajouter une nouvelle page publique :

1. determiner si elle doit etre une page Blade simple, une page Blade avec ilot Vue, ou une vraie page SPA ;
2. si c'est une page publique classique, privilegier `resources/views/public/...` ;
3. n'ajouter un point de montage Vue que si l'interaction le justifie ;
4. verifier `resources/js/app.ts` avant de documenter ou creer un nouvel id de montage ;
5. ne pas documenter `/contact` comme fonctionnel tant que sa vue n'existe pas.
