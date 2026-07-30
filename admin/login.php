<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if (current_admin() !== null) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (attempt_login($username, $password)) {
        header('Location: /admin/index.php');
        exit;
    }

    $error = 'Incorrect username or password, or the account is temporarily locked.';
}

$pageTitle = 'Log in';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle . ' — Admin — ' . SITE_NAME) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
<link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>">
</head>
<body class="admin-body admin-body-centered">
<main class="admin-login-card">
<h1>Admin log in</h1>
<?php if ($error !== ''): ?>
<div class="admin-flash admin-flash-error"><?= e($error) ?></div>
<?php endif; ?>
<form method="post" novalidate>
<?= csrf_field() ?>
<div class="field">
<label for="username">Username</label>
<input class="input" type="text" id="username" name="username" autocomplete="username" required autofocus>
</div>
<div class="field">
<label for="password">Password</label>
<input class="input" type="password" id="password" name="password" autocomplete="current-password" required>
</div>
<button type="submit" class="btn btn-primary btn-block">Log in</button>
</form>
</main>
</body>
</html>
