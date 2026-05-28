# Pages publiques MPA et ilots Vue

Ce document explique l'architecture des pages publiques classiques du site, c'est-a-dire les pages rendues par Blade avec des composants Vue montes ponctuellement dans la page.

## Scope

Ce document concerne les pages publiques MPA du site principal.

Il ne concerne pas :

- le back-office `/admin`, qui fonctionne comme une SPA Vue servie par `resources/views/app.blade.php` ;
- les pages co-brandees `/collecte/{brand}/{token}`, qui passent aussi par la SPA Vue et recoivent leurs donnees via `window.__APP__.coBrandedCollecte` ;
- les endpoints JSON de l'admin.

## Principe general

Les pages MPA sont rendues par Laravel/Blade. Le HTML principal vient du serveur, puis Vue est utilise uniquement pour enrichir certaines zones de la page.

Le fichier d'entree front reste unique :

```text
resources/js/app.ts
```

Ce fichier detecte la presence de points de montage dans le DOM et monte seulement les composants Vue necessaires.

## Layout public

Le layout principal des pages MPA est :

```text
resources/views/layouts/public.blade.php
```

Il inclut :

- les metas HTML communes ;
- les assets Vite `resources/css/app.css` et `resources/js/app.ts` ;
- le header public `resources/views/partials/public-header.blade.php` ;
- le contenu de page via `@yield('content')` ;
- le footer public `resources/views/partials/public-footer.blade.php` ;
- le point de montage Vue `#cookie-consent-root` pour la popup cookies.

## Header et footer

Le header public est dans :

```text
resources/views/partials/public-header.blade.php
```

Il gere la navigation publique et l'etat actif du lien selon `request()->path()`.

Le footer public est dans :

```text
resources/views/partials/public-footer.blade.php
```

Il est partage par les pages qui etendent le layout public.

## Accueil

La route `/` utilise :

```text
App\Http\Controllers\PublicSiteController@home
resources/views/public/home.blade.php
```

Le controller prepare les donnees serveur :

- entreprises participantes ;
- podiums par edition ;
- jury.

La vue Blade rend le contenu principal et injecte certaines donnees JSON dans des attributs `data-*`.

Exemple :

```blade
<div id="podium" data-podiums='@json($podiums)'></div>
<div id="companies" data-companies='@json($companies)'></div>
```

Puis `resources/js/app.ts` lit ces attributs et monte les composants Vue correspondants :

```ts
createApp(Podium, { initialPodiums }).mount(podiumRoot);
createApp(Companies, { initialCompanies }).mount(companiesRoot);
```

## Page collecte publique simple

La route `/collecte` utilise :

```text
resources/views/public/collection.blade.php
```

Cette page est encore une page Blade autonome. Elle inclut le header public, charge Vite et expose le point de montage :

```html
<div id="collecte-form"></div>
```

`resources/js/app.ts` monte ensuite :

```ts
createApp(CollecteForm).mount(el);
```

Cette page est differente des pages co-brandees tokenisees.

## Popup cookies

Les pages MPA affichent la popup cookies via :

```html
<div id="cookie-consent-root"></div>
```

Quand ce noeud existe, `resources/js/app.ts` monte :

```ts
createApp(CookieConsentModal).mount(cookieConsentRoot);
```

Le composant garde sa propre logique de consentement dans :

```text
resources/js/components/modals/CookieConsentModal.vue
resources/js/services/cookieConsent.ts
```

Important : ce montage est dedie aux pages MPA. La SPA admin/co-brandee ne doit pas dependre de ce point de montage.

## Routes publiques actuelles

Etat actuel a verifier dans `routes/web.php` :

```text
/                         MPA Blade via PublicSiteController@home
/collecte                 page Blade autonome avec ilot Vue CollecteForm
/collecte/{brand}/{token} page co-brandee SPA, hors scope de ce document
/admin                    SPA admin, hors scope de ce document
```

Certaines routes publiques comme `/label`, `/trophee` ou `/contact` peuvent encore pointer vers `resources/views/app.blade.php` selon l'etat du chantier. Tant qu'elles passent par `app.blade.php`, elles ne suivent pas completement le pattern MPA decrit ici.

## Regle de contribution

Pour une nouvelle page publique MPA :

1. creer ou reutiliser une vue Blade dans `resources/views/public/` ;
2. etendre `layouts.public` quand c'est possible ;
3. preparer les donnees cote Laravel dans un controller public ;
4. injecter les donnees dans Blade via variables serveur ou attributs `data-*` ;
5. monter un composant Vue seulement si la page a besoin d'interactivite locale ;
6. ne pas melanger cette logique avec les routes `/admin` ou co-brandees.
