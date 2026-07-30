<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php#contact');
    exit;
}

// Honeypot: a field real visitors never see or fill (hidden via CSS on the
// public form). A filled value means a bot filled every field it found.
// No session exists for anonymous visitors and there's no privileged action
// to forge here, so this replaces CSRF as the anti-abuse check.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    header('Location: /index.php?contact=sent#contact');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$details = trim((string) ($_POST['details'] ?? ''));

$isValidEmail = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

if ($name === '' || $details === '' || !$isValidEmail) {
    header('Location: /index.php?contact=error#contact');
    exit;
}

$stmt = get_db()->prepare(
    'INSERT INTO contact_submissions (name, email, details, ip_address) VALUES (?, ?, ?, ?)'
);
$stmt->execute([
    mb_substr($name, 0, 160),
    mb_substr($email, 0, 191),
    $details,
    $_SERVER['REMOTE_ADDR'] ?? null,
]);

header('Location: /index.php?contact=sent#contact');
exit;
