<?php
declare(strict_types=1);

/**
 * Painel inicial: reúne indicadores dos módulos de agenda, clientes, produtos e financeiro.
 */
function render_overview(PDO $pdo, array $user): string
{
    $today = date('Y-m-d');
    $sums = current_month_totals($pdo);
    $dayAppointments = upcoming_day_appointments($pdo, $today, 5);
    $dayStats = day_appointment_stats($pdo, $today);
    $clientsCount = count_clients($pdo);
    $lowStock = count_low_stock_products($pdo);
    $lowProducts = low_stock_products($pdo, 4);
    $months = monthly_cash_flow($pdo, 6);
    $maxChart = max(1.0, ...array_map(static fn(array $month): float => max($month['revenue'], $month['expense']), $months));
    $barHeight = static fn(float $value): int => max(4, (int) ($value / $maxChart * 100));

    ob_start();
    ?>
    <?= page_header(date_heading_pt(), 'Olá, ' . first_name((string) $user['name']) . '!', 'A Pet Mania está com tudo organizado para hoje.', button_link('overview', 'appointment', 'Novo agendamento', ['date' => $today])) ?>
    <section class="stats-grid">
      <?= stat_card('wallet', 'green', 'Faturamento no mês', money($sums['Receita']), 'Receitas registradas') ?>
      <?= stat_card('trend', 'blue', 'Lucro líquido', money(net_profit($sums)), 'Após custos e comissões') ?>
      <?= stat_card('calendar', 'coral', 'Atendimentos hoje', (string) $dayStats['total'], $dayStats['completed'] . ' concluído(s)') ?>
      <?= stat_card('users', 'gold', 'Clientes cadastrados', (string) $clientsCount, $lowStock . ' produto(s) com estoque baixo') ?>
    </section>
    <section class="overview-grid">
      <article class="panel chart-panel">
        <div class="panel-head"><div><span>DESEMPENHO</span><h2>Visão financeira</h2></div><span class="period-pill">Últimos 6 meses</span></div>
        <div class="chart-legend"><span><i class="dot dot--green"></i>Receita</span><span><i class="dot dot--coral"></i>Despesas</span></div>
        <div class="bar-chart" aria-label="Receitas e despesas nos últimos seis meses">
          <?php foreach ($months as $month): ?>
            <div class="bar-group"><div class="bars"><i title="Receita: <?= e(money($month['revenue'])) ?>" style="height:<?= $barHeight($month['revenue']) ?>%"></i><i title="Despesas: <?= e(money($month['expense'])) ?>" style="height:<?= $barHeight($month['expense']) ?>%"></i></div><span><?= e($month['label']) ?></span></div>
          <?php endforeach; ?>
        </div>
      </article>
      <article class="panel overview-appointments">
        <div class="panel-head"><div><span>HOJE</span><h2>Próximos atendimentos</h2></div><a class="text-button" href="<?= e(page_url('schedule', ['date' => $today])) ?>">Ver agenda <?= icon('arrow', 14) ?></a></div>
        <div class="timeline">
          <?php if (!$dayAppointments): ?><?= table_empty(1, 'Nenhum atendimento previsto para hoje.') ?><?php endif; ?>
          <?php foreach ($dayAppointments as $item): ?>
            <div class="timeline-item"><time><?= e(short_time($item['appointment_time'])) ?></time><span class="pet-avatar"><?= icon('paw', 16) ?></span><div class="timeline-copy"><strong><?= e($item['pet_name']) ?></strong><span><?= e($item['service_name']) ?> · <?= e($item['client_name']) ?></span></div><?= status_badge($item['status']) ?></div>
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
        <div class="activity-list">
          <?php if (!$lowProducts): ?><div class="empty-inline">Tudo certo: não há produtos com estoque baixo.</div><?php endif; ?>
          <?php foreach ($lowProducts as $product): ?>
            <div><span class="activity-icon coral"><?= icon('box', 17) ?></span><p><strong><?= e($product['name']) ?></strong><small><?= e($product['stock']) ?> unidade(s) disponíveis</small></p><a href="<?= e(page_url('products', ['modal' => 'product', 'id' => $product['id']])) ?>">Atualizar</a></div>
          <?php endforeach; ?>
        </div>
      </article>
    </section>
    <?php
    return (string) ob_get_clean();
}
