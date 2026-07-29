# Aashish Portfolio

Plain-PHP site scaffolded for Hostinger shared hosting, deployed via Hostinger's Git auto-deploy.

## Structure

The repo root **is** the web root — everything here gets deployed as-is into Hostinger's `public_html`. `config/` sits alongside the public pages rather than outside them (Git-based deploy clones the whole repo into one directory, so there's no "outside the web root" to put it in). It's protected instead by `config/.htaccess`, which denies all requests to that folder regardless of filename. `.env` is protected by the dotfile-deny rule in the root `.htaccess`.

## Local Setup

1. Copy `.env.example` to `.env` and fill in your MySQL credentials (from Hostinger's hPanel > Databases).
2. Serve locally with PHP's built-in server:
   ```
   php -S localhost:8000 -t .
   ```
3. Visit `http://localhost:8000/`.

## Deploying to Hostinger

This repo is connected to Hostinger's Git auto-deploy (hPanel > Websites > Manage > Advanced > Git). The deploy **Directory** is set to `public_html`, so every push to `main` clones the full repo directly into it — meaning `index.php`, `config/`, `.htaccess`, etc. all land at the top level of `public_html`, exactly matching this repo's layout. Don't nest a `public_html/` folder inside this repo — the repo root already **is** the site root.

`.env` isn't committed (it's git-ignored), so it won't be created by the auto-deploy. Create it once directly in Hostinger's `public_html` via File Manager with real production credentials — it'll persist across future deploys as long as the deploy doesn't do a clean wipe.

If you ever move away from Git auto-deploy back to manual FTP/File Manager upload, the process is the same: upload everything in this repo directly into `public_html`, plus a `.env` with real credentials.

## Database

`config/database.php` exposes `get_db(): PDO`, a lazily-created PDO connection built from the `DB_*` environment variables. Call it wherever you need database access:

```php
require_once __DIR__ . '/config/database.php';
$pdo = get_db();
```
