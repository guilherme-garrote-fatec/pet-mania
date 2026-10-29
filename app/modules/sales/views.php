<?php
declare(strict_types=1);

function render_sales(PDO $pdo, array $user): string
{
    $rows = all_sales($pdo);
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

function render_sale_form(PDO $pdo, ?array $record): string
{
    $products = all_products($pdo);
    ob_start();
    ?>
    <label class="field"><span>Produto <b>*</b></span><select name="product_id" required data-sale-product><option value="">Selecione o produto</option><?php foreach ($products as $product): ?><option value="<?= e($product['id']) ?>" data-price="<?= e($product['price']) ?>" data-stock="<?= e($product['stock']) ?>"><?= e($product['name']) ?> · <?= e($product['stock']) ?> em estoque</option><?php endforeach; ?></select></label>
    <div class="form-grid"><label class="field"><span>Quantidade <b>*</b></span><input type="number" name="quantity" value="1" min="1" step="1" required data-sale-quantity></label><label class="field"><span>Cliente (opcional)</span><input name="client" maxlength="120" placeholder="Venda balcão"></label></div>
    <div class="sale-total"><span>Total da venda</span><strong data-sale-total>R$ 0,00</strong></div>
    <?php
    return (string) ob_get_clean();
}
