<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$pdo = db();
if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
    $schema = file_get_contents(__DIR__ . '/schema.sqlite.sql');
    if ($schema === false) {
        throw new RuntimeException('Não foi possível ler o schema SQLite.');
    }
    $pdo->exec($schema);
}

$users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($users > 0) {
    fwrite(STDERR, "Já existe um usuário cadastrado. Nenhuma alteração foi feita.\n");
    exit(1);
}

$email = $argv[1] ?? 'admin@petmania.local';
$name = $argv[2] ?? 'Sônia Martins';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Informe um e-mail válido como primeiro argumento.\n");
    exit(1);
}
$password = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
$statement = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
$statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'Administradora']);

$clientsCount = (int) $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
if ($clientsCount === 0) {
    $clients = [
        ['Mariana Costa', '(18) 99124-8033', 'mariana@email.com', ['Bento', 'Golden Retriever', 'Macho'], ['Lola', 'Shih-tzu', 'Fêmea']],
        ['Rafael Nunes', '(18) 99732-1045', 'rafael@email.com', ['Theo', 'Spitz Alemão', 'Macho']],
        ['Beatriz Lima', '(18) 98813-4420', 'bia.lima@email.com', ['Amora', 'Poodle', 'Fêmea']],
        ['Lucas Almeida', '(18) 99641-7820', 'lucas@email.com', ['Max', 'Bulldog Francês', 'Macho']],
    ];
    $insertClient = $pdo->prepare('INSERT INTO clients (name, phone, email) VALUES (?, ?, ?)');
    $insertPet = $pdo->prepare('INSERT INTO pets (client_id, name, breed, sex) VALUES (?, ?, ?, ?)');
    foreach ($clients as $client) {
        [$clientName, $phone, $clientEmail] = array_slice($client, 0, 3);
        $pets = array_slice($client, 3);
        $insertClient->execute([$clientName, $phone, $clientEmail]);
        $clientId = (int) $pdo->lastInsertId();
        foreach ($pets as $pet) {
            $insertPet->execute([$clientId, ...$pet]);
        }
    }

    $insertService = $pdo->prepare('INSERT INTO services (name, price, cost, duration, active) VALUES (?, ?, ?, ?, 1)');
    foreach ([['Banho completo', 75, 19, 60], ['Banho + tosa', 125, 31, 90], ['Tosa higiênica', 55, 10, 45], ['Hidratação premium', 48, 16, 30]] as $service) {
        $insertService->execute($service);
    }
    $insertProduct = $pdo->prepare('INSERT INTO products (name, category, stock, price, cost) VALUES (?, ?, ?, ?, ?)');
    foreach ([['Shampoo neutro 500ml', 'Higiene', 14, 39.90, 20], ['Perfume Pet Fresh', 'Higiene', 5, 34.90, 16], ['Coleira Comfort', 'Acessórios', 22, 49.90, 23], ['Condicionador Soft', 'Higiene', 3, 42, 21], ['Brinquedo mordedor', 'Brinquedos', 18, 27.50, 11]] as $product) {
        $insertProduct->execute($product);
    }

    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $insertAppointment = $pdo->prepare('INSERT INTO appointments (client_id, pet_id, service_id, professional, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ([[1, 1, 1, 'Eliane', $today, '08:30', 'Concluído'], [2, 3, 2, 'Eliane', $today, '10:00', 'Em andamento'], [3, 4, 3, 'Carla', $today, '13:30', 'Agendado'], [1, 2, 4, 'Carla', $today, '15:00', 'Agendado'], [4, 5, 1, 'Eliane', $tomorrow, '09:00', 'Agendado']] as $appointment) {
        $insertAppointment->execute($appointment);
    }

    $insertTransaction = $pdo->prepare('INSERT INTO transactions (transaction_date, description, type, value, is_manual) VALUES (?, ?, ?, ?, 0)');
    foreach ([[$today, 'Banho completo · Bento', 'Receita', 75], [$today, 'Insumos · Banho Bento', 'Custo', 19], [$today, 'Comissão · Eliane', 'Comissão', 11.25], [$today, 'Venda balcão', 'Receita', 386.40], [date('Y-m-d', strtotime('-1 day')), 'Reposição de produtos', 'Custo', 128], [date('Y-m-d', strtotime('-2 days')), 'Serviços realizados', 'Receita', 490]] as $transaction) {
        $insertTransaction->execute($transaction);
    }
    $insertSale = $pdo->prepare('INSERT INTO sales (sale_date, product_id, quantity, client, unit_price, total) VALUES (?, ?, ?, ?, ?, ?)');
    $insertSale->execute([$today, 3, 1, 'Mariana Costa', 49.90, 49.90]);
    $insertSale->execute([date('Y-m-d', strtotime('-1 day')), 1, 2, 'Venda balcão', 39.90, 79.80]);
}

fwrite(STDOUT, "Banco inicializado com dados de demonstração.\n");
fwrite(STDOUT, "E-mail: {$email}\nSenha temporária: {$password}\n");
fwrite(STDOUT, "Guarde a senha agora; ela não será exibida novamente.\n");
