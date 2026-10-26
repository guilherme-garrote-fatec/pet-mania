<?php
declare(strict_types=1);

require_once __DIR__ . '/repository.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/views.php';

return [
    'label' => 'Serviços',
    'icon' => 'scissors',
    'render' => 'render_services',
    'actions' => [
        'save_service' => 'save_service_action',
        'toggle_service' => 'toggle_service_action',
    ],
    'forms' => [
        'service' => [
            'title' => 'Novo serviço',
            'description' => 'Defina preço, custo e duração média.',
            'action' => 'save_service',
            'render' => 'render_service_form',
            'load' => 'find_service',
        ],
    ],
];
