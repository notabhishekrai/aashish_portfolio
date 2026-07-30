<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/upload.php';

$adminUser = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/journal-entries.php');
    exit;
}

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$pdo = get_db();

$stmt = $pdo->prepare('SELECT hero_image_path, inline_image_path FROM journal_entries WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if ($row !== false) {
    delete_uploaded_image($row['hero_image_path']);
    delete_uploaded_image($row['inline_image_path']);

    $stmt = $pdo->prepare('DELETE FROM journal_entries WHERE id = ?');
    $stmt->execute([$id]);

    set_flash('success', 'Entry deleted.');
}

header('Location: /admin/journal-entries.php');
exit;
