<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(isset($pageTitle) ? $pageTitle . ' — ' . SITE_NAME : SITE_NAME) ?></title>
    <?php if (isset($pageDescription)): ?>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
</head>
<body>
