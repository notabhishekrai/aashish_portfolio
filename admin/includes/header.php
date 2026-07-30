<?php
/**
 * Shared admin chrome. The including page must have already run
 * admin/includes/auth.php's require_login() (so $adminUser is available)
 * and set an optional $pageTitle before requiring this file.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(isset($pageTitle) ? $pageTitle . ' — Admin — ' . SITE_NAME : 'Admin — ' . SITE_NAME) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
<link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
<nav class="admin-sidebar">
<a href="/admin/index.php" class="admin-brand"><?= e(SITE_NAME) ?> Admin</a>
<div class="admin-nav-group">
<a href="/admin/settings-home.php">Home page</a>
<a href="/admin/settings-work.php">Work archive page</a>
<a href="/admin/work-projects.php">Work projects</a>
<a href="/admin/settings-journal.php">Journal archive page</a>
<a href="/admin/journal-entries.php">Journal entries</a>
<a href="/admin/contact-submissions.php">Contact inbox</a>
</div>
<div class="admin-nav-group">
<span class="admin-nav-user">Logged in as <?= e($adminUser['username'] ?? '') ?></span>
<a href="/admin/change-password.php">Change password</a>
<form action="/admin/logout.php" method="post" class="admin-logout-form">
<?= csrf_field() ?>
<button type="submit" class="admin-link-button">Log out</button>
</form>
</div>
</nav>
<main class="admin-main">
<?php if ($flash = $_SESSION['admin_flash'] ?? null): unset($_SESSION['admin_flash']); ?>
<div class="admin-flash admin-flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
