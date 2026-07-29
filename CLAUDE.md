# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Plain-PHP (no framework, no Composer, no build step) portfolio site for Aashish Rai, scaffolded to deploy on Hostinger shared hosting.

## Commands

```bash
cp .env.example .env                    # first-time local setup
php -S localhost:8000 -t .              # run locally at http://localhost:8000
php -l path/to/file.php                 # lint a single file before committing
find . -name "*.php" -exec php -l {} \; # lint everything
```

There is no test suite, linter config, or build step beyond `php -l`.

## Architecture

- **The repo root is the web root.** Hostinger's Git auto-deploy clones the whole repo into one directory (`public_html`), so there's no separate `public_html/` subfolder in this repo — `index.php`, `config/`, `includes/`, `assets/`, `.htaccess` all sit at the top level and get deployed as-is. Don't reintroduce a nested `public_html/` folder.
- **`config/` is protected by its own `.htaccess`** (`Require all denied` / `Deny from all`, both syntaxes for Apache 2.2 and 2.4+/LiteSpeed compatibility) rather than by living outside the web root — Git-based single-directory deploy can't split files across two locations the way manual FTP upload could. `.env` is protected by the dotfile-deny `FilesMatch` rule in the root `.htaccess`, plus an explicit `.git/` block since some deploy setups leave `.git` inside the served directory.
- **`config/config.php`** is the entry point every page requires first, via `require_once __DIR__ . '/config/config.php'`. It hand-rolls a `.env` parser (`load_env()`) instead of using a Composer dotenv package — this project has no `vendor/`. It also defines `APP_ENV`/`SITE_NAME`, sets the error-display mode, and starts the session.
- **`config/database.php`** exposes `get_db(): PDO`, a lazily-instantiated singleton (static var inside the function) built from `DB_*` env vars. In production it swallows `PDOException` and dies with a generic message rather than leaking connection details; in non-production `APP_ENV` it rethrows.
- **Page structure**: each `*.php` page at the repo root requires `config/config.php` and `includes/functions.php`, then `includes/header.php`, then its own markup, then `includes/footer.php`. `header.php` reads two optional variables set by the page *before* it's included — `$pageTitle` and `$pageDescription` — to fill in `<title>` and the meta description; see `index.php` for the pattern.
- **`includes/functions.php`** holds two small helpers used throughout: `e($string)` (htmlspecialchars wrapper — always escape interpolated content) and `asset_url($path)` (prefixes `/assets/`).
- **Content-as-data pattern**: page content lives in plain PHP associative arrays at the top of the page file (see the `$hero`, `$about`, `$services`, `$work`, `$journal`, `$contact` arrays in `index.php`), rendered below with `foreach` loops. There's no CMS or database-backed content yet — new sections should follow this same array-then-loop shape rather than inlining strings into markup.
- **Image placeholders**: real photography isn't in the repo yet. Slots where an image belongs are `<div class="img-placeholder">` (see `assets/css/style.css`) — swap these for real `<img>` tags as photos become available; keep the `grayscale` class where present, it matches the site's design treatment.
- **Other pages implied by nav/work/journal links** (`project-detail.php`, `journal-archive.php`, `journal-detail.php`) don't exist yet — they're linked from `index.php`/`work-archive.php` in anticipation of being built.

## Development Workflow

- **Branch per change**: `feature/<short-name>` for new pages/sections, `fix/<short-name>` for bugs. Don't commit straight to `main`.
- **Commits**: focused, one logical change each. Message explains *why*, not just what changed (e.g. `Add contact form validation` not `update index.php`). Follow conventional-commit-style prefixes where it reads naturally (`feat:`, `fix:`, `chore:`) but don't force it if a plain sentence is clearer.
- **Never commit `.env`** or anything under `config/` that contains real credentials — `.gitignore` already excludes `.env`; double-check `git status` before committing if you touched `config/`.
- **Lint before committing**: run `php -l` on every changed `.php` file — there's no CI yet to catch a syntax error.
- **PR-based review**: push the branch, open a PR against `main`, merge only after review — even solo, this keeps `main` deployable at all times since Hostinger's Git auto-deploy pushes whatever's on `main` straight to production.
- **Deploying**: `main` is the deployable branch. Hostinger's Git auto-deploy (hPanel > Websites > Manage > Advanced > Git) is connected to this repo with deploy Directory set to `public_html` — merging to `main` deploys automatically. `.env` isn't part of the repo; it's created once directly on the server via File Manager with real credentials. See `README.md` for details.
