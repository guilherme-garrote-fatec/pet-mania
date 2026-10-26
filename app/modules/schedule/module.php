<?php
declare(strict_types=1);

require_once __DIR__ . '/repository.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/views.php';

return [
    'label' => 'Agenda',
    'icon' => 'calendar',
    'render' => 'render_schedule',
    'actions' => [
        'save_appointment' => 'save_appointment_action',
        'complete_appointment' => 'complete_appointment_action',
        'cancel_appointment' => 'cancel_appointment_action',
    ],
    'forms' => [
        'appointment' => [
            'title' => 'Novo agendamento',
            'description' => 'Organize o próximo cuidado do pet.',
            'action' => 'save_appointment',
            'render' => 'render_appointment_form',
            'load' => 'find_editable_appointment',
        ],
    ],
];
