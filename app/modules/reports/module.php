<?php
declare(strict_types=1);

require_once __DIR__ . '/repository.php';
require_once __DIR__ . '/views.php';

return [
    'label' => 'Relatórios',
    'icon' => 'chart',
    'render' => 'render_reports',
    'exports' => [
        'csv' => 'export_report_csv',
    ],
];
