<?php
declare(strict_types=1);

function database_dsn(): string
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'pet_mania';
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
    return "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
}

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $connection = new PDO(
        database_dsn(),
        getenv('DB_USER') ?: '',
        getenv('DB_PASSWORD') ?: '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );

    return $connection;
}

function db_driver(PDO $pdo): string
{
    return (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
}

/**
 * Cláusula de bloqueio de linha para leituras dentro de transações.
 */
function lock_clause(PDO $pdo): string
{
    return db_driver($pdo) === 'mysql' ? ' FOR UPDATE' : '';
}

function fetch_one(PDO $pdo, string $sql, array $params = []): ?array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch();
    return is_array($row) ? $row : null;
}

function fetch_all(PDO $pdo, string $sql, array $params = []): array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function fetch_value(PDO $pdo, string $sql, array $params = []): mixed
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return $statement->fetchColumn();
}

function execute(PDO $pdo, string $sql, array $params = []): int
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return $statement->rowCount();
}

/**
 * Padrão para LIKE com os curingas do usuário escapados. Use com `LIKE ? ESCAPE '!'`.
 */
function like_pattern(string $term): string
{
    return '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
}
