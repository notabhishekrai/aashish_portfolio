# Aashish Portfolio

Plain-PHP site scaffolded for Hostinger shared hosting.

## Structure

- `public_html/` — the web root. Everything inside is publicly served.
- `config/` — sits **outside** `public_html` so it's not web-accessible on Hostinger.
- `.env` — local/production secrets (never committed).

## Local Setup

1. Copy `.env.example` to `.env` and fill in your MySQL credentials (from Hostinger's hPanel > Databases).
2. Serve locally with PHP's built-in server:
   ```
   php -S localhost:8000 -t public_html
   ```
3. Visit `http://localhost:8000/`.

## Deploying to Hostinger

Hostinger's file manager / FTP root has `public_html` as a folder alongside other private folders. Upload like this:

```
/ (Hostinger account root)
├── public_html/   <- upload the contents of this project's public_html/ here
├── config/         <- upload this project's config/ here (sibling of public_html)
└── .env            <- upload here, filled with production DB credentials
```

Because `config/` and `.env` live outside `public_html`, they are not reachable via the browser — only PHP running inside `public_html` can `require` them via `../config/...` paths.

## Database

`config/database.php` exposes `get_db(): PDO`, a lazily-created PDO connection built from the `DB_*` environment variables. Call it wherever you need database access:

```php
require_once __DIR__ . '/../config/database.php';
$pdo = get_db();
```
