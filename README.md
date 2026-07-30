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

### Schema setup (one-time)

The site's content — every section on every page, all work projects, and all journal entries — lives in the database rather than in hardcoded PHP arrays. Before the site will work, apply `database/schema.sql` once:

1. In Hostinger's hPanel (or your local MySQL/MariaDB client), open phpMyAdmin for the site's database.
2. Import/run `database/schema.sql` in full. It creates every table and seeds them with the site's current copy, so the public pages render the same content they do today, plus a default admin login.
3. Locally, the same file works against `mysql -u root -p your_db < database/schema.sql` or your GUI client of choice.

This is a one-time step, not idempotent — re-running it will fail because the tables already exist.

## Admin panel

Content for every page is edited at `/admin/` (e.g. `https://yoursite/admin/login.php`), a plain-PHP, session-authenticated admin panel — no separate app or build step.

- **Default login** (seeded by `database/schema.sql`): username `admin`, password `ChangeMe123!`. **Change this immediately** after your first login, via `/admin/change-password.php` — there is no other way to reset it besides updating the `password_hash` column directly in the database.
- From the dashboard you can edit every section of the home, work-archive, and journal-archive pages, and manage work projects and journal entries (each project/entry gets its own page at `work-detail.php?slug=...` / `journal-detail.php?slug=...`, built from one shared template).
- Uploaded images are validated (type, size) and stored under `assets/images/uploads/` with randomized filenames; the folder isn't committed to Git (see `.gitignore`) since it holds generated content, not source.
- Contact form submissions are stored in the database and viewable at `/admin/contact-submissions.php` — there's no outgoing email.
