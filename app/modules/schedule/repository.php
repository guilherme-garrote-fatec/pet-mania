<?php
declare(strict_types=1);

const APPOINTMENT_STATUSES = ['Agendado', 'Em andamento', 'Concluído', 'Cancelado'];
/** Percentual do preço do serviço pago ao profissional na conclusão do atendimento. */
const COMMISSION_RATE = 0.15;

const APPOINTMENT_DETAILS_SQL = 'SELECT a.*, c.name AS client_name, p.name AS pet_name, p.breed, s.name AS service_name, s.duration FROM appointments a JOIN clients c ON c.id = a.client_id JOIN pets p ON p.id = a.pet_id JOIN services s ON s.id = a.service_id';

/**
 * Somente agendamentos pendentes podem ser editados.
 */
function find_editable_appointment(PDO $pdo, int $id): ?array
{
    return fetch_one($pdo, "SELECT * FROM appointments WHERE id = ? AND status = 'Agendado'", [$id]);
}

function day_appointments(PDO $pdo, string $date, string $status = 'Todos', string $term = ''): array
{
    $sql = APPOINTMENT_DETAILS_SQL . ' WHERE a.appointment_date = ?';
    $params = [$date];
    if ($status !== 'Todos') {
        $sql .= ' AND a.status = ?';
        $params[] = $status;
    }
    if ($term !== '') {
        $sql .= " AND (c.name LIKE ? ESCAPE '!' OR p.name LIKE ? ESCAPE '!' OR s.name LIKE ? ESCAPE '!')";
        $pattern = like_pattern($term);
        array_push($params, $pattern, $pattern, $pattern);
    }
    return fetch_all($pdo, $sql . ' ORDER BY a.appointment_time', $params);
}

function upcoming_day_appointments(PDO $pdo, string $date, int $limit): array
{
    return fetch_all($pdo, APPOINTMENT_DETAILS_SQL . " WHERE a.appointment_date = ? AND a.status <> 'Cancelado' ORDER BY a.appointment_time LIMIT " . $limit, [$date]);
}

/**
 * @return array{total: int, completed: int}
 */
function day_appointment_stats(PDO $pdo, string $date): array
{
    $row = fetch_one($pdo, "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'Concluído' THEN 1 ELSE 0 END) AS completed FROM appointments WHERE appointment_date = ? AND status <> 'Cancelado'", [$date]);
    return ['total' => (int) ($row['total'] ?? 0), 'completed' => (int) ($row['completed'] ?? 0)];
}

/**
 * @return array{total: int, completed: int, cancelled: int}
 */
function period_appointment_stats(PDO $pdo, string $start, string $end): array
{
    $row = fetch_one($pdo, "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'Concluído' THEN 1 ELSE 0 END) AS completed, SUM(CASE WHEN status = 'Cancelado' THEN 1 ELSE 0 END) AS cancelled FROM appointments WHERE appointment_date >= ? AND appointment_date < ?", [$start, $end]);
    return ['total' => (int) ($row['total'] ?? 0), 'completed' => (int) ($row['completed'] ?? 0), 'cancelled' => (int) ($row['cancelled'] ?? 0)];
}

function client_appointment_history(PDO $pdo, int $clientId, int $limit): array
{
    return fetch_all($pdo, 'SELECT a.*, p.name AS pet_name, s.name AS service_name FROM appointments a JOIN pets p ON p.id = a.pet_id JOIN services s ON s.id = a.service_id WHERE a.client_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT ' . $limit, [$clientId]);
}

function is_time_slot_taken(PDO $pdo, string $date, string $time, ?int $ignoreId = null): bool
{
    $sql = "SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status <> 'Cancelado'";
    $params = [$date, $time];
    if ($ignoreId !== null) {
        $sql .= ' AND id <> ?';
        $params[] = $ignoreId;
    }
    return (int) fetch_value($pdo, $sql, $params) > 0;
}
