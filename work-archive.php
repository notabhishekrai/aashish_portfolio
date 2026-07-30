<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$site = [
    'brand' => 'AASHISH RAI',
    'brandHref' => 'index.php#hero',
    'homeHref' => 'index.php',
    'homeLabel' => 'All work',
    'navCtaLabel' => 'Get in touch',
    'navCtaHref' => 'index.php#contact',
];

$hero = [
    'eyebrow' => 'Archive',
    'heading' => 'Every project, in order.',
    'description' => 'Documentary features, accessibility campaigns, podcasts, brand films, and photography — the full record, 2019 to present.',
];

$items = [
    ['href' => 'work-detail.php', 'placeholder' => 'In Plain Sight — still', 'tag' => 'Documentary', 'year' => '2025', 'title' => 'In Plain Sight'],
    ['href' => 'index.php#work', 'placeholder' => 'Signal & Silence — still', 'tag' => 'Podcast', 'year' => '2024', 'title' => 'Signal & Silence'],
    ['href' => 'index.php#work', 'placeholder' => 'The Unseen Frame — still', 'tag' => 'Accessibility', 'year' => '2024', 'title' => 'The Unseen Frame'],
    ['href' => 'index.php#work', 'placeholder' => 'Currents — still', 'tag' => 'Digital Campaign', 'year' => '2023', 'title' => 'Currents'],
    ['href' => 'index.php#work', 'placeholder' => 'Still, Moving — series', 'tag' => 'Photography', 'year' => '2023', 'title' => 'Still, Moving'],
    ['href' => 'index.php#work', 'placeholder' => 'Ordinary Light — still', 'tag' => 'Documentary Short', 'year' => '2022', 'title' => 'Ordinary Light'],
    ['href' => 'index.php#work', 'placeholder' => 'Held Ground — still', 'tag' => 'Documentary', 'year' => '2021', 'title' => 'Held Ground'],
    ['href' => 'index.php#work', 'placeholder' => 'Waiting Room — podcast', 'tag' => 'Podcast', 'year' => '2021', 'title' => 'Waiting Room'],
    ['href' => 'index.php#work', 'placeholder' => 'Threshold — campaign', 'tag' => 'Digital Campaign', 'year' => '2020', 'title' => 'Threshold'],
    ['href' => 'index.php#work', 'placeholder' => 'Field Notes — series', 'tag' => 'Photography', 'year' => '2019', 'title' => 'Field Notes'],
];

$footerData = [
    'copyright' => '© 2026 Aashish Rai',
    'backHomeLabel' => 'Back to home ↑',
];

$pageTitle = 'Work Archive';
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
<div aria-hidden="true" style="position:absolute;top:100px;right:48px;font:800 200px var(--font-heading);color:var(--color-accent-100);line-height:1;z-index:0;pointer-events:none">04</div>
<div data-reveal="fade" style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-accent-700);margin-bottom:20px;position:relative;z-index:1"><?= e($hero['eyebrow']) ?></div>
<h1 data-reveal="fade" style="font-size:clamp(44px,7vw,92px);line-height:.95;margin:0 0 28px;max-width:18ch;position:relative;z-index:1"><?= e($hero['heading']) ?></h1>
<p data-reveal="fade" style="font-size:18px;max-width:56ch;opacity:.7;line-height:1.6;margin:0;position:relative;z-index:1"><?= e($hero['description']) ?></p>
</section>

<section style="padding:80px 48px 140px;max-width:1400px;margin:0 auto">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:6px 28px">
<?php foreach ($items as $i => $item): ?>
<a href="<?= e($item['href']) ?>" class="work-card" style="text-decoration:none;color:inherit;display:block">
<div data-reveal="clip" class="stagger-clip" style="--i:<?= (int) $i ?>;position:relative;aspect-ratio:4/5;overflow:hidden;clip-path:inset(0 0 100% 0);transition:clip-path 1.1s cubic-bezier(.22,1,.36,1)">
<div class="img-placeholder grayscale work-card-img"><?= e($item['placeholder']) ?></div>
</div>
<div style="padding:20px 2px 40px;border-bottom:1px solid var(--color-divider)">
<div style="display:flex;gap:8px;margin-bottom:10px"><span class="tag tag-accent"><?= e($item['tag']) ?></span><span class="tag tag-neutral"><?= e($item['year']) ?></span></div>
<h3 style="margin:0;font-size:22px"><?= e($item['title']) ?></h3>
<span style="display:inline-flex;align-items:center;gap:6px;margin-top:14px;font-size:13px;color:var(--color-accent-700);font-weight:600">Learn more<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></span>
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
