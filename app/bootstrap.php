<?php
declare(strict_types=1);

function load_environment(): void
{
    $file = dirname(__DIR__) . '/.env';
    if (!is_file($file)) {
        return;
    }

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name !== '' && getenv($name) === false) {
            putenv($name . '=' . trim($value, "\"'"));
        }
    }
}

load_environment();
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'America/Sao_Paulo');

$https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
if (session_status() !== PHP_SESSION_ACTIVE && PHP_SAPI !== 'cli') {
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

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $dsn = getenv('DB_DSN');
    if ($dsn === false || $dsn === '') {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'pet_mania';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
    }
    if (str_starts_with($dsn, 'sqlite:')) {
        $sqlitePath = substr($dsn, strlen('sqlite:'));
        $isAbsolute = str_starts_with($sqlitePath, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $sqlitePath) === 1;
        if ($sqlitePath !== ':memory:' && !$isAbsolute) {
            $sqlitePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sqlitePath);
            $dsn = 'sqlite:' . $sqlitePath;
        }
    }

    $connection = new PDO(
        $dsn,
        getenv('DB_USER') ?: '',
        getenv('DB_PASSWORD') ?: '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );

    return $connection;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function text_length(string $value): int
{
    $length = preg_match_all('/./us', $value, $matches);
    return $length === false ? strlen($value) : $length;
}

function money(float|int|string $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function date_br(?string $value): string
{
    if (!$value) {
        return '—';
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', substr($value, 0, 10));
    return $date ? $date->format('d/m/Y') : '—';
}

function date_heading_pt(?string $value = null): string
{
    $date = new DateTimeImmutable($value ?: 'today');
    $weekdays = [1 => 'SEGUNDA-FEIRA', 'TERÇA-FEIRA', 'QUARTA-FEIRA', 'QUINTA-FEIRA', 'SEXTA-FEIRA', 'SÁBADO', 'DOMINGO'];
    $months = [1 => 'JANEIRO', 'FEVEREIRO', 'MARÇO', 'ABRIL', 'MAIO', 'JUNHO', 'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO'];
    return $weekdays[(int) $date->format('N')] . ', ' . $date->format('d') . ' DE ' . $months[(int) $date->format('n')];
}

function page_url(string $page, array $params = []): string
{
    return '?' . http_build_query(['page' => $page] + $params);
}

function redirect_to(string $page, array $params = []): never
{
    header('Location: ' . page_url($page, $params), true, 303);
    exit;
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

function signed_in_user(PDO $pdo): ?array
{
    $id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
        return null;
    }
    $statement = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
    $statement->execute([$id]);
    $user = $statement->fetch();
    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }
    return $user;
}

function icon(string $name, int $size = 19): string
{
    $paths = [
        'paw' => '<circle cx="7.5" cy="8" r="1.6"/><circle cx="16.5" cy="8" r="1.6"/><circle cx="5" cy="13" r="1.5"/><circle cx="19" cy="13" r="1.5"/><path d="M12 11c-3.1 0-6 4.1-6 6.5 0 1.6 1.3 2.5 2.8 2 2.1-.7 4.3-.7 6.4 0 1.5.5 2.8-.4 2.8-2 0-2.4-2.9-6.5-6-6.5Z"/>',
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'box' => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 8 9 5 9-5M3 8v9l9 5 9-5V8M12 13v9"/>',
        'scissors' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="m8.1 8.1 12.8 12.8M14 6l-3.5 3.5M8.1 15.9 12 12"/>',
        'bag' => '<path d="M5 8h14l1 13H4L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'wallet' => '<rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 9h18M16 14h2"/>',
        'chart' => '<path d="M4 19V5M4 19h17"/><path d="m7 15 4-4 3 2 6-7"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'close' => '<path d="m18 6-12 12M6 6l12 12"/>',
        'edit' => '<path d="m15 5 4 4M4 20l4-.8L19 8a2.8 2.8 0 0 0-4-4L4 15v5Z"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'trend' => '<path d="m3 17 6-6 4 4 8-9"/><path d="M15 6h6v6"/>',
    ];
    $body = $paths[$name] ?? $paths['paw'];
    return '<svg aria-hidden="true" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $body . '</svg>';
}
