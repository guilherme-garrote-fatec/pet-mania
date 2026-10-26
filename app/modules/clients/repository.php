<?php
declare(strict_types=1);

const PET_SEXES = ['Não informado', 'Macho', 'Fêmea'];

function find_client(PDO $pdo, int $id): ?array
{
    return fetch_one($pdo, 'SELECT * FROM clients WHERE id = ?', [$id]);
}

function search_clients(PDO $pdo, string $term): array
{
    if ($term === '') {
        return fetch_all($pdo, 'SELECT c.* FROM clients c ORDER BY c.name');
    }
    $pattern = like_pattern($term);
    return fetch_all(
        $pdo,
        "SELECT c.* FROM clients c WHERE c.name LIKE ? ESCAPE '!' OR c.phone LIKE ? ESCAPE '!' OR EXISTS (SELECT 1 FROM pets p WHERE p.client_id = c.id AND p.name LIKE ? ESCAPE '!') ORDER BY c.name",
        [$pattern, $pattern, $pattern],
    );
}

function all_clients(PDO $pdo): array
{
    return fetch_all($pdo, 'SELECT id, name FROM clients ORDER BY name');
}

function count_clients(PDO $pdo): int
{
    return (int) fetch_value($pdo, 'SELECT COUNT(*) FROM clients');
}

function count_pets(PDO $pdo): int
{
    return (int) fetch_value($pdo, 'SELECT COUNT(*) FROM pets');
}

function all_pets(PDO $pdo): array
{
    return fetch_all($pdo, 'SELECT id, client_id, name FROM pets ORDER BY name');
}

function client_pets(PDO $pdo, int $clientId): array
{
    return fetch_all($pdo, 'SELECT * FROM pets WHERE client_id = ? ORDER BY name', [$clientId]);
}

/**
 * Nomes dos pets agrupados por cliente, em uma única consulta.
 *
 * @param list<int> $clientIds
 * @return array<int, list<string>>
 */
function pet_names_by_client(PDO $pdo, array $clientIds): array
{
    if (!$clientIds) {
        return [];
    }
    $placeholders = implode(', ', array_fill(0, count($clientIds), '?'));
    $names = [];
    foreach (fetch_all($pdo, "SELECT client_id, name FROM pets WHERE client_id IN ({$placeholders}) ORDER BY id", array_values($clientIds)) as $pet) {
        $names[(int) $pet['client_id']][] = $pet['name'];
    }
    return $names;
}

function pet_belongs_to_client(PDO $pdo, int $petId, int $clientId): bool
{
    return fetch_one($pdo, 'SELECT id FROM pets WHERE id = ? AND client_id = ?', [$petId, $clientId]) !== null;
}

function insert_pet(PDO $pdo, int $clientId, string $name, string $breed, string $sex): void
{
    execute($pdo, 'INSERT INTO pets (client_id, name, breed, sex) VALUES (?, ?, ?, ?)', [$clientId, $name, $breed, $sex]);
}
