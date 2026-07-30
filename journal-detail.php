<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/content.php';

$slug = $_GET['slug'] ?? '';
$row = $slug !== '' ? get_journal_by_slug($slug) : null;

if ($row === null) {
    require __DIR__ . '/404.php';
    exit;
}

$site = get_section('journal', 'nav');

$allEntries = get_all_journal();
$entryNumber = 1;
foreach ($allEntries as $position => $listedEntry) {
    if ($listedEntry['id'] === $row['id']) {
        $entryNumber = $position + 1;
        break;
    }
}

$entry = [
    'tags' => $row['tags'],
    'title' => $row['title'],
    'subtitle' => $row['subtitle'],
    'heroImagePath' => $row['hero_image_path'],
    'heroPlaceholder' => $row['hero_placeholder_text'],
    'introParagraphs' => $row['intro_paragraphs'],
    'inlineImagePath' => $row['inline_image_path'],
    'inlineImagePlaceholder' => $row['inline_placeholder_text'],
    'subheading' => $row['subheading'],
    'subheadingParagraphs' => $row['subheading_paragraphs'],
];

$next = get_next_journal((int) $row['id']);
$entry['nextLabel'] = 'More from the journal';
$entry['nextTitle'] = $next['title'] ?? '';
$entry['nextHref'] = $next !== null ? 'journal-detail.php?slug=' . urlencode($next['slug']) : 'journal-archive.php';

$footerData = get_section('journal', 'footer');

$pageTitle = $entry['title'];
$pageDescription = $entry['subtitle'];

require_once __DIR__ . '/includes/header.php';
?>
<div style="position:relative;background:var(--color-bg);color:var(--color-text)">
<nav class="nav" style="padding:22px 48px">
<a href="<?= e($site['brandHref']) ?>" class="nav-brand" style="letter-spacing:.03em;font-size:16px;text-decoration:none;color:inherit"><?= e($site['brand']) ?></a>
<a href="<?= e($site['archiveHref']) ?>" style="margin-left:auto"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px"><path d="M19 12H5"></path><path d="m12 19-7-7 7-7"></path></svg><?= e($site['archiveLabel']) ?></a>
<a href="<?= e($site['navCtaHref']) ?>" class="btn btn-primary"><?= e($site['navCtaLabel']) ?></a>
</nav>

<section style="padding:160px 48px 60px;max-width:900px;margin:0 auto;position:relative;overflow:hidden">
<div aria-hidden="true" style="position:absolute;top:80px;right:0;font:800 160px var(--font-heading);color:var(--color-accent-100);line-height:1;z-index:0;pointer-events:none"><?= str_pad((string) $entryNumber, 2, '0', STR_PAD_LEFT) ?></div>
<div data-reveal="fade" style="display:flex;gap:10px;margin-bottom:24px;position:relative;z-index:1">
<?php foreach ($entry['tags'] as $tag): ?>
<span class="tag tag-<?= e($tag['variant']) ?>"><?= e($tag['label']) ?></span>
<?php endforeach; ?>
</div>
<h1 data-reveal="fade" style="font-size:clamp(38px,6vw,64px);line-height:1.05;margin:0 0 28px;position:relative;z-index:1"><?= e($entry['title']) ?></h1>
<p data-reveal="fade" style="font-size:19px;opacity:.72;line-height:1.6;margin:0;position:relative;z-index:1"><?= e($entry['subtitle']) ?></p>
</section>

<section data-reveal="clip" style="position:relative;aspect-ratio:16/8;overflow:hidden;clip-path:inset(0 0 100% 0);transition:clip-path 1.1s cubic-bezier(.22,1,.36,1);margin:0 48px 80px;max-width:1200px;margin-left:auto;margin-right:auto">
<?= img_or_placeholder($entry['heroImagePath'], $entry['heroPlaceholder']) ?>
</section>

<article style="padding:0 48px 140px;max-width:760px;margin:0 auto">
<?php foreach ($entry['introParagraphs'] as $para): ?>
<p data-reveal="fade" style="font-size:17px;line-height:1.85;opacity:.8;margin:0 0 26px"><?= e($para['text']) ?></p>
<?php endforeach; ?>
<div data-reveal="clip" style="position:relative;aspect-ratio:16/10;overflow:hidden;clip-path:inset(0 0 100% 0);transition:clip-path 1s cubic-bezier(.22,1,.36,1);margin:8px 0 36px">
<?= img_or_placeholder($entry['inlineImagePath'], $entry['inlineImagePlaceholder']) ?>
</div>
<h2 data-reveal="fade" style="font-size:26px;margin:48px 0 20px"><?= e($entry['subheading']) ?></h2>
<?php foreach ($entry['subheadingParagraphs'] as $para): ?>
<p data-reveal="fade" style="font-size:17px;line-height:1.85;opacity:.8;margin:0 0 16px"><?= e($para['text']) ?></p>
<?php endforeach; ?>
</article>

<a href="<?= e($entry['nextHref']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:56px 48px;border-top:2px solid var(--color-divider);text-decoration:none;color:inherit;max-width:1400px;margin:0 auto">
<div>
<div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;opacity:.5;margin-bottom:8px"><?= e($entry['nextLabel']) ?></div>
<div style="font-size:24px;font-weight:800;font-family:var(--font-heading)"><?= e($entry['nextTitle']) ?></div>
</div>
<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
</a>

<footer style="padding:32px 48px;display:flex;justify-content:space-between;align-items:center;border-top:2px solid var(--color-divider);font-size:12px;opacity:.6">
<span><?= e($footerData['copyright']) ?></span>
<a href="<?= e($site['brandHref']) ?>" style="color:inherit"><?= e($footerData['backHomeLabel']) ?></a>
</footer>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
