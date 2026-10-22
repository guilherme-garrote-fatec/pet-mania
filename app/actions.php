<?php
declare(strict_types=1);

function action_error(string $message, string $page): never
{
    set_flash($message, 'error');
    redirect_to($page);
}

function post_value(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function required_value(string $key, string $label, string $page): string
{
    $value = post_value($key);
    if ($value === '') {
        action_error("Informe {$label}.", $page);
    }
    return $value;
}

function valid_id(string $key, string $label, string $page): int
{
    $id = filter_var(post_value($key), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        action_error("Selecione {$label}.", $page);
    }
    return $id;
}

function valid_date(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function handle_post(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $token = post_value('_csrf');
    if ($token === '' || !hash_equals(csrf_token(), $token)) {
        action_error('Sua sessão expirou. Atualize a página e tente novamente.', 'overview');
    }

    $action = post_value('action');
    $page = post_value('_page');
    $allowedPages = ['overview', 'schedule', 'clients', 'products', 'services', 'sales', 'finance', 'reports'];
    if (!in_array($page, $allowedPages, true)) {
        $page = 'overview';
    }

    if ($action === 'login') {
        $email = filter_var(post_value('email'), FILTER_VALIDATE_EMAIL);
        $password = post_value('password');
        $statement = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $statement->execute([$email ?: '']);
        $account = $statement->fetch();
        if (!$account || !password_verify($password, $account['password_hash'])) {
            action_error('E-mail ou senha inválidos.', 'overview');
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $account['id'];
        unset($_SESSION['csrf_token']);
        set_flash('Bem-vinda(o) de volta!');
        redirect_to('overview');
    }

    if ($action === 'logout') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], '', $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect_to('overview');
    }

    if (signed_in_user($pdo) === null) {
        action_error('Entre no sistema para continuar.', 'overview');
    }

    if ($action === 'save_client') {
        $id = filter_var(post_value('id'), FILTER_VALIDATE_INT) ?: null;
        $name = required_value('name', 'o nome do tutor', 'clients');
        $phone = required_value('phone', 'o telefone', 'clients');
        $email = post_value('email');
        $petName = post_value('pet_name');
        $breed = post_value('breed');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            action_error('Informe um e-mail válido.', 'clients');
        }
        if (text_length($name) > 120 || text_length($phone) > 40 || text_length($email) > 160 || text_length($petName) > 100 || text_length($breed) > 100) {
            action_error('Um dos campos ultrapassou o tamanho permitido.', 'clients');
        }
        if (($petName !== '' || $breed !== '') && ($petName === '' || $breed === '')) {
            action_error('Para cadastrar um pet, informe o nome e a raça.', 'clients');
        }

        if ($id) {
            $statement = $pdo->prepare('UPDATE clients SET name = ?, phone = ?, email = ? WHERE id = ?');
            $statement->execute([$name, $phone, $email ?: null, $id]);
            if ($statement->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM clients WHERE id = ?');
                $check->execute([$id]);
                if (!$check->fetch()) {
                    action_error('Cliente não encontrado.', 'clients');
                }
            }
            if ($petName !== '' || $breed !== '') {
                $statement = $pdo->prepare('INSERT INTO pets (client_id, name, breed, sex) VALUES (?, ?, ?, ?)');
                $statement->execute([$id, $petName, $breed, post_value('sex') ?: 'Não informado']);
            }
            set_flash('Cadastro do cliente atualizado.');
        } else {
            if ($petName === '' || $breed === '') {
                action_error('Informe o nome e a raça do primeiro pet.', 'clients');
            }
            $pdo->beginTransaction();
            $statement = $pdo->prepare('INSERT INTO clients (name, phone, email) VALUES (?, ?, ?)');
            $statement->execute([$name, $phone, $email ?: null]);
            $clientId = (int) $pdo->lastInsertId();
            $statement = $pdo->prepare('INSERT INTO pets (client_id, name, breed, sex) VALUES (?, ?, ?, ?)');
            $statement->execute([$clientId, $petName, $breed, post_value('sex') ?: 'Não informado']);
            $pdo->commit();
            set_flash('Cliente e pet cadastrados com sucesso.');
        }
        redirect_to('clients');
    }

    if ($action === 'save_product') {
        $id = filter_var(post_value('id'), FILTER_VALIDATE_INT) ?: null;
        $name = required_value('name', 'o nome do produto', 'products');
        $category = required_value('category', 'a categoria', 'products');
        if (text_length($name) > 160 || text_length($category) > 80) {
            action_error('O nome ou a categoria ultrapassou o tamanho permitido.', 'products');
        }
        $price = filter_var(post_value('price'), FILTER_VALIDATE_FLOAT);
        $cost = filter_var(post_value('cost'), FILTER_VALIDATE_FLOAT);
        $stock = filter_var(post_value('stock'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($price === false || $price <= 0 || $cost === false || $cost < 0 || $stock === false) {
            action_error('Informe preço, custo e estoque válidos.', 'products');
        }
        if ($id) {
            $statement = $pdo->prepare('UPDATE products SET name = ?, category = ?, price = ?, cost = ?, stock = ? WHERE id = ?');
            $statement->execute([$name, $category, $price, $cost, $stock, $id]);
            if ($statement->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM products WHERE id = ?');
                $check->execute([$id]);
                if (!$check->fetch()) {
                    action_error('Produto não encontrado.', 'products');
                }
            }
            set_flash('Produto atualizado.');
        } else {
            $statement = $pdo->prepare('INSERT INTO products (name, category, price, cost, stock) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([$name, $category, $price, $cost, $stock]);
            set_flash('Produto adicionado ao catálogo.');
        }
        redirect_to('products');
    }

    if ($action === 'delete_product') {
        $id = valid_id('id', 'o produto', 'products');
        $statement = $pdo->prepare('SELECT COUNT(*) FROM sales WHERE product_id = ?');
        $statement->execute([$id]);
        if ((int) $statement->fetchColumn() > 0) {
            action_error('Este produto possui vendas registradas e não pode ser removido.', 'products');
        }
        $statement = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $statement->execute([$id]);
        set_flash($statement->rowCount() ? 'Produto removido.' : 'Produto não encontrado.', $statement->rowCount() ? 'success' : 'error');
        redirect_to('products');
    }

    if ($action === 'save_service') {
        $id = filter_var(post_value('id'), FILTER_VALIDATE_INT) ?: null;
        $name = required_value('name', 'o nome do serviço', 'services');
        if (text_length($name) > 120) {
            action_error('O nome do serviço ultrapassou o tamanho permitido.', 'services');
        }
        $price = filter_var(post_value('price'), FILTER_VALIDATE_FLOAT);
        $cost = filter_var(post_value('cost'), FILTER_VALIDATE_FLOAT);
        $duration = filter_var(post_value('duration'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($price === false || $price <= 0 || $cost === false || $cost < 0 || $duration === false) {
            action_error('Informe preço, custo e duração válidos.', 'services');
        }
        if ($id) {
            $statement = $pdo->prepare('UPDATE services SET name = ?, price = ?, cost = ?, duration = ? WHERE id = ?');
            $statement->execute([$name, $price, $cost, $duration, $id]);
            if ($statement->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM services WHERE id = ?');
                $check->execute([$id]);
                if (!$check->fetch()) {
                    action_error('Serviço não encontrado.', 'services');
                }
            }
            set_flash('Serviço atualizado.');
        } else {
            $statement = $pdo->prepare('INSERT INTO services (name, price, cost, duration, active) VALUES (?, ?, ?, ?, 1)');
            $statement->execute([$name, $price, $cost, $duration]);
            set_flash('Serviço cadastrado com sucesso.');
        }
        redirect_to('services');
    }

    if ($action === 'toggle_service') {
        $id = valid_id('id', 'o serviço', 'services');
        $statement = $pdo->prepare('UPDATE services SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END WHERE id = ?');
        $statement->execute([$id]);
        set_flash('Status do serviço atualizado.');
        redirect_to('services');
    }

    if ($action === 'save_appointment') {
        $id = filter_var(post_value('id'), FILTER_VALIDATE_INT) ?: null;
        $clientId = valid_id('client_id', 'o cliente', 'schedule');
        $petId = valid_id('pet_id', 'o pet', 'schedule');
        $serviceId = valid_id('service_id', 'o serviço', 'schedule');
        $date = required_value('date', 'a data', 'schedule');
        $time = required_value('time', 'o horário', 'schedule');
        $professional = required_value('professional', 'o profissional', 'schedule');
        if (text_length($professional) > 120 || !valid_date($date) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
            action_error('Informe profissional, data e horário válidos.', 'schedule');
        }
        $statement = $pdo->prepare('SELECT id FROM pets WHERE id = ? AND client_id = ?');
        $statement->execute([$petId, $clientId]);
        if (!$statement->fetch()) {
            action_error('O pet selecionado não pertence a este cliente.', 'schedule');
        }
        $statement = $pdo->prepare('SELECT id FROM services WHERE id = ? AND active = 1');
        $statement->execute([$serviceId]);
        if (!$statement->fetch()) {
            action_error('Selecione um serviço ativo.', 'schedule');
        }
        $sql = "SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status <> 'Cancelado'";
        $params = [$date, $time];
        if ($id) {
            $sql .= ' AND id <> ?';
            $params[] = $id;
        }
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        if ((int) $statement->fetchColumn() > 0) {
            action_error('Este horário já está ocupado. Escolha outro horário.', 'schedule');
        }
        if ($id) {
            $statement = $pdo->prepare('UPDATE appointments SET client_id = ?, pet_id = ?, service_id = ?, professional = ?, appointment_date = ?, appointment_time = ? WHERE id = ?');
            $statement->execute([$clientId, $petId, $serviceId, $professional, $date, $time, $id]);
            set_flash('Agendamento atualizado.');
        } else {
            $statement = $pdo->prepare("INSERT INTO appointments (client_id, pet_id, service_id, professional, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?, 'Agendado')");
            $statement->execute([$clientId, $petId, $serviceId, $professional, $date, $time]);
            set_flash('Agendamento criado com sucesso.');
        }
        redirect_to('schedule', ['date' => $date]);
    }

    if ($action === 'complete_appointment') {
        $id = valid_id('id', 'o agendamento', 'schedule');
        $pdo->beginTransaction();
        $lock = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
        $statement = $pdo->prepare("SELECT a.id, a.appointment_date, a.status, a.professional, p.name AS pet_name, s.name AS service_name, s.price, s.cost FROM appointments a JOIN pets p ON p.id = a.pet_id JOIN services s ON s.id = a.service_id WHERE a.id = ?{$lock}");
        $statement->execute([$id]);
        $appointment = $statement->fetch();
        if (!$appointment || $appointment['status'] === 'Cancelado' || $appointment['status'] === 'Concluído') {
            $pdo->rollBack();
            action_error('Este agendamento não pode ser concluído.', 'schedule');
        }
        $statement = $pdo->prepare("UPDATE appointments SET status = 'Concluído' WHERE id = ? AND status <> 'Concluído' AND status <> 'Cancelado'");
        $statement->execute([$id]);
        if ($statement->rowCount() !== 1) {
            $pdo->rollBack();
            action_error('O agendamento já foi atualizado.', 'schedule');
        }
        $statement = $pdo->prepare('INSERT INTO transactions (transaction_date, description, type, value, is_manual) VALUES (?, ?, ?, ?, 0)');
        $statement->execute([$appointment['appointment_date'], 'Serviço · ' . $appointment['service_name'] . ' · ' . $appointment['pet_name'], 'Receita', $appointment['price']]);
        $statement->execute([$appointment['appointment_date'], 'Insumos · ' . $appointment['pet_name'], 'Custo', $appointment['cost']]);
        $statement->execute([$appointment['appointment_date'], 'Comissão · ' . $appointment['professional'], 'Comissão', (float) $appointment['price'] * 0.15]);
        $pdo->commit();
        set_flash('Atendimento concluído e financeiro atualizado.');
        redirect_to('schedule', ['date' => $appointment['appointment_date']]);
    }

    if ($action === 'cancel_appointment') {
        $id = valid_id('id', 'o agendamento', 'schedule');
        $dateQuery = $pdo->prepare('SELECT appointment_date FROM appointments WHERE id = ?');
        $dateQuery->execute([$id]);
        $date = $dateQuery->fetchColumn();
        $statement = $pdo->prepare("UPDATE appointments SET status = 'Cancelado' WHERE id = ? AND status = 'Agendado'");
        $statement->execute([$id]);
        set_flash($statement->rowCount() ? 'Agendamento cancelado.' : 'Somente agendamentos pendentes podem ser cancelados.', $statement->rowCount() ? 'success' : 'error');
        redirect_to('schedule', is_string($date) ? ['date' => $date] : []);
    }

    if ($action === 'save_sale') {
        $productId = valid_id('product_id', 'o produto', 'sales');
        $quantity = filter_var(post_value('quantity'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($quantity === false) {
            action_error('Informe uma quantidade válida.', 'sales');
        }
        $client = post_value('client') ?: 'Venda balcão';
        if (text_length($client) > 120) {
            action_error('O nome do cliente ultrapassou o tamanho permitido.', 'sales');
        }
        $pdo->beginTransaction();
        $lock = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
        $statement = $pdo->prepare("SELECT id, name, stock, price, cost FROM products WHERE id = ?{$lock}");
        $statement->execute([$productId]);
        $product = $statement->fetch();
        if (!$product || (int) $product['stock'] < $quantity) {
            $pdo->rollBack();
            action_error('Estoque insuficiente para a quantidade solicitada.', 'sales');
        }
        $statement = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        $statement->execute([$quantity, $productId, $quantity]);
        if ($statement->rowCount() !== 1) {
            $pdo->rollBack();
            action_error('O estoque foi alterado por outra venda. Tente novamente.', 'sales');
        }
        $total = (float) $product['price'] * $quantity;
        $today = date('Y-m-d');
        $statement = $pdo->prepare('INSERT INTO sales (sale_date, product_id, quantity, client, unit_price, total) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$today, $productId, $quantity, $client, $product['price'], $total]);
        $statement = $pdo->prepare('INSERT INTO transactions (transaction_date, description, type, value, is_manual) VALUES (?, ?, ?, ?, 0)');
        $statement->execute([$today, 'Venda · ' . $product['name'], 'Receita', $total]);
        $statement->execute([$today, 'CMV · ' . $product['name'], 'Custo', (float) $product['cost'] * $quantity]);
        $pdo->commit();
        set_flash('Venda registrada e estoque atualizado.');
        redirect_to('sales');
    }

    if ($action === 'save_transaction') {
        $id = filter_var(post_value('id'), FILTER_VALIDATE_INT) ?: null;
        $description = required_value('description', 'a descrição', 'finance');
        $type = post_value('type');
        $date = post_value('date') ?: date('Y-m-d');
        $value = filter_var(post_value('value'), FILTER_VALIDATE_FLOAT);
        if (text_length($description) > 180 || !in_array($type, ['Receita', 'Custo', 'Comissão'], true) || !valid_date($date) || $value === false || $value <= 0) {
            action_error('Informe tipo, data e valor válidos para o lançamento.', 'finance');
        }
        if ($id) {
            $statement = $pdo->prepare('UPDATE transactions SET transaction_date = ?, description = ?, type = ?, value = ? WHERE id = ? AND is_manual = 1');
            $statement->execute([$date, $description, $type, $value, $id]);
            if (!$statement->rowCount()) {
                $check = $pdo->prepare('SELECT id FROM transactions WHERE id = ? AND is_manual = 1');
                $check->execute([$id]);
                if (!$check->fetch()) {
                    action_error('Lançamento manual não encontrado.', 'finance');
                }
            }
            set_flash('Lançamento financeiro atualizado.');
        } else {
            $statement = $pdo->prepare('INSERT INTO transactions (transaction_date, description, type, value, is_manual) VALUES (?, ?, ?, ?, 1)');
            $statement->execute([$date, $description, $type, $value]);
            set_flash('Lançamento financeiro registrado.');
        }
        redirect_to('finance');
    }

    if ($action === 'delete_transaction') {
        $id = valid_id('id', 'o lançamento', 'finance');
        $statement = $pdo->prepare('DELETE FROM transactions WHERE id = ? AND is_manual = 1');
        $statement->execute([$id]);
        set_flash($statement->rowCount() ? 'Lançamento manual removido.' : 'Lançamentos automáticos não podem ser removidos.', $statement->rowCount() ? 'success' : 'error');
        redirect_to('finance');
    }

    action_error('Ação não reconhecida.', $page);
}
