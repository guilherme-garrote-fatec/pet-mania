<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$pdo = db();
if (db_driver($pdo) === 'sqlite') {
    $schema = file_get_contents(__DIR__ . '/schema.sqlite.sql');
    if ($schema === false) {
        throw new RuntimeException('Não foi possível ler o schema SQLite.');
    }
    $pdo->exec($schema);
}

if ((int) fetch_value($pdo, 'SELECT COUNT(*) FROM users') > 0) {
    fwrite(STDERR, "Já existe um usuário cadastrado. Nenhuma alteração foi feita.\n");
    exit(1);
}

$email = $argv[1] ?? 'admin@petmania.local';
$name = $argv[2] ?? 'Sônia Martins';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Informe um e-mail válido como primeiro argumento.\n");
    exit(1);
}
if (trim($name) === '' || text_length($name) > 120) {
    fwrite(STDERR, "Informe um nome com até 120 caracteres como segundo argumento.\n");
    exit(1);
}

$password = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');

$pdo->beginTransaction();
execute($pdo, 'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)', [$name, $email, password_hash($password, PASSWORD_DEFAULT), 'Administradora']);

if ((int) fetch_value($pdo, 'SELECT COUNT(*) FROM clients') === 0) {
    seed_demo_data($pdo);
}
$pdo->commit();

fwrite(STDOUT, "Banco inicializado com dados de demonstração.\n");
fwrite(STDOUT, "E-mail: {$email}\nSenha temporária: {$password}\n");
fwrite(STDOUT, "Guarde a senha agora; ela não será exibida novamente.\n");

/**
 * Dados de demonstração. Os vínculos usam os IDs gerados, e não valores fixos, para funcionar
 * mesmo quando o auto incremento das tabelas não começa em 1.
 */
function seed_demo_data(PDO $pdo): void
{
    $clients = [
        'mariana' => ['Mariana Costa', '(18) 99124-8033', 'mariana@email.com', ['bento' => ['Bento', 'Golden Retriever', 'Macho'], 'lola' => ['Lola', 'Shih-tzu', 'Fêmea']]],
        'rafael' => ['Rafael Nunes', '(18) 99732-1045', 'rafael@email.com', ['theo' => ['Theo', 'Spitz Alemão', 'Macho']]],
        'beatriz' => ['Beatriz Lima', '(18) 98813-4420', 'bia.lima@email.com', ['amora' => ['Amora', 'Poodle', 'Fêmea']]],
        'lucas' => ['Lucas Almeida', '(18) 99641-7820', 'lucas@email.com', ['max' => ['Max', 'Bulldog Francês', 'Macho']]],
    ];
    $clientIds = [];
    $petIds = [];
    foreach ($clients as $clientKey => [$clientName, $phone, $clientEmail, $pets]) {
        execute($pdo, 'INSERT INTO clients (name, phone, email) VALUES (?, ?, ?)', [$clientName, $phone, $clientEmail]);
        $clientIds[$clientKey] = (int) $pdo->lastInsertId();
        foreach ($pets as $petKey => $pet) {
            execute($pdo, 'INSERT INTO pets (client_id, name, breed, sex) VALUES (?, ?, ?, ?)', [$clientIds[$clientKey], ...$pet]);
            $petIds[$petKey] = (int) $pdo->lastInsertId();
        }
    }

    $serviceIds = [];
    foreach (['bath' => ['Banho completo', 75, 19, 60], 'grooming' => ['Banho + tosa', 125, 31, 90], 'hygienic' => ['Tosa higiênica', 55, 10, 45], 'hydration' => ['Hidratação premium', 48, 16, 30]] as $key => $service) {
        execute($pdo, 'INSERT INTO services (name, price, cost, duration, active) VALUES (?, ?, ?, ?, 1)', $service);
        $serviceIds[$key] = (int) $pdo->lastInsertId();
    }

    $productIds = [];
    foreach (['shampoo' => ['Shampoo neutro 500ml', 'Higiene', 14, 39.90, 20], 'perfume' => ['Perfume Pet Fresh', 'Higiene', 5, 34.90, 16], 'collar' => ['Coleira Comfort', 'Acessórios', 22, 49.90, 23], 'conditioner' => ['Condicionador Soft', 'Higiene', 3, 42, 21], 'toy' => ['Brinquedo mordedor', 'Brinquedos', 18, 27.50, 11]] as $key => $product) {
        execute($pdo, 'INSERT INTO products (name, category, stock, price, cost) VALUES (?, ?, ?, ?, ?)', $product);
        $productIds[$key] = (int) $pdo->lastInsertId();
    }

    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $appointments = [
        ['mariana', 'bento', 'bath', 'Eliane', $today, '08:30', 'Concluído'],
        ['rafael', 'theo', 'grooming', 'Eliane', $today, '10:00', 'Em andamento'],
        ['beatriz', 'amora', 'hygienic', 'Carla', $today, '13:30', 'Agendado'],
        ['mariana', 'lola', 'hydration', 'Carla', $today, '15:00', 'Agendado'],
        ['lucas', 'max', 'bath', 'Eliane', $tomorrow, '09:00', 'Agendado'],
    ];
    foreach ($appointments as [$client, $pet, $service, $professional, $date, $time, $status]) {
        execute($pdo, 'INSERT INTO appointments (client_id, pet_id, service_id, professional, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)', [$clientIds[$client], $petIds[$pet], $serviceIds[$service], $professional, $date, $time, $status]);
    }

    $transactions = [
        [$today, 'Banho completo · Bento', 'Receita', 75],
        [$today, 'Insumos · Banho Bento', 'Custo', 19],
        [$today, 'Comissão · Eliane', 'Comissão', 11.25],
        [$today, 'Venda balcão', 'Receita', 386.40],
        [$yesterday, 'Reposição de produtos', 'Custo', 128],
        [date('Y-m-d', strtotime('-2 days')), 'Serviços realizados', 'Receita', 490],
    ];
    foreach ($transactions as $transaction) {
        execute($pdo, 'INSERT INTO transactions (transaction_date, description, type, value, is_manual) VALUES (?, ?, ?, ?, 0)', $transaction);
    }

    execute($pdo, 'INSERT INTO sales (sale_date, product_id, quantity, client, unit_price, total) VALUES (?, ?, ?, ?, ?, ?)', [$today, $productIds['collar'], 1, 'Mariana Costa', 49.90, 49.90]);
    execute($pdo, 'INSERT INTO sales (sale_date, product_id, quantity, client, unit_price, total) VALUES (?, ?, ?, ?, ?, ?)', [$yesterday, $productIds['shampoo'], 2, 'Venda balcão', 39.90, 79.80]);
}
