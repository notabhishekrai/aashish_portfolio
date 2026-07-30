<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/upload.php';

$adminUser = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/work-projects.php');
    exit;
}

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$pdo = get_db();

$stmt = $pdo->prepare('SELECT card_image_path, hero_image_path, stills FROM work_projects WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if ($row !== false) {
    delete_uploaded_image($row['card_image_path']);
    delete_uploaded_image($row['hero_image_path']);

    foreach (json_decode($row['stills'], true) ?? [] as $still) {
        delete_uploaded_image($still['image_path'] ?? null);
    }

    $stmt = $pdo->prepare('DELETE FROM work_projects WHERE id = ?');
    $stmt->execute([$id]);

    set_flash('success', 'Project deleted.');
}

header('Location: /admin/work-projects.php');
exit;
