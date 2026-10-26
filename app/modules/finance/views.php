<?php
declare(strict_types=1);

function transaction_type_class(string $type): string
{
    return match ($type) {
        'Comissão' => 'comissao',
        'Custo' => 'custo',
        default => 'receita',
    };
}

function render_finance(PDO $pdo, array $user): string
{
    $totals = current_month_totals($pdo);
    $profit = net_profit($totals);
    $rows = recent_transactions($pdo, 100);
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
      <?php foreach ($rows as $item): $income = $item['type'] === 'Receita'; ?><div class="table-row finance-row"><span><?= e(date_br($item['transaction_date'])) ?></span><span><strong><?= e($item['description']) ?></strong></span><span><span class="transaction-type transaction-type--<?= e(transaction_type_class($item['type'])) ?>"><?= e($item['type']) ?></span></span><span class="<?= $income ? 'positive' : 'negative' ?>"><strong><?= $income ? '+' : '−' ?> <?= e(money($item['value'])) ?></strong></span><span class="row-actions"><?php if ((int) $item['is_manual'] === 1): ?><a class="icon-button" title="Editar lançamento" href="<?= e(page_url('finance', ['modal' => 'transaction', 'id' => $item['id']])) ?>"><?= icon('edit', 15) ?></a><?= post_button('delete_transaction', 'finance', icon('close', 15), 'icon-button icon-button--danger', ['id' => $item['id']], 'Remover este lançamento manual?') ?><?php else: ?><span class="muted">Automático</span><?php endif; ?></span></div><?php endforeach; ?>
    </div></article>
    <?php
    return (string) ob_get_clean();
}

function render_transaction_form(PDO $pdo, ?array $record): string
{
    $types = array_combine(TRANSACTION_TYPES, TRANSACTION_TYPES);
    ob_start();
    ?>
    <div class="form-grid"><label class="field"><span>Tipo <b>*</b></span><select name="type" required><option value="">Selecione</option><?= select_options($types, $record['type'] ?? null) ?></select></label><label class="field"><span>Data <b>*</b></span><input type="date" name="date" value="<?= e($record['transaction_date'] ?? date('Y-m-d')) ?>" required></label></div>
    <label class="field"><span>Descrição <b>*</b></span><input name="description" value="<?= e($record['description'] ?? '') ?>" maxlength="180" required placeholder="Ex.: Compra de insumos"></label>
    <label class="field"><span>Valor <b>*</b></span><input type="number" name="value" min="0.01" max="<?= e(MAX_AMOUNT) ?>" step="0.01" value="<?= e($record['value'] ?? '') ?>" required></label>
    <?php
    return (string) ob_get_clean();
}
