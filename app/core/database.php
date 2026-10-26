<?php
declare(strict_types=1);

function database_dsn(): string
{
    $dsn = getenv('DB_DSN');
    if ($dsn === false || $dsn === '') {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'pet_mania';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
        return "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
    }

    if (str_starts_with($dsn, 'sqlite:')) {
        $sqlitePath = substr($dsn, strlen('sqlite:'));
        $isAbsolute = str_starts_with($sqlitePath, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $sqlitePath) === 1;
        if ($sqlitePath !== ':memory:' && !$isAbsolute) {
            $dsn = 'sqlite:' . APP_ROOT . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sqlitePath);
        }
    }
    return $dsn;
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
    if (db_driver($connection) === 'sqlite') {
        // O SQLite só aplica as chaves estrangeiras quando habilitadas por conexão.
        $connection->exec('PRAGMA foreign_keys = ON');
    }

    return $connection;
}

function db_driver(PDO $pdo): string
{
    return (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
}

/**
 * Cláusula de bloqueio de linha para leituras dentro de transações (o SQLite já bloqueia o banco inteiro).
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
