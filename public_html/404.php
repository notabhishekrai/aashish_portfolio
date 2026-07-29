<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
require_once __DIR__ . '/includes/header.php';
?>

<main>
    <h1>404 - Page Not Found</h1>
    <p>The page you're looking for doesn't exist.</p>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
