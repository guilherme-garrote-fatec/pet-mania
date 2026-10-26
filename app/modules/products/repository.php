<?php
declare(strict_types=1);

const PRODUCT_CATEGORIES = ['Higiene', 'Acessórios', 'Brinquedos', 'Alimentação'];
/** Produtos com estoque igual ou abaixo deste valor são destacados. */
const LOW_STOCK_THRESHOLD = 5;

function find_product(PDO $pdo, int $id): ?array
{
    return fetch_one($pdo, 'SELECT * FROM products WHERE id = ?', [$id]);
}

function search_products(PDO $pdo, string $term): array
{
    $pattern = like_pattern($term);
    return fetch_all($pdo, "SELECT * FROM products WHERE name LIKE ? ESCAPE '!' OR category LIKE ? ESCAPE '!' ORDER BY name", [$pattern, $pattern]);
}

function all_products(PDO $pdo): array
{
    return fetch_all($pdo, 'SELECT id, name, stock, price FROM products ORDER BY name');
}

function count_low_stock_products(PDO $pdo): int
{
    return (int) fetch_value($pdo, 'SELECT COUNT(*) FROM products WHERE stock <= ?', [LOW_STOCK_THRESHOLD]);
}

function low_stock_products(PDO $pdo, int $limit): array
{
    return fetch_all($pdo, 'SELECT id, name, stock FROM products WHERE stock <= ? ORDER BY stock, name LIMIT ' . $limit, [LOW_STOCK_THRESHOLD]);
}

function product_has_sales(PDO $pdo, int $id): bool
{
    return (int) fetch_value($pdo, 'SELECT COUNT(*) FROM sales WHERE product_id = ?', [$id]) > 0;
}
