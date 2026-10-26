<?php
declare(strict_types=1);

function render_reports(PDO $pdo, array $user): string
{
    [$period, $start, $end] = report_period();
    $type = report_type();
    $totals = transaction_totals($pdo, $start, $end);
    $appointmentStats = period_appointment_stats($pdo, $start, $end);
    $services = service_performance($pdo, $start, $end);
    $maxCount = max(array_map(static fn(array $service): int => (int) $service['count'], $services) ?: [1]);
    $rows = transactions_between($pdo, $start, $end, $type === 'all' ? null : $type, 50);
    $rate = $appointmentStats['total'] > 0 ? round($appointmentStats['completed'] / $appointmentStats['total'] * 100) : 0;
    $exportUrl = page_url('reports', ['period' => $period, 'type' => $type, 'export' => 'csv']);
    ob_start();
    ?>
    <?= page_header('ANÁLISE DO NEGÓCIO', 'Relatórios', 'Dados consolidados para decisões mais inteligentes.', '<a class="button button--primary" href="' . e($exportUrl) . '">' . icon('chart', 17) . ' Exportar CSV</a>') ?>
    <form method="get" class="report-filters"><input type="hidden" name="page" value="reports"><label><span>Período</span><select name="period"><?= select_options(REPORT_PERIODS, $period) ?></select></label><label><span>Tipo de relatório</span><select name="type"><?= select_options(REPORT_TYPES, $type) ?></select></label><button class="button button--secondary" type="submit">Aplicar filtros</button></form>
    <section class="report-grid">
      <article class="panel"><div class="panel-head"><div><span>DESEMPENHO</span><h2>Serviços mais realizados</h2></div></div><div class="ranking">
        <?php if (!$services): ?><p class="muted">Nenhum serviço cadastrado.</p><?php endif; ?>
        <?php foreach ($services as $index => $service): ?><div><b><?= $index + 1 ?></b><span><strong><?= e($service['name']) ?></strong><small><?= e($service['count']) ?> atendimento(s) concluído(s)</small></span><i><em style="width:<?= $maxCount > 0 ? max(3, (int) ((int) $service['count'] / $maxCount * 100)) : 3 ?>%"></em></i><strong><?= e(money((float) $service['price'] * (int) $service['count'])) ?></strong></div><?php endforeach; ?>
      </div></article>
      <article class="panel report-summary"><div class="panel-head"><div><span>RESUMO</span><h2>Indicadores do período</h2></div><span class="period-pill"><?= e(REPORT_PERIODS[$period]) ?></span></div><div class="summary-number"><span>Taxa de conclusão</span><strong><?= $rate ?>%</strong><small><?= e($appointmentStats['completed']) ?> de <?= e($appointmentStats['total']) ?> atendimentos</small></div><div class="summary-grid"><div><span>Receita</span><strong><?= e(money($totals['Receita'])) ?></strong></div><div><span>Custos</span><strong><?= e(money($totals['Custo'])) ?></strong></div><div><span>Comissões</span><strong><?= e(money($totals['Comissão'])) ?></strong></div><div><span>Cancelamentos</span><strong><?= e($appointmentStats['cancelled']) ?></strong></div></div></article>
    </section>
    <article class="panel table-panel"><div class="panel-head padded"><div><span>ANÁLISE</span><h2>Resultado por serviço</h2></div></div><div class="data-table"><div class="table-row report-row table-head"><span>Serviço</span><span>Atendimentos</span><span>Receita estimada</span><span>Custo estimado</span><span>Margem</span></div>
      <?php foreach ($services as $service): ?><div class="table-row report-row"><span><strong><?= e($service['name']) ?></strong></span><span><?= e($service['count']) ?></span><span><?= e(money((float) $service['price'] * (int) $service['count'])) ?></span><span><?= e(money((float) $service['cost'] * (int) $service['count'])) ?></span><span class="positive"><strong><?= margin_percent($service['price'], $service['cost']) ?>%</strong></span></div><?php endforeach; ?>
      <?php if (!$services): ?><?= table_empty(5, 'Os serviços concluídos no período aparecerão aqui.') ?><?php endif; ?>
    </div></article>
    <article class="panel table-panel report-transactions"><div class="panel-head padded"><div><span>MOVIMENTAÇÕES</span><h2>Lançamentos do período</h2></div></div><div class="data-table"><div class="table-row finance-row table-head"><span>Data</span><span>Descrição</span><span>Tipo</span><span>Valor</span></div>
      <?php foreach ($rows as $item): ?><div class="table-row finance-row"><span><?= e(date_br($item['transaction_date'])) ?></span><span><strong><?= e($item['description']) ?></strong></span><span><?= e($item['type']) ?></span><span class="<?= $item['type'] === 'Receita' ? 'positive' : 'negative' ?>"><strong><?= e(money($item['value'])) ?></strong></span></div><?php endforeach; ?>
      <?php if (!$rows): ?><?= table_empty(4, 'Não há lançamentos para esse período e filtro.') ?><?php endif; ?>
    </div></article>
    <?php
    return (string) ob_get_clean();
}
