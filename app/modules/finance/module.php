<?php
declare(strict_types=1);

require_once __DIR__ . '/repository.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/views.php';

return [
    'label' => 'Financeiro',
    'icon' => 'wallet',
    'render' => 'render_finance',
    'actions' => [
        'save_transaction' => 'save_transaction_action',
        'delete_transaction' => 'delete_transaction_action',
    ],
    'forms' => [
        'transaction' => [
            'title' => 'Novo lançamento',
            'description' => 'Registre uma receita, custo ou comissão.',
            'action' => 'save_transaction',
            'render' => 'render_transaction_form',
            'load' => 'find_manual_transaction',
        ],
    ],
];
