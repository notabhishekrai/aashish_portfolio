<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$adminUser = require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    $stmt = get_db()->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
    $stmt->execute([$adminUser['id']]);
    $currentHash = $stmt->fetchColumn();

    if (!password_verify($currentPassword, $currentHash)) {
        $errors[] = 'Current password is incorrect.';
    }

    if (strlen($newPassword) < 10) {
        $errors[] = 'New password must be at least 10 characters.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirmation do not match.';
    }

    if ($errors === []) {
        $stmt = get_db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $adminUser['id']]);

        set_flash('success', 'Password changed.');
        header('Location: /admin/index.php');
        exit;
    }
}

$pageTitle = 'Change password';
require __DIR__ . '/includes/header.php';
?>
<h1>Change password</h1>

<?php foreach ($errors as $error): ?>
<div class="admin-flash admin-flash-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" class="admin-form" novalidate>
<?= csrf_field() ?>
<div class="field">
<label for="current_password">Current password</label>
<input class="input" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
</div>
<div class="field">
<label for="new_password">New password</label>
<input class="input" type="password" id="new_password" name="new_password" autocomplete="new-password" required minlength="10">
</div>
<div class="field">
<label for="confirm_password">Confirm new password</label>
<input class="input" type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required minlength="10">
</div>
<button type="submit" class="btn btn-primary">Update password</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
