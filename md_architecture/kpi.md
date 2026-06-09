[KPI]

Nb de visites (site public) - Phase 3.1

Taux de participation (nb connectés/ nb de collaborateurs), * et filtrer par entreprise - Phase 3.1

connectés = réussi a se connecter sur le site co-brandé avec le code au moins 1x - Phase 3.1

Nombre d’entreprises labellisées (et nb entreprises voulant pas être affichées) - Phase 3.1

Sources des entreprises sous forme de liste - Phase 3.1

Conversion connexion → inscription (click lien OneDoc) - Phase 3.2

Taux d’abandon sur le questionnaire (swipe ou messagerie) - Phase 3.2

[Migration]

Migrations nécessaires pour couvrir les KPIs de la phase 3.1.

## companies — ajout `labelled`

```php
$table->boolean('labelled')->default(false)->after('trophy');
```

`trophy` (existant) couvre les entreprises récompensées au trophée.
`labelled` est un statut distinct : entreprise officiellement labelliée HUG, indépendamment d'un trophée.
Sert au KPI "Nombre d'entreprises labellisées".

`is_public` (existant) couvre le KPI "nb entreprises voulant pas être affichées" — aucune migration nécessaire.
`source` (existant) couvre le KPI "Sources des entreprises" — aucune migration nécessaire.
`employee_count` (existant) couvre le dénominateur du taux de participation — aucune migration nécessaire.

## collections_users — ajout `first_connected_at`

```php
$table->timestamp('first_connected_at')->nullable()->after('abandonment');
```

Définit ce qu'est un "connecté" : collaborateur ayant accédé au site co-brandé avec son code au moins une fois.
Rempli par le controller au moment où la session co-brandée est validée, si la colonne est encore NULL.
Sert au numérateur du taux de participation : `COUNT(first_connected_at IS NOT NULL) / companies.employee_count`.

## page_visits — nouvelle table

```php
Schema::create('page_visits', function (Blueprint $table) {
    $table->id();
    $table->string('path', 255);
    $table->string('ip_hash', 64); // SHA-256 anonymisé, jamais l'IP brute
    $table->timestamp('created_at');
});
```

Pas de `updated_at` (enregistrement immuable).
Sert au KPI "Nb de visites du site public".
Le hash IP permet de déduire les visites uniques sans stocker de donnée personnelle directe.
Seules les routes publiques (`/`, `/collecte`, `/trophee`, `/label`) sont trackées — pas les routes `/admin` ni `/collecte/{brand}/{token}`.