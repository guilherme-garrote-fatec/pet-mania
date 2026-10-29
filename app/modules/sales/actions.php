<?php
declare(strict_types=1);

/**
 * Grava a venda, baixa o estoque e lança receita e CMV em uma única transação.
 */
function save_sale_action(PDO $pdo): never
{
    $productId = valid_id('product_id', 'o produto', 'sales');
    $quantity = post_integer('quantity', 1);
    if ($quantity === null) {
        action_error('Informe uma quantidade válida.', 'sales');
    }
    $client = post_value('client') ?: 'Venda balcão';
    if (!within_lengths([$client, 120])) {
        action_error('O nome do cliente ultrapassou o tamanho permitido.', 'sales');
    }

    $pdo->beginTransaction();
    $product = fetch_one($pdo, 'SELECT id, name, stock, price, cost FROM products WHERE id = ?' . lock_clause($pdo), [$productId]);
    if ($product === null || (int) $product['stock'] < $quantity) {
        $pdo->rollBack();
        action_error('Estoque insuficiente para a quantidade solicitada.', 'sales');
    }
    $total = round((float) $product['price'] * $quantity, 2);
    if ($total > MAX_AMOUNT) {
        $pdo->rollBack();
        action_error('O total da venda ultrapassou o valor permitido.', 'sales');
    }
    if (execute($pdo, 'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?', [$quantity, $productId, $quantity]) !== 1) {
        $pdo->rollBack();
        action_error('O estoque foi alterado por outra venda. Tente novamente.', 'sales');
    }

    $today = date('Y-m-d');
    execute($pdo, 'INSERT INTO sales (sale_date, product_id, quantity, client, unit_price, total) VALUES (?, ?, ?, ?, ?, ?)', [$today, $productId, $quantity, $client, $product['price'], $total]);
    record_automatic_transaction($pdo, $today, 'Venda · ' . $product['name'], 'Receita', $total);
    record_automatic_transaction($pdo, $today, 'CMV · ' . $product['name'], 'Custo', (float) $product['cost'] * $quantity);
    $pdo->commit();

    set_flash('Venda registrada e estoque atualizado.');
    redirect_to('sales');
}
