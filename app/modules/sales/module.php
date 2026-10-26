<?php
declare(strict_types=1);

require_once __DIR__ . '/repository.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/views.php';

return [
    'label' => 'Vendas',
    'icon' => 'bag',
    'render' => 'render_sales',
    'actions' => [
        'save_sale' => 'save_sale_action',
    ],
    'forms' => [
        'sale' => [
            'title' => 'Registrar venda',
            'description' => 'A baixa no estoque será feita automaticamente.',
            'action' => 'save_sale',
            'render' => 'render_sale_form',
        ],
    ],
];
