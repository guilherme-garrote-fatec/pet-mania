<?php
declare(strict_types=1);

require_once __DIR__ . '/repository.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/views.php';

return [
    'label' => 'Clientes & Pets',
    'icon' => 'users',
    'render' => 'render_clients',
    'actions' => [
        'save_client' => 'save_client_action',
    ],
    'forms' => [
        'client' => [
            'title' => 'Novo cliente e pet',
            'description' => 'Cadastre o tutor e seu primeiro pet.',
            'action' => 'save_client',
            'render' => 'render_client_form',
            'load' => 'find_client',
        ],
    ],
];
