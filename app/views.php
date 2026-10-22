<?php
declare(strict_types=1);

function page_header(string $eyebrow, string $title, string $description, string $action = ''): string
{
    return '<div class="page-header"><div><span>' . e($eyebrow) . '</span><h1>' . e($title) . '</h1><p>' . e($description) . '</p></div>' . $action . '</div>';
}

function button_link(string $page, string $modal, string $label, array $params = []): string
{
    return '<a class="button button--primary" href="' . e(page_url($page, ['modal' => $modal] + $params)) . '">' . icon('plus', 17) . e($label) . '</a>';
}

function stat_card(string $iconName, string $tone, string $label, string $value, string $detail): string
{
    return '<article class="stat-card"><span class="stat-icon stat-icon--' . e($tone) . '">' . icon($iconName, 20) . '</span><div class="stat-copy"><span>' . e($label) . '</span><strong>' . e($value) . '</strong><small>' . e($detail) . '</small></div></article>';
}

function post_button(string $action, string $page, string $label, string $class = 'button button--secondary', array $hidden = [], string $confirm = ''): string
{
    $html = '<form method="post" class="inline-form"' . ($confirm !== '' ? ' data-confirm="' . e($confirm) . '"' : '') . '>' . csrf_field() .
        '<input type="hidden" name="action" value="' . e($action) . '">' .
        '<input type="hidden" name="_page" value="' . e($page) . '">';
    foreach ($hidden as $key => $value) {
        $html .= '<input type="hidden" name="' . e($key) . '" value="' . e($value) . '">';
    }
    return $html . '<button class="' . e($class) . '" type="submit">' . $label . '</button></form>';
}

function table_empty(int $columns, string $message): string
{
    return '<div class="table-empty" style="grid-column:span ' . $columns . '">' . icon('paw', 25) . '<strong>Nenhum registro encontrado</strong><span>' . e($message) . '</span></div>';
}

function render_login(?array $flash): string
{
    ob_start();
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="theme-color" content="#123f3b">
      <title>Entrar · Pet Mania</title>
      <link rel="stylesheet" href="assets/css/app.css">
      <script src="assets/js/app.js" defer></script>
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

function render_layout(string $page, array $user, string $content, ?array $flash, ?string $modal = null, ?array $modalRecord = null, ?PDO $pdo = null): string
{
    $items = [
        'overview' => ['Visão geral', 'home'],
        'schedule' => ['Agenda', 'calendar'],
        'clients' => ['Clientes & Pets', 'users'],
        'products' => ['Produtos', 'box'],
        'services' => ['Serviços', 'scissors'],
        'sales' => ['Vendas', 'bag'],
        'finance' => ['Financeiro', 'wallet'],
        'reports' => ['Relatórios', 'chart'],
    ];
    $title = $items[$page][0] ?? 'Visão geral';
    $initials = strtoupper(substr((string) $user['name'], 0, 1) . (str_contains((string) $user['name'], ' ') ? substr((string) $user['name'], (int) strpos((string) $user['name'], ' ') + 1, 1) : ''));
    ob_start();
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="theme-color" content="#123f3b">
      <title><?= e($title) ?> · Pet Mania</title>
      <link rel="stylesheet" href="assets/css/app.css">
      <script src="assets/js/app.js" defer></script>
    </head>
    <body>
      <div class="app">
        <aside class="sidebar" id="sidebar">
          <a class="brand" href="<?= e(page_url('overview')) ?>"><span class="brand-mark"><?= icon('paw', 22) ?></span><span>Pet <b>Mania</b></span></a>
          <nav class="nav" aria-label="Menu principal">
            <span class="nav-label">MENU PRINCIPAL</span>
            <?php foreach ($items as $key => [$label, $iconName]): ?>
              <a class="nav-item<?= $page === $key ? ' nav-item--active' : '' ?>" href="<?= e(page_url($key)) ?>"<?= $page === $key ? ' aria-current="page"' : '' ?>><?= icon($iconName, 19) ?><span><?= e($label) ?></span></a>
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
        <?php if ($modal !== null && $pdo instanceof PDO): ?>
          <?= render_modal($pdo, $page, $modal, $modalRecord) ?>
        <?php endif; ?>
      </div>
    </body>
    </html>
    <?php
    return (string) ob_get_clean();
}

function render_modal(PDO $pdo, string $page, string $type, ?array $record): string
{
    $titles = [
        'appointment' => ['Novo agendamento', 'Organize o próximo cuidado do pet.'],
        'client' => ['Novo cliente e pet', 'Cadastre o tutor e seu primeiro pet.'],
        'product' => ['Novo produto', 'Adicione um item ao catálogo da loja.'],
        'service' => ['Novo serviço', 'Defina preço, custo e duração média.'],
        'sale' => ['Registrar venda', 'A baixa no estoque será feita automaticamente.'],
        'transaction' => ['Novo lançamento', 'Registre uma receita, custo ou comissão.'],
    ];
    if (!isset($titles[$type])) {
        return '';
    }
    $editing = $record !== null;
    $action = match ($type) {
        'appointment' => 'save_appointment',
        'client' => 'save_client',
        'product' => 'save_product',
        'service' => 'save_service',
        'sale' => 'save_sale',
        default => 'save_transaction',
    };
    $today = date('Y-m-d');
    ob_start();
    ?>
    <dialog class="modal" aria-modal="true" aria-labelledby="modal-title">
      <div class="modal-card">
        <div class="modal-head"><div><span>CADASTRO</span><h2 id="modal-title"><?= e($editing ? str_replace('Novo', 'Editar', $titles[$type][0]) : $titles[$type][0]) ?></h2><p><?= e($titles[$type][1]) ?></p></div><a class="icon-button" href="<?= e(page_url($page)) ?>" aria-label="Fechar"><?= icon('close', 19) ?></a></div>
        <form method="post" class="prototype-form" data-main-form>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= e($action) ?>">
          <input type="hidden" name="_page" value="<?= e($page) ?>">
          <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($record['id']) ?>"><?php endif; ?>
          <?php if ($type === 'appointment'): ?>
            <?php $clients = $pdo->query('SELECT id, name FROM clients ORDER BY name')->fetchAll(); ?>
            <?php $pets = $pdo->query('SELECT id, client_id, name FROM pets ORDER BY name')->fetchAll(); ?>
            <?php $services = $pdo->query('SELECT id, name, price FROM services WHERE active = 1 ORDER BY name')->fetchAll(); ?>
            <div class="form-grid">
              <label class="field"><span>Cliente <b>*</b></span><select name="client_id" required data-client-select><option value="">Selecione</option><?php foreach ($clients as $client): ?><option value="<?= e($client['id']) ?>"<?= (string) ($record['client_id'] ?? '') === (string) $client['id'] ? ' selected' : '' ?>><?= e($client['name']) ?></option><?php endforeach; ?></select></label>
              <label class="field"><span>Pet <b>*</b></span><select name="pet_id" required data-pet-select><option value="">Selecione</option><?php foreach ($pets as $pet): ?><option value="<?= e($pet['id']) ?>" data-client="<?= e($pet['client_id']) ?>"<?= (string) ($record['pet_id'] ?? '') === (string) $pet['id'] ? ' selected' : '' ?>><?= e($pet['name']) ?></option><?php endforeach; ?></select></label>
            </div>
            <label class="field"><span>Serviço <b>*</b></span><select name="service_id" required><option value="">Selecione o serviço</option><?php foreach ($services as $service): ?><option value="<?= e($service['id']) ?>"<?= (string) ($record['service_id'] ?? '') === (string) $service['id'] ? ' selected' : '' ?>><?= e($service['name']) ?> · <?= e(money($service['price'])) ?></option><?php endforeach; ?></select></label>
            <div class="form-grid"><label class="field"><span>Data <b>*</b></span><input type="date" name="date" value="<?= e($record['appointment_date'] ?? $_GET['date'] ?? $today) ?>" required></label><label class="field"><span>Horário <b>*</b></span><input type="time" name="time" value="<?= e(substr((string) ($record['appointment_time'] ?? ''), 0, 5)) ?>" required></label></div>
            <label class="field"><span>Profissional <b>*</b></span><input name="professional" value="<?= e($record['professional'] ?? 'Eliane') ?>" maxlength="120" required></label>
          <?php elseif ($type === 'client'): ?>
            <div class="form-section-title">Dados do tutor</div>
            <label class="field"><span>Nome completo <b>*</b></span><input name="name" value="<?= e($record['name'] ?? '') ?>" maxlength="120" required placeholder="Ex.: Fernanda Souza"></label>
            <div class="form-grid"><label class="field"><span>Telefone <b>*</b></span><input name="phone" value="<?= e($record['phone'] ?? '') ?>" maxlength="40" required placeholder="(00) 00000-0000"></label><label class="field"><span>E-mail</span><input type="email" name="email" value="<?= e($record['email'] ?? '') ?>" maxlength="160" placeholder="email@exemplo.com"></label></div>
            <div class="form-section-title"><?= $editing ? 'Adicionar pet' : 'Dados do primeiro pet' ?></div>
            <?php if ($editing): ?><p class="muted">Deixe os campos abaixo vazios para manter os pets já cadastrados.</p><?php endif; ?>
            <div class="form-grid"><label class="field"><span>Nome do pet <?= $editing ? '' : '<b>*</b>' ?></span><input name="pet_name" maxlength="100" <?= $editing ? '' : 'required' ?>></label><label class="field"><span>Raça <?= $editing ? '' : '<b>*</b>' ?></span><input name="breed" maxlength="100" <?= $editing ? '' : 'required' ?>></label></div>
            <label class="field"><span>Sexo</span><select name="sex"><option>Não informado</option><option>Macho</option><option>Fêmea</option></select></label>
          <?php elseif ($type === 'product'): ?>
            <label class="field"><span>Nome do produto <b>*</b></span><input name="name" value="<?= e($record['name'] ?? '') ?>" maxlength="160" required></label>
            <label class="field"><span>Categoria <b>*</b></span><select name="category" required><option value="">Selecione</option><?php foreach (['Higiene', 'Acessórios', 'Brinquedos', 'Alimentação'] as $category): ?><option<?= ($record['category'] ?? '') === $category ? ' selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></label>
            <div class="form-grid form-grid--three"><label class="field"><span>Preço <b>*</b></span><input type="number" name="price" min="0.01" step="0.01" value="<?= e($record['price'] ?? '') ?>" required></label><label class="field"><span>Custo <b>*</b></span><input type="number" name="cost" min="0" step="0.01" value="<?= e($record['cost'] ?? '') ?>" required></label><label class="field"><span>Estoque <b>*</b></span><input type="number" name="stock" min="0" step="1" value="<?= e($record['stock'] ?? '') ?>" required></label></div>
          <?php elseif ($type === 'service'): ?>
            <label class="field"><span>Nome do serviço <b>*</b></span><input name="name" value="<?= e($record['name'] ?? '') ?>" maxlength="120" required></label>
            <div class="form-grid form-grid--three"><label class="field"><span>Preço <b>*</b></span><input type="number" name="price" min="0.01" step="0.01" value="<?= e($record['price'] ?? '') ?>" required></label><label class="field"><span>Custo <b>*</b></span><input type="number" name="cost" min="0" step="0.01" value="<?= e($record['cost'] ?? '') ?>" required></label><label class="field"><span>Duração (min) <b>*</b></span><input type="number" name="duration" min="1" step="1" value="<?= e($record['duration'] ?? '') ?>" required></label></div>
          <?php elseif ($type === 'sale'): ?>
            <?php $products = $pdo->query('SELECT id, name, stock, price FROM products ORDER BY name')->fetchAll(); ?>
            <label class="field"><span>Produto <b>*</b></span><select name="product_id" required data-sale-product><option value="">Selecione o produto</option><?php foreach ($products as $product): ?><option value="<?= e($product['id']) ?>" data-price="<?= e($product['price']) ?>" data-stock="<?= e($product['stock']) ?>"><?= e($product['name']) ?> · <?= e($product['stock']) ?> em estoque</option><?php endforeach; ?></select></label>
            <div class="form-grid"><label class="field"><span>Quantidade <b>*</b></span><input type="number" name="quantity" value="1" min="1" step="1" required data-sale-quantity></label><label class="field"><span>Cliente (opcional)</span><input name="client" maxlength="120" placeholder="Venda balcão"></label></div>
            <div class="sale-total"><span>Total da venda</span><strong data-sale-total>R$ 0,00</strong></div>
          <?php else: ?>
            <div class="form-grid"><label class="field"><span>Tipo <b>*</b></span><select name="type" required><option value="">Selecione</option><?php foreach (['Receita', 'Custo', 'Comissão'] as $transactionType): ?><option<?= ($record['type'] ?? '') === $transactionType ? ' selected' : '' ?>><?= e($transactionType) ?></option><?php endforeach; ?></select></label><label class="field"><span>Data <b>*</b></span><input type="date" name="date" value="<?= e($record['transaction_date'] ?? $today) ?>" required></label></div>
            <label class="field"><span>Descrição <b>*</b></span><input name="description" value="<?= e($record['description'] ?? '') ?>" maxlength="180" required placeholder="Ex.: Compra de insumos"></label>
            <label class="field"><span>Valor <b>*</b></span><input type="number" name="value" min="0.01" step="0.01" value="<?= e($record['value'] ?? '') ?>" required></label>
          <?php endif; ?>
          <div class="modal-actions"><a class="button button--secondary" href="<?= e(page_url($page)) ?>">Cancelar</a><button class="button button--primary" type="submit"><?= icon('check', 16) ?> Confirmar</button></div>
        </form>
      </div>
    </dialog>
    <?php
    return (string) ob_get_clean();
}

function render_page(PDO $pdo, string $page): string
{
    return match ($page) {
        'schedule' => render_schedule($pdo),
        'clients' => render_clients($pdo),
        'products' => render_products($pdo),
        'services' => render_services($pdo),
        'sales' => render_sales($pdo),
        'finance' => render_finance($pdo),
        'reports' => render_reports($pdo),
        default => render_overview($pdo),
    };
}

function render_overview(PDO $pdo): string
{
    $today = date('Y-m-d');
    $monthStart = date('Y-m-01');
    $nextMonth = date('Y-m-01', strtotime('first day of next month'));
    $stmt = $pdo->prepare("SELECT type, COALESCE(SUM(value), 0) AS total FROM transactions WHERE transaction_date >= ? AND transaction_date < ? GROUP BY type");
    $stmt->execute([$monthStart, $nextMonth]);
    $sums = ['Receita' => 0.0, 'Custo' => 0.0, 'Comissão' => 0.0];
    foreach ($stmt->fetchAll() as $row) {
        $sums[$row['type']] = (float) $row['total'];
    }
    $appointments = $pdo->prepare("SELECT a.*, c.name AS client_name, p.name AS pet_name, p.breed, s.name AS service_name, s.duration FROM appointments a JOIN clients c ON c.id = a.client_id JOIN pets p ON p.id = a.pet_id JOIN services s ON s.id = a.service_id WHERE a.appointment_date = ? AND a.status <> 'Cancelado' ORDER BY a.appointment_time LIMIT 5");
    $appointments->execute([$today]);
    $dayAppointments = $appointments->fetchAll();
    $stats = $pdo->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'Concluído' THEN 1 ELSE 0 END) AS completed FROM appointments WHERE appointment_date = ? AND status <> 'Cancelado'");
    $stats->execute([$today]);
    $dayStats = $stats->fetch();
    $clientsCount = (int) $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
    $lowStock = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 5')->fetchColumn();

    $monthLabels = [];
    $chartValues = [];
    for ($offset = 5; $offset >= 0; $offset--) {
        $month = new DateTimeImmutable('first day of this month -' . $offset . ' months');
        $start = $month->format('Y-m-01');
        $end = $month->modify('+1 month')->format('Y-m-01');
        $query = $pdo->prepare("SELECT type, SUM(value) AS total FROM transactions WHERE transaction_date >= ? AND transaction_date < ? AND type IN ('Receita', 'Custo', 'Comissão') GROUP BY type");
        $query->execute([$start, $end]);
        $monthSums = ['Receita' => 0.0, 'Custo' => 0.0, 'Comissão' => 0.0];
        foreach ($query->fetchAll() as $row) {
            $monthSums[$row['type']] = (float) $row['total'];
        }
        $monthLabels[] = ['label' => $month->format('M'), 'revenue' => $monthSums['Receita'], 'expense' => $monthSums['Custo'] + $monthSums['Comissão']];
        $chartValues[] = $monthSums['Receita'];
    }
    $maxChart = max($chartValues ?: [1]);

    ob_start();
    ?>
    <?= page_header(date_heading_pt(), 'Olá, ' . explode(' ', signed_in_user($pdo)['name'])[0] . '!', 'A Pet Mania está com tudo organizado para hoje.', button_link('overview', 'appointment', 'Novo agendamento', ['date' => $today])) ?>
    <section class="stats-grid">
      <?= stat_card('wallet', 'green', 'Faturamento no mês', money($sums['Receita']), 'Receitas registradas') ?>
      <?= stat_card('trend', 'blue', 'Lucro líquido', money($sums['Receita'] - $sums['Custo'] - $sums['Comissão']), 'Após custos e comissões') ?>
      <?= stat_card('calendar', 'coral', 'Atendimentos hoje', (string) ($dayStats['total'] ?? 0), (string) ($dayStats['completed'] ?? 0) . ' concluído(s)') ?>
      <?= stat_card('users', 'gold', 'Clientes cadastrados', (string) $clientsCount, $lowStock . ' produto(s) com estoque baixo') ?>
    </section>
    <section class="overview-grid">
      <article class="panel chart-panel">
        <div class="panel-head"><div><span>DESEMPENHO</span><h2>Visão financeira</h2></div><span class="period-pill">Últimos 6 meses</span></div>
        <div class="chart-legend"><span><i class="dot dot--green"></i>Receita</span><span><i class="dot dot--coral"></i>Despesas</span></div>
        <div class="bar-chart" aria-label="Receitas e despesas nos últimos seis meses">
          <?php foreach ($monthLabels as $month): ?>
            <div class="bar-group"><div class="bars"><i title="Receita: <?= e(money($month['revenue'])) ?>" style="height:<?= $maxChart > 0 ? max(4, (int) ($month['revenue'] / $maxChart * 100)) : 4 ?>%"></i><i title="Despesas: <?= e(money($month['expense'])) ?>" style="height:<?= $maxChart > 0 ? max(4, (int) ($month['expense'] / $maxChart * 100)) : 4 ?>%"></i></div><span><?= e($month['label']) ?></span></div>
          <?php endforeach; ?>
        </div>
      </article>
      <article class="panel overview-appointments">
        <div class="panel-head"><div><span>HOJE</span><h2>Próximos atendimentos</h2></div><a class="text-button" href="<?= e(page_url('schedule', ['date' => $today])) ?>">Ver agenda <?= icon('arrow', 14) ?></a></div>
        <div class="timeline">
          <?php if (!$dayAppointments): ?><?= table_empty(1, 'Nenhum atendimento previsto para hoje.') ?><?php endif; ?>
          <?php foreach ($dayAppointments as $item): ?>
            <div class="timeline-item"><time><?= e(substr($item['appointment_time'], 0, 5)) ?></time><span class="pet-avatar"><?= icon('paw', 16) ?></span><div class="timeline-copy"><strong><?= e($item['pet_name']) ?></strong><span><?= e($item['service_name']) ?> · <?= e($item['client_name']) ?></span></div><span class="status-badge status-badge--<?= e(status_class($item['status'])) ?>"><?= e($item['status']) ?></span></div>
          <?php endforeach; ?>
        </div>
      </article>
    </section>
    <section class="bottom-grid">
      <article class="panel"><div class="panel-head"><div><span>ATALHOS</span><h2>Ações rápidas</h2></div></div><div class="quick-actions">
        <a href="<?= e(page_url('schedule', ['modal' => 'appointment', 'date' => $today])) ?>"><span><?= icon('calendar', 19) ?></span><strong>Agendamento</strong><small>Reservar horário</small></a>
        <a href="<?= e(page_url('sales', ['modal' => 'sale'])) ?>"><span><?= icon('bag', 19) ?></span><strong>Nova venda</strong><small>Registrar produto</small></a>
        <a href="<?= e(page_url('clients', ['modal' => 'client'])) ?>"><span><?= icon('users', 19) ?></span><strong>Novo cliente</strong><small>Cadastrar tutor</small></a>
      </div></article>
      <article class="panel activity-panel"><div class="panel-head"><div><span>ESTOQUE</span><h2>Itens que precisam de atenção</h2></div><a class="text-button" href="<?= e(page_url('products')) ?>">Ver produtos <?= icon('arrow', 14) ?></a></div>
        <div class="activity-list"><?php
          $lowProducts = $pdo->query('SELECT id, name, stock FROM products WHERE stock <= 5 ORDER BY stock, name LIMIT 4')->fetchAll();
          if (!$lowProducts) {
              echo '<div class="empty-inline">Tudo certo: não há produtos com estoque baixo.</div>';
          }
          foreach ($lowProducts as $product) {
              echo '<div><span class="activity-icon coral">' . icon('box', 17) . '</span><p><strong>' . e($product['name']) . '</strong><small>' . e($product['stock']) . ' unidade(s) disponíveis</small></p><a href="' . e(page_url('products', ['modal' => 'product', 'id' => $product['id']])) . '">Atualizar</a></div>';
          }
        ?></div>
      </article>
    </section>
    <?php
    return (string) ob_get_clean();
}

function status_class(string $status): string
{
    return match ($status) {
        'Agendado' => 'scheduled',
        'Em andamento' => 'progress',
        'Concluído' => 'complete',
        'Cancelado' => 'cancelled',
        'Ativo' => 'active',
        default => 'muted',
    };
}

function render_schedule(PDO $pdo): string
{
    $date = $_GET['date'] ?? date('Y-m-d');
    if (!is_string($date) || !valid_date($date)) {
        $date = date('Y-m-d');
    }
    $query = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
    $status = is_string($_GET['status'] ?? null) ? $_GET['status'] : 'Todos';
    $statuses = ['Todos', 'Agendado', 'Em andamento', 'Concluído', 'Cancelado'];
    if (!in_array($status, $statuses, true)) {
        $status = 'Todos';
    }
    $sql = 'SELECT a.*, c.name AS client_name, p.name AS pet_name, p.breed, s.name AS service_name, s.duration FROM appointments a JOIN clients c ON c.id = a.client_id JOIN pets p ON p.id = a.pet_id JOIN services s ON s.id = a.service_id WHERE a.appointment_date = ?';
    $params = [$date];
    if ($status !== 'Todos') {
        $sql .= ' AND a.status = ?';
        $params[] = $status;
    }
    if ($query !== '') {
        $sql .= ' AND (c.name LIKE ? OR p.name LIKE ? OR s.name LIKE ?)';
        array_push($params, '%' . $query . '%', '%' . $query . '%', '%' . $query . '%');
    }
    $sql .= ' ORDER BY a.appointment_time';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $previous = date('Y-m-d', strtotime($date . ' -1 day'));
    $next = date('Y-m-d', strtotime($date . ' +1 day'));
    ob_start();
    ?>
    <?= page_header('ROTINA DO PET SHOP', 'Agenda', 'Acompanhe e organize todos os atendimentos.', button_link('schedule', 'appointment', 'Novo agendamento', ['date' => $date])) ?>
    <form method="get" class="toolbar">
      <input type="hidden" name="page" value="schedule">
      <input type="hidden" name="date" value="<?= e($date) ?>">
      <label class="search-box"><?= icon('search', 17) ?><input type="search" name="q" value="<?= e($query) ?>" placeholder="Buscar pet, tutor ou serviço..."></label>
      <label class="filter-select"><span>Status</span><select name="status" onchange="this.form.submit()"><?php foreach ($statuses as $option): ?><option<?= $status === $option ? ' selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></label>
      <div class="segmented"><?php foreach (['Todos', 'Agendado', 'Em andamento', 'Concluído'] as $option): ?><a class="<?= $status === $option ? 'active' : '' ?>" href="<?= e(page_url('schedule', ['date' => $date, 'status' => $option, 'q' => $query])) ?>"><?= e($option) ?></a><?php endforeach; ?></div>
      <button class="button button--secondary" type="submit">Filtrar</button>
    </form>
    <article class="panel schedule-panel">
      <div class="date-nav"><a class="icon-button" href="<?= e(page_url('schedule', ['date' => $previous, 'status' => $status, 'q' => $query])) ?>" aria-label="Dia anterior">‹</a><label><?= icon('calendar', 17) ?><input type="date" value="<?= e($date) ?>" data-date-navigation></label><a class="icon-button" href="<?= e(page_url('schedule', ['date' => $next, 'status' => $status, 'q' => $query])) ?>" aria-label="Próximo dia">›</a><span><?= count($rows) ?> atendimento(s)</span></div>
      <div class="appointment-list">
      <?php if (!$rows): ?><?= table_empty(1, 'Tente mudar a data ou os filtros da agenda.') ?><?php endif; ?>
      <?php foreach ($rows as $item): ?>
        <div class="appointment-card">
          <div class="appointment-time"><strong><?= e(substr($item['appointment_time'], 0, 5)) ?></strong><span><?= e($item['duration']) ?> min</span></div><div class="appointment-line"></div><span class="pet-avatar pet-avatar--large"><?= icon('paw', 20) ?></span>
          <div class="appointment-main"><strong><?= e($item['pet_name']) ?><small><?= e($item['breed']) ?></small></strong><span><?= e($item['client_name']) ?> · <?= e($item['service_name']) ?></span></div>
          <div class="professional"><span class="avatar avatar--small"><?= e(strtoupper(substr($item['professional'], 0, 2))) ?></span><?= e($item['professional']) ?></div>
          <span class="status-badge status-badge--<?= e(status_class($item['status'])) ?>"><?= e($item['status']) ?></span>
          <div class="row-actions">
            <?php if ($item['status'] === 'Agendado' || $item['status'] === 'Em andamento'): ?>
              <?= post_button('complete_appointment', 'schedule', icon('check', 16), 'icon-button', ['id' => $item['id']], 'Concluir este atendimento e registrar receita, custo e comissão?') ?>
            <?php endif; ?>
            <?php if ($item['status'] === 'Agendado'): ?>
              <a class="icon-button" title="Editar" href="<?= e(page_url('schedule', ['modal' => 'appointment', 'id' => $item['id']])) ?>"><?= icon('edit', 15) ?></a>
              <?= post_button('cancel_appointment', 'schedule', icon('close', 16), 'icon-button', ['id' => $item['id']], 'Cancelar este agendamento?') ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    </article>
    <?php
    return (string) ob_get_clean();
}

function render_clients(PDO $pdo): string
{
    $query = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
    $selectedId = filter_var($_GET['selected'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $sql = 'SELECT c.* FROM clients c';
    $params = [];
    if ($query !== '') {
        $sql .= ' WHERE c.name LIKE ? OR c.phone LIKE ? OR EXISTS (SELECT 1 FROM pets p WHERE p.client_id = c.id AND p.name LIKE ?)';
        array_push($params, '%' . $query . '%', '%' . $query . '%', '%' . $query . '%');
    }
    $sql .= ' ORDER BY c.name';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clients = $stmt->fetchAll();
    if (!$selectedId && $clients) {
        $selectedId = (int) $clients[0]['id'];
    }
    $active = null;
    foreach ($clients as $client) {
        if ((int) $client['id'] === $selectedId) {
            $active = $client;
            break;
        }
    }
    $totalPets = (int) $pdo->query('SELECT COUNT(*) FROM pets')->fetchColumn();
    ob_start();
    ?>
    <?= page_header('RELACIONAMENTO', 'Clientes & Pets', count($clients) . ' tutores na busca · ' . $totalPets . ' pets cadastrados.', button_link('clients', 'client', 'Novo cliente')) ?>
    <section class="clients-layout">
      <article class="panel client-list-panel">
        <form method="get" class="search-box"><?= icon('search', 17) ?><input type="hidden" name="page" value="clients"><input type="search" name="q" value="<?= e($query) ?>" placeholder="Buscar cliente ou pet..."></form>
        <div class="client-list">
          <?php foreach ($clients as $client):
              $petsQuery = $pdo->prepare('SELECT name FROM pets WHERE client_id = ? ORDER BY id');
              $petsQuery->execute([$client['id']]);
              $petNames = array_column($petsQuery->fetchAll(), 'name');
              $isActive = $active && (int) $active['id'] === (int) $client['id'];
          ?>
            <a class="<?= $isActive ? 'active' : '' ?>" href="<?= e(page_url('clients', ['q' => $query, 'selected' => $client['id']])) ?>"><span class="avatar"><?= e(strtoupper(substr($client['name'], 0, 1))) ?></span><p><strong><?= e($client['name']) ?></strong><small><?= e(implode(', ', $petNames) ?: 'Sem pets cadastrados') ?></small></p><?= icon('arrow', 15) ?></a>
          <?php endforeach; ?>
          <?php if (!$clients): ?><?= table_empty(1, 'Cadastre um tutor para começar.') ?><?php endif; ?>
        </div>
      </article>
      <?php if ($active): ?>
        <?php
        $petsQuery = $pdo->prepare('SELECT * FROM pets WHERE client_id = ? ORDER BY name');
        $petsQuery->execute([$active['id']]);
        $activePets = $petsQuery->fetchAll();
        $history = $pdo->prepare('SELECT a.*, p.name AS pet_name, s.name AS service_name FROM appointments a JOIN pets p ON p.id = a.pet_id JOIN services s ON s.id = a.service_id WHERE a.client_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 8');
        $history->execute([$active['id']]);
        $appointments = $history->fetchAll();
        ?>
        <article class="panel client-detail">
          <div class="client-hero"><span class="avatar avatar--large"><?= e(strtoupper(substr($active['name'], 0, 1))) ?></span><div><span>CLIENTE CADASTRADO</span><h2><?= e($active['name']) ?></h2><p><?= e($active['phone']) ?> · <?= e($active['email'] ?: 'E-mail não informado') ?></p></div><a class="icon-button" title="Editar cliente" href="<?= e(page_url('clients', ['modal' => 'client', 'id' => $active['id']])) ?>"><?= icon('edit', 17) ?></a></div>
          <div class="section-title"><div><span>PETS</span><strong>Companheiros cadastrados</strong></div><a href="<?= e(page_url('clients', ['modal' => 'client', 'id' => $active['id']])) ?>">+ Adicionar pet</a></div>
          <div class="pet-cards"><?php foreach ($activePets as $pet): ?><div><span class="pet-avatar pet-avatar--large"><?= icon('paw', 19) ?></span><p><strong><?= e($pet['name']) ?></strong><small><?= e($pet['breed']) ?> · <?= e($pet['sex']) ?></small></p></div><?php endforeach; ?><?php if (!$activePets): ?><p class="muted">Nenhum pet cadastrado.</p><?php endif; ?></div>
          <div class="section-title"><div><span>HISTÓRICO</span><strong>Últimos atendimentos</strong></div></div>
          <div class="simple-list"><?php foreach ($appointments as $item): ?><div><span><?= icon('scissors', 16) ?></span><p><strong><?= e($item['service_name']) ?> · <?= e($item['pet_name']) ?></strong><small><?= e(date_br($item['appointment_date'])) ?> · <?= e(substr($item['appointment_time'], 0, 5)) ?></small></p><span class="status-badge status-badge--<?= e(status_class($item['status'])) ?>"><?= e($item['status']) ?></span></div><?php endforeach; ?><?php if (!$appointments): ?><p class="muted">Nenhum atendimento registrado.</p><?php endif; ?></div>
        </article>
      <?php else: ?>
        <article class="panel empty-panel"><?= table_empty(1, 'Selecione um cliente para ver os detalhes.') ?></article>
      <?php endif; ?>
    </section>
    <?php
    return (string) ob_get_clean();
}

function render_products(PDO $pdo): string
{
    $query = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
    $stmt = $pdo->prepare('SELECT * FROM products WHERE name LIKE ? OR category LIKE ? ORDER BY name');
    $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
    $products = $stmt->fetchAll();
    $low = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 5')->fetchColumn();
    ob_start();
    ?>
    <?= page_header('CATÁLOGO E ESTOQUE', 'Produtos', 'Gerencie catálogo, preços e níveis de estoque.', button_link('products', 'product', 'Novo produto')) ?>
    <div class="toolbar"><form method="get" class="search-box"><?= icon('search', 17) ?><input type="hidden" name="page" value="products"><input type="search" name="q" value="<?= e($query) ?>" placeholder="Buscar produto..."></form><div class="stock-alert"><?= icon('box', 16) ?> <?= $low ?> item(ns) com estoque baixo</div></div>
    <article class="panel table-panel"><div class="data-table">
      <div class="table-row table-head product-row"><span>Produto</span><span>Categoria</span><span>Estoque</span><span>Preço</span><span>Margem</span><span></span></div>
      <?php if (!$products): ?><?= table_empty(6, 'Tente outra busca ou cadastre um produto.') ?><?php endif; ?>
      <?php foreach ($products as $item): $margin = (float) $item['price'] > 0 ? round(((float) $item['price'] - (float) $item['cost']) / (float) $item['price'] * 100) : 0; ?>
        <div class="table-row product-row"><span class="product-name"><i><?= icon('box', 18) ?></i><strong><?= e($item['name']) ?><small>SKU PM-<?= e(str_pad((string) $item['id'], 4, '0', STR_PAD_LEFT)) ?></small></strong></span><span><?= e($item['category']) ?></span><span><strong><?= e($item['stock']) ?> un.</strong><?= (int) $item['stock'] <= 5 ? '<span class="status-badge status-badge--low">Estoque baixo</span>' : '' ?></span><span><?= e(money($item['price'])) ?></span><span class="positive"><?= $margin ?>%</span><span class="row-actions"><a class="icon-button" title="Editar produto" href="<?= e(page_url('products', ['modal' => 'product', 'id' => $item['id']])) ?>"><?= icon('edit', 15) ?></a><?= post_button('delete_product', 'products', icon('close', 16), 'icon-button icon-button--danger', ['id' => $item['id']], 'Remover este produto? Produtos com vendas anteriores não podem ser removidos.') ?></span></div>
      <?php endforeach; ?>
    </div></article>
    <?php
    return (string) ob_get_clean();
}

function render_services(PDO $pdo): string
{
    $services = $pdo->query('SELECT * FROM services ORDER BY active DESC, name')->fetchAll();
    ob_start();
    ?>
    <?= page_header('CUIDADO E BEM-ESTAR', 'Serviços', 'Configure os cuidados oferecidos pela Pet Mania.', button_link('services', 'service', 'Novo serviço')) ?>
    <div class="service-grid">
      <?php if (!$services): ?><article class="panel empty-panel"><?= table_empty(1, 'Cadastre o primeiro serviço.') ?></article><?php endif; ?>
      <?php foreach ($services as $index => $item): $margin = (float) $item['price'] > 0 ? round(((float) $item['price'] - (float) $item['cost']) / (float) $item['price'] * 100) : 0; ?>
        <article class="service-card<?= (int) $item['active'] ? '' : ' service-card--inactive' ?>"><div class="service-card-head"><span><?= icon($index % 2 ? 'scissors' : 'paw', 21) ?></span><span class="status-badge status-badge--<?= (int) $item['active'] ? 'active' : 'muted' ?>"><?= (int) $item['active'] ? 'Ativo' : 'Inativo' ?></span></div><h2><?= e($item['name']) ?></h2><p>Atendimento realizado com produtos selecionados e equipe especializada.</p><div class="service-price"><strong><?= e(money($item['price'])) ?></strong><span><?= icon('calendar', 14) ?> <?= e($item['duration']) ?> min</span></div><div class="service-metrics"><div><span>Custo estimado</span><strong><?= e(money($item['cost'])) ?></strong></div><div><span>Margem</span><strong><?= $margin ?>%</strong></div></div><div class="service-actions"><a class="button button--secondary" href="<?= e(page_url('services', ['modal' => 'service', 'id' => $item['id']])) ?>"><?= icon('edit', 14) ?> Editar</a><?= post_button('toggle_service', 'services', (int) $item['active'] ? 'Desativar' : 'Ativar', 'link-button', ['id' => $item['id']]) ?></div></article>
      <?php endforeach; ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

function render_sales(PDO $pdo): string
{
    $rows = $pdo->query('SELECT s.*, p.name AS product_name FROM sales s JOIN products p ON p.id = s.product_id ORDER BY s.sale_date DESC, s.id DESC')->fetchAll();
    $total = array_sum(array_map(static fn(array $row): float => (float) $row['total'], $rows));
    $units = array_sum(array_map(static fn(array $row): int => (int) $row['quantity'], $rows));
    ob_start();
    ?>
    <?= page_header('LOJA', 'Vendas', 'Registre e acompanhe as vendas de produtos.', button_link('sales', 'sale', 'Nova venda')) ?>
    <section class="stats-grid stats-grid--three">
      <?= stat_card('bag', 'green', 'Vendas registradas', (string) count($rows), 'Histórico completo') ?>
      <?= stat_card('wallet', 'blue', 'Receita em produtos', money($total), 'Vendas registradas') ?>
      <?= stat_card('box', 'coral', 'Itens vendidos', (string) $units, 'Unidades no período') ?>
    </section>
    <article class="panel table-panel"><div class="panel-head padded"><div><span>HISTÓRICO</span><h2>Vendas recentes</h2></div></div><div class="data-table"><div class="table-row sales-row table-head"><span>Data</span><span>Produto</span><span>Cliente</span><span>Quantidade</span><span>Total</span></div>
      <?php if (!$rows): ?><?= table_empty(5, 'As vendas registradas aparecerão aqui.') ?><?php endif; ?>
      <?php foreach ($rows as $item): ?><div class="table-row sales-row"><span><?= e(date_br($item['sale_date'])) ?></span><span class="product-name"><i><?= icon('bag', 17) ?></i><strong><?= e($item['product_name']) ?></strong></span><span><?= e($item['client']) ?></span><span><?= e($item['quantity']) ?> un.</span><span class="positive"><strong><?= e(money($item['total'])) ?></strong></span></div><?php endforeach; ?>
    </div></article>
    <?php
    return (string) ob_get_clean();
}

function render_finance(PDO $pdo): string
{
    $monthStart = date('Y-m-01');
    $nextMonth = date('Y-m-01', strtotime('first day of next month'));
    $stmt = $pdo->prepare("SELECT type, COALESCE(SUM(value), 0) AS total FROM transactions WHERE transaction_date >= ? AND transaction_date < ? GROUP BY type");
    $stmt->execute([$monthStart, $nextMonth]);
    $totals = ['Receita' => 0.0, 'Custo' => 0.0, 'Comissão' => 0.0];
    foreach ($stmt->fetchAll() as $row) {
        $totals[$row['type']] = (float) $row['total'];
    }
    $rows = $pdo->query('SELECT * FROM transactions ORDER BY transaction_date DESC, id DESC LIMIT 100')->fetchAll();
    $profit = $totals['Receita'] - $totals['Custo'] - $totals['Comissão'];
    ob_start();
    ?>
    <?= page_header('CONTROLE DO NEGÓCIO', 'Financeiro', 'Visão clara das receitas, despesas e resultados.', button_link('finance', 'transaction', 'Novo lançamento')) ?>
    <section class="stats-grid">
      <?= stat_card('trend', 'green', 'Receitas no mês', money($totals['Receita']), 'Lançamentos confirmados') ?>
      <?= stat_card('wallet', 'coral', 'Custos no mês', money($totals['Custo']), 'Custos registrados') ?>
      <?= stat_card('users', 'gold', 'Comissões no mês', money($totals['Comissão']), 'Equipe de serviços') ?>
      <?= stat_card('chart', 'blue', 'Lucro líquido', money($profit), $totals['Receita'] > 0 ? round($profit / $totals['Receita'] * 100) . '% de margem' : 'Sem receita neste mês') ?>
    </section>
    <article class="panel table-panel"><div class="panel-head padded"><div><span>MOVIMENTAÇÕES</span><h2>Lançamentos recentes</h2></div><span class="period-pill">Até 100 registros</span></div><div class="data-table"><div class="table-row finance-row table-head"><span>Data</span><span>Descrição</span><span>Tipo</span><span>Valor</span><span></span></div>
      <?php if (!$rows): ?><?= table_empty(4, 'Registre uma movimentação para acompanhar as finanças.') ?><?php endif; ?>
      <?php foreach ($rows as $item): $income = $item['type'] === 'Receita'; ?><div class="table-row finance-row"><span><?= e(date_br($item['transaction_date'])) ?></span><span><strong><?= e($item['description']) ?></strong></span><span><span class="transaction-type transaction-type--<?= e($item['type'] === 'Comissão' ? 'comissao' : strtolower($item['type'])) ?>"><?= e($item['type']) ?></span></span><span class="<?= $income ? 'positive' : 'negative' ?>"><strong><?= $income ? '+' : '−' ?> <?= e(money($item['value'])) ?></strong></span><span class="row-actions"><?php if ((int) $item['is_manual'] === 1): ?><a class="icon-button" title="Editar lançamento" href="<?= e(page_url('finance', ['modal' => 'transaction', 'id' => $item['id']])) ?>"><?= icon('edit', 15) ?></a><?= post_button('delete_transaction', 'finance', icon('close', 15), 'icon-button icon-button--danger', ['id' => $item['id']], 'Remover este lançamento manual?') ?><?php else: ?><span class="muted">Automático</span><?php endif; ?></span></div><?php endforeach; ?>
    </div></article>
    <?php
    return (string) ob_get_clean();
}

function report_period(): array
{
    $period = is_string($_GET['period'] ?? null) ? $_GET['period'] : 'month';
    $start = match ($period) {
        'quarter' => date('Y-m-01', strtotime('-2 months', strtotime(date('Y-m-01')))),
        'year' => date('Y-01-01'),
        default => date('Y-m-01'),
    };
    $end = date('Y-m-d', strtotime('tomorrow'));
    if ($period === 'year') {
        $end = date('Y-m-d', strtotime('tomorrow'));
    }
    $period = in_array($period, ['month', 'quarter', 'year'], true) ? $period : 'month';
    return [$period, $start, $end];
}

function export_report(PDO $pdo): never
{
    [$period, $start, $end] = report_period();
    $type = is_string($_GET['type'] ?? null) ? $_GET['type'] : 'all';
    $sql = 'SELECT transaction_date, description, type, value FROM transactions WHERE transaction_date >= ? AND transaction_date < ?';
    $params = [$start, $end];
    if (in_array($type, ['Receita', 'Custo'], true)) {
        $sql .= ' AND type = ?';
        $params[] = $type;
    }
    $sql .= ' ORDER BY transaction_date DESC, id DESC';
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="pet-mania-relatorio-' . $period . '.csv"');
    $output = fopen('php://output', 'wb');
    if ($output === false) {
        throw new RuntimeException('Não foi possível gerar o relatório.');
    }
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['Data', 'Descrição', 'Tipo', 'Valor'], ';');
    foreach ($statement->fetchAll() as $row) {
        $description = (string) $row['description'];
        if (preg_match('/^[\t\r\n ]*[=+\-@]/', $description) === 1) {
            $description = "'" . $description;
        }
        fputcsv($output, [$row['transaction_date'], $description, $row['type'], number_format((float) $row['value'], 2, '.', '')], ';');
    }
    fclose($output);
    exit;
}

function render_reports(PDO $pdo): string
{
    [$period, $start, $end] = report_period();
    $type = is_string($_GET['type'] ?? null) ? $_GET['type'] : 'all';
    if (!in_array($type, ['all', 'Receita', 'Custo'], true)) {
        $type = 'all';
    }
    $totalsQuery = $pdo->prepare("SELECT type, SUM(value) AS total FROM transactions WHERE transaction_date >= ? AND transaction_date < ? GROUP BY type");
    $totalsQuery->execute([$start, $end]);
    $totals = ['Receita' => 0.0, 'Custo' => 0.0, 'Comissão' => 0.0];
    foreach ($totalsQuery->fetchAll() as $item) {
        $totals[$item['type']] = (float) $item['total'];
    }
    $appointmentQuery = $pdo->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'Concluído' THEN 1 ELSE 0 END) AS completed, SUM(CASE WHEN status = 'Cancelado' THEN 1 ELSE 0 END) AS cancelled FROM appointments WHERE appointment_date >= ? AND appointment_date < ?");
    $appointmentQuery->execute([$start, $end]);
    $appointmentStats = $appointmentQuery->fetch();
    $serviceQuery = $pdo->prepare("SELECT s.id, s.name, s.price, s.cost, COUNT(a.id) AS count FROM services s LEFT JOIN appointments a ON a.service_id = s.id AND a.status = 'Concluído' AND a.appointment_date >= ? AND a.appointment_date < ? GROUP BY s.id, s.name, s.price, s.cost ORDER BY count DESC, s.name");
    $serviceQuery->execute([$start, $end]);
    $services = $serviceQuery->fetchAll();
    $maxCount = max(array_map(static fn(array $service): int => (int) $service['count'], $services) ?: [1]);
    $rowsQuery = $pdo->prepare('SELECT transaction_date, description, type, value FROM transactions WHERE transaction_date >= ? AND transaction_date < ?' . ($type !== 'all' ? ' AND type = ?' : '') . ' ORDER BY transaction_date DESC, id DESC LIMIT 50');
    $type === 'all' ? $rowsQuery->execute([$start, $end]) : $rowsQuery->execute([$start, $end, $type]);
    $rows = $rowsQuery->fetchAll();
    $rate = (int) $appointmentStats['total'] > 0 ? round((int) $appointmentStats['completed'] / (int) $appointmentStats['total'] * 100) : 0;
    $periodLabels = ['month' => 'Este mês', 'quarter' => 'Últimos 3 meses', 'year' => 'Este ano'];
    $exportUrl = page_url('reports', ['period' => $period, 'type' => $type, 'export' => 'csv']);
    ob_start();
    ?>
    <?= page_header('ANÁLISE DO NEGÓCIO', 'Relatórios', 'Dados consolidados para decisões mais inteligentes.', '<a class="button button--primary" href="' . e($exportUrl) . '">' . icon('chart', 17) . ' Exportar CSV</a>') ?>
    <form method="get" class="report-filters"><input type="hidden" name="page" value="reports"><label><span>Período</span><select name="period"><?php foreach ($periodLabels as $key => $label): ?><option value="<?= e($key) ?>"<?= $period === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><label><span>Tipo de relatório</span><select name="type"><option value="all"<?= $type === 'all' ? ' selected' : '' ?>>Visão completa</option><option value="Receita"<?= $type === 'Receita' ? ' selected' : '' ?>>Somente receitas</option><option value="Custo"<?= $type === 'Custo' ? ' selected' : '' ?>>Somente custos</option></select></label><button class="button button--secondary" type="submit">Aplicar filtros</button></form>
    <section class="report-grid">
      <article class="panel"><div class="panel-head"><div><span>DESEMPENHO</span><h2>Serviços mais realizados</h2></div></div><div class="ranking">
        <?php if (!$services): ?><p class="muted">Nenhum serviço cadastrado.</p><?php endif; ?>
        <?php foreach ($services as $index => $service): ?><div><b><?= $index + 1 ?></b><span><strong><?= e($service['name']) ?></strong><small><?= e($service['count']) ?> atendimento(s) concluído(s)</small></span><i><em style="width:<?= $maxCount > 0 ? max(3, (int) ((int) $service['count'] / $maxCount * 100)) : 3 ?>%"></em></i><strong><?= e(money((float) $service['price'] * (int) $service['count'])) ?></strong></div><?php endforeach; ?>
      </div></article>
      <article class="panel report-summary"><div class="panel-head"><div><span>RESUMO</span><h2>Indicadores do período</h2></div><span class="period-pill"><?= e($periodLabels[$period]) ?></span></div><div class="summary-number"><span>Taxa de conclusão</span><strong><?= $rate ?>%</strong><small><?= e($appointmentStats['completed'] ?? 0) ?> de <?= e($appointmentStats['total'] ?? 0) ?> atendimentos</small></div><div class="summary-grid"><div><span>Receita</span><strong><?= e(money($totals['Receita'])) ?></strong></div><div><span>Custos</span><strong><?= e(money($totals['Custo'])) ?></strong></div><div><span>Comissões</span><strong><?= e(money($totals['Comissão'])) ?></strong></div><div><span>Cancelamentos</span><strong><?= e($appointmentStats['cancelled'] ?? 0) ?></strong></div></div></article>
    </section>
    <article class="panel table-panel"><div class="panel-head padded"><div><span>ANÁLISE</span><h2>Resultado por serviço</h2></div></div><div class="data-table"><div class="table-row report-row table-head"><span>Serviço</span><span>Atendimentos</span><span>Receita estimada</span><span>Custo estimado</span><span>Margem</span></div>
      <?php foreach ($services as $service): $margin = (float) $service['price'] > 0 ? round(((float) $service['price'] - (float) $service['cost']) / (float) $service['price'] * 100) : 0; ?><div class="table-row report-row"><span><strong><?= e($service['name']) ?></strong></span><span><?= e($service['count']) ?></span><span><?= e(money((float) $service['price'] * (int) $service['count'])) ?></span><span><?= e(money((float) $service['cost'] * (int) $service['count'])) ?></span><span class="positive"><strong><?= $margin ?>%</strong></span></div><?php endforeach; ?>
      <?php if (!$services): ?><?= table_empty(5, 'Os serviços concluídos no período aparecerão aqui.') ?><?php endif; ?>
    </div></article>
    <article class="panel table-panel report-transactions"><div class="panel-head padded"><div><span>MOVIMENTAÇÕES</span><h2>Lançamentos do período</h2></div></div><div class="data-table"><div class="table-row finance-row table-head"><span>Data</span><span>Descrição</span><span>Tipo</span><span>Valor</span></div>
      <?php foreach ($rows as $item): ?><div class="table-row finance-row"><span><?= e(date_br($item['transaction_date'])) ?></span><span><strong><?= e($item['description']) ?></strong></span><span><?= e($item['type']) ?></span><span class="<?= $item['type'] === 'Receita' ? 'positive' : 'negative' ?>"><strong><?= e(money($item['value'])) ?></strong></span></div><?php endforeach; ?>
      <?php if (!$rows): ?><?= table_empty(4, 'Não há lançamentos para esse período e filtro.') ?><?php endif; ?>
    </div></article>
    <?php
    return (string) ob_get_clean();
}
