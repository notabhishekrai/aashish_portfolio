# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Plain-PHP (no framework, no Composer, no build step) portfolio site for Aashish Rai, scaffolded to deploy on Hostinger shared hosting.

## Commands

```bash
cp .env.example .env                    # first-time local setup
php -S localhost:8000 -t public_html    # run locally at http://localhost:8000
php -l path/to/file.php                 # lint a single file before committing
find . -name "*.php" -exec php -l {} \; # lint everything
```

There is no test suite, linter config, or build step beyond `php -l`.

## Architecture

- **`config/` lives outside `public_html/`.** Hostinger only serves `public_html` to the web, so anything in `config/` (DB credentials, site constants) is unreachable by URL even in production. Pages under `public_html/` reach it via `require_once __DIR__ . '/../config/...'`. Don't move config files into `public_html/`.
- **`config/config.php`** is the entry point every page requires first. It hand-rolls a `.env` parser (`load_env()`) instead of using a Composer dotenv package — this project has no `vendor/`. It also defines `APP_ENV`/`SITE_NAME`, sets the error-display mode, and starts the session.
- **`config/database.php`** exposes `get_db(): PDO`, a lazily-instantiated singleton (static var inside the function) built from `DB_*` env vars. In production it swallows `PDOException` and dies with a generic message rather than leaking connection details; in non-production `APP_ENV` it rethrows.
- **Page structure**: each `public_html/*.php` page requires `config/config.php` and `includes/functions.php`, then `includes/header.php`, then its own markup, then `includes/footer.php`. `header.php` reads two optional variables set by the page *before* it's included — `$pageTitle` and `$pageDescription` — to fill in `<title>` and the meta description; see `index.php` for the pattern.
- **`includes/functions.php`** holds two small helpers used throughout: `e($string)` (htmlspecialchars wrapper — always escape interpolated content) and `asset_url($path)` (prefixes `/assets/`).
- **Content-as-data pattern**: page content lives in plain PHP associative arrays at the top of the page file (see the `$hero`, `$about`, `$services`, `$work`, `$journal`, `$contact` arrays in `index.php`), rendered below with `foreach` loops. There's no CMS or database-backed content yet — new sections should follow this same array-then-loop shape rather than inlining strings into markup.
- **Image placeholders**: real photography isn't in the repo yet. Slots where an image belongs are `<div class="img-placeholder">` (see `public_html/assets/css/style.css`) — swap these for real `<img>` tags as photos become available; keep the `grayscale` class where present, it matches the site's design treatment.
- **Other pages implied by nav/work/journal links** (`work-archive.php`, `project-detail.php`, `journal-archive.php`, `journal-detail.php`) don't exist yet — they're linked from `index.php` in anticipation of being built.

## Development Workflow

- **Branch per change**: `feature/<short-name>` for new pages/sections, `fix/<short-name>` for bugs. Don't commit straight to `main`.
- **Commits**: focused, one logical change each. Message explains *why*, not just what changed (e.g. `Add contact form validation` not `update index.php`). Follow conventional-commit-style prefixes where it reads naturally (`feat:`, `fix:`, `chore:`) but don't force it if a plain sentence is clearer.
- **Never commit `.env`** or anything under `config/` that contains real credentials — `.gitignore` already excludes `.env`; double-check `git status` before committing if you touched `config/`.
- **Lint before committing**: run `php -l` on every changed `.php` file — there's no CI yet to catch a syntax error.
- **PR-based review**: push the branch, open a PR against `main`, merge only after review — even solo, this keeps `main` deployable at all times since it's what gets uploaded to Hostinger.
- **Deploying**: `main` is the deployable branch. See `README.md` for the exact Hostinger upload layout (`public_html/` contents → hosting's `public_html`; `config/` and `.env` → one level above it, outside the web root). There's no automated deploy — it's a manual upload (FTP/file manager/git-based deploy if configured on Hostinger's end).
