<?php
declare(strict_types=1);

/** Duração máxima de um serviço, em minutos. */
const MAX_SERVICE_DURATION = 1440;

function find_service(PDO $pdo, int $id): ?array
{
    return fetch_one($pdo, 'SELECT * FROM services WHERE id = ?', [$id]);
}

function all_services(PDO $pdo): array
{
    return fetch_all($pdo, 'SELECT * FROM services ORDER BY active DESC, name');
}

function active_services(PDO $pdo): array
{
    return fetch_all($pdo, 'SELECT id, name, price FROM services WHERE active = 1 ORDER BY name');
}

function is_active_service(PDO $pdo, int $id): bool
{
    return fetch_one($pdo, 'SELECT id FROM services WHERE id = ? AND active = 1', [$id]) !== null;
}

/**
 * Serviços com a quantidade de atendimentos concluídos no intervalo [$start, $end).
 */
function service_performance(PDO $pdo, string $start, string $end): array
{
    return fetch_all(
        $pdo,
        "SELECT s.id, s.name, s.price, s.cost, COUNT(a.id) AS count FROM services s LEFT JOIN appointments a ON a.service_id = s.id AND a.status = 'Concluído' AND a.appointment_date >= ? AND a.appointment_date < ? GROUP BY s.id, s.name, s.price, s.cost ORDER BY count DESC, s.name",
        [$start, $end],
    );
}
