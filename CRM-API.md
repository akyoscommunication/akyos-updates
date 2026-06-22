# API CRM — liaison MAW & maintenance distante

Routes REST pour lier un site WordPress à MAW, remonter l’état de maintenance et déclencher des actions à distance.

## Configuration

### Option A — Interface admin (recommandée)

1. Ouvrir **Akyos Updates → Réglages → Connexion MAW**
2. Coller la **clé de liaison** fournie par MAW
3. Cliquer **Enregistrer et lier à MAW**

Le plugin génère une **clé API du site** (`akyos_updates_link`) utilisée pour authentifier les appels distants.

### Option B — `wp-config.php` (legacy)

```php
define('AKYOS_UPDATES_MAW_KEY', 'votre-cle-secrete-longue-et-aleatoire');
```

Si cette constante est définie, elle **prime** sur la clé stockée en base.

HTTPS obligatoire en production.

## Authentification

Envoyer la clé via l’un des headers :

- `X-Akyos-Api-Key: votre-cle`
- `Authorization: Bearer votre-cle`

Sans clé valide : `401`.

## Endpoints

Base : `{site}/wp-json/akyos-updates/v1`

### GET `/crm/site-identity`

Identité légère du site (sans analyse) : URLs, panels, état de liaison.

```bash
curl -sS 'https://example.com/wp-json/akyos-updates/v1/crm/site-identity' \
  -H 'X-Akyos-Api-Key: VOTRE_CLE'
```

### POST `/crm/site-status`

Lance une **analyse complète**, persiste le rapport et renvoie le JSON CRM.

| Paramètre | Description |
|-----------|-------------|
| `force=1` | Ignore le rate-limit analyse (1 / 5 min) |
| `detail=summary` | Réponse allégée |
| `categories[]` | Sous-ensemble de catégories |

### GET `/crm/site-status`

Dernier rapport sans relancer l’analyse. `404` si aucun rapport.

### POST `/crm/fix`

Déclenche une **action corrective** à distance (même contrat que `/fix` admin).

```bash
curl -sS -X POST 'https://example.com/wp-json/akyos-updates/v1/crm/fix' \
  -H 'X-Akyos-Api-Key: VOTRE_CLE' \
  -H 'Content-Type: application/json' \
  -d '{"actionId":"security.defender_enable_security_headers","checkId":"security.defender_security_headers"}'
```

Rate-limit : 1 action / 60 s par clé.

### POST `/crm/login-link`

Génère un **lien de connexion unique** (5 min, usage unique) vers le back-office.

```bash
curl -sS -X POST 'https://example.com/wp-json/akyos-updates/v1/crm/login-link' \
  -H 'X-Akyos-Api-Key: VOTRE_CLE' \
  -H 'Content-Type: application/json' \
  -d '{"redirect":"https://example.com/wp-admin/admin.php?page=akyos-updates"}'
```

Réponse :

```json
{
  "url": "https://example.com/mon-login/?akyos_maw_login=…",
  "expiresIn": 300,
  "expiresAt": "2026-06-19T12:00:00+00:00"
}
```

Le lien utilise l’URL de connexion réelle (Defender Mask Login si actif). Connexion en tant que premier administrateur du site.

## Réponse site-status (extrait)

```json
{
  "site": {
    "installationType": "bedrock",
    "wordpressVersion": "6.7.1",
    "homeUrl": "https://example.com/",
    "siteName": "Mon site",
    "adminUrl": "https://example.com/wp-admin/",
    "loginUrl": "https://example.com/secret-login/",
    "loginMasked": true,
    "reportUrl": "https://example.com/wp-admin/admin.php?page=akyos-updates",
    "panels": {
      "maintenance": "https://example.com/wp-admin/admin.php?page=akyos-updates",
      "rgpd": "https://example.com/wp-admin/admin.php?page=akyos-updates#rgpd"
    }
  },
  "link": {
    "linked": true,
    "linked_at": "2026-06-19T10:00:00+00:00",
    "pairing_key": "…",
    "api_key_preview": "abcd••••wxyz"
  },
  "summary": { "total": 32, "ok": 24, "warn": 6, "fail": 2 },
  "health": { "status": "critical", "score": 75 }
}
```

## Liaison site → MAW (côté plugin)

Lors de **Enregistrer et lier**, le plugin appelle :

`POST {URL_MAW}/api/akyos-updates/link`

Configuration de l’URL MAW (par ordre de priorité) :

1. Constante `AKYOS_UPDATES_MAW_URL` dans `wp-config.php` (URL de base, ex. `https://app.mon-agence-web.io`)
2. Filtre `akyos_updates_maw_url` (URL de base)
3. Valeur par défaut (`http://maw.wip` en dev)

Filtres WordPress :

- `akyos_updates_maw_url` — URL **de base** de MAW (recommandé)
- `akyos_updates_maw_register_url` — surcharge de l’URL **complète** d’enregistrement (compat)
- `akyos_updates_maw_maintenance_url` — URL du widget forfait maintenance

Corps envoyé : `pairingKey`, `apiKey`, `homeUrl`, `loginUrl`, `adminUrl`, `siteName`, versions.

## Stratégie client MAW

1. Créer une clé de liaison dans MAW → la donner au client
2. Client lie le site dans l’admin WordPress
3. MAW stocke `homeUrl` + `apiKey` + panels
4. Cron MAW : `GET /crm/site-status` (dashboard) ou `POST` (refresh)
5. Intervention : `POST /crm/fix` ou `POST /crm/login-link` → ouvrir l’URL retournée

## Codes d’erreur

| Code | HTTP | Cause |
|------|------|--------|
| `akyos_updates_unauthorized` | 401 | Clé absente ou invalide |
| `akyos_updates_rate_limited` | 429 | Trop d’appels récents |
| `akyos_updates_no_report` | 404 | GET sans rapport stocké |
