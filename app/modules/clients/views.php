<?php
declare(strict_types=1);

function render_clients(PDO $pdo, array $user): string
{
    $query = query_value('q');
    $clients = search_clients($pdo, $query);
    $selectedId = query_id('selected') ?? (int) ($clients[0]['id'] ?? 0);
    $active = null;
    foreach ($clients as $client) {
        if ((int) $client['id'] === $selectedId) {
            $active = $client;
            break;
        }
    }
    $petNames = pet_names_by_client($pdo, array_map(static fn(array $client): int => (int) $client['id'], $clients));
    $totalPets = count_pets($pdo);
    ob_start();
    ?>
    <?= page_header('RELACIONAMENTO', 'Clientes & Pets', count($clients) . ' tutores na busca · ' . $totalPets . ' pets cadastrados.', button_link('clients', 'client', 'Novo cliente')) ?>
    <section class="clients-layout">
      <article class="panel client-list-panel">
        <form method="get" class="search-box"><?= icon('search', 17) ?><input type="hidden" name="page" value="clients"><input type="search" name="q" value="<?= e($query) ?>" placeholder="Buscar cliente ou pet..."></form>
        <div class="client-list">
          <?php foreach ($clients as $client): $isActive = $active && (int) $active['id'] === (int) $client['id']; ?>
            <a class="<?= $isActive ? 'active' : '' ?>" href="<?= e(page_url('clients', ['q' => $query, 'selected' => $client['id']])) ?>"><span class="avatar"><?= e(text_prefix($client['name'], 1)) ?></span><p><strong><?= e($client['name']) ?></strong><small><?= e(implode(', ', $petNames[(int) $client['id']] ?? []) ?: 'Sem pets cadastrados') ?></small></p><?= icon('arrow', 15) ?></a>
          <?php endforeach; ?>
          <?php if (!$clients): ?><?= table_empty(1, 'Cadastre um tutor para começar.') ?><?php endif; ?>
        </div>
      </article>
      <?= $active ? render_client_detail($pdo, $active) : '<article class="panel empty-panel">' . table_empty(1, 'Selecione um cliente para ver os detalhes.') . '</article>' ?>
    </section>
    <?php
    return (string) ob_get_clean();
}

function render_client_detail(PDO $pdo, array $client): string
{
    $pets = client_pets($pdo, (int) $client['id']);
    $appointments = client_appointment_history($pdo, (int) $client['id'], 8);
    $editUrl = page_url('clients', ['modal' => 'client', 'id' => $client['id']]);
    ob_start();
    ?>
    <article class="panel client-detail">
      <div class="client-hero"><span class="avatar avatar--large"><?= e(text_prefix($client['name'], 1)) ?></span><div><span>CLIENTE CADASTRADO</span><h2><?= e($client['name']) ?></h2><p><?= e($client['phone']) ?> · <?= e($client['email'] ?: 'E-mail não informado') ?></p></div><a class="icon-button" title="Editar cliente" href="<?= e($editUrl) ?>"><?= icon('edit', 17) ?></a></div>
      <div class="section-title"><div><span>PETS</span><strong>Companheiros cadastrados</strong></div><a href="<?= e($editUrl) ?>">+ Adicionar pet</a></div>
      <div class="pet-cards"><?php foreach ($pets as $pet): ?><div><span class="pet-avatar pet-avatar--large"><?= icon('paw', 19) ?></span><p><strong><?= e($pet['name']) ?></strong><small><?= e($pet['breed']) ?> · <?= e($pet['sex']) ?></small></p></div><?php endforeach; ?><?php if (!$pets): ?><p class="muted">Nenhum pet cadastrado.</p><?php endif; ?></div>
      <div class="section-title"><div><span>HISTÓRICO</span><strong>Últimos atendimentos</strong></div></div>
      <div class="simple-list"><?php foreach ($appointments as $item): ?><div><span><?= icon('scissors', 16) ?></span><p><strong><?= e($item['service_name']) ?> · <?= e($item['pet_name']) ?></strong><small><?= e(date_br($item['appointment_date'])) ?> · <?= e(short_time($item['appointment_time'])) ?></small></p><?= status_badge($item['status']) ?></div><?php endforeach; ?><?php if (!$appointments): ?><p class="muted">Nenhum atendimento registrado.</p><?php endif; ?></div>
    </article>
    <?php
    return (string) ob_get_clean();
}

function render_client_form(PDO $pdo, ?array $record): string
{
    $editing = $record !== null;
    ob_start();
    ?>
    <div class="form-section-title">Dados do tutor</div>
    <label class="field"><span>Nome completo <b>*</b></span><input name="name" value="<?= e($record['name'] ?? '') ?>" maxlength="120" required placeholder="Ex.: Fernanda Souza"></label>
    <div class="form-grid"><label class="field"><span>Telefone <b>*</b></span><input name="phone" value="<?= e($record['phone'] ?? '') ?>" maxlength="40" required placeholder="(00) 00000-0000"></label><label class="field"><span>E-mail</span><input type="email" name="email" value="<?= e($record['email'] ?? '') ?>" maxlength="160" placeholder="email@exemplo.com"></label></div>
    <div class="form-section-title"><?= $editing ? 'Adicionar pet' : 'Dados do primeiro pet' ?></div>
    <?php if ($editing): ?><p class="muted">Deixe os campos abaixo vazios para manter os pets já cadastrados.</p><?php endif; ?>
    <div class="form-grid"><label class="field"><span>Nome do pet <?= $editing ? '' : '<b>*</b>' ?></span><input name="pet_name" maxlength="100" <?= $editing ? '' : 'required' ?>></label><label class="field"><span>Raça <?= $editing ? '' : '<b>*</b>' ?></span><input name="breed" maxlength="100" <?= $editing ? '' : 'required' ?>></label></div>
    <label class="field"><span>Sexo</span><select name="sex"><?= select_options(array_combine(PET_SEXES, PET_SEXES), null) ?></select></label>
    <?php
    return (string) ob_get_clean();
}
