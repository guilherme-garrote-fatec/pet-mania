<?php
declare(strict_types=1);

/**
 * Usuário da sessão atual. O resultado é guardado por requisição; use $refresh após login/logout.
 */
function signed_in_user(PDO $pdo, bool $refresh = false): ?array
{
    static $loaded = false;
    static $user = null;
    if ($loaded && !$refresh) {
        return $user;
    }

    $loaded = true;
    $user = null;
    $id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        $user = fetch_one($pdo, 'SELECT id, name, email, role FROM users WHERE id = ?', [$id]);
        if ($user === null) {
            unset($_SESSION['user_id']);
        }
    }
    return $user;
}

/**
 * Ações disponíveis sem login.
 *
 * @return array<string, callable>
 */
function public_actions(): array
{
    return [
        'login' => 'login_action',
        'logout' => 'logout_action',
    ];
}

function login_action(PDO $pdo): never
{
    $email = filter_var(post_value('email'), FILTER_VALIDATE_EMAIL);
    $account = fetch_one($pdo, 'SELECT id, password_hash FROM users WHERE email = ?', [$email ?: '']);
    if ($account === null || !password_verify(post_value('password'), $account['password_hash'])) {
        action_error('E-mail ou senha inválidos.', 'overview');
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $account['id'];
    unset($_SESSION['csrf_token']);
    set_flash('Bem-vinda(o) de volta!');
    redirect_to('overview');
}

function logout_action(PDO $pdo): never
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], '', $params['secure'], $params['httponly']);
    }
    session_destroy();
    redirect_to('overview');
}
