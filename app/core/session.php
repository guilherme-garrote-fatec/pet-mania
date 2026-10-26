<?php
declare(strict_types=1);

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE || PHP_SAPI === 'cli') {
        return;
    }

    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('petmania_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function valid_csrf(string $token): bool
{
    return $token !== '' && hash_equals(csrf_token(), $token);
}

function set_flash(string $message, string $kind = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'kind' => $kind];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}
