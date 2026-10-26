<?php
declare(strict_types=1);

function all_sales(PDO $pdo): array
{
    return fetch_all($pdo, 'SELECT s.*, p.name AS product_name FROM sales s JOIN products p ON p.id = s.product_id ORDER BY s.sale_date DESC, s.id DESC');
}
