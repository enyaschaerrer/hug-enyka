# Systeme co-brande — cheminement complet

Ce document explique comment fonctionne le systeme de pages co-brandees, de la creation en admin jusqu'a l'acces de l'employe.

---

## Vue d'ensemble

Une page co-brandee est une version du site personnalisee avec les couleurs et le logo d'une entreprise partenaire. Elle est accessible via un lien unique qui contient un token — ce lien est l'authentification. Toute personne sans le lien ne peut pas acceder a la page.

```
/collecte/{slug_entreprise}/{access_token}
```

Exemple :

```
/collecte/rolex/7f4c9b8e2a6d4f01
```

---

## Entites en base de donnees

Deux tables sont impliquees :

### `companies`

Contient les informations de chaque entreprise partenaire :

```
id, name, logo, slug, short_description, email, primaryColor, secondaryColor, thirdColor, ...
```

Le `slug` est unique et sert a identifier l'entreprise dans l'URL (ex: `rolex`).

### `collections`

Chaque collecte est liee a une entreprise. Elle a une date de debut, une date de fin, un lien OneDoc de prise de RDV et un token d'acces.

```
id, company_id, start, end, access_token, linkOneDoc
```

Le `access_token` est genere automatiquement a la creation (16 caracteres hexadecimaux via `bin2hex(random_bytes(8))`). Il est unique globalement (`UNIQUE` sur la colonne).

---

## Flux de creation (cote admin CTS)

1. Le CTS se connecte sur `/admin/login`.
2. Sur le dashboard `/admin`, il cree une entreprise via le formulaire `/admin/companies/create` (nom, slug, logo, couleurs, email, ...).
3. Depuis la liste des entreprises sur le dashboard, il cree une collecte pour cette entreprise (debut, fin, lien OneDoc). Le token est genere automatiquement.
4. Le lien co-brande apparait dans la liste : `/collecte/{slug}/{token}`.
5. Le CTS copie ce lien et le transmet a l'entreprise (email, communication interne, QR code, ...).

---

## Flux d'acces (cote employe)

1. L'employe recoit le lien unique.
2. Il clique sur le lien : `/collecte/rolex/7f4c9b8e2a6d4f01`.
3. Laravel recoit la requete, cherche une collection dont `access_token = token` et dont l'entreprise a `slug = brand`.
4. Si non trouve : 404.
5. Si trouve : Laravel injecte les donnees de l'entreprise et de la collecte dans `window.__APP__.coBrandedCollecte` et sert le shell Vue.
6. Vue detecte l'URL via le regex `/^\/collecte\/[^/]+\/[^/]+$/` dans `App.vue` et affiche `CoBrandedCollectePage`.
7. La page lit `window.__APP__.coBrandedCollecte` et affiche l'interface co-brandee (couleurs, logo, modules d'eligibilite, lien RDV).

---

## Fichiers impliques

### Backend

```
app/Http/Controllers/CoBrandedCollecteController.php   Resolution de la page via brand + token
app/Http/Controllers/Admin/CompanyController.php        Liste et creation d'entreprises (admin)
app/Http/Controllers/Admin/CollectionController.php     Creation de collecte + generation du token
app/Http/Requests/Admin/StoreCompanyRequest.php         Validation creation entreprise
app/Http/Requests/Admin/StoreCollectionRequest.php      Validation creation collecte
app/Models/Company.php                                  Modele entreprise
app/Models/Collection.php                               Modele collecte
routes/web.php                                          Routes (co-brande + admin API)
```

### Frontend

```
resources/js/App.vue                                    Detecte la route co-brandee par regex
resources/js/pages/public/CoBrandedCollectePage.vue     Page co-brandee (lit window.__APP__)
resources/js/components/public/CoBrandedHeader.vue      Header avec couleurs/logo entreprise
resources/js/pages/admin/DashboardPage.vue              Liste entreprises + liens + creation collecte
resources/js/pages/admin/CompanyCreatePage.vue          Formulaire creation entreprise
```

### Base de donnees

```
database/migrations/2026_05_26_104101_create_uml_22_05_tables.php   Tables companies + collections
database/seeders/CoBrandedCollecteSeeder.php                         Donnees test Rolex
```

---

## Routes

```
GET  /collecte/{brand}/{token}                → page co-brandee (public, token = auth)
GET  /admin/api/companies                     → liste entreprises + collectes (JSON, auth+role)
POST /admin/companies                         → creer entreprise (JSON, auth+role)
POST /admin/companies/{company}/collections   → creer collecte + generer token (JSON, auth+role)
```

---

## Injection des donnees dans Vue

Le controller `CoBrandedCollecteController` passe les donnees au Blade via `view('app', ['coBrandedCollecte' => [...]])`.

Le template Blade (`resources/views/app.blade.php`) les injecte dans le JS :

```js
window.__APP__ = {
    auth: { user: ... },
    csrfToken: "...",
    coBrandedCollecte: { company: { ... }, collection: { ... } },
};
```

`CoBrandedCollectePage.vue` lit ensuite `window.__APP__.coBrandedCollecte` au montage.

---

## Generation du token

Le token est genere dans `CollectionController@store` :

```php
'access_token' => bin2hex(random_bytes(8)),
```

Cela produit 16 caracteres hexadecimaux (8 bytes en hex). Le token est unique en base (`UNIQUE` sur la colonne).

---

## Donnees de test (seeder Rolex)

Le seeder `CoBrandedCollecteSeeder` cree une entreprise Rolex et une collecte de demo :

```
Entreprise : rolex (slug)
Lien de test : /collecte/rolex/7f4c9b8e2a6d4f01
Collecte : 3 juin 2026, 09h00 → 16h30
```

Pour relancer le seeder :

```bash
php artisan db:seed --class=CoBrandedCollecteSeeder
```

---

## Points d'attention

- Le token n'est pas cryptographiquement signe (pas de JWT). C'est suffisant pour des informations non sensibles (employes anonymes).
- Plusieurs collectes peuvent exister pour la meme entreprise. La distinction se fait par l'`access_token` unique.
- Le logo est pour l'instant un chemin vers un fichier statique dans `public/img/companies/`. L'upload de fichiers n'est pas encore implemente.
