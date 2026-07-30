<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/content.php';

// The only place session_start() is called anywhere in the codebase --
// scoped to admin/ so public pages stay session-free.
// use_strict_mode rejects a client-supplied session ID that was never
// issued by the server, instead of adopting it -- defense in depth against
// session fixation alongside the session_regenerate_id() call on login.
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

/** One-shot success/error banner shown at the top of the next page load. */
function set_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}
