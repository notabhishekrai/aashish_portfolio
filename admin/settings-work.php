<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/settings-helper.php';

$adminUser = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    save_settings_section('work', 'nav', [
        'brand' => trim((string) ($_POST['nav_brand'] ?? '')),
        'brandHref' => trim((string) ($_POST['nav_brandHref'] ?? '')),
        'homeHref' => trim((string) ($_POST['nav_homeHref'] ?? '')),
        'homeLabel' => trim((string) ($_POST['nav_homeLabel'] ?? '')),
        'navCtaLabel' => trim((string) ($_POST['nav_navCtaLabel'] ?? '')),
        'navCtaHref' => trim((string) ($_POST['nav_navCtaHref'] ?? '')),
    ]);

    save_settings_section('work', 'hero', [
        'eyebrow' => trim((string) ($_POST['hero_eyebrow'] ?? '')),
        'heading' => trim((string) ($_POST['hero_heading'] ?? '')),
        'description' => trim((string) ($_POST['hero_description'] ?? '')),
    ]);

    save_settings_section('work', 'footer', [
        'copyright' => trim((string) ($_POST['footer_copyright'] ?? '')),
        'backHomeLabel' => trim((string) ($_POST['footer_backHomeLabel'] ?? '')),
    ]);

    set_flash('success', 'Work archive page updated.');
    header('Location: /admin/settings-work.php');
    exit;
}

$nav = get_section('work', 'nav');
$hero = get_section('work', 'hero');
$footer = get_section('work', 'footer');

$pageTitle = 'Work archive page';
require __DIR__ . '/includes/header.php';
?>
<h1>Work archive page</h1>
<p class="help-text">Shared chrome for work-archive.php and every work-detail.php project page. Individual projects live in <a href="/admin/work-projects.php">Work projects</a>.</p>

<form method="post" class="admin-form">
<?= csrf_field() ?>

<fieldset>
<legend>Navigation &amp; brand</legend>
<div class="field-row">
<div class="field"><label>Brand text</label><input class="input" name="nav_brand" value="<?= e($nav['brand'] ?? '') ?>"></div>
<div class="field"><label>Brand link (href)</label><input class="input" name="nav_brandHref" value="<?= e($nav['brandHref'] ?? '') ?>"></div>
</div>
<div class="field-row">
<div class="field"><label>"Home" link label</label><input class="input" name="nav_homeLabel" value="<?= e($nav['homeLabel'] ?? '') ?>"></div>
<div class="field"><label>"Home" link href</label><input class="input" name="nav_homeHref" value="<?= e($nav['homeHref'] ?? '') ?>"></div>
</div>
<div class="field-row">
<div class="field"><label>CTA button label</label><input class="input" name="nav_navCtaLabel" value="<?= e($nav['navCtaLabel'] ?? '') ?>"></div>
<div class="field"><label>CTA button link (href)</label><input class="input" name="nav_navCtaHref" value="<?= e($nav['navCtaHref'] ?? '') ?>"></div>
</div>
</fieldset>

<fieldset>
<legend>Hero</legend>
<div class="field"><label>Eyebrow</label><input class="input" name="hero_eyebrow" value="<?= e($hero['eyebrow'] ?? '') ?>"></div>
<div class="field"><label>Heading</label><input class="input" name="hero_heading" value="<?= e($hero['heading'] ?? '') ?>"></div>
<div class="field"><label>Description</label><textarea class="input" name="hero_description"><?= e($hero['description'] ?? '') ?></textarea></div>
</fieldset>

<fieldset>
<legend>Footer</legend>
<div class="field-row">
<div class="field"><label>Copyright text</label><input class="input" name="footer_copyright" value="<?= e($footer['copyright'] ?? '') ?>"></div>
<div class="field"><label>"Back to home" label</label><input class="input" name="footer_backHomeLabel" value="<?= e($footer['backHomeLabel'] ?? '') ?>"></div>
</div>
</fieldset>

<button type="submit" class="btn btn-primary">Save work archive page</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
