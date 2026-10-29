<?php
declare(strict_types=1);

function render_schedule(PDO $pdo, array $user): string
{
    $date = query_date('date', date('Y-m-d'));
    $query = query_value('q');
    $statuses = ['Todos', ...APPOINTMENT_STATUSES];
    $status = query_choice('status', $statuses, 'Todos');
    $rows = day_appointments($pdo, $date, $status, $query);
    $previous = date('Y-m-d', strtotime($date . ' -1 day'));
    $next = date('Y-m-d', strtotime($date . ' +1 day'));
    ob_start();
    ?>
    <?= page_header('ROTINA DO PET SHOP', 'Agenda', 'Acompanhe e organize todos os atendimentos.', button_link('schedule', 'appointment', 'Novo agendamento', ['date' => $date])) ?>
    <form method="get" class="toolbar">
      <input type="hidden" name="page" value="schedule">
      <input type="hidden" name="date" value="<?= e($date) ?>">
      <label class="search-box"><?= icon('search', 17) ?><input type="search" name="q" value="<?= e($query) ?>" placeholder="Buscar pet, tutor ou serviço..."></label>
      <div class="segmented"><?php foreach (['Todos', 'Agendado', 'Em andamento', 'Concluído'] as $option): ?><a class="<?= $status === $option ? 'active' : '' ?>" href="<?= e(page_url('schedule', ['date' => $date, 'status' => $option, 'q' => $query])) ?>"><?= e($option) ?></a><?php endforeach; ?></div>
    </form>
    <article class="panel schedule-panel">
      <div class="date-nav"><a class="icon-button" href="<?= e(page_url('schedule', ['date' => $previous, 'status' => $status, 'q' => $query])) ?>" aria-label="Dia anterior">‹</a><label><?= icon('calendar', 17) ?><input type="date" value="<?= e($date) ?>" data-date-navigation></label><a class="icon-button" href="<?= e(page_url('schedule', ['date' => $next, 'status' => $status, 'q' => $query])) ?>" aria-label="Próximo dia">›</a><span><?= count($rows) ?> atendimento(s)</span></div>
      <div class="appointment-list">
      <?php if (!$rows): ?><?= table_empty(1, 'Tente mudar a data ou os filtros da agenda.') ?><?php endif; ?>
      <?php foreach ($rows as $item): ?>
        <div class="appointment-card">
          <div class="appointment-time"><strong><?= e(short_time($item['appointment_time'])) ?></strong><span><?= e($item['duration']) ?> min</span></div><div class="appointment-line"></div><span class="pet-avatar pet-avatar--large"><?= icon('paw', 20) ?></span>
          <div class="appointment-main"><strong><?= e($item['pet_name']) ?><small><?= e($item['breed']) ?></small></strong><span><?= e($item['client_name']) ?> · <?= e($item['service_name']) ?></span></div>
          <div class="professional"><span class="avatar avatar--small"><?= e(text_prefix($item['professional'], 2)) ?></span><?= e($item['professional']) ?></div>
          <?= status_badge($item['status']) ?>
          <div class="row-actions">
            <?php if ($item['status'] === 'Agendado' || $item['status'] === 'Em andamento'): ?>
              <?= post_button('complete_appointment', 'schedule', icon('check', 16), 'icon-button', ['id' => $item['id']], 'Concluir este atendimento e registrar receita, custo e comissão?') ?>
            <?php endif; ?>
            <?php if ($item['status'] === 'Agendado'): ?>
              <a class="icon-button" title="Editar" href="<?= e(page_url('schedule', ['modal' => 'appointment', 'id' => $item['id'], 'date' => $date])) ?>"><?= icon('edit', 15) ?></a>
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

function render_appointment_form(PDO $pdo, ?array $record): string
{
    $clients = all_clients($pdo);
    $pets = all_pets($pdo);
    $services = active_services($pdo);
    $date = $record['appointment_date'] ?? query_date('date', date('Y-m-d'));
    ob_start();
    ?>
    <div class="form-grid">
      <label class="field"><span>Cliente <b>*</b></span><select name="client_id" required data-client-select><option value="">Selecione</option><?= select_options(array_column($clients, 'name', 'id'), isset($record['client_id']) ? (string) $record['client_id'] : null) ?></select></label>
      <label class="field"><span>Pet <b>*</b></span><select name="pet_id" required data-pet-select><option value="">Selecione</option><?php foreach ($pets as $pet): ?><option value="<?= e($pet['id']) ?>" data-client="<?= e($pet['client_id']) ?>"<?= (string) ($record['pet_id'] ?? '') === (string) $pet['id'] ? ' selected' : '' ?>><?= e($pet['name']) ?></option><?php endforeach; ?></select></label>
    </div>
    <label class="field"><span>Serviço <b>*</b></span><select name="service_id" required><option value="">Selecione o serviço</option><?php foreach ($services as $service): ?><option value="<?= e($service['id']) ?>"<?= (string) ($record['service_id'] ?? '') === (string) $service['id'] ? ' selected' : '' ?>><?= e($service['name']) ?> · <?= e(money($service['price'])) ?></option><?php endforeach; ?></select></label>
    <div class="form-grid"><label class="field"><span>Data <b>*</b></span><input type="date" name="date" value="<?= e($date) ?>" required></label><label class="field"><span>Horário <b>*</b></span><input type="time" name="time" value="<?= e(short_time($record['appointment_time'] ?? '')) ?>" required></label></div>
    <label class="field"><span>Profissional <b>*</b></span><input name="professional" value="<?= e($record['professional'] ?? 'Eliane') ?>" maxlength="120" required></label>
    <?php
    return (string) ob_get_clean();
}
