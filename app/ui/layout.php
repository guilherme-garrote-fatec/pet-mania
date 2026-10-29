<?php
declare(strict_types=1);

function document_head(string $title): string
{
    return '<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#123f3b">' .
        '<title>' . e($title) . ' · Pet Mania</title><link rel="stylesheet" href="assets/css/app.css"><script src="assets/js/app.js" defer></script>';
}

function render_login(?array $flash): string
{
    ob_start();
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
      <?= document_head('Entrar') ?>
    </head>
    <body class="auth-body">
      <main class="auth-page">
        <section class="auth-visual">
          <div class="auth-brand"><span class="brand-mark"><?= icon('paw', 22) ?></span><span>Pet <b>Mania</b></span></div>
          <div class="auth-message">
            <span>GESTÃO COM CARINHO</span>
            <h1>Um cuidado especial em cada detalhe.</h1>
            <p>Organize a rotina do seu pet shop em um só lugar: agenda, clientes, estoque e resultados.</p>
            <div class="auth-benefits">
              <div><span><?= icon('calendar', 19) ?></span><p><strong>Agenda organizada</strong><small>Todos os atendimentos sob controle.</small></p></div>
              <div><span><?= icon('trend', 19) ?></span><p><strong>Gestão inteligente</strong><small>Informações para cuidar e crescer.</small></p></div>
            </div>
          </div>
          <span class="auth-paw auth-paw--one"><?= icon('paw', 170) ?></span>
          <span class="auth-paw auth-paw--two"><?= icon('paw', 100) ?></span>
        </section>
        <section class="auth-form-side">
          <div class="auth-card">
            <div class="auth-mobile-brand"><span class="brand-mark"><?= icon('paw', 21) ?></span> Pet <b>Mania</b></div>
            <span class="auth-eyebrow">BEM-VINDA(O) DE VOLTA</span>
            <h2>Acesse sua conta</h2>
            <p>Entre para continuar cuidando do seu negócio.</p>
            <?php if ($flash): ?><div class="notice notice--<?= e($flash['kind']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
            <form method="post" class="auth-form">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="login">
              <input type="hidden" name="_page" value="overview">
              <label class="field"><span>E-mail</span><input type="email" name="email" autocomplete="username" placeholder="voce@petmania.com" required autofocus></label>
              <label class="field"><span>Senha</span><input type="password" name="password" autocomplete="current-password" placeholder="Sua senha" required></label>
              <button class="button button--primary auth-submit" type="submit">Entrar no sistema <?= icon('arrow', 17) ?></button>
            </form>
            <small class="auth-note">Acesso privado. As credenciais iniciais são geradas durante a configuração do sistema.</small>
          </div>
        </section>
      </main>
    </body>
    </html>
    <?php
    return (string) ob_get_clean();
}

function render_layout(string $page, array $user, string $content, ?array $flash, string $modal = ''): string
{
    $modules = modules();
    $title = $modules[$page]['label'];
    $initials = initials((string) $user['name']);
    ob_start();
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
      <?= document_head($title) ?>
    </head>
    <body>
      <div class="app">
        <aside class="sidebar" id="sidebar">
          <a class="brand" href="<?= e(page_url('overview')) ?>"><span class="brand-mark"><?= icon('paw', 22) ?></span><span>Pet <b>Mania</b></span></a>
          <nav class="nav" aria-label="Menu principal">
            <span class="nav-label">MENU PRINCIPAL</span>
            <?php foreach ($modules as $key => $module): ?>
              <a class="nav-item<?= $page === $key ? ' nav-item--active' : '' ?>" href="<?= e(page_url($key)) ?>"<?= $page === $key ? ' aria-current="page"' : '' ?>><?= icon($module['icon'], 19) ?><span><?= e($module['label']) ?></span></a>
            <?php endforeach; ?>
          </nav>
          <div class="sidebar-help"><span><?= icon('paw', 18) ?></span><strong>Precisa de ajuda?</strong><p>Consulte a documentação de instalação e configuração do sistema.</p></div>
          <div class="profile"><span class="avatar"><?= e($initials) ?></span><div><strong><?= e($user['name']) ?></strong><span><?= e($user['role']) ?></span></div>
            <?= post_button('logout', $page, icon('logout', 17), 'icon-button logout-button') ?>
          </div>
        </aside>
        <div class="menu-backdrop" data-menu-close></div>
        <main class="main">
          <header class="topbar">
            <button class="icon-button mobile-only" type="button" aria-label="Abrir menu" data-menu-toggle><?= icon('menu', 22) ?></button>
            <div class="topbar-title"><span>Pet Mania</span><strong><?= e($title) ?></strong></div>
            <div class="topbar-actions"><span class="topbar-greeting"><?= e($user['name']) ?></span><span class="topbar-avatar"><?= e($initials) ?></span></div>
          </header>
          <div class="content">
            <?php if ($flash): ?><div class="notice notice--<?= e($flash['kind']) ?>" role="status"><?= e($flash['message']) ?><button type="button" aria-label="Fechar aviso" data-notice-close><?= icon('close', 15) ?></button></div><?php endif; ?>
            <?= $content ?>
          </div>
        </main>
        <?= $modal ?>
      </div>
    </body>
    </html>
    <?php
    return (string) ob_get_clean();
}

/**
 * Moldura do modal de cadastro/edição; os campos vêm do formulário registrado pelo módulo.
 */
function render_modal(PDO $pdo, string $page, array $form, ?array $record): string
{
    $editing = $record !== null;
    $title = $editing ? str_replace('Novo', 'Editar', $form['title']) : $form['title'];
    ob_start();
    ?>
    <dialog class="modal" aria-modal="true" aria-labelledby="modal-title">
      <div class="modal-card">
        <div class="modal-head"><div><span>CADASTRO</span><h2 id="modal-title"><?= e($title) ?></h2><p><?= e($form['description']) ?></p></div><a class="icon-button" href="<?= e(page_url($page)) ?>" aria-label="Fechar"><?= icon('close', 19) ?></a></div>
        <form method="post" class="prototype-form" data-main-form>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= e($form['action']) ?>">
          <input type="hidden" name="_page" value="<?= e($page) ?>">
          <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($record['id']) ?>"><?php endif; ?>
          <?= $form['render']($pdo, $record) ?>
          <div class="modal-actions"><a class="button button--secondary" href="<?= e(page_url($page)) ?>">Cancelar</a><button class="button button--primary" type="submit"><?= icon('check', 16) ?> Confirmar</button></div>
        </form>
      </div>
    </dialog>
    <?php
    return (string) ob_get_clean();
}

function render_database_error(): string
{
    return '<!doctype html><html lang="pt-BR"><head>' . document_head('Banco indisponível') . '</head><body><main class="setup-error"><span class="brand-mark">' . icon('paw', 22) . '</span><h1>Não foi possível acessar o banco de dados</h1><p>Verifique a configuração do arquivo <code>.env</code>, confirme que o banco está ativo e que o esquema foi importado. Consulte o README do projeto para as instruções de instalação.</p></main></body></html>';
}
