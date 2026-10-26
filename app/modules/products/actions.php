<?php
declare(strict_types=1);

function save_product_action(PDO $pdo): never
{
    $id = optional_id('id');
    $name = required_value('name', 'o nome do produto', 'products');
    $category = required_value('category', 'a categoria', 'products');
    if (!within_lengths([$name, 160], [$category, 80])) {
        action_error('O nome ou a categoria ultrapassou o tamanho permitido.', 'products');
    }
    $price = post_amount('price');
    $cost = post_amount('cost', allowZero: true);
    $stock = post_integer('stock', 0);
    if ($price === null || $cost === null || $stock === null) {
        action_error('Informe preço, custo e estoque válidos.', 'products');
    }

    if ($id) {
        if (find_product($pdo, $id) === null) {
            action_error('Produto não encontrado.', 'products');
        }
        execute($pdo, 'UPDATE products SET name = ?, category = ?, price = ?, cost = ?, stock = ? WHERE id = ?', [$name, $category, $price, $cost, $stock, $id]);
        set_flash('Produto atualizado.');
    } else {
        execute($pdo, 'INSERT INTO products (name, category, price, cost, stock) VALUES (?, ?, ?, ?, ?)', [$name, $category, $price, $cost, $stock]);
        set_flash('Produto adicionado ao catálogo.');
    }
    redirect_to('products');
}

function delete_product_action(PDO $pdo): never
{
    $id = valid_id('id', 'o produto', 'products');
    if (product_has_sales($pdo, $id)) {
        action_error('Este produto possui vendas registradas e não pode ser removido.', 'products');
    }
    $deleted = execute($pdo, 'DELETE FROM products WHERE id = ?', [$id]);
    set_flash($deleted ? 'Produto removido.' : 'Produto não encontrado.', $deleted ? 'success' : 'error');
    redirect_to('products');
}
