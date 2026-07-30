<?php

require_once __DIR__ . '/bootstrap.php';

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES = 15;

function current_admin(): ?array
{
    if (!isset($_SESSION['admin_user_id'])) {
        return null;
    }

    $stmt = get_db()->prepare('SELECT id, username FROM admin_users WHERE id = ?');
    $stmt->execute([$_SESSION['admin_user_id']]);
    $user = $stmt->fetch();

    return $user === false ? null : $user;
}

function require_login(): array
{
    $user = current_admin();

    if ($user === null) {
        header('Location: /admin/login.php');
        exit;
    }

    return $user;
}

function attempt_login(string $username, string $password): bool
{
    $pdo = get_db();

    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user === false) {
        // Run a dummy hash comparison so a valid vs. invalid username isn't
        // distinguishable by response timing.
        password_verify($password, '$2y$10$invalidinvalidinvalidinvalidinvalidinvalidinvalidinva');
        return false;
    }

    if ($user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
        // Same dummy comparison as the "no such user" branch above, so a
        // locked account isn't distinguishable from a nonexistent one or a
        // wrong password on an unlocked account by response timing.
        password_verify($password, '$2y$10$invalidinvalidinvalidinvalidinvalidinvalidinvalidinva');
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        $failedAttempts = (int) $user['failed_attempts'] + 1;
        $lockedUntil = $failedAttempts >= MAX_LOGIN_ATTEMPTS
            ? date('Y-m-d H:i:s', strtotime('+' . LOCKOUT_MINUTES . ' minutes'))
            : null;

        $stmt = $pdo->prepare('UPDATE admin_users SET failed_attempts = ?, locked_until = ? WHERE id = ?');
        $stmt->execute([$failedAttempts, $lockedUntil, $user['id']]);

        return false;
    }

    $stmt = $pdo->prepare(
        'UPDATE admin_users SET failed_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?'
    );
    $stmt->execute([$user['id']]);

    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = $user['id'];

    return true;
}

function admin_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
