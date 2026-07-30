<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/content.php';

$site = get_section('home', 'nav');
$site['navLinks'] = [];
$journalNavLink = null;
foreach (get_nav_links() as $link) {
    if ($link['id'] === 'journal') {
        $journalNavLink = $link;
    } else {
        $site['navLinks'][] = $link;
    }
}
$showJournal = $journalNavLink !== null;
if ($showJournal) {
    $site['journalNav'] = $journalNavLink;
}

$hero = get_section('home', 'hero');

$about = get_section('home', 'about');
$about['stats'] = get_stats();

$services = get_section('home', 'services_intro');
$services['items'] = get_services();

$work = get_section('home', 'work_intro');
$work['items'] = array_map(static function (array $item): array {
    return [
        'href' => 'work-detail.php?slug=' . urlencode($item['slug']),
        'image_path' => $item['card_image_path'],
        'placeholder' => $item['card_placeholder_text'],
        'tag' => $item['tag'],
        'year' => $item['year'],
        'title' => $item['title'],
    ];
}, get_featured_work(6));

$journal = get_section('home', 'journal_intro');
$journal['items'] = array_map(static function (array $item): array {
    return [
        'href' => 'journal-detail.php?slug=' . urlencode($item['slug']),
        'kicker' => $item['kicker'],
        'title' => $item['title'],
        'body' => $item['card_body'],
        'date' => date('M Y', strtotime($item['published_on'])),
    ];
}, get_featured_journal(3));

$contact = get_section('home', 'contact');
$contact['socials'] = get_socials();

$footerData = get_section('home', 'footer');

$contactStatus = $_GET['contact'] ?? null;

$pageDescription = $hero['subtitle'];

require_once __DIR__ . '/includes/header.php';
?>
<div style="position:relative;background:var(--color-bg);color:var(--color-text);overflow:hidden">
<div id="scroll-progress" style="position:fixed;top:0;left:0;height:3px;width:0%;background:var(--color-accent);z-index:101"></div>
<nav class="nav" id="site-nav" style="position:fixed;top:0;left:0;right:0;z-index:100;background:transparent;border-bottom:2px solid transparent;transition:background .4s ease,border-color .4s ease;padding:22px 48px">
<a href="<?= e($site['brandHref']) ?>" class="nav-brand" style="letter-spacing:.03em;font-size:16px;text-decoration:none;color:inherit"><?= e($site['brand']) ?></a>
<?php foreach ($site['navLinks'] as $link): ?>
<a href="<?= e($link['href']) ?>" data-nav-link="<?= e($link['id']) ?>"><?= e($link['label']) ?></a>
<?php endforeach; ?>
<?php if ($showJournal): ?><a href="<?= e($site['journalNav']['href']) ?>" data-nav-link="<?= e($site['journalNav']['id']) ?>"><?= e($site['journalNav']['label']) ?></a><?php endif; ?>
<a href="<?= e($site['navCtaHref']) ?>" class="btn btn-primary" style="margin-left: 8px; color: var(--color-bg)"><?= e($site['navCtaLabel']) ?></a>
</nav>

<section id="hero" style="min-height:100vh;display:grid;grid-template-columns:1.25fr 1px 1fr;align-items:center;gap:56px;padding:0 48px;position:relative">
<div aria-hidden="true" style="position:absolute;top:14px;left:14px;right:14px;bottom:14px;border:1px solid var(--color-accent-200);pointer-events:none;z-index:1"></div>
<div aria-hidden="true" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;overflow:hidden;font:800 34vw var(--font-heading);color:var(--color-accent-100);line-height:1;white-space:nowrap;letter-spacing:-.02em;display:flex;align-items:center;transform:translateY(2%)">AR</div>
<div style="padding-top:88px;padding-bottom:70px;position:relative;z-index:2;min-height:100%;display:flex;flex-direction:column">
<div style="font:600 12px var(--font-heading);letter-spacing:.22em;text-transform:uppercase;color:var(--color-accent-700);margin-bottom:26px" data-reveal="fade"><?= e($hero['eyebrow']) ?></div>
<h1 style="font-size:clamp(52px,9vw,124px);line-height:.93;margin:0 0 30px;letter-spacing:-.02em;font-weight:800">
<span class="hero-word" style="display:inline-block;opacity:0;transform:translateY(40px)"><?= e($hero['titleLine1']) ?></span><br>
<span class="hero-word" style="display:inline-block;opacity:0;transform:translateY(40px)"><?= e($hero['titleLine2']) ?></span>
</h1>
<p style="font-size:19px;max-width:44ch;opacity:.72;margin:0 0 40px;line-height:1.6" data-reveal="fade"><?= e($hero['subtitle']) ?></p>
<div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:auto" data-reveal="fade">
<a href="<?= e($hero['ctaPrimaryHref']) ?>" class="btn btn-primary"><?= e($hero['ctaPrimaryLabel']) ?><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a>
<a href="<?= e($hero['ctaSecondaryHref']) ?>" class="btn btn-secondary"><?= e($hero['ctaSecondaryLabel']) ?></a>
</div>
<div style="padding-top:64px;display:flex;align-items:center;gap:10px;font-size:11px;letter-spacing:.16em;text-transform:uppercase;opacity:.6;animation:pulseCue 2.4s ease-in-out infinite">
<?= e($hero['scrollLabel']) ?>
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
</div>
</div>
<div style="width:1px;height:64%;background:var(--color-divider);justify-self:center;position:relative;z-index:2"></div>
<div style="position:relative;aspect-ratio:3/4;width:100%;z-index:2" data-parallax="0.12">
<div aria-hidden="true" style="position:absolute;inset:16px -16px -16px 16px;border:2px solid var(--color-accent);z-index:-1"></div>
<?= img_or_placeholder($hero['portraitImagePath'] ?? null, $hero['portraitPlaceholder']) ?>
</div>
<div aria-hidden="true" style="position:absolute;top:32px;right:48px;display:flex;align-items:center;gap:8px;font:600 11px var(--font-heading);letter-spacing:.14em;color:var(--color-accent-700);opacity:.75;z-index:2">
<span style="width:8px;height:8px;border-radius:50%;background:var(--color-accent);animation:pulseCue 1.6s ease-in-out infinite"></span><?= e($hero['recLabel']) ?>
</div>
<svg aria-hidden="true" width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="var(--color-accent-300)" stroke-width="1.5" style="position:absolute;top:14px;left:14px;z-index:2"><path d="M1 8V1h7"></path></svg>
<svg aria-hidden="true" width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="var(--color-accent-300)" stroke-width="1.5" style="position:absolute;bottom:14px;right:14px;z-index:2"><path d="M21 14v7h-7"></path></svg>
</section>

<section id="about" style="padding:140px 48px;max-width:1400px;margin:0 auto;position:relative;display:grid;grid-template-columns:0.85fr 1.15fr;gap:80px;align-items:center;border-top:2px solid var(--color-divider);overflow:hidden">
<div aria-hidden="true" style="position:absolute;top:60px;left:48px;font:800 220px var(--font-heading);color:var(--color-accent-100);line-height:1;z-index:0;pointer-events:none">02</div>
<svg aria-hidden="true" width="120" height="120" viewBox="0 0 120 120" style="position:absolute;bottom:40px;right:60px;z-index:0;pointer-events:none"><circle cx="60" cy="60" r="58" fill="none" stroke="var(--color-accent-200)" stroke-width="1"></circle><circle cx="60" cy="60" r="1" fill="var(--color-accent-300)"></circle></svg>
<div style="position:relative">
<div aria-hidden="true" style="position:absolute;inset:20px -20px -20px 20px;background:var(--color-accent-100);z-index:0"></div>
<div data-reveal="clip" style="position:relative;z-index:1;aspect-ratio:3/4;overflow:hidden;clip-path:inset(0 0 100% 0);transition:clip-path 1.1s cubic-bezier(.22,1,.36,1)">
<div data-parallax="0.08" style="position:absolute;inset:-6% 0;height:112%">
<?= img_or_placeholder($about['portraitImagePath'] ?? null, $about['portraitPlaceholder']) ?>
</div>
</div>
</div>
<div style="position:relative;z-index:1">
<div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-accent-700);margin-bottom:16px" data-reveal="fade"><?= e($about['eyebrow']) ?></div>
<h2 style="font-size:clamp(30px,3.6vw,44px);margin:0 0 24px;max-width:18ch" data-reveal="fade"><?= e($about['heading']) ?></h2>
<p style="opacity:.75;font-size:16px;line-height:1.7;max-width:58ch;margin:0 0 20px" data-reveal="fade"><?= e($about['paragraph1']) ?></p>
<p style="opacity:.75;font-size:16px;line-height:1.7;max-width:58ch;margin:0 0 36px" data-reveal="fade"><?= e($about['paragraph2']) ?></p>
<div style="display:flex;gap:40px;padding-top:28px;border-top:2px solid var(--color-divider)" data-reveal="fade">
<?php foreach ($about['stats'] as $stat): ?>
<div><div data-slot-count="<?= (int) $stat['value'] ?>" style="font:800 34px var(--font-heading);color:var(--color-accent-700);font-variant-numeric:tabular-nums">0+</div><div style="font-size:12px;opacity:.6;letter-spacing:.05em"><?= e($stat['label']) ?></div></div>
<?php endforeach; ?>
</div>
</div>
</section>

<section id="services" style="padding:120px 48px;max-width:1400px;margin:0 auto;border-top:2px solid var(--color-divider);position:relative;overflow:hidden">
<div aria-hidden="true" style="position:absolute;top:40px;right:48px;font:800 200px var(--font-heading);color:var(--color-accent-100);line-height:1;z-index:0;pointer-events:none">03</div>
<div aria-hidden="true" style="position:absolute;top:0;bottom:0;left:0;width:2px;display:flex;flex-direction:column;justify-content:space-evenly;z-index:0;pointer-events:none">
<span style="width:10px;height:1px;background:var(--color-accent-300)"></span><span style="width:10px;height:1px;background:var(--color-accent-300)"></span><span style="width:10px;height:1px;background:var(--color-accent-300)"></span><span style="width:10px;height:1px;background:var(--color-accent-300)"></span><span style="width:10px;height:1px;background:var(--color-accent-300)"></span>
</div>
<div style="margin-bottom:56px;position:relative;z-index:1" data-reveal="fade">
<div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-accent-700);margin-bottom:12px"><?= e($services['eyebrow']) ?></div>
<h2 style="font-size:clamp(32px,4vw,52px);margin:0;max-width:20ch"><?= e($services['heading']) ?></h2>
</div>

<?php foreach ($services['items'] as $i => $svc): ?>
<div data-reveal="fade" class="svc-row" style="display:grid;grid-template-columns:80px 1fr 1.3fr;gap:32px;padding:34px 24px;margin:0 -24px;border-bottom:1px solid var(--color-divider);align-items:baseline;transition:background .3s ease,padding-left .3s ease">
<span style="font:800 30px var(--font-heading);color:var(--color-accent-300)"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
<h3 style="margin:0;font-size:23px"><?= e($svc['title']) ?></h3>
<p style="margin:0;opacity:.72;font-size:15px;max-width:56ch"><?= e($svc['description']) ?></p>
</div>
<?php endforeach; ?>
</section>

<section id="work" style="padding:160px 48px 120px;max-width:1400px;margin:0 auto;position:relative;overflow:hidden">
<div aria-hidden="true" style="position:absolute;top:60px;left:48px;font:800 200px var(--font-heading);color:var(--color-accent-100);line-height:1;z-index:0;pointer-events:none">04</div>
<svg aria-hidden="true" width="90" height="90" viewBox="0 0 90 90" style="position:absolute;top:36px;right:48px;z-index:0;pointer-events:none"><line x1="0" y1="0" x2="90" y2="90" stroke="var(--color-accent-200)" stroke-width="1"></line><line x1="90" y1="0" x2="0" y2="90" stroke="var(--color-accent-200)" stroke-width="1"></line></svg>
<div style="display:grid;grid-template-columns:1.1fr 1fr;gap:32px;align-items:end;margin-bottom:72px;border-bottom:2px solid var(--color-divider);padding-bottom:32px;position:relative;z-index:1" data-reveal="fade">
<div>
<div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-accent-700);margin-bottom:12px"><?= e($work['eyebrow']) ?></div>
<h2 style="font-size:clamp(32px,4vw,52px);margin:0;max-width:15ch"><?= e($work['heading']) ?></h2>
</div>
<div style="justify-self:end;display:flex;flex-direction:column;align-items:flex-end;gap:20px">
<p style="margin:0;opacity:.68;font-size:15px;max-width:44ch;text-align:right"><?= e($work['description']) ?></p>
<a href="<?= e($work['viewAllHref']) ?>" class="btn btn-ghost"><?= e($work['viewAllLabel']) ?><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a>
</div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:6px 28px;position:relative;z-index:1">
<?php foreach ($work['items'] as $i => $item): ?>
<a href="<?= e($item['href']) ?>" class="work-card" style="text-decoration:none;color:inherit;display:block">
<div data-reveal="clip" class="stagger-clip" style="--i:<?= (int) $i ?>;position:relative;aspect-ratio:4/5;overflow:hidden;clip-path:inset(0 0 100% 0);transition:clip-path 1.1s cubic-bezier(.22,1,.36,1)">
<?= img_or_placeholder($item['image_path'], $item['placeholder'], 'work-card-img', 'data-parallax="0.06" style="left:0;right:0;top:-9%;height:118%"') ?>
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

<?php if ($showJournal): ?>
<section id="journal" style="padding:140px 48px;max-width:1400px;margin:0 auto;border-top:2px solid var(--color-divider);position:relative;overflow:hidden">
<div aria-hidden="true" style="position:absolute;top:40px;right:48px;font:800 200px var(--font-heading);color:var(--color-accent-100);line-height:1;z-index:0;pointer-events:none">05</div>
<svg aria-hidden="true" width="60" height="60" viewBox="0 0 60 60" style="position:absolute;bottom:60px;left:52px;z-index:0;pointer-events:none"><path d="M6 44 Q6 6 44 6" fill="none" stroke="var(--color-accent-200)" stroke-width="1.5"></path><path d="M6 44 L6 34 M6 44 L16 44" fill="none" stroke="var(--color-accent-300)" stroke-width="1.5"></path></svg>
<div style="display:grid;grid-template-columns:1.1fr 1fr;gap:32px;align-items:end;margin-bottom:56px;position:relative;z-index:1" data-reveal="fade">
<div data-parallax="0.03">
<div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-accent-700);margin-bottom:12px"><?= e($journal['eyebrow']) ?></div>
<h2 style="font-size:clamp(32px,4vw,52px);margin:0;max-width:16ch"><?= e($journal['heading']) ?></h2>
</div>
<a href="<?= e($journal['viewAllHref']) ?>" class="btn btn-ghost" style="justify-self:end"><?= e($journal['viewAllLabel']) ?><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;position:relative;z-index:1">
<?php foreach ($journal['items'] as $item): ?>
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
<?php endif; ?>

<section id="contact" style="padding:160px 48px 120px;max-width:1400px;margin:0 auto;border-top:2px solid var(--color-divider);display:grid;grid-template-columns:1fr 1fr;gap:80px;position:relative;overflow:hidden">
<div aria-hidden="true" style="position:absolute;bottom:-40px;left:48px;font:800 220px var(--font-heading);color:var(--color-accent-100);line-height:1;z-index:0;pointer-events:none">06</div>
<svg aria-hidden="true" width="70" height="70" viewBox="0 0 70 70" style="position:absolute;top:44px;right:48px;z-index:0;pointer-events:none"><line x1="35" y1="0" x2="35" y2="70" stroke="var(--color-accent-200)" stroke-width="1"></line><line x1="0" y1="35" x2="70" y2="35" stroke="var(--color-accent-200)" stroke-width="1"></line><circle cx="35" cy="35" r="16" fill="none" stroke="var(--color-accent-300)" stroke-width="1"></circle></svg>
<div data-reveal="fade" style="position:relative;z-index:1">
<div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-accent-700);margin-bottom:20px"><?= e($contact['eyebrow']) ?></div>
<h2 style="font-size:clamp(36px,5vw,64px);margin:0 0 32px;max-width:14ch" data-parallax="0.04"><?= e($contact['heading']) ?></h2>
<a href="mailto:<?= e($contact['email']) ?>" style="display:inline-block;font-size:22px;color:var(--color-accent-700);text-decoration:none;border-bottom:2px solid var(--color-accent-700);padding-bottom:4px;margin-bottom:40px"><?= e($contact['email']) ?></a>
<div style="display:flex;gap:20px;font-size:14px;flex-wrap:wrap">
<?php foreach ($contact['socials'] as $soc): ?>
<a href="<?= e($soc['href']) ?>" class="social-link" style="color:inherit;text-decoration:none"><?= e($soc['label']) ?></a>
<?php endforeach; ?>
</div>
</div>
<form id="contact-form" action="contact-submit.php" method="post" data-reveal="fade" style="display:flex;flex-direction:column;gap:18px;position:relative;z-index:1">
<?php if ($contactStatus === 'sent'): ?>
<p style="padding:12px 16px;margin:0;background:var(--color-accent-100);color:var(--color-accent-800);font-size:14px;border-radius:var(--radius-sm)">Thanks — your message has been sent.</p>
<?php elseif ($contactStatus === 'error'): ?>
<p style="padding:12px 16px;margin:0;background:#fbe4e4;color:#7a1f1f;font-size:14px;border-radius:var(--radius-sm)">Please fill in all fields with a valid email address.</p>
<?php endif; ?>
<div style="position:absolute;left:-9999px;top:-9999px" aria-hidden="true">
<label for="website">Leave this field empty</label>
<input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
</div>
<div class="field"><label for="name"><?= e($contact['formNameLabel']) ?></label><input class="input" id="name" name="name" type="text" placeholder="<?= e($contact['formNamePlaceholder']) ?>"></div>
<div class="field"><label for="email"><?= e($contact['formEmailLabel']) ?></label><input class="input" id="email" name="email" type="email" placeholder="<?= e($contact['formEmailPlaceholder']) ?>"></div>
<div class="field"><label for="details"><?= e($contact['formDetailsLabel']) ?></label><textarea class="input" id="details" name="details" placeholder="<?= e($contact['formDetailsPlaceholder']) ?>"></textarea></div>
<button type="submit" class="btn btn-primary btn-block"><?= e($contact['submitLabel']) ?><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></button>
</form>
</section>

<footer style="padding:32px 48px;display:flex;justify-content:space-between;align-items:center;border-top:2px solid var(--color-divider);font-size:12px;opacity:.6">
<span><?= e($footerData['copyright']) ?></span>
<a href="<?= e($site['brandHref']) ?>" style="color:inherit"><?= e($footerData['backToTopLabel']) ?></a>
</footer>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
