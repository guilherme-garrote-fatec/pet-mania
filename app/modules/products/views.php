<?php
declare(strict_types=1);

function render_products(PDO $pdo, array $user): string
{
    $query = query_value('q');
    $products = search_products($pdo, $query);
    $low = count_low_stock_products($pdo);
    ob_start();
    ?>
    <?= page_header('CATÁLOGO E ESTOQUE', 'Produtos', 'Gerencie catálogo, preços e níveis de estoque.', button_link('products', 'product', 'Novo produto')) ?>
    <div class="toolbar"><form method="get" class="search-box"><?= icon('search', 17) ?><input type="hidden" name="page" value="products"><input type="search" name="q" value="<?= e($query) ?>" placeholder="Buscar produto..."></form><div class="stock-alert"><?= icon('box', 16) ?> <?= $low ?> item(ns) com estoque baixo</div></div>
    <article class="panel table-panel"><div class="data-table">
      <div class="table-row table-head product-row"><span>Produto</span><span>Categoria</span><span>Estoque</span><span>Preço</span><span>Margem</span><span></span></div>
      <?php if (!$products): ?><?= table_empty(6, 'Tente outra busca ou cadastre um produto.') ?><?php endif; ?>
      <?php foreach ($products as $item): ?>
        <div class="table-row product-row"><span class="product-name"><i><?= icon('box', 18) ?></i><strong><?= e($item['name']) ?><small>SKU PM-<?= e(str_pad((string) $item['id'], 4, '0', STR_PAD_LEFT)) ?></small></strong></span><span><?= e($item['category']) ?></span><span><strong><?= e($item['stock']) ?> un.</strong><?= (int) $item['stock'] <= LOW_STOCK_THRESHOLD ? '<span class="status-badge status-badge--low">Estoque baixo</span>' : '' ?></span><span><?= e(money($item['price'])) ?></span><span class="positive"><?= margin_percent($item['price'], $item['cost']) ?>%</span><span class="row-actions"><a class="icon-button" title="Editar produto" href="<?= e(page_url('products', ['modal' => 'product', 'id' => $item['id']])) ?>"><?= icon('edit', 15) ?></a><?= post_button('delete_product', 'products', icon('close', 16), 'icon-button icon-button--danger', ['id' => $item['id']], 'Remover este produto? Produtos com vendas anteriores não podem ser removidos.') ?></span></div>
      <?php endforeach; ?>
    </div></article>
    <?php
    return (string) ob_get_clean();
}

function render_product_form(PDO $pdo, ?array $record): string
{
    $categories = PRODUCT_CATEGORIES;
    if (isset($record['category']) && !in_array($record['category'], $categories, true)) {
        $categories[] = $record['category'];
    }
    ob_start();
    ?>
    <label class="field"><span>Nome do produto <b>*</b></span><input name="name" value="<?= e($record['name'] ?? '') ?>" maxlength="160" required></label>
    <label class="field"><span>Categoria <b>*</b></span><select name="category" required><option value="">Selecione</option><?= select_options(array_combine($categories, $categories), $record['category'] ?? null) ?></select></label>
    <div class="form-grid form-grid--three"><label class="field"><span>Preço <b>*</b></span><input type="number" name="price" min="0.01" max="<?= e(MAX_AMOUNT) ?>" step="0.01" value="<?= e($record['price'] ?? '') ?>" required></label><label class="field"><span>Custo <b>*</b></span><input type="number" name="cost" min="0" max="<?= e(MAX_AMOUNT) ?>" step="0.01" value="<?= e($record['cost'] ?? '') ?>" required></label><label class="field"><span>Estoque <b>*</b></span><input type="number" name="stock" min="0" max="<?= e(MAX_QUANTITY) ?>" step="1" value="<?= e($record['stock'] ?? '') ?>" required></label></div>
    <?php
    return (string) ob_get_clean();
}
