<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/content.php';

$site = get_section('journal', 'nav');
$hero = get_section('journal', 'hero');

$items = array_map(static function (array $entry): array {
    return [
        'href' => 'journal-detail.php?slug=' . urlencode($entry['slug']),
        'kicker' => $entry['kicker'],
        'title' => $entry['title'],
        'body' => $entry['card_body'],
        'date' => date('M Y', strtotime($entry['published_on'])),
    ];
}, get_all_journal());

$footerData = get_section('journal', 'footer');

$pageTitle = 'Journal';
$pageDescription = $hero['description'];

require_once __DIR__ . '/includes/header.php';
?>
<div style="position:relative;background:var(--color-bg);color:var(--color-text)">
<nav class="nav" style="padding:22px 48px">
<a href="<?= e($site['brandHref']) ?>" class="nav-brand" style="letter-spacing:.03em;font-size:16px;text-decoration:none;color:inherit"><?= e($site['brand']) ?></a>
<a href="<?= e($site['homeHref']) ?>" style="margin-left:auto"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px"><path d="M19 12H5"></path><path d="m12 19-7-7 7-7"></path></svg><?= e($site['homeLabel']) ?></a>
<a href="<?= e($site['navCtaHref']) ?>" class="btn btn-primary"><?= e($site['navCtaLabel']) ?></a>
</nav>

<section style="padding:160px 48px 80px;max-width:1400px;margin:0 auto;border-bottom:2px solid var(--color-divider);position:relative;overflow:hidden">
<div aria-hidden="true" style="position:absolute;top:100px;right:48px;font:800 200px var(--font-heading);color:var(--color-accent-100);line-height:1;z-index:0;pointer-events:none">05</div>
<svg aria-hidden="true" width="60" height="60" viewBox="0 0 60 60" style="position:absolute;top:160px;left:0;z-index:0;pointer-events:none"><path d="M6 44 Q6 6 44 6" fill="none" stroke="var(--color-accent-200)" stroke-width="1.5"></path><path d="M6 44 L6 34 M6 44 L16 44" fill="none" stroke="var(--color-accent-300)" stroke-width="1.5"></path></svg>
<div data-reveal="fade" style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-accent-700);margin-bottom:20px;position:relative;z-index:1"><?= e($hero['eyebrow']) ?></div>
<h1 data-reveal="fade" style="font-size:clamp(44px,7vw,92px);line-height:.95;margin:0 0 28px;max-width:18ch;position:relative;z-index:1"><?= e($hero['heading']) ?></h1>
<p data-reveal="fade" style="font-size:18px;max-width:56ch;opacity:.7;line-height:1.6;margin:0;position:relative;z-index:1"><?= e($hero['description']) ?></p>
</section>

<section style="padding:80px 48px 140px;max-width:1400px;margin:0 auto">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px">
<?php foreach ($items as $item): ?>
<a href="<?= e($item['href']) ?>" class="journal-card" style="text-decoration:none;color:inherit" data-reveal="fade">
<div class="card elev-sm" style="height:100%">
<div class="card-kicker"><?= e($item['kicker']) ?></div>
<div class="card-title"><?= e($item['title']) ?></div>
<p class="card-body"><?= e($item['body']) ?></p>
<div class="card-meta" style="justify-content:space-between"><span><?= e($item['date']) ?></span><span style="display:inline-flex;align-items:center;gap:4px;color:var(--color-accent-700);font-weight:600">Read more<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></span></div>
</div>
</a>
<?php endforeach; ?>
</div>
</section>

<footer style="padding:32px 48px;display:flex;justify-content:space-between;align-items:center;border-top:2px solid var(--color-divider);font-size:12px;opacity:.6">
<span><?= e($footerData['copyright']) ?></span>
<a href="<?= e($site['homeHref']) ?>#hero" style="color:inherit"><?= e($footerData['backHomeLabel']) ?></a>
</footer>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
