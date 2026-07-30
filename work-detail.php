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

$project = [
    'tags' => [
        ['label' => 'Documentary', 'variant' => 'accent'],
        ['label' => '2025', 'variant' => 'neutral'],
        ['label' => '42 min', 'variant' => 'outline'],
    ],
    'title' => 'In Plain Sight',
    'lead' => 'A feature documentary following three accessibility advocates rebuilding public space for their own communities — filmed over eighteen months with audio description built into the shoot, not added after.',
    'heroPlaceholder' => 'In Plain Sight — key frame',
    'synopsisHeading' => 'Synopsis',
    'synopsisParagraphs' => [
        ['text' => 'Shot across four cities, In Plain Sight follows Meera, Josiah, and Tomas as they push their local governments to rebuild sidewalks, transit stops, and public buildings around the way they actually move through the world — not the way planners assumed they would.'],
        ['text' => 'The film was directed with an accessibility consultant embedded from the first location scout, and every frame was cut with an eye toward what an audio-described version would need to carry.'],
    ],
    'credits' => [
        ['label' => 'Director', 'value' => 'Aashish Rai'],
        ['label' => 'Producer', 'value' => 'Aashish Rai'],
        ['label' => 'Runtime', 'value' => '42 min'],
        ['label' => 'Format', 'value' => 'Documentary Feature'],
        ['label' => 'Year', 'value' => '2025'],
        ['label' => 'Selection', 'value' => 'Riverside Doc Festival'],
    ],
    'stillsEyebrow' => 'Stills',
    'stillsHeading' => 'From the field.',
    'stills' => [
        ['placeholder' => 'Still 1'],
        ['placeholder' => 'Still 2'],
        ['placeholder' => 'Still 3'],
    ],
    'nextLabel' => 'Next project',
    'nextTitle' => 'Signal & Silence',
    'nextHref' => 'index.php#work',
];

$pageTitle = $project['title'];
$pageDescription = $project['lead'];

require_once __DIR__ . '/includes/header.php';
?>
<div style="position:relative;background:var(--color-bg);color:var(--color-text)">
<nav class="nav" style="padding:22px 48px">
<a href="<?= e($site['brandHref']) ?>" class="nav-brand" style="letter-spacing:.03em;font-size:16px;text-decoration:none;color:inherit"><?= e($site['brand']) ?></a>
<a href="<?= e($site['homeHref']) ?>" style="margin-left:auto"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px"><path d="M19 12H5"></path><path d="m12 19-7-7 7-7"></path></svg><?= e($site['homeLabel']) ?></a>
<a href="<?= e($site['navCtaHref']) ?>" class="btn btn-primary"><?= e($site['navCtaLabel']) ?></a>
</nav>

<section style="padding:160px 48px 80px;max-width:1400px;margin:0 auto;position:relative;overflow:hidden">
<div aria-hidden="true" style="position:absolute;top:100px;right:48px;font:800 200px var(--font-heading);color:var(--color-accent-100);line-height:1;z-index:0;pointer-events:none">01</div>
<svg aria-hidden="true" width="70" height="70" viewBox="0 0 70 70" style="position:absolute;top:150px;left:0;z-index:0;pointer-events:none"><line x1="35" y1="0" x2="35" y2="70" stroke="var(--color-accent-200)" stroke-width="1"></line><line x1="0" y1="35" x2="70" y2="35" stroke="var(--color-accent-200)" stroke-width="1"></line></svg>
<div data-reveal="fade" style="display:flex;gap:10px;margin-bottom:24px;position:relative;z-index:1">
<?php foreach ($project['tags'] as $tag): ?>
<span class="tag tag-<?= e($tag['variant']) ?>"><?= e($tag['label']) ?></span>
<?php endforeach; ?>
</div>
<h1 data-reveal="fade" style="font-size:clamp(48px,8vw,104px);line-height:.95;margin:0 0 32px;max-width:18ch;position:relative;z-index:1"><?= e($project['title']) ?></h1>
<p data-reveal="fade" style="font-size:19px;max-width:56ch;opacity:.72;line-height:1.65;margin:0;position:relative;z-index:1"><?= e($project['lead']) ?></p>
</section>

<section data-reveal="clip" style="position:relative;aspect-ratio:16/7;overflow:hidden;clip-path:inset(0 0 100% 0);transition:clip-path 1.2s cubic-bezier(.22,1,.36,1);margin:0 48px 120px;max-width:1400px;margin-left:auto;margin-right:auto">
<div class="img-placeholder grayscale"><?= e($project['heroPlaceholder']) ?></div>
</section>

<section style="padding:0 48px 120px;max-width:1400px;margin:0 auto;display:grid;grid-template-columns:1.4fr 1fr;gap:80px">
<div data-reveal="fade">
<h2 style="font-size:28px;margin:0 0 20px"><?= e($project['synopsisHeading']) ?></h2>
<?php foreach ($project['synopsisParagraphs'] as $para): ?>
<p style="font-size:16px;line-height:1.75;opacity:.75;margin:0 0 20px"><?= e($para['text']) ?></p>
<?php endforeach; ?>
</div>
<div data-reveal="fade" style="border-top:2px solid var(--color-divider);padding-top:8px">
<table class="table">
<tbody>
<?php foreach ($project['credits'] as $row): ?>
<tr><td style="opacity:.6"><?= e($row['label']) ?></td><td><?= e($row['value']) ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>

<section style="padding:0 48px 140px;max-width:1400px;margin:0 auto;border-top:2px solid var(--color-divider);padding-top:100px">
<div data-reveal="fade" style="margin-bottom:40px">
<div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-accent-700);margin-bottom:12px"><?= e($project['stillsEyebrow']) ?></div>
<h2 style="font-size:clamp(28px,3.4vw,40px);margin:0"><?= e($project['stillsHeading']) ?></h2>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px">
<?php foreach ($project['stills'] as $i => $still): ?>
<div class="work-card stagger-clip" data-reveal="clip" style="--i:<?= (int) $i ?>;position:relative;aspect-ratio:4/5;overflow:hidden;clip-path:inset(0 0 100% 0);transition:clip-path 1s cubic-bezier(.22,1,.36,1)"><div class="img-placeholder grayscale work-card-img"><?= e($still['placeholder']) ?></div></div>
<?php endforeach; ?>
</div>
</section>

<a href="<?= e($project['nextHref']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:56px 48px;border-top:2px solid var(--color-divider);text-decoration:none;color:inherit">
<div>
<div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;opacity:.5;margin-bottom:8px"><?= e($project['nextLabel']) ?></div>
<div style="font-size:28px;font-weight:800;font-family:var(--font-heading)"><?= e($project['nextTitle']) ?></div>
</div>
<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
</a>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
