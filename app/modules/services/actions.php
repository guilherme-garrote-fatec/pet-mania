<?php
declare(strict_types=1);

function save_service_action(PDO $pdo): never
{
    $id = optional_id('id');
    $name = required_value('name', 'o nome do serviço', 'services');
    if (!within_lengths([$name, 120])) {
        action_error('O nome do serviço ultrapassou o tamanho permitido.', 'services');
    }
    $price = post_amount('price');
    $cost = post_amount('cost', allowZero: true);
    $duration = post_integer('duration', 1, MAX_SERVICE_DURATION);
    if ($price === null || $cost === null || $duration === null) {
        action_error('Informe preço, custo e duração válidos.', 'services');
    }

    if ($id) {
        if (find_service($pdo, $id) === null) {
            action_error('Serviço não encontrado.', 'services');
        }
        execute($pdo, 'UPDATE services SET name = ?, price = ?, cost = ?, duration = ? WHERE id = ?', [$name, $price, $cost, $duration, $id]);
        set_flash('Serviço atualizado.');
    } else {
        execute($pdo, 'INSERT INTO services (name, price, cost, duration, active) VALUES (?, ?, ?, ?, 1)', [$name, $price, $cost, $duration]);
        set_flash('Serviço cadastrado com sucesso.');
    }
    redirect_to('services');
}

function toggle_service_action(PDO $pdo): never
{
    $id = valid_id('id', 'o serviço', 'services');
    $updated = execute($pdo, 'UPDATE services SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END WHERE id = ?', [$id]);
    set_flash($updated ? 'Status do serviço atualizado.' : 'Serviço não encontrado.', $updated ? 'success' : 'error');
    redirect_to('services');
}
