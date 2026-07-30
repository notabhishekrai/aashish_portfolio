<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$adminUser = require_login();

$unreadCount = (int) get_db()->query('SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0')->fetchColumn();
$workCount = (int) get_db()->query('SELECT COUNT(*) FROM work_projects')->fetchColumn();
$journalCount = (int) get_db()->query('SELECT COUNT(*) FROM journal_entries')->fetchColumn();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<h1>Dashboard</h1>
<p>Manage every section of the site from here.</p>

<div class="admin-card-grid">
<a class="admin-card" href="/admin/settings-home.php">
<h2>Home page</h2>
<p>Hero, about, services, section intros, contact info, footer.</p>
</a>
<a class="admin-card" href="/admin/work-projects.php">
<h2>Work projects</h2>
<p><?= $workCount ?> project<?= $workCount === 1 ? '' : 's' ?> — powers the work archive, homepage featured work, and every work detail page.</p>
</a>
<a class="admin-card" href="/admin/journal-entries.php">
<h2>Journal entries</h2>
<p><?= $journalCount ?> entr<?= $journalCount === 1 ? 'y' : 'ies' ?> — powers the journal archive, homepage featured journal, and every journal detail page.</p>
</a>
<a class="admin-card" href="/admin/settings-work.php">
<h2>Work archive page</h2>
<p>Hero copy and footer for the work archive/detail pages.</p>
</a>
<a class="admin-card" href="/admin/settings-journal.php">
<h2>Journal archive page</h2>
<p>Hero copy and footer for the journal archive/detail pages.</p>
</a>
<a class="admin-card" href="/admin/contact-submissions.php">
<h2>Contact inbox</h2>
<p><?= $unreadCount > 0 ? $unreadCount . ' unread message' . ($unreadCount === 1 ? '' : 's') : 'No unread messages' ?>.</p>
</a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
