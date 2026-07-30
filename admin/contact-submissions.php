<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$adminUser = require_login();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'mark_read') {
        $stmt = $pdo->prepare('UPDATE contact_submissions SET is_read = 1 WHERE id = ?');
        $stmt->execute([$id]);
    } elseif ($action === 'mark_unread') {
        $stmt = $pdo->prepare('UPDATE contact_submissions SET is_read = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }

    header('Location: /admin/contact-submissions.php');
    exit;
}

$submissions = $pdo->query(
    'SELECT * FROM contact_submissions ORDER BY is_read ASC, created_at DESC'
)->fetchAll();

$pageTitle = 'Contact inbox';
require __DIR__ . '/includes/header.php';
?>
<h1>Contact inbox</h1>

<?php if ($submissions === []): ?>
<p>No messages yet.</p>
<?php endif; ?>

<div class="admin-card-grid" style="grid-template-columns:1fr">
<?php foreach ($submissions as $sub): ?>
<div class="admin-card" style="<?= $sub['is_read'] ? 'opacity:.6' : '' ?>">
<h2><?= e($sub['name']) ?> <span class="help-text">&lt;<?= e($sub['email']) ?>&gt;</span></h2>
<p class="help-text"><?= e(date('M j, Y g:ia', strtotime($sub['created_at']))) ?><?= $sub['ip_address'] ? ' · ' . e($sub['ip_address']) : '' ?></p>
<p><?= nl2br(e($sub['details'])) ?></p>
<form method="post">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int) $sub['id'] ?>">
<input type="hidden" name="action" value="<?= $sub['is_read'] ? 'mark_unread' : 'mark_read' ?>">
<button type="submit" class="admin-link-button"><?= $sub['is_read'] ? 'Mark unread' : 'Mark read' ?></button>
</form>
</div>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
