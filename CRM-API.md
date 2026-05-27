# API CRM — état du site (Akyos Updates)

Route REST pour remonter l’état de maintenance d’un site WordPress vers un CRM, sans session admin WordPress.

## Configuration

Dans `wp-config.php` (une clé unique par site) :

```php
define('AKYOS_UPDATES_MAW_KEY', 'votre-cle-secrete-longue-et-aleatoire');
```

HTTPS obligatoire en production.

## Authentification

Envoyer la clé via l’un des headers :

- `X-Akyos-Api-Key: votre-cle`
- `Authorization: Bearer votre-cle`

Sans clé valide : `401`.

## Endpoints

Base : `{site}/wp-json/akyos-updates/v1`

Version API: `v1` (contract stable). Si breaking change future: nouvelle base `/v2/...`.

### POST `/crm/site-status`

Lance une **analyse complète**, persiste le rapport (`akyos_updates_last_report`) et renvoie le JSON CRM. L’interface admin affiche ce rapport sans relancer l’analyse.

**Query / body (optionnel)**

| Paramètre | Description |
|-----------|-------------|
| `force=1` | Ignore le rate-limit (1 analyse / 5 min par défaut) |
| `detail=summary` | Réponse allégée : pas de `checks` ni `pluginPresence` |
| `categories[]` | Sous-ensemble de catégories (ex. `WordPress`, `Sécurité`) |

**Exemple**

```bash
curl -sS -X POST 'https://example.com/wp-json/akyos-updates/v1/crm/site-status' \
  -H 'X-Akyos-Api-Key: VOTRE_CLE' \
  -H 'Content-Type: application/json'
```

### GET `/crm/site-status`

Retourne le **dernier rapport** sans relancer l’analyse. `404` si aucun rapport.

```bash
curl -sS 'https://example.com/wp-json/akyos-updates/v1/crm/site-status?detail=summary' \
  -H 'X-Akyos-Api-Key: VOTRE_CLE'
```

## Réponse (extrait)

```json
{
  "site": {
    "installationType": "bedrock",
    "activeTheme": "mon-theme",
    "phpVersion": "8.2.12",
    "wordpressVersion": "6.7.1",
    "wpCliAvailable": true,
    "composerAvailable": true
  },
  "summary": { "total": 32, "ok": 24, "warn": 6, "fail": 2 },
  "health": { "status": "critical", "score": 75 },
  "categories": {
    "WordPress": { "total": 5, "ok": 4, "warn": 1, "fail": 0 }
  },
  "checks": [],
  "updatedAt": "2026-05-27T14:30:00+00:00",
  "reportUrl": "https://example.com/wp-admin/admin.php?page=akyos-updates"
}
```

## Contrat de types (pour agent CRM)

- `site.installationType`: `string`
- `site.activeTheme`: `string`
- `site.phpVersion`: `string`
- `site.wordpressVersion`: `string`
- `site.wpCliAvailable`: `boolean`
- `site.composerAvailable`: `boolean`
- `summary.total`: `int`
- `summary.ok`: `int`
- `summary.warn`: `int`
- `summary.fail`: `int`
- `health.status`: `"ok" | "warn" | "critical"`
- `health.score`: `int` (0..100)
- `categories`: `Record<string, { total: int, ok: int, warn: int, fail: int }>`
- `checks`: `Check[]` (absent si `detail=summary`)
- `updatedAt`: `string` (ISO-8601 UTC)
- `reportUrl`: `string` (URL absolue)

### Type `Check` (forme réelle)

```json
{
  "id": "wordpress.version",
  "category": "WordPress",
  "title": "Version WordPress",
  "status": "warn",
  "severity": "warning",
  "message": "Une mise à jour WordPress est disponible.",
  "actionable": true,
  "actionId": "wordpress.change_version",
  "payload": { "targetVersion": "6.7.1" },
  "countsTowardCategoryStats": true
}
```

Notes:
- `status` pilote les couleurs CRM (`ok|warn|fail`).
- `severity` est informatif (ne remplace pas `status`).
- Certains checks peuvent avoir `status: "info"` ou `status: "skipped"` (à gérer côté UI CRM).

### Mapping statuts

| Plugin (`status`) | CRM (couleur) |
|-------------------|---------------|
| `ok` | vert |
| `warn` | orange |
| `fail` | rouge |

### `health.status`

- `ok` : aucun `warn` ni `fail`
- `warn` : des `warn`, aucun `fail`
- `critical` : au moins un `fail`

`health.score` = `round(ok / total * 100)` (0 si `total` = 0 → 100).

## Limites

- **Timeout** : analyse synchrone (~30+ checks). `set_time_limit(120)` côté PHP ; hébergement lent → augmenter timeout serveur ou passer en async (phase 2).
- **Rate-limit** : 1 POST / 5 min par clé (sauf `force=1`).
- **Chemins** : `projectRootPath`, etc. ne sont pas exposés dans `site`.

## Codes d’erreur

| Code | HTTP | Cause |
|------|------|--------|
| `akyos_updates_unauthorized` | 401 | Clé absente, invalide ou non configurée |
| `akyos_updates_rate_limited` | 429 | Analyse trop récente |
| `akyos_updates_no_report` | 404 | GET sans rapport stocké |
| `akyos_updates_invalid_category` | 400 | Catégorie inconnue |
| `akyos_updates_empty_categories` | 400 | `categories[]` vide après validation |
| `akyos_updates_exception` | 500 | Erreur interne plugin pendant traitement |

Format erreur WordPress REST:

```json
{
  "code": "akyos_updates_rate_limited",
  "message": "Analyse récente : réessaie dans quelques minutes ou utilise ?force=1.",
  "data": { "status": 429 }
}
```

Exemples:

```json
{
  "code": "akyos_updates_unauthorized",
  "message": "Clé API invalide.",
  "data": { "status": 401 }
}
```

```json
{
  "code": "akyos_updates_no_report",
  "message": "Aucun rapport disponible. Lance une analyse via POST /crm/site-status.",
  "data": { "status": 404 }
}
```

```json
{
  "code": "akyos_updates_invalid_category",
  "message": "Catégorie de rapport inconnue.",
  "data": { "status": 400 }
}
```

## Stratégie client CRM (recommandée)

- `POST /crm/site-status`: action volontaire (refresh manuel ou cron planifié).
- `GET /crm/site-status`: lecture dashboard sans relancer.
- Retry:
  - `401`: ne pas retry, alerter "clé invalide/non configurée".
  - `400`: ne pas retry, corriger requête.
  - `404` sur GET: déclencher un POST initial.
  - `429`: retry après 5 minutes (ou POST avec `force=1` si assumé).
  - `5xx`: retry exponentiel (ex: 10s, 30s, 60s, max 5 tentatives).
