<?php

function authUrl(string $path): string
{
    $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $directory = preg_replace('~/includes(?:/actions)?$~', '', $directory);
    $base = $directory === '/' || $directory === '.' ? '' : rtrim($directory, '/');

    return $base . '/' . ltrim($path, '/');
}

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('VAULTSESSID');
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => authUrl(''),
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    if (!headers_sent()) {
        header('Cache-Control: no-store');
    }
}

function currentUser(): ?array
{
    startSession();
    $user = $_SESSION['user'] ?? null;

    if (!is_array($user)
        || !isset($user['id'], $user['username'], $user['email'], $user['role'])
        || !is_int($user['id']) || $user['id'] <= 0
        || !is_string($user['username']) || !is_string($user['email'])
        || !in_array($user['role'], ['user', 'admin'], true)) {
        return null;
    }

    return $user;
}

function requireLogin(): void
{
    if (currentUser() === null) {
        header('Location: ' . authUrl('login.php'), true, 302);
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();

    if (currentUser()['role'] !== 'admin') {
        http_response_code(403);
        exit('Accès réservé aux administrateurs.');
    }
}

function csrfToken(): string
{
    startSession();

    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(mixed $token): bool
{
    startSession();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}