<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/settings-helper.php';

$adminUser = require_login();
$pdo = get_db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    try {
        // ---- Nav & brand ----
        save_settings_section('home', 'nav', [
            'brand' => trim((string) ($_POST['nav_brand'] ?? '')),
            'brandHref' => trim((string) ($_POST['nav_brandHref'] ?? '')),
            'navCtaLabel' => trim((string) ($_POST['nav_navCtaLabel'] ?? '')),
            'navCtaHref' => trim((string) ($_POST['nav_navCtaHref'] ?? '')),
        ]);

        $navLinkRows = [];
        foreach ($_POST['nav_links'] ?? [] as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $href = trim((string) ($row['href'] ?? ''));
            $linkKey = trim((string) ($row['link_key'] ?? ''));
            if ($label === '' || $href === '' || $linkKey === '') {
                continue;
            }
            $navLinkRows[] = [$linkKey, $label, $href, count($navLinkRows) + 1, 1];
        }
        // The "journal" nav link is managed by the checkbox in the Journal
        // section below, not this repeater, so its row is added separately.
        $journalEnabled = isset($_POST['journal_section_enabled']) ? 1 : 0;
        $navLinkRows[] = ['journal', 'Journal', '#journal', count($navLinkRows) + 1, $journalEnabled];
        replace_repeater_rows('nav_links', ['link_key', 'label', 'href', 'sort_order', 'enabled'], $navLinkRows);

        // ---- Hero ----
        $stmt = $pdo->prepare("SELECT field_value FROM site_settings WHERE page='home' AND section='hero' AND field_key='portraitImagePath'");
        $stmt->execute();
        $existingHeroImage = $stmt->fetchColumn() ?: null;

        $heroImagePath = process_uploaded_file($_FILES['hero_portrait_image'] ?? null);
        if ($heroImagePath !== null) {
            delete_uploaded_image($existingHeroImage ?: null);
        }

        save_settings_section('home', 'hero', [
            'eyebrow' => trim((string) ($_POST['hero_eyebrow'] ?? '')),
            'titleLine1' => trim((string) ($_POST['hero_titleLine1'] ?? '')),
            'titleLine2' => trim((string) ($_POST['hero_titleLine2'] ?? '')),
            'subtitle' => trim((string) ($_POST['hero_subtitle'] ?? '')),
            'ctaPrimaryLabel' => trim((string) ($_POST['hero_ctaPrimaryLabel'] ?? '')),
            'ctaPrimaryHref' => trim((string) ($_POST['hero_ctaPrimaryHref'] ?? '')),
            'ctaSecondaryLabel' => trim((string) ($_POST['hero_ctaSecondaryLabel'] ?? '')),
            'ctaSecondaryHref' => trim((string) ($_POST['hero_ctaSecondaryHref'] ?? '')),
            'scrollLabel' => trim((string) ($_POST['hero_scrollLabel'] ?? '')),
            'recLabel' => trim((string) ($_POST['hero_recLabel'] ?? '')),
            'portraitPlaceholder' => trim((string) ($_POST['hero_portraitPlaceholder'] ?? '')),
            'portraitImagePath' => $heroImagePath ?? ($existingHeroImage ?: ''),
        ]);

        // ---- About ----
        $stmt = $pdo->prepare("SELECT field_value FROM site_settings WHERE page='home' AND section='about' AND field_key='portraitImagePath'");
        $stmt->execute();
        $existingAboutImage = $stmt->fetchColumn() ?: null;

        $aboutImagePath = process_uploaded_file($_FILES['about_portrait_image'] ?? null);
        if ($aboutImagePath !== null) {
            delete_uploaded_image($existingAboutImage ?: null);
        }

        save_settings_section('home', 'about', [
            'eyebrow' => trim((string) ($_POST['about_eyebrow'] ?? '')),
            'heading' => trim((string) ($_POST['about_heading'] ?? '')),
            'paragraph1' => trim((string) ($_POST['about_paragraph1'] ?? '')),
            'paragraph2' => trim((string) ($_POST['about_paragraph2'] ?? '')),
            'portraitPlaceholder' => trim((string) ($_POST['about_portraitPlaceholder'] ?? '')),
            'portraitImagePath' => $aboutImagePath ?? ($existingAboutImage ?: ''),
        ]);

        $statRows = [];
        foreach ($_POST['stats'] ?? [] as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $statRows[] = [(int) ($row['value'] ?? 0), $label, count($statRows) + 1];
        }
        replace_repeater_rows('stats', ['value', 'label', 'sort_order'], $statRows);

        // ---- Services ----
        save_settings_section('home', 'services_intro', [
            'eyebrow' => trim((string) ($_POST['services_eyebrow'] ?? '')),
            'heading' => trim((string) ($_POST['services_heading'] ?? '')),
        ]);

        $serviceRows = [];
        foreach ($_POST['services'] ?? [] as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));
            if ($title === '' || $description === '') {
                continue;
            }
            $serviceRows[] = [$title, $description, count($serviceRows) + 1];
        }
        replace_repeater_rows('services', ['title', 'description', 'sort_order'], $serviceRows);

        // ---- Work section intro ----
        save_settings_section('home', 'work_intro', [
            'eyebrow' => trim((string) ($_POST['work_eyebrow'] ?? '')),
            'heading' => trim((string) ($_POST['work_heading'] ?? '')),
            'description' => trim((string) ($_POST['work_description'] ?? '')),
            'viewAllLabel' => trim((string) ($_POST['work_viewAllLabel'] ?? '')),
            'viewAllHref' => trim((string) ($_POST['work_viewAllHref'] ?? '')),
        ]);

        // ---- Journal section intro ----
        save_settings_section('home', 'journal_intro', [
            'eyebrow' => trim((string) ($_POST['journal_eyebrow'] ?? '')),
            'heading' => trim((string) ($_POST['journal_heading'] ?? '')),
            'viewAllLabel' => trim((string) ($_POST['journal_viewAllLabel'] ?? '')),
            'viewAllHref' => trim((string) ($_POST['journal_viewAllHref'] ?? '')),
            'section_enabled' => $journalEnabled ? '1' : '0',
        ]);

        // ---- Contact ----
        save_settings_section('home', 'contact', [
            'eyebrow' => trim((string) ($_POST['contact_eyebrow'] ?? '')),
            'heading' => trim((string) ($_POST['contact_heading'] ?? '')),
            'email' => trim((string) ($_POST['contact_email'] ?? '')),
            'formNameLabel' => trim((string) ($_POST['contact_formNameLabel'] ?? '')),
            'formNamePlaceholder' => trim((string) ($_POST['contact_formNamePlaceholder'] ?? '')),
            'formEmailLabel' => trim((string) ($_POST['contact_formEmailLabel'] ?? '')),
            'formEmailPlaceholder' => trim((string) ($_POST['contact_formEmailPlaceholder'] ?? '')),
            'formDetailsLabel' => trim((string) ($_POST['contact_formDetailsLabel'] ?? '')),
            'formDetailsPlaceholder' => trim((string) ($_POST['contact_formDetailsPlaceholder'] ?? '')),
            'submitLabel' => trim((string) ($_POST['contact_submitLabel'] ?? '')),
        ]);

        $socialRows = [];
        foreach ($_POST['socials'] ?? [] as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $href = trim((string) ($row['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }
            $socialRows[] = [$label, $href, count($socialRows) + 1];
        }
        replace_repeater_rows('socials', ['label', 'href', 'sort_order'], $socialRows);

        // ---- Footer ----
        save_settings_section('home', 'footer', [
            'copyright' => trim((string) ($_POST['footer_copyright'] ?? '')),
            'backToTopLabel' => trim((string) ($_POST['footer_backToTopLabel'] ?? '')),
        ]);

        set_flash('success', 'Home page updated.');
        header('Location: /admin/settings-home.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $pdo->inTransaction() && $pdo->rollBack();
        $errors[] = $e->getMessage();
    }
}

$nav = get_section('home', 'nav');
$hero = get_section('home', 'hero');
$about = get_section('home', 'about');
$servicesIntro = get_section('home', 'services_intro');
$workIntro = get_section('home', 'work_intro');
$journalIntro = get_section('home', 'journal_intro');
$contact = get_section('home', 'contact');
$footer = get_section('home', 'footer');

$navLinks = $pdo->query("SELECT link_key, label, href, enabled FROM nav_links WHERE link_key != 'journal' ORDER BY sort_order")->fetchAll();
$journalNavRow = $pdo->query("SELECT enabled FROM nav_links WHERE link_key = 'journal' LIMIT 1")->fetch();
$stats = $pdo->query('SELECT value, label FROM stats ORDER BY sort_order')->fetchAll();
$services = $pdo->query('SELECT title, description FROM services ORDER BY sort_order')->fetchAll();
$socials = $pdo->query('SELECT label, href FROM socials ORDER BY sort_order')->fetchAll();

$pageTitle = 'Home page';
require __DIR__ . '/includes/header.php';
?>
<h1>Home page</h1>

<?php foreach ($errors as $error): ?>
<div class="admin-flash admin-flash-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="admin-form">
<?= csrf_field() ?>

<fieldset>
<legend>Navigation &amp; brand</legend>
<div class="field-row">
<div class="field"><label>Brand text</label><input class="input" name="nav_brand" value="<?= e($nav['brand'] ?? '') ?>"></div>
<div class="field"><label>Brand link (href)</label><input class="input" name="nav_brandHref" value="<?= e($nav['brandHref'] ?? '') ?>"></div>
</div>
<div class="field-row">
<div class="field"><label>CTA button label</label><input class="input" name="nav_navCtaLabel" value="<?= e($nav['navCtaLabel'] ?? '') ?>"></div>
<div class="field"><label>CTA button link (href)</label><input class="input" name="nav_navCtaHref" value="<?= e($nav['navCtaHref'] ?? '') ?>"></div>
</div>

<label>Nav links</label>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($navLinks as $link): ?>
<div class="repeater-item">
<input type="hidden" name="nav_links[<?= e($link['link_key']) ?>][link_key]" value="<?= e($link['link_key']) ?>">
<input class="input" name="nav_links[<?= e($link['link_key']) ?>][label]" value="<?= e($link['label']) ?>" placeholder="Label">
<input class="input" name="nav_links[<?= e($link['link_key']) ?>][href]" value="<?= e($link['href']) ?>" placeholder="#section">
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
</div>
<p class="help-text">The "Journal" nav link is controlled by the checkbox in the Journal section below.</p>
</fieldset>

<fieldset>
<legend>Hero</legend>
<?php if (!empty($hero['portraitImagePath'])): ?>
<div class="admin-current-image"><img src="<?= e(asset_url($hero['portraitImagePath'])) ?>" alt=""> Current portrait image</div>
<?php endif; ?>
<div class="field"><label>Portrait image (optional)</label><input class="input" type="file" name="hero_portrait_image" accept="image/jpeg,image/png,image/webp"></div>
<div class="field"><label>Portrait placeholder text (used if no image)</label><input class="input" name="hero_portraitPlaceholder" value="<?= e($hero['portraitPlaceholder'] ?? '') ?>"></div>
<div class="field"><label>Eyebrow</label><input class="input" name="hero_eyebrow" value="<?= e($hero['eyebrow'] ?? '') ?>"></div>
<div class="field-row">
<div class="field"><label>Title line 1</label><input class="input" name="hero_titleLine1" value="<?= e($hero['titleLine1'] ?? '') ?>"></div>
<div class="field"><label>Title line 2</label><input class="input" name="hero_titleLine2" value="<?= e($hero['titleLine2'] ?? '') ?>"></div>
</div>
<div class="field"><label>Subtitle</label><textarea class="input" name="hero_subtitle"><?= e($hero['subtitle'] ?? '') ?></textarea></div>
<div class="field-row">
<div class="field"><label>Primary CTA label</label><input class="input" name="hero_ctaPrimaryLabel" value="<?= e($hero['ctaPrimaryLabel'] ?? '') ?>"></div>
<div class="field"><label>Primary CTA link</label><input class="input" name="hero_ctaPrimaryHref" value="<?= e($hero['ctaPrimaryHref'] ?? '') ?>"></div>
</div>
<div class="field-row">
<div class="field"><label>Secondary CTA label</label><input class="input" name="hero_ctaSecondaryLabel" value="<?= e($hero['ctaSecondaryLabel'] ?? '') ?>"></div>
<div class="field"><label>Secondary CTA link</label><input class="input" name="hero_ctaSecondaryHref" value="<?= e($hero['ctaSecondaryHref'] ?? '') ?>"></div>
</div>
<div class="field-row">
<div class="field"><label>Scroll cue label</label><input class="input" name="hero_scrollLabel" value="<?= e($hero['scrollLabel'] ?? '') ?>"></div>
<div class="field"><label>REC label</label><input class="input" name="hero_recLabel" value="<?= e($hero['recLabel'] ?? '') ?>"></div>
</div>
</fieldset>

<fieldset>
<legend>About</legend>
<?php if (!empty($about['portraitImagePath'])): ?>
<div class="admin-current-image"><img src="<?= e(asset_url($about['portraitImagePath'])) ?>" alt=""> Current portrait image</div>
<?php endif; ?>
<div class="field"><label>Portrait image (optional)</label><input class="input" type="file" name="about_portrait_image" accept="image/jpeg,image/png,image/webp"></div>
<div class="field"><label>Portrait placeholder text (used if no image)</label><input class="input" name="about_portraitPlaceholder" value="<?= e($about['portraitPlaceholder'] ?? '') ?>"></div>
<div class="field"><label>Eyebrow</label><input class="input" name="about_eyebrow" value="<?= e($about['eyebrow'] ?? '') ?>"></div>
<div class="field"><label>Heading</label><input class="input" name="about_heading" value="<?= e($about['heading'] ?? '') ?>"></div>
<div class="field"><label>Paragraph 1</label><textarea class="input" name="about_paragraph1"><?= e($about['paragraph1'] ?? '') ?></textarea></div>
<div class="field"><label>Paragraph 2</label><textarea class="input" name="about_paragraph2"><?= e($about['paragraph2'] ?? '') ?></textarea></div>

<label>Stats</label>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($stats as $i => $stat): ?>
<div class="repeater-item">
<input class="input" type="number" name="stats[<?= $i ?>][value]" value="<?= (int) $stat['value'] ?>" placeholder="Value" style="max-width:100px">
<input class="input" name="stats[<?= $i ?>][label]" value="<?= e($stat['label']) ?>" placeholder="Label">
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost repeater-add">+ Add stat</button>
<template>
<div class="repeater-item">
<input class="input" type="number" name="stats[__INDEX__][value]" placeholder="Value" style="max-width:100px">
<input class="input" name="stats[__INDEX__][label]" placeholder="Label">
<button type="button" class="repeater-remove">Remove</button>
</div>
</template>
</div>
</fieldset>

<fieldset>
<legend>Services</legend>
<div class="field"><label>Eyebrow</label><input class="input" name="services_eyebrow" value="<?= e($servicesIntro['eyebrow'] ?? '') ?>"></div>
<div class="field"><label>Heading</label><input class="input" name="services_heading" value="<?= e($servicesIntro['heading'] ?? '') ?>"></div>

<label>Service items</label>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($services as $i => $svc): ?>
<div class="repeater-item">
<input class="input" name="services[<?= $i ?>][title]" value="<?= e($svc['title']) ?>" placeholder="Title">
<input class="input" name="services[<?= $i ?>][description]" value="<?= e($svc['description']) ?>" placeholder="Description">
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost repeater-add">+ Add service</button>
<template>
<div class="repeater-item">
<input class="input" name="services[__INDEX__][title]" placeholder="Title">
<input class="input" name="services[__INDEX__][description]" placeholder="Description">
<button type="button" class="repeater-remove">Remove</button>
</div>
</template>
</div>
</fieldset>

<fieldset>
<legend>Work section intro</legend>
<p class="help-text">The individual work items shown here come from <a href="/admin/work-projects.php">Work projects</a>.</p>
<div class="field"><label>Eyebrow</label><input class="input" name="work_eyebrow" value="<?= e($workIntro['eyebrow'] ?? '') ?>"></div>
<div class="field"><label>Heading</label><input class="input" name="work_heading" value="<?= e($workIntro['heading'] ?? '') ?>"></div>
<div class="field"><label>Description</label><input class="input" name="work_description" value="<?= e($workIntro['description'] ?? '') ?>"></div>
<div class="field-row">
<div class="field"><label>"View all" label</label><input class="input" name="work_viewAllLabel" value="<?= e($workIntro['viewAllLabel'] ?? '') ?>"></div>
<div class="field"><label>"View all" link</label><input class="input" name="work_viewAllHref" value="<?= e($workIntro['viewAllHref'] ?? '') ?>"></div>
</div>
</fieldset>

<fieldset>
<legend>Journal section intro</legend>
<p class="help-text">The individual entries shown here come from <a href="/admin/journal-entries.php">Journal entries</a>.</p>
<div class="admin-checkbox-field">
<input type="checkbox" id="journal_section_enabled" name="journal_section_enabled" value="1" <?= ($journalNavRow['enabled'] ?? 1) ? 'checked' : '' ?>>
<label for="journal_section_enabled">Show the Journal section on the homepage</label>
</div>
<div class="field"><label>Eyebrow</label><input class="input" name="journal_eyebrow" value="<?= e($journalIntro['eyebrow'] ?? '') ?>"></div>
<div class="field"><label>Heading</label><input class="input" name="journal_heading" value="<?= e($journalIntro['heading'] ?? '') ?>"></div>
<div class="field-row">
<div class="field"><label>"View all" label</label><input class="input" name="journal_viewAllLabel" value="<?= e($journalIntro['viewAllLabel'] ?? '') ?>"></div>
<div class="field"><label>"View all" link</label><input class="input" name="journal_viewAllHref" value="<?= e($journalIntro['viewAllHref'] ?? '') ?>"></div>
</div>
</fieldset>

<fieldset>
<legend>Contact</legend>
<div class="field"><label>Eyebrow</label><input class="input" name="contact_eyebrow" value="<?= e($contact['eyebrow'] ?? '') ?>"></div>
<div class="field"><label>Heading</label><input class="input" name="contact_heading" value="<?= e($contact['heading'] ?? '') ?>"></div>
<div class="field"><label>Contact email</label><input class="input" type="email" name="contact_email" value="<?= e($contact['email'] ?? '') ?>"></div>

<label>Social links</label>
<div class="repeater" data-repeater>
<div class="repeater-items">
<?php foreach ($socials as $i => $soc): ?>
<div class="repeater-item">
<input class="input" name="socials[<?= $i ?>][label]" value="<?= e($soc['label']) ?>" placeholder="Label">
<input class="input" name="socials[<?= $i ?>][href]" value="<?= e($soc['href']) ?>" placeholder="https://...">
<button type="button" class="repeater-remove">Remove</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost repeater-add">+ Add social link</button>
<template>
<div class="repeater-item">
<input class="input" name="socials[__INDEX__][label]" placeholder="Label">
<input class="input" name="socials[__INDEX__][href]" placeholder="https://...">
<button type="button" class="repeater-remove">Remove</button>
</div>
</template>
</div>

<div class="field-row">
<div class="field"><label>Name field label</label><input class="input" name="contact_formNameLabel" value="<?= e($contact['formNameLabel'] ?? '') ?>"></div>
<div class="field"><label>Name field placeholder</label><input class="input" name="contact_formNamePlaceholder" value="<?= e($contact['formNamePlaceholder'] ?? '') ?>"></div>
</div>
<div class="field-row">
<div class="field"><label>Email field label</label><input class="input" name="contact_formEmailLabel" value="<?= e($contact['formEmailLabel'] ?? '') ?>"></div>
<div class="field"><label>Email field placeholder</label><input class="input" name="contact_formEmailPlaceholder" value="<?= e($contact['formEmailPlaceholder'] ?? '') ?>"></div>
</div>
<div class="field-row">
<div class="field"><label>Details field label</label><input class="input" name="contact_formDetailsLabel" value="<?= e($contact['formDetailsLabel'] ?? '') ?>"></div>
<div class="field"><label>Details field placeholder</label><input class="input" name="contact_formDetailsPlaceholder" value="<?= e($contact['formDetailsPlaceholder'] ?? '') ?>"></div>
</div>
<div class="field"><label>Submit button label</label><input class="input" name="contact_submitLabel" value="<?= e($contact['submitLabel'] ?? '') ?>"></div>
</fieldset>

<fieldset>
<legend>Footer</legend>
<div class="field-row">
<div class="field"><label>Copyright text</label><input class="input" name="footer_copyright" value="<?= e($footer['copyright'] ?? '') ?>"></div>
<div class="field"><label>"Back to top" label</label><input class="input" name="footer_backToTopLabel" value="<?= e($footer['backToTopLabel'] ?? '') ?>"></div>
</div>
</fieldset>

<button type="submit" class="btn btn-primary">Save home page</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
