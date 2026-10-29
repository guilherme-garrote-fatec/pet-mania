<?php
declare(strict_types=1);

function save_appointment_action(PDO $pdo): never
{
    $id = optional_id('id');
    $clientId = valid_id('client_id', 'o cliente', 'schedule');
    $petId = valid_id('pet_id', 'o pet', 'schedule');
    $serviceId = valid_id('service_id', 'o serviço', 'schedule');
    $date = required_value('date', 'a data', 'schedule');
    $time = required_value('time', 'o horário', 'schedule');
    $professional = required_value('professional', 'o profissional', 'schedule');
    if (!within_lengths([$professional, 120]) || !valid_date($date) || !valid_time($time)) {
        action_error('Informe profissional, data e horário válidos.', 'schedule');
    }
    if ($id && find_editable_appointment($pdo, $id) === null) {
        action_error('Somente agendamentos pendentes podem ser editados.', 'schedule');
    }
    if (!pet_belongs_to_client($pdo, $petId, $clientId)) {
        action_error('O pet selecionado não pertence a este cliente.', 'schedule');
    }
    if (!is_active_service($pdo, $serviceId)) {
        action_error('Selecione um serviço ativo.', 'schedule');
    }
    if (is_time_slot_taken($pdo, $date, $time, $id)) {
        action_error('Este horário já está ocupado. Escolha outro horário.', 'schedule', ['date' => $date]);
    }

    if ($id) {
        execute($pdo, "UPDATE appointments SET client_id = ?, pet_id = ?, service_id = ?, professional = ?, appointment_date = ?, appointment_time = ? WHERE id = ? AND status = 'Agendado'", [$clientId, $petId, $serviceId, $professional, $date, $time, $id]);
        set_flash('Agendamento atualizado.');
    } else {
        execute($pdo, "INSERT INTO appointments (client_id, pet_id, service_id, professional, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?, 'Agendado')", [$clientId, $petId, $serviceId, $professional, $date, $time]);
        set_flash('Agendamento criado com sucesso.');
    }
    redirect_to('schedule', ['date' => $date]);
}

/**
 * Conclui o atendimento e lança receita, custo de insumos e comissão na mesma transação.
 */
function complete_appointment_action(PDO $pdo): never
{
    $id = valid_id('id', 'o agendamento', 'schedule');
    $pdo->beginTransaction();
    $appointment = fetch_one($pdo, 'SELECT a.id, a.appointment_date, a.status, a.professional, p.name AS pet_name, s.name AS service_name, s.price, s.cost FROM appointments a JOIN pets p ON p.id = a.pet_id JOIN services s ON s.id = a.service_id WHERE a.id = ?' . lock_clause($pdo), [$id]);
    if ($appointment === null || in_array($appointment['status'], ['Cancelado', 'Concluído'], true)) {
        $pdo->rollBack();
        action_error('Este agendamento não pode ser concluído.', 'schedule');
    }
    $updated = execute($pdo, "UPDATE appointments SET status = 'Concluído' WHERE id = ? AND status <> 'Concluído' AND status <> 'Cancelado'", [$id]);
    if ($updated !== 1) {
        $pdo->rollBack();
        action_error('O agendamento já foi atualizado.', 'schedule');
    }

    $date = $appointment['appointment_date'];
    record_automatic_transaction($pdo, $date, 'Serviço · ' . $appointment['service_name'] . ' · ' . $appointment['pet_name'], 'Receita', (float) $appointment['price']);
    record_automatic_transaction($pdo, $date, 'Insumos · ' . $appointment['pet_name'], 'Custo', (float) $appointment['cost']);
    record_automatic_transaction($pdo, $date, 'Comissão · ' . $appointment['professional'], 'Comissão', (float) $appointment['price'] * COMMISSION_RATE);
    $pdo->commit();

    set_flash('Atendimento concluído e financeiro atualizado.');
    redirect_to('schedule', ['date' => $date]);
}

function cancel_appointment_action(PDO $pdo): never
{
    $id = valid_id('id', 'o agendamento', 'schedule');
    $date = fetch_value($pdo, 'SELECT appointment_date FROM appointments WHERE id = ?', [$id]);
    $cancelled = execute($pdo, "UPDATE appointments SET status = 'Cancelado' WHERE id = ? AND status = 'Agendado'", [$id]);
    set_flash($cancelled ? 'Agendamento cancelado.' : 'Somente agendamentos pendentes podem ser cancelados.', $cancelled ? 'success' : 'error');
    redirect_to('schedule', is_string($date) ? ['date' => $date] : []);
}
