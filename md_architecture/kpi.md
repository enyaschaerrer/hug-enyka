[KPI]

Nombre d’entreprises labellisées (et nb entreprises voulant pas être affichées) - Done

Sources des entreprises sous forme de liste : classement / liste scrollable  - Done

Nb de visites (site public) - Phase 3.1 -> filtre mois + années - Done

Taux de participation (nb connectés co-brandé/ nb de collaborateurs), * et filtrer par entreprise - 

connectés = réussi a se connecter sur le site co-brandé avec le code au moins 1x - 

Conversion connexion → inscription (click lien OneDoc) - Phase 3.2

Taux d’abandon sur le questionnaire (swipe ou messagerie) - Phase 3.2

[Migration]

Migration : `2026_06_10_000000_update_collections_users_and_add_page_visits.php`

---

## collections_users — ajout `connected`

```php
$table->boolean('connected')->default(false)->after('clicked_onedoc');
```

Sert au KPI **Taux de participation** (Phase 3.1).
- Mis à `true` la première fois qu'un collaborateur accède au site co-brandé avec son code.
- Numérateur : `COUNT(connected = true)` sur les lignes liées à la collecte/entreprise.
- Dénominateur : `companies.employee_count` (existant).
- Filtre par entreprise : jointure `collections_users → collections → companies`.
- Pas de filtre temporel par mois/année (non requis phase 3.1).

## collections_users — ajout `quiz_step`

```php
$table->enum('quiz_step', ['quiz', 'chat', 'done'])->default('done')->after('user_id');
```

Remplace la colonne `abandonment` (booléen, supprimée).
Sert au KPI **Taux d'abandon sur le questionnaire** (Phase 3.2).
- `quiz` : collaborateur ayant quitté au niveau du swipe (questionnaire de compatibilité).
- `chat` : collaborateur ayant quitté au niveau de la messagerie SMS.
- `done` : parcours complété.
- Taux d'abandon global : `COUNT(quiz_step != 'done') / COUNT(*)`.
- Taux par étape : filtrer sur `quiz` ou `chat` séparément.

## collections_users — ajout `clicked_onedoc`

```php
$table->boolean('clicked_onedoc')->default(false)->after('quiz_step');
```

Sert au KPI **Conversion connexion → inscription** (Phase 3.2).
- Mis à `true` quand le collaborateur clique sur le lien OneDoc de prise de rendez-vous.
- Taux de conversion : `COUNT(clicked_onedoc = true) / COUNT(connected = true)`.

## page_visits — nouvelle table

```php
Schema::create('page_visits', function (Blueprint $table) {
    $table->id();
    $table->string('ip_hash', 64);
    $table->dateTime('created_at');
    $table->dateTime('updated_at');
});
```

Sert au KPI **Nb de visites du site public** (Phase 3.1).
- Enregistre une ligne à chaque visite d'une page publique.
- `ip_hash` : SHA-256 de l'IP, anonymisé, permet de déduire les visites uniques sans stocker de donnée personnelle directe.
- Pas de colonne `path` : comptage global uniquement (par mois/année via `created_at`).
- Seules les routes publiques sont trackées, pas `/admin` ni `/collecte/{brand}/{token}`.

## companies — aucune migration nécessaire

- `is_public` (existant) : couvre le KPI "nb entreprises en participation anonyme".
- `source` (existant) : couvre le KPI "Sources des entreprises".
- `employee_count` (existant) : dénominateur du taux de participation.
- Labellisées : `COUNT(companies ayant au moins une collection)` via `whereExists` — aucune colonne supplémentaire.

