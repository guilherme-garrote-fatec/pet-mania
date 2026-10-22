<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/actions.php';
require_once __DIR__ . '/app/views.php';

try {
    $pdo = db();
handle_post($pdo);
$user = signed_in_user($pdo);
$flash = take_flash();
if (!$user) {
    echo render_login($flash);
    exit;
}

$pages = ['overview', 'schedule', 'clients', 'products', 'services', 'sales', 'finance', 'reports'];
$page = is_string($_GET['page'] ?? null) ? $_GET['page'] : 'overview';
if (!in_array($page, $pages, true)) {
    $page = 'overview';
}
if ($page === 'reports' && ($_GET['export'] ?? '') === 'csv') {
    export_report($pdo);
}

$modal = is_string($_GET['modal'] ?? null) ? $_GET['modal'] : null;
$modalRecord = null;
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($modal && $id && in_array($modal, ['appointment', 'client', 'product', 'service', 'transaction'], true)) {
    $table = match ($modal) {
        'appointment' => 'appointments',
        'client' => 'clients',
        'product' => 'products',
        'transaction' => 'transactions',
        default => 'services',
    };
    $statement = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
    $statement->execute([$id]);
    $modalRecord = $statement->fetch() ?: null;
}

echo render_layout($page, $user, render_page($pdo, $page), $flash, $modal, $modalRecord, $pdo);
} catch (PDOException $exception) {
    error_log('Pet Mania database error: ' . $exception->getMessage());
    http_response_code(503);
    echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Banco indisponível · Pet Mania</title><link rel="stylesheet" href="assets/css/app.css"><main class="setup-error"><span class="brand-mark">' . icon('paw', 22) . '</span><h1>Não foi possível acessar o banco de dados</h1><p>Verifique a configuração do arquivo <code>.env</code>, confirme que o banco está ativo e que o esquema foi importado. Consulte o README do projeto para as instruções de instalação.</p></main></html>';
}
