# Consentement cookies et tracking des visites

Ce document décrit le système de consentement cookies et son intégration avec le tracking des visites de pages (`page_visits`).

## Objectif

Respecter le RGPD en ne trackant les visites du site public que si l'utilisateur a explicitement accepté les cookies analytics.

---

## Architecture

### Côté client — `resources/js/services/cookieConsent.ts`

Les préférences de consentement sont stockées à deux endroits :

1. **`localStorage`** sous la clé `hug-enyka-cookie-consent-v1` (JSON) — utilisé par le front pour afficher/masquer la modale et connaître l'état du consentement.
2. **Cookie HTTP `hug_analytics_consent`** — valeur `1` si accepté, supprimé si refusé. Ce cookie est lisible côté serveur (PHP) et permet au middleware de vérifier le consentement sans accès au localStorage.

Structure JSON dans localStorage :
```json
{
  "version": 1,
  "necessary": true,
  "analytics": true,
  "updatedAt": "2026-06-10T12:00:00.000Z"
}
```

Fonctions exportées :
- `saveCookieConsentPreferences(analytics: boolean)` — sauvegarde dans localStorage **et** écrit/supprime le cookie HTTP.
- `getCookieConsentPreferences()` — lit localStorage, retourne `null` si absent ou invalide.
- `hasCookieConsentDecision()` — `true` si l'utilisateur a déjà répondu.
- `hasAnalyticsConsent()` — `true` si analytics accepté.

### Côté client — `resources/js/components/modals/CookieConsentModal.vue`

Modale affichée à la première visite si aucune décision n'a été enregistrée. Appelle `saveCookieConsentPreferences()` selon le choix de l'utilisateur.

---

### Côté serveur — `app/Http/Middleware/TrackPageVisit.php`

Middleware appliqué aux routes publiques (`/`, `/collecte`, `/prix`, `/label`). Il insère une ligne dans la table `page_visits` (IP hashée en SHA-256) **uniquement si** le cookie `hug_analytics_consent` vaut `1`.

```
Requête entrante
  → next($request) (traitement normal)
  → cookie hug_analytics_consent === '1' ?
      Oui → INSERT INTO page_visits
      Non → pas d'insertion, réponse retournée normalement
```

### `bootstrap/app.php`

Le cookie `hug_analytics_consent` est exclu du chiffrement automatique de Laravel (`encryptCookies`) pour être lisible en clair par le middleware.

---

## Table `page_visits`

| Colonne      | Type        | Description                          |
|--------------|-------------|--------------------------------------|
| `id`         | bigint PK   | Auto-increment                       |
| `ip_hash`    | varchar     | SHA-256 de l'IP (anonymisation)      |
| `created_at` | timestamp   | Date de la visite                    |
| `updated_at` | timestamp   | —                                    |

---

## KPI "Visites du site public"

Calculé dans `KpiController::pageVisits()` avec filtre sur une période (mois en cours par défaut, 3m, 6m, année). Affiché dans le tableau de bord admin.

Ce compteur ne reflète que les visites des utilisateurs ayant consenti aux analytics — ce qui est cohérent avec le RGPD et doit être mentionné si les chiffres sont partagés.

---

## Comportement selon le consentement

| Situation                             | localStorage | Cookie HTTP | page_visits insérée |
|---------------------------------------|:------------:|:-----------:|:-------------------:|
| Première visite (aucune décision)     | absent       | absent      | Non                 |
| Refus des analytics                   | `analytics: false` | absent | Non              |
| Acceptation des analytics             | `analytics: true`  | `1`    | Oui              |
