<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/slug.php';

$adminUser = require_login();
$pdo = get_db();

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
$isNew = $id === 0;
$errors = [];

$entry = [
    'title' => '', 'slug' => '', 'kicker' => '', 'card_body' => '',
    'published_on' => date('Y-m-d'), 'subtitle' => '',
    'hero_image_path' => null, 'hero_placeholder_text' => '',
    'intro_paragraphs' => [],
    'inline_image_path' => null, 'inline_placeholder_text' => '',
    'subheading' => '', 'subheading_paragraphs' => [],
    'tags' => [],
    'archive_sort_order' => 0, 'featured_sort_order' => null,
    'status' => 'published',
];

if (!$isNew) {
    $stmt = $pdo->prepare('SELECT * FROM journal_entries WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row === false) {
        http_response_code(404);
        exit('Entry not found.');
    }

    $entry = decode_journal_entry($row);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    try {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $kicker = trim((string) ($_POST['kicker'] ?? ''));
        $cardBody = trim((string) ($_POST['card_body'] ?? ''));
        $publishedOn = trim((string) ($_POST['published_on'] ?? ''));

        if ($title === '') {
            $errors[] = 'Title is required.';
        }
        if ($kicker === '') {
            $errors[] = 'Kicker is required.';
        }
        if ($cardBody === '') {
            $errors[] = 'Card teaser text is required.';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishedOn)) {
            $errors[] = 'Published date is required.';
        }

        $slug = slugify($slugInput !== '' ? $slugInput : $title);

        $stmt = $pdo->prepare('SELECT id FROM journal_entries WHERE slug = ? AND id != ?');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch() !== false) {
            $errors[] = 'That slug is already used by another entry. Choose a different one.';
        }

        $introParagraphs = [];
        foreach ($_POST['intro_paragraphs'] ?? [] as $row) {
            $text = trim((string) ($row['text'] ?? ''));
            if ($text !== '') {
                $introParagraphs[] = ['text' => $text];
            }
        }

        $subheadingParagraphs = [];
        foreach ($_POST['subheading_paragraphs'] ?? [] as $row) {
            $text = trim((string) ($row['text'] ?? ''));
            if ($text !== '') {
                $subheadingParagraphs[] = ['text' => $text];
            }
        }

        $tags = [];
        foreach ($_POST['tags'] ?? [] as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $variant = (string) ($row['variant'] ?? 'accent');
            if (!in_array($variant, ['accent', 'neutral', 'outline'], true)) {
                $variant = 'accent';
            }
            if ($label !== '') {
                $tags[] = ['label' => $label, 'variant' => $variant];
            }
        }

        $existingHeroImage = $entry['hero_image_path'] ?? null;
        $heroImagePath = process_uploaded_file($_FILES['hero_image'] ?? null);
        if ($heroImagePath !== null && $existingHeroImage) {
            delete_uploaded_image($existingHeroImage);
        }

        $existingInlineImage = $entry['inline_image_path'] ?? null;
        $inlineImagePath = process_uploaded_file($_FILES['inline_image'] ?? null);
        if ($inlineImagePath !== null && $existingInlineImage) {
            delete_uploaded_image($existingInlineImage);
        }

        $isFeatured = isset($_POST['is_featured']);
        $featuredSortOrder = $isFeatured ? (int) ($_POST['featured_sort_order'] ?? 0) : null;

        $data = [
            'slug' => $slug,
            'title' => $title,
            'kicker' => $kicker,
            'card_body' => $cardBody,
            'published_on' => $publishedOn,
            'subtitle' => trim((string) ($_POST['subtitle'] ?? '')),
            'hero_image_path' => $heroImagePath ?? $existingHeroImage,
            'hero_placeholder_text' => trim((string) ($_POST['hero_placeholder_text'] ?? '')),
            'intro_paragraphs' => json_encode($introParagraphs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'inline_image_path' => $inlineImagePath ?? $existingInlineImage,
            'inline_placeholder_text' => trim((string) ($_POST['inline_placeholder_text'] ?? '')),
            'subheading' => trim((string) ($_POST['subheading'] ?? '')),
            'subheading_paragraphs' => json_encode($subheadingParagraphs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'tags' => json_encode($tags, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'archive_sort_order' => (int) ($_POST['archive_sort_order'] ?? 0),
            'featured_sort_order' => $featuredSortOrder,
            'status' => in_array($_POST['status'] ?? '', ['published', 'draft'], true) ? $_POST['status'] : 'published',
        ];

        $entry = array_merge($entry, $data, [
            'intro_paragraphs' => $introParagraphs,
            'subheading_paragraphs' => $subheadingParagraphs,
            'tags' => $tags,
        ]);

        if ($errors === []) {
            if ($isNew) {
                $columns = array_keys($data);
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $stmt = $pdo->prepare(
                    'INSERT INTO journal_entries (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')'
                );
                $stmt->execute(array_values($data));
                $id = (int) $pdo->lastInsertId();
            } else {
                $setSql = implode(', ', array_map(static fn($col) => "$col = ?", array_keys($data)));
                $stmt = $pdo->prepare("UPDATE journal_entries SET $setSql WHERE id = ?");
                $stmt->execute([...array_values($data), $id]);
            }

            set_flash('success', 'Entry saved.');
            header('Location: /admin/journal-entries.php');
            exit;
        }
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    }
}

$pageTitle = $isNew ? 'New journal entry' : 'Edit: ' . $entry['title'];
require __DIR__ . '/includes/header.php';
?>
<h1><?= $isNew ? 'New journal entry' : e('Edit: ' . $entry['title']) ?></h1>

<?php foreach ($errors as $error): ?>
<div class="admin-flash admin-flash-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="admin-form">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int) $id ?>">

<fieldset>
<legend>Basics</legend>
<div class="field-row">
<div class="field"><label>Title</label><input class="input" name="title" value="<?= e($entry['title']) ?>" required></div>
<div class="field"><label>Slug (optional — auto-generated from title if blank)</label><input class="input" name="slug" value="<?= e($entry['slug']) ?>" placeholder="e.g. on-filming-what-cant-be-seen"></div>
</div>
<div class="field-row">
<div class="field"><label>Kicker</label><input class="input" name="kicker" value="<?= e($entry['kicker']) ?>" required></div>
<div class="field"><label>Published date</label><input class="input" type="date" name="published_on" value="<?= e($entry['published_on']) ?>" required></div>
</div>
<div class="field"><label>Card teaser text (shown on the archive/homepage cards)</label><textarea class="input" name="card_body" required><?= e($entry['card_body']) ?></textarea></div>
<div class="field-row">
<div class="field"><label>Status</label>
<select class="input" name="status">
<option value="published" <?= $entry['status'] === 'published' ? 'selected' : '' ?>>Published</option>
<option value="draft" <?= $entry['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
</select>
</div>
<div class="field admin-checkbox-field" style="align-self:flex-end">
<input type="checkbox" id="is_featured" name="is_featured" value="1" <?= $entry['featured_sort_order'] !== null ? 'checked' : '' ?>>
<label for="is_featured">Show in homepage "Journal" section</label>
</div>
</div>
<div class="field-row">
<div class="field"><label>Featured order (lower = earlier, when featured)</label><input class="input" type="number" name="featured_sort_order" value="<?= (int) ($entry['featured_sort_order'] ?? 0) ?>"></div>
<div class="field"><label>Archive order (higher = appears first)</label><input class="input" type="number" name="archive_sort_order" value="<?= (int) $entry['archive_sort_order'] ?>"></div>
</div>
</fieldset>

<fieldset>
<legend>Hero &amp; subtitle</legend>
<?php if (!empty($entry['hero_image_path'])): ?>
<div class="admin-current-image"><img src="<?= e(asset_url($entry['hero_image_path'])) ?>" alt=""> Current hero image</div>
<?php endif; ?>
<div class="field"><label>Hero image (optional)</label><input class="input" type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"></div>
<div class="field"><label>Hero placeholder text (used if no image)</label><input class="input" name="hero_placeholder_text" value="<?= e($entry['hero_placeholder_text']) ?>"></div>
<div class="field"><label>Subtitle</label><textarea class="input" name="subtitle"><?= e($entry['subtitle']) ?></textarea></div>
</fieldset>

<fieldset>
<legend>Tags</legend>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($entry['tags'] as $i => $tag): ?>
<div class="repeater-item">
<input class="input" name="tags[<?= $i ?>][label]" value="<?= e($tag['label']) ?>" placeholder="Label">
<select name="tags[<?= $i ?>][variant]">
<option value="accent" <?= $tag['variant'] === 'accent' ? 'selected' : '' ?>>Accent</option>
<option value="neutral" <?= $tag['variant'] === 'neutral' ? 'selected' : '' ?>>Neutral</option>
<option value="outline" <?= $tag['variant'] === 'outline' ? 'selected' : '' ?>>Outline</option>
</select>
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost repeater-add">+ Add tag</button>
<template>
<div class="repeater-item">
<input class="input" name="tags[__INDEX__][label]" placeholder="Label">
<select name="tags[__INDEX__][variant]">
<option value="accent">Accent</option>
<option value="neutral">Neutral</option>
<option value="outline">Outline</option>
</select>
<button type="button" class="repeater-remove">Remove</button>
</div>
</template>
</div>
</fieldset>

<fieldset>
<legend>Article body</legend>
<label>Intro paragraphs</label>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($entry['intro_paragraphs'] as $i => $para): ?>
<div class="repeater-item">
<textarea class="input" name="intro_paragraphs[<?= $i ?>][text]" placeholder="Paragraph text"><?= e($para['text']) ?></textarea>
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost repeater-add">+ Add paragraph</button>
<template>
<div class="repeater-item">
<textarea class="input" name="intro_paragraphs[__INDEX__][text]" placeholder="Paragraph text"></textarea>
<button type="button" class="repeater-remove">Remove</button>
</div>
</template>
</div>

<?php if (!empty($entry['inline_image_path'])): ?>
<div class="admin-current-image"><img src="<?= e(asset_url($entry['inline_image_path'])) ?>" alt=""> Current inline image</div>
<?php endif; ?>
<div class="field"><label>Inline image, shown after the intro paragraphs (optional)</label><input class="input" type="file" name="inline_image" accept="image/jpeg,image/png,image/webp"></div>
<div class="field"><label>Inline image placeholder text (used if no image)</label><input class="input" name="inline_placeholder_text" value="<?= e($entry['inline_placeholder_text']) ?>"></div>

<div class="field"><label>Subheading</label><input class="input" name="subheading" value="<?= e($entry['subheading']) ?>"></div>
<label>Subheading paragraphs</label>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($entry['subheading_paragraphs'] as $i => $para): ?>
<div class="repeater-item">
<textarea class="input" name="subheading_paragraphs[<?= $i ?>][text]" placeholder="Paragraph text"><?= e($para['text']) ?></textarea>
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost repeater-add">+ Add paragraph</button>
<template>
<div class="repeater-item">
<textarea class="input" name="subheading_paragraphs[__INDEX__][text]" placeholder="Paragraph text"></textarea>
<button type="button" class="repeater-remove">Remove</button>
</div>
</template>
</div>
</fieldset>

<button type="submit" class="btn btn-primary">Save entry</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
