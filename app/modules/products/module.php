<?php
declare(strict_types=1);

require_once __DIR__ . '/repository.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/views.php';

return [
    'label' => 'Produtos',
    'icon' => 'box',
    'render' => 'render_products',
    'actions' => [
        'save_product' => 'save_product_action',
        'delete_product' => 'delete_product_action',
    ],
    'forms' => [
        'product' => [
            'title' => 'Novo produto',
            'description' => 'Adicione um item ao catálogo da loja.',
            'action' => 'save_product',
            'render' => 'render_product_form',
            'load' => 'find_product',
        ],
    ],
];
