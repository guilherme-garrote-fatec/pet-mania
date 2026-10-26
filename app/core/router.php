<?php
declare(strict_types=1);

/**
 * Ponto de entrada da aplicação web (chamado por public/index.php).
 */
function run_application(): void
{
    $pdo = null;
    $bufferLevel = ob_get_level();
    try {
        $pdo = db();
        modules();
        handle_post($pdo);

        $user = signed_in_user($pdo);
        $flash = take_flash();
        if ($user === null) {
            echo render_login($flash);
            return;
        }

        $page = query_choice('page', module_names(), 'overview');
        $module = modules()[$page];
        $export = query_value('export');
        if ($export !== '' && isset($module['exports'][$export])) {
            $module['exports'][$export]($pdo);
        }

        $content = $module['render']($pdo, $user);
        echo render_layout($page, $user, $content, $flash, render_requested_modal($pdo, $page));
    } catch (PDOException $exception) {
        while (ob_get_level() > $bufferLevel) {
            ob_end_clean();
        }
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Pet Mania database error: ' . $exception->getMessage());
        http_response_code(503);
        echo render_database_error();
    }
}

function handle_post(PDO $pdo): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!valid_csrf(post_value('_csrf'))) {
        action_error('Sua sessão expirou. Atualize a página e tente novamente.', 'overview');
    }

    $action = post_value('action');
    $page = post_value('_page');
    if (!in_array($page, module_names(), true)) {
        $page = 'overview';
    }

    $public = public_actions();
    if (isset($public[$action])) {
        $public[$action]($pdo);
    }

    if (signed_in_user($pdo) === null) {
        action_error('Entre no sistema para continuar.', 'overview');
    }

    $actions = module_actions();
    if (isset($actions[$action])) {
        $actions[$action]($pdo);
    }

    action_error('Ação não reconhecida.', $page);
}

function render_requested_modal(PDO $pdo, string $page): string
{
    $type = query_value('modal');
    $forms = modal_forms();
    if (!isset($forms[$type])) {
        return '';
    }

    $form = $forms[$type];
    $record = null;
    $id = query_id('id');
    if ($id !== null && isset($form['load'])) {
        $record = $form['load']($pdo, $id);
    }
    return render_modal($pdo, $page, $form, $record);
}
