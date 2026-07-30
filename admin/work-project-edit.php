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

$project = [
    'title' => '', 'slug' => '', 'tag' => '', 'year' => '',
    'card_image_path' => null, 'card_placeholder_text' => '',
    'lead' => '', 'hero_image_path' => null, 'hero_placeholder_text' => '',
    'synopsis_heading' => 'Synopsis', 'synopsis_paragraphs' => [],
    'credits' => [], 'tags' => [],
    'stills_eyebrow' => 'Stills', 'stills_heading' => 'From the field.', 'stills' => [],
    'is_featured' => 0, 'featured_sort_order' => 0, 'archive_sort_order' => 0,
    'status' => 'published',
];

if (!$isNew) {
    $stmt = $pdo->prepare('SELECT * FROM work_projects WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row === false) {
        http_response_code(404);
        exit('Project not found.');
    }

    $project = decode_work_project($row);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    try {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $tag = trim((string) ($_POST['tag'] ?? ''));
        $year = trim((string) ($_POST['year'] ?? ''));

        if ($title === '') {
            $errors[] = 'Title is required.';
        }
        if ($tag === '') {
            $errors[] = 'Tag is required.';
        }
        if ($year === '') {
            $errors[] = 'Year is required.';
        }

        $slug = slugify($slugInput !== '' ? $slugInput : $title);

        $stmt = $pdo->prepare('SELECT id FROM work_projects WHERE slug = ? AND id != ?');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch() !== false) {
            $errors[] = 'That slug is already used by another project. Choose a different one.';
        }

        $synopsisParagraphs = [];
        foreach ($_POST['synopsis_paragraphs'] ?? [] as $row) {
            $text = trim((string) ($row['text'] ?? ''));
            if ($text !== '') {
                $synopsisParagraphs[] = ['text' => $text];
            }
        }

        $credits = [];
        foreach ($_POST['credits'] ?? [] as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($label !== '' && $value !== '') {
                $credits[] = ['label' => $label, 'value' => $value];
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

        $stillsFiles = reshape_files('stills');
        $stills = [];
        foreach ($_POST['stills'] ?? [] as $index => $row) {
            $placeholder = trim((string) ($row['placeholder'] ?? ''));
            if ($placeholder === '') {
                continue;
            }
            $existingPath = trim((string) ($row['existing_image_path'] ?? ''));
            $uploadedPath = process_uploaded_file($stillsFiles[$index]['image'] ?? null);
            if ($uploadedPath !== null && $existingPath !== '') {
                delete_uploaded_image($existingPath);
            }
            $stills[] = [
                'image_path' => $uploadedPath ?? ($existingPath !== '' ? $existingPath : null),
                'placeholder' => $placeholder,
            ];
        }

        $existingCardImage = $project['card_image_path'] ?? null;
        $cardImagePath = process_uploaded_file($_FILES['card_image'] ?? null);
        if ($cardImagePath !== null && $existingCardImage) {
            delete_uploaded_image($existingCardImage);
        }

        $existingHeroImage = $project['hero_image_path'] ?? null;
        $heroImagePath = process_uploaded_file($_FILES['hero_image'] ?? null);
        if ($heroImagePath !== null && $existingHeroImage) {
            delete_uploaded_image($existingHeroImage);
        }

        $data = [
            'slug' => $slug,
            'title' => $title,
            'tag' => $tag,
            'year' => $year,
            'card_image_path' => $cardImagePath ?? $existingCardImage,
            'card_placeholder_text' => trim((string) ($_POST['card_placeholder_text'] ?? '')),
            'lead' => trim((string) ($_POST['lead'] ?? '')),
            'hero_image_path' => $heroImagePath ?? $existingHeroImage,
            'hero_placeholder_text' => trim((string) ($_POST['hero_placeholder_text'] ?? '')),
            'synopsis_heading' => trim((string) ($_POST['synopsis_heading'] ?? '')) ?: 'Synopsis',
            'synopsis_paragraphs' => json_encode($synopsisParagraphs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'credits' => json_encode($credits, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'tags' => json_encode($tags, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'stills_eyebrow' => trim((string) ($_POST['stills_eyebrow'] ?? '')) ?: 'Stills',
            'stills_heading' => trim((string) ($_POST['stills_heading'] ?? '')) ?: 'From the field.',
            'stills' => json_encode($stills, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'featured_sort_order' => (int) ($_POST['featured_sort_order'] ?? 0),
            'archive_sort_order' => (int) ($_POST['archive_sort_order'] ?? 0),
            'status' => in_array($_POST['status'] ?? '', ['published', 'draft'], true) ? $_POST['status'] : 'published',
        ];

        // Re-populate $project so the form redisplays what was submitted,
        // whether or not validation passed.
        $project = array_merge($project, $data, [
            'synopsis_paragraphs' => $synopsisParagraphs,
            'credits' => $credits,
            'tags' => $tags,
            'stills' => $stills,
        ]);

        if ($errors === []) {
            if ($isNew) {
                $columns = array_keys($data);
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $stmt = $pdo->prepare(
                    'INSERT INTO work_projects (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')'
                );
                $stmt->execute(array_values($data));
                $id = (int) $pdo->lastInsertId();
            } else {
                $setSql = implode(', ', array_map(static fn($col) => "$col = ?", array_keys($data)));
                $stmt = $pdo->prepare("UPDATE work_projects SET $setSql WHERE id = ?");
                $stmt->execute([...array_values($data), $id]);
            }

            set_flash('success', 'Project saved.');
            header('Location: /admin/work-projects.php');
            exit;
        }
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    }
}

$pageTitle = $isNew ? 'New work project' : 'Edit: ' . $project['title'];
require __DIR__ . '/includes/header.php';
?>
<h1><?= $isNew ? 'New work project' : e('Edit: ' . $project['title']) ?></h1>

<?php foreach ($errors as $error): ?>
<div class="admin-flash admin-flash-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="admin-form">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int) $id ?>">

<fieldset>
<legend>Basics</legend>
<div class="field-row">
<div class="field"><label>Title</label><input class="input" name="title" value="<?= e($project['title']) ?>" required></div>
<div class="field"><label>Slug (optional — auto-generated from title if blank)</label><input class="input" name="slug" value="<?= e($project['slug']) ?>" placeholder="e.g. in-plain-sight"></div>
</div>
<div class="field-row">
<div class="field"><label>Tag</label><input class="input" name="tag" value="<?= e($project['tag']) ?>" required></div>
<div class="field"><label>Year</label><input class="input" name="year" value="<?= e($project['year']) ?>" required></div>
</div>
<div class="field-row">
<div class="field"><label>Status</label>
<select class="input" name="status">
<option value="published" <?= $project['status'] === 'published' ? 'selected' : '' ?>>Published</option>
<option value="draft" <?= $project['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
</select>
</div>
<div class="field admin-checkbox-field" style="align-self:flex-end">
<input type="checkbox" id="is_featured" name="is_featured" value="1" <?= $project['is_featured'] ? 'checked' : '' ?>>
<label for="is_featured">Show in homepage "Selected Work"</label>
</div>
</div>
<div class="field-row">
<div class="field"><label>Featured order (lower = earlier, when featured)</label><input class="input" type="number" name="featured_sort_order" value="<?= (int) $project['featured_sort_order'] ?>"></div>
<div class="field"><label>Archive order (higher = appears first)</label><input class="input" type="number" name="archive_sort_order" value="<?= (int) $project['archive_sort_order'] ?>"></div>
</div>
</fieldset>

<fieldset>
<legend>Card &amp; hero image</legend>
<?php if (!empty($project['card_image_path'])): ?>
<div class="admin-current-image"><img src="<?= e(asset_url($project['card_image_path'])) ?>" alt=""> Current card image</div>
<?php endif; ?>
<div class="field"><label>Card image (optional)</label><input class="input" type="file" name="card_image" accept="image/jpeg,image/png,image/webp"></div>
<div class="field"><label>Card placeholder text (used if no image)</label><input class="input" name="card_placeholder_text" value="<?= e($project['card_placeholder_text']) ?>"></div>

<?php if (!empty($project['hero_image_path'])): ?>
<div class="admin-current-image"><img src="<?= e(asset_url($project['hero_image_path'])) ?>" alt=""> Current hero image</div>
<?php endif; ?>
<div class="field"><label>Hero image (optional)</label><input class="input" type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"></div>
<div class="field"><label>Hero placeholder text (used if no image)</label><input class="input" name="hero_placeholder_text" value="<?= e($project['hero_placeholder_text']) ?>"></div>

<div class="field"><label>Lead paragraph (shown under the title)</label><textarea class="input" name="lead"><?= e($project['lead']) ?></textarea></div>
</fieldset>

<fieldset>
<legend>Tags</legend>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($project['tags'] as $i => $tag): ?>
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
<legend>Synopsis</legend>
<div class="field"><label>Synopsis heading</label><input class="input" name="synopsis_heading" value="<?= e($project['synopsis_heading']) ?>"></div>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($project['synopsis_paragraphs'] as $i => $para): ?>
<div class="repeater-item">
<textarea class="input" name="synopsis_paragraphs[<?= $i ?>][text]" placeholder="Paragraph text"><?= e($para['text']) ?></textarea>
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost repeater-add">+ Add paragraph</button>
<template>
<div class="repeater-item">
<textarea class="input" name="synopsis_paragraphs[__INDEX__][text]" placeholder="Paragraph text"></textarea>
<button type="button" class="repeater-remove">Remove</button>
</div>
</template>
</div>
</fieldset>

<fieldset>
<legend>Credits</legend>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($project['credits'] as $i => $credit): ?>
<div class="repeater-item">
<input class="input" name="credits[<?= $i ?>][label]" value="<?= e($credit['label']) ?>" placeholder="Label (e.g. Director)">
<input class="input" name="credits[<?= $i ?>][value]" value="<?= e($credit['value']) ?>" placeholder="Value">
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost repeater-add">+ Add credit</button>
<template>
<div class="repeater-item">
<input class="input" name="credits[__INDEX__][label]" placeholder="Label (e.g. Director)">
<input class="input" name="credits[__INDEX__][value]" placeholder="Value">
<button type="button" class="repeater-remove">Remove</button>
</div>
</template>
</div>
</fieldset>

<fieldset>
<legend>Stills</legend>
<div class="field-row">
<div class="field"><label>Stills eyebrow</label><input class="input" name="stills_eyebrow" value="<?= e($project['stills_eyebrow']) ?>"></div>
<div class="field"><label>Stills heading</label><input class="input" name="stills_heading" value="<?= e($project['stills_heading']) ?>"></div>
</div>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($project['stills'] as $i => $still): ?>
<div class="repeater-item">
<?php if (!empty($still['image_path'])): ?>
<div class="admin-current-image"><img src="<?= e(asset_url($still['image_path'])) ?>" alt=""></div>
<?php endif; ?>
<input type="hidden" name="stills[<?= $i ?>][existing_image_path]" value="<?= e($still['image_path'] ?? '') ?>">
<input class="input" name="stills[<?= $i ?>][placeholder]" value="<?= e($still['placeholder']) ?>" placeholder="Caption / placeholder text">
<input class="input" type="file" name="stills[<?= $i ?>][image]" accept="image/jpeg,image/png,image/webp">
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost repeater-add">+ Add still</button>
<template>
<div class="repeater-item">
<input type="hidden" name="stills[__INDEX__][existing_image_path]" value="">
<input class="input" name="stills[__INDEX__][placeholder]" placeholder="Caption / placeholder text">
<input class="input" type="file" name="stills[__INDEX__][image]" accept="image/jpeg,image/png,image/webp">
<button type="button" class="repeater-remove">Remove</button>
</div>
</template>
</div>
</fieldset>

<button type="submit" class="btn btn-primary">Save project</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
