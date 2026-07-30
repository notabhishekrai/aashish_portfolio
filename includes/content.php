<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Fetches a page/section's singleton scalar fields from site_settings,
 * shaped exactly like the hardcoded arrays (e.g. $hero, $about, $contact)
 * that used to live at the top of each page file.
 */
function get_section(string $page, string $section): array
{
    $stmt = get_db()->prepare(
        'SELECT field_key, field_value FROM site_settings WHERE page = ? AND section = ?'
    );
    $stmt->execute([$page, $section]);

    $fields = [];
    foreach ($stmt->fetchAll() as $row) {
        $fields[$row['field_key']] = $row['field_value'];
    }

    return $fields;
}

function get_nav_links(): array
{
    return get_db()
        ->query('SELECT link_key AS id, label, href FROM nav_links WHERE enabled = 1 ORDER BY sort_order')
        ->fetchAll();
}

function get_socials(): array
{
    return get_db()
        ->query('SELECT label, href FROM socials WHERE enabled = 1 ORDER BY sort_order')
        ->fetchAll();
}

function get_services(): array
{
    return get_db()
        ->query('SELECT title, description FROM services WHERE enabled = 1 ORDER BY sort_order')
        ->fetchAll();
}

function get_stats(): array
{
    return get_db()
        ->query('SELECT value, label FROM stats WHERE enabled = 1 ORDER BY sort_order')
        ->fetchAll();
}

function decode_work_project(array $row): array
{
    $row['synopsis_paragraphs'] = json_decode($row['synopsis_paragraphs'], true);
    $row['credits'] = json_decode($row['credits'], true);
    $row['tags'] = json_decode($row['tags'], true);
    $row['stills'] = json_decode($row['stills'], true);

    return $row;
}

function get_featured_work(int $limit = 6): array
{
    $stmt = get_db()->prepare(
        "SELECT * FROM work_projects WHERE status = 'published' AND is_featured = 1
         ORDER BY featured_sort_order LIMIT :limit"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return array_map('decode_work_project', $stmt->fetchAll());
}

function get_all_work(): array
{
    $stmt = get_db()->query(
        "SELECT * FROM work_projects WHERE status = 'published' ORDER BY archive_sort_order DESC"
    );

    return array_map('decode_work_project', $stmt->fetchAll());
}

function get_work_by_slug(string $slug): ?array
{
    $stmt = get_db()->prepare(
        "SELECT * FROM work_projects WHERE slug = ? AND status = 'published' LIMIT 1"
    );
    $stmt->execute([$slug]);
    $row = $stmt->fetch();

    return $row === false ? null : decode_work_project($row);
}

function get_next_work(int $currentId): ?array
{
    $pdo = get_db();

    $stmt = $pdo->prepare(
        "SELECT * FROM work_projects
         WHERE status = 'published' AND archive_sort_order < (
             SELECT archive_sort_order FROM work_projects WHERE id = ?
         )
         ORDER BY archive_sort_order DESC LIMIT 1"
    );
    $stmt->execute([$currentId]);
    $row = $stmt->fetch();

    if ($row === false) {
        // Reached the oldest project -- wrap around to the newest.
        $row = $pdo->query(
            "SELECT * FROM work_projects WHERE status = 'published' ORDER BY archive_sort_order DESC LIMIT 1"
        )->fetch();
    }

    return $row === false ? null : decode_work_project($row);
}

function decode_journal_entry(array $row): array
{
    $row['intro_paragraphs'] = json_decode($row['intro_paragraphs'], true);
    $row['subheading_paragraphs'] = json_decode($row['subheading_paragraphs'], true);
    $row['tags'] = json_decode($row['tags'], true);

    return $row;
}

function get_featured_journal(int $limit = 3): array
{
    $stmt = get_db()->prepare(
        "SELECT * FROM journal_entries WHERE status = 'published' AND featured_sort_order IS NOT NULL
         ORDER BY featured_sort_order LIMIT :limit"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return array_map('decode_journal_entry', $stmt->fetchAll());
}

function get_all_journal(): array
{
    $stmt = get_db()->query(
        "SELECT * FROM journal_entries WHERE status = 'published' ORDER BY archive_sort_order DESC"
    );

    return array_map('decode_journal_entry', $stmt->fetchAll());
}

function get_journal_by_slug(string $slug): ?array
{
    $stmt = get_db()->prepare(
        "SELECT * FROM journal_entries WHERE slug = ? AND status = 'published' LIMIT 1"
    );
    $stmt->execute([$slug]);
    $row = $stmt->fetch();

    return $row === false ? null : decode_journal_entry($row);
}

function get_next_journal(int $currentId): ?array
{
    $pdo = get_db();

    $stmt = $pdo->prepare(
        "SELECT * FROM journal_entries
         WHERE status = 'published' AND archive_sort_order < (
             SELECT archive_sort_order FROM journal_entries WHERE id = ?
         )
         ORDER BY archive_sort_order DESC LIMIT 1"
    );
    $stmt->execute([$currentId]);
    $row = $stmt->fetch();

    if ($row === false) {
        // Reached the oldest entry -- wrap around to the newest.
        $row = $pdo->query(
            "SELECT * FROM journal_entries WHERE status = 'published' ORDER BY archive_sort_order DESC LIMIT 1"
        )->fetch();
    }

    return $row === false ? null : decode_journal_entry($row);
}

/**
 * Renders an image slot: a real <img> when $path is set, otherwise the same
 * placeholder <div> every page already uses. $extraAttrs is a literal,
 * template-authored HTML attribute string (never user input) copied onto
 * whichever element is rendered, so parallax/data attributes survive the
 * placeholder-to-real-image swap unchanged.
 */
function img_or_placeholder(?string $path, string $placeholderText, string $extraClass = '', string $extraAttrs = ''): string
{
    $class = trim('img-placeholder grayscale ' . $extraClass);
    $attrs = $extraAttrs !== '' ? ' ' . $extraAttrs : '';

    if ($path === null || $path === '') {
        return '<div class="' . e($class) . '"' . $attrs . '>' . e($placeholderText) . '</div>';
    }

    return '<img class="' . e($class) . '" src="' . e(asset_url($path)) . '" alt="' . e($placeholderText) . '"' . $attrs . '>';
}
