<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';
const APP_PATH = __DIR__;

/**
 * Módulos da aplicação, na ordem em que aparecem no menu. Cada módulo vive em
 * app/modules/<nome>/module.php e devolve a sua definição (veja core/modules.php).
 */
const APP_MODULES = ['overview', 'schedule', 'clients', 'products', 'services', 'sales', 'finance', 'reports'];

foreach (['env', 'database', 'session', 'http', 'format', 'validation', 'modules', 'auth', 'router'] as $file) {
    require_once APP_PATH . "/core/{$file}.php";
}
foreach (['icons', 'components', 'layout'] as $file) {
    require_once APP_PATH . "/ui/{$file}.php";
}

load_environment(APP_ROOT . '/.env');
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'America/Sao_Paulo');
start_session();
