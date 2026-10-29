<?php
declare(strict_types=1);

function save_transaction_action(PDO $pdo): never
{
    $id = optional_id('id');
    $description = required_value('description', 'a descrição', 'finance');
    $type = post_value('type');
    $date = post_value('date') ?: date('Y-m-d');
    $value = post_amount('value');
    if (!within_lengths([$description, 180]) || !in_array($type, TRANSACTION_TYPES, true) || !valid_date($date) || $value === null) {
        action_error('Informe tipo, data e valor válidos para o lançamento.', 'finance');
    }

    if ($id) {
        if (find_manual_transaction($pdo, $id) === null) {
            action_error('Lançamento manual não encontrado.', 'finance');
        }
        execute($pdo, 'UPDATE transactions SET transaction_date = ?, description = ?, type = ?, value = ? WHERE id = ? AND is_manual = 1', [$date, $description, $type, $value, $id]);
        set_flash('Lançamento financeiro atualizado.');
    } else {
        execute($pdo, 'INSERT INTO transactions (transaction_date, description, type, value, is_manual) VALUES (?, ?, ?, ?, 1)', [$date, $description, $type, $value]);
        set_flash('Lançamento financeiro registrado.');
    }
    redirect_to('finance');
}

function delete_transaction_action(PDO $pdo): never
{
    $id = valid_id('id', 'o lançamento', 'finance');
    $deleted = execute($pdo, 'DELETE FROM transactions WHERE id = ? AND is_manual = 1', [$id]);
    set_flash($deleted ? 'Lançamento manual removido.' : 'Lançamentos automáticos não podem ser removidos.', $deleted ? 'success' : 'error');
    redirect_to('finance');
}
