<?php
declare(strict_types=1);

/**
 * Teste de fumaça ponta a ponta: cria um banco SQLite temporário, executa o seed,
 * sobe o servidor embutido do PHP e percorre as telas e ações de todos os módulos.
 *
 * Uso: php tests/smoke.php   (requer pdo_sqlite)
 *
 * Para testar com MySQL/MariaDB, informe um banco vazio com o schema importado:
 *   SMOKE_DB_DSN="mysql:host=127.0.0.1;dbname=pet_mania_test;charset=utf8mb4" DB_USER=... DB_PASSWORD=... php tests/smoke.php
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$root = dirname(__DIR__);
$workDir = sys_get_temp_dir() . '/pet-mania-smoke-' . bin2hex(random_bytes(4));
mkdir($workDir);
$dsn = getenv('SMOKE_DB_DSN') ?: 'sqlite:' . $workDir . '/smoke.sqlite';
$serverLog = $workDir . '/server.log';
$env = ['DB_DSN' => $dsn, 'APP_TIMEZONE' => 'America/Sao_Paulo'] + getenv();

$failures = 0;
$checks = 0;
function check(bool $condition, string $label): void
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures++;
        fwrite(STDERR, "  FALHOU: {$label}\n");
    }
}

// Seed
$seed = proc_open([PHP_BINARY, $root . '/database/seed.php', 'smoke@petmania.local', 'Ângela Teste'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, $env);
$seedOutput = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
$seedStatus = proc_close($seed);
if ($seedStatus !== 0 || !preg_match('/Senha temporária: (\S+)/', $seedOutput, $match)) {
    fwrite(STDERR, "Falha ao executar o seed:\n{$seedOutput}\n");
    exit(1);
}
$password = $match[1];

// Servidor
$socket = stream_socket_server('tcp://127.0.0.1:0');
$port = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
fclose($socket);
$server = proc_open([PHP_BINARY, '-S', "127.0.0.1:{$port}", '-t', $root . '/public'], [1 => ['file', $serverLog, 'a'], 2 => ['file', $serverLog, 'a']], $pipes, $root, $env);
register_shutdown_function(static function () use ($server, $workDir): void {
    proc_terminate($server);
    proc_close($server);
    array_map('unlink', glob($workDir . '/*') ?: []);
    rmdir($workDir);
});
$base = "http://127.0.0.1:{$port}/";
for ($attempt = 0; $attempt < 50 && @file_get_contents($base) === false; $attempt++) {
    usleep(100000);
}

$cookies = [];
/**
 * @return array{status: int, location: string, body: string, headers: list<string>}
 */
function request(string $method, string $path, array $data = []): array
{
    global $base, $cookies;
    $headers = ['Cookie: ' . http_build_query($cookies, '', '; ')];
    $options = ['method' => $method, 'ignore_errors' => true, 'follow_location' => 0, 'header' => $headers];
    if ($method === 'POST') {
        $options['header'][] = 'Content-Type: application/x-www-form-urlencoded';
        $options['content'] = http_build_query($data);
    }
    $body = (string) file_get_contents($base . $path, false, stream_context_create(['http' => $options]));
    $responseHeaders = $http_response_header ?? [];
    $status = (int) (explode(' ', $responseHeaders[0] ?? 'HTTP/1.1 0')[1] ?? 0);
    $location = '';
    foreach ($responseHeaders as $header) {
        if (preg_match('/^Set-Cookie: ([^=]+)=([^;]*)/i', $header, $cookie)) {
            $cookies[$cookie[1]] = urldecode($cookie[2]);
        }
        if (preg_match('/^Location: (.*)$/i', $header, $redirect)) {
            $location = trim($redirect[1]);
        }
    }
    return ['status' => $status, 'location' => $location, 'body' => $body, 'headers' => $responseHeaders];
}

function csrf(string $path = ''): string
{
    preg_match('/name="_csrf" value="([^"]+)"/', request('GET', $path)['body'], $match);
    return $match[1] ?? '';
}

/**
 * Envia uma ação e devolve a mensagem exibida após o redirecionamento.
 */
function act(string $action, string $page, array $data = []): string
{
    $response = request('POST', '', ['_csrf' => csrf('?page=' . $page), 'action' => $action, '_page' => $page] + $data);
    $target = request('GET', ltrim($response['location'] ?: '?page=' . $page, '/'));
    preg_match('/<div class="notice notice--(\w+)"[^>]*>([^<]*)/', $target['body'], $match);
    return isset($match[1]) ? $match[1] . ': ' . html_entity_decode($match[2]) : '(sem aviso)';
}

function db_value(string $sql, array $params = []): mixed
{
    global $dsn;
    static $pdo = null;
    $pdo ??= new PDO($dsn, getenv('DB_USER') ?: null, getenv('DB_PASSWORD') ?: null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return $statement->fetchColumn();
}

echo "Autenticação\n";
check(str_contains(request('GET', '?page=finance')['body'], 'Acesse sua conta'), 'páginas exigem login');
check(str_starts_with(act('login', 'overview', ['email' => 'smoke@petmania.local', 'password' => 'errada']), 'error: E-mail ou senha'), 'senha incorreta é rejeitada');
check(act('save_product', 'products', ['name' => 'X', 'category' => 'Higiene', 'price' => '1', 'cost' => '0', 'stock' => '1']) === 'error: Entre no sistema para continuar.', 'ações exigem login');
check(act('login', 'overview', ['email' => 'smoke@petmania.local', 'password' => $password]) === 'success: Bem-vinda(o) de volta!', 'login com a senha do seed');
$rejected = request('POST', '', ['_csrf' => 'invalido', 'action' => 'save_product', '_page' => 'products']);
check($rejected['status'] === 303, 'token CSRF inválido é rejeitado');
request('GET', '?page=overview');

echo "Páginas e modais\n";
foreach (['overview', 'schedule', 'clients', 'products', 'services', 'sales', 'finance', 'reports'] as $page) {
    $response = request('GET', '?page=' . $page);
    check($response['status'] === 200 && str_contains($response['body'], 'nav-item--active'), "página {$page} renderiza");
}
$overview = request('GET', '?page=overview')['body'];
check(str_contains($overview, 'Olá, Ângela!') && str_contains($overview, '<span class="avatar">ÂT</span>'), 'saudação e iniciais com acentos');
check(preg_match('/<span>(Jan|Fev|Mar|Abr|Mai|Jun|Jul|Ago|Set|Out|Nov|Dez)<\/span>/', $overview) === 1, 'meses do gráfico em português');
foreach (['appointment', 'client', 'product', 'service', 'sale', 'transaction'] as $modal) {
    check(str_contains(request('GET', '?page=overview&modal=' . $modal)['body'], '<dialog class="modal"'), "modal {$modal} renderiza");
}
check(!str_contains(request('GET', '?page=schedule&modal=appointment&date[]=x&q[]=y')['body'], 'value="Array"'), 'parâmetros em array são ignorados');
check(request('GET', '?page=inexistente')['status'] === 200, 'página desconhecida cai na visão geral');

echo "Clientes\n";
check(act('save_client', 'clients', ['name' => 'Joana Prado', 'phone' => '(11) 90000-0000', 'email' => '', 'pet_name' => 'Pipoca', 'breed' => 'SRD', 'sex' => 'Fêmea']) === 'success: Cliente e pet cadastrados com sucesso.', 'cadastra cliente com pet');
$clientId = (int) db_value("SELECT id FROM clients WHERE name = 'Joana Prado'");
$petId = (int) db_value('SELECT id FROM pets WHERE client_id = ?', [$clientId]);
check(str_starts_with(act('save_client', 'clients', ['name' => 'Sem Pet', 'phone' => '1']), 'error:'), 'cliente novo exige pet');
check(str_starts_with(act('save_client', 'clients', ['id' => $clientId, 'name' => 'Joana Prado', 'phone' => '1', 'pet_name' => 'Bolt', 'breed' => 'SRD', 'sex' => 'Inválido']), 'error:'), 'sexo inválido é rejeitado');
check(act('save_client', 'clients', ['id' => $clientId, 'name' => 'Joana P. Prado', 'phone' => '(11) 91111-1111', 'pet_name' => 'Bolt', 'breed' => 'Beagle', 'sex' => 'Macho']) === 'success: Cadastro do cliente atualizado.', 'edita cliente e adiciona pet');
check((int) db_value('SELECT COUNT(*) FROM pets WHERE client_id = ?', [$clientId]) === 2, 'segundo pet gravado');
check(str_starts_with(act('save_client', 'clients', ['id' => 999999, 'name' => 'X', 'phone' => '1']), 'error: Cliente não encontrado'), 'edição de cliente inexistente');
check(str_contains(request('GET', '?page=clients&q=' . urlencode('%'))['body'], '0 tutores na busca'), 'curingas de LIKE são escapados');
check(str_contains(request('GET', '?page=clients&q=Bolt')['body'], 'Joana P. Prado'), 'busca por nome do pet');

echo "Serviços e produtos\n";
check(act('save_service', 'services', ['name' => 'Corte de unhas', 'price' => '30', 'cost' => '2', 'duration' => '15']) === 'success: Serviço cadastrado com sucesso.', 'cadastra serviço');
$serviceId = (int) db_value("SELECT id FROM services WHERE name = 'Corte de unhas'");
check(str_starts_with(act('save_service', 'services', ['name' => 'Caro', 'price' => '1e12', 'cost' => '0', 'duration' => '15']), 'error:'), 'valor acima do limite é rejeitado');
check(act('save_product', 'products', ['name' => 'Ração 1kg', 'category' => 'Alimentação', 'price' => '25.50', 'cost' => '15', 'stock' => '10']) === 'success: Produto adicionado ao catálogo.', 'cadastra produto');
$productId = (int) db_value("SELECT id FROM products WHERE name = 'Ração 1kg'");
check(str_starts_with(act('save_product', 'products', ['name' => 'X', 'category' => 'Higiene', 'price' => '0', 'cost' => '0', 'stock' => '1']), 'error:'), 'preço zero é rejeitado');

echo "Agenda\n";
$date = date('Y-m-d', strtotime('+3 days'));
$appointment = ['client_id' => $clientId, 'pet_id' => $petId, 'service_id' => $serviceId, 'date' => $date, 'time' => '11:00', 'professional' => 'Carla'];
check(act('save_appointment', 'schedule', $appointment) === 'success: Agendamento criado com sucesso.', 'cria agendamento');
check(act('save_appointment', 'schedule', $appointment) === 'error: Este horário já está ocupado. Escolha outro horário.', 'impede horário duplicado');
check(act('save_appointment', 'schedule', ['pet_id' => (int) db_value('SELECT MIN(id) FROM pets')] + $appointment + ['time' => '12:00']) === 'error: O pet selecionado não pertence a este cliente.', 'valida o dono do pet');
$appointmentId = (int) db_value('SELECT id FROM appointments WHERE client_id = ? AND appointment_date = ?', [$clientId, $date]);
check(act('save_appointment', 'schedule', ['id' => $appointmentId, 'time' => '11:30'] + $appointment) === 'success: Agendamento atualizado.', 'edita agendamento');
check(act('complete_appointment', 'schedule', ['id' => $appointmentId]) === 'success: Atendimento concluído e financeiro atualizado.', 'conclui atendimento');
check((int) db_value('SELECT COUNT(*) FROM transactions WHERE transaction_date = ? AND is_manual = 0', [$date]) === 3, 'gera receita, custo e comissão');
check((float) db_value("SELECT value FROM transactions WHERE transaction_date = ? AND type = 'Comissão'", [$date]) === 4.5, 'comissão de 15%');
check(act('complete_appointment', 'schedule', ['id' => $appointmentId]) === 'error: Este agendamento não pode ser concluído.', 'não conclui duas vezes');
check(str_starts_with(act('save_appointment', 'schedule', ['id' => $appointmentId, 'time' => '16:00'] + $appointment), 'error: Somente agendamentos pendentes'), 'não edita agendamento concluído');
check(str_contains(request('GET', '?page=schedule&modal=appointment&id=' . $appointmentId)['body'], '>Novo agendamento</h2>'), 'modal não carrega agendamento concluído');
act('save_appointment', 'schedule', ['time' => '14:00'] + $appointment);
$pendingId = (int) db_value("SELECT id FROM appointments WHERE appointment_date = ? AND status = 'Agendado'", [$date]);
check(act('cancel_appointment', 'schedule', ['id' => $pendingId]) === 'success: Agendamento cancelado.', 'cancela agendamento');
check(act('cancel_appointment', 'schedule', ['id' => $pendingId]) === 'error: Somente agendamentos pendentes podem ser cancelados.', 'não cancela duas vezes');
check(str_contains(request('GET', '?page=schedule&date=' . $date . '&status=Cancelado')['body'], '1 atendimento(s)'), 'filtro de status da agenda');

echo "Vendas e financeiro\n";
check(act('save_sale', 'sales', ['product_id' => $productId, 'quantity' => '4', 'client' => '']) === 'success: Venda registrada e estoque atualizado.', 'registra venda');
check((int) db_value('SELECT stock FROM products WHERE id = ?', [$productId]) === 6, 'baixa o estoque');
check(act('save_sale', 'sales', ['product_id' => $productId, 'quantity' => '7']) === 'error: Estoque insuficiente para a quantidade solicitada.', 'impede venda sem estoque');
check(act('delete_product', 'products', ['id' => $productId]) === 'error: Este produto possui vendas registradas e não pode ser removido.', 'protege produto com vendas');
check(act('save_transaction', 'finance', ['type' => 'Custo', 'date' => date('Y-m-d'), 'description' => '=HYPERLINK("x")', 'value' => '12.34']) === 'success: Lançamento financeiro registrado.', 'cria lançamento manual');
$transactionId = (int) db_value('SELECT id FROM transactions WHERE is_manual = 1');
check(act('save_transaction', 'finance', ['id' => $transactionId, 'type' => 'Custo', 'date' => date('Y-m-d'), 'description' => '=HYPERLINK("x")', 'value' => '20']) === 'success: Lançamento financeiro atualizado.', 'edita lançamento manual');
$automaticId = (int) db_value('SELECT id FROM transactions WHERE is_manual = 0');
check(act('delete_transaction', 'finance', ['id' => $automaticId]) === 'error: Lançamentos automáticos não podem ser removidos.', 'protege lançamento automático');
check(act('save_transaction', 'finance', ['id' => $automaticId, 'type' => 'Custo', 'description' => 'x', 'value' => '1']) === 'error: Lançamento manual não encontrado.', 'não edita lançamento automático');

echo "Relatórios\n";
$csv = request('GET', '?page=reports&period=year&type=Custo&export=csv');
check(in_array('Content-Type: text/csv; charset=utf-8', $csv['headers'], true), 'exporta CSV');
check(str_contains($csv['body'], "\"'=HYPERLINK(\"\"x\"\")\""), 'CSV neutraliza fórmulas');
check(!str_contains($csv['body'], ';Receita;'), 'CSV respeita o filtro de tipo');
check(act('delete_transaction', 'finance', ['id' => $transactionId]) === 'success: Lançamento manual removido.', 'remove lançamento manual');
check(str_contains(request('GET', '?page=reports&period=quarter')['body'], 'Últimos 3 meses'), 'filtro de período');

echo "Sessão\n";
act('logout', 'overview');
check(str_contains(request('GET', '?page=overview')['body'], 'Acesse sua conta'), 'logout encerra a sessão');

$log = (string) file_get_contents($serverLog);
check(preg_match('/PHP (Warning|Notice|Deprecated|Fatal|Parse)/', $log) !== 1, 'servidor sem avisos ou erros do PHP');
if (preg_match_all('/PHP (Warning|Notice|Deprecated|Fatal error|Parse error).*/', $log, $matches)) {
    fwrite(STDERR, implode("\n", array_unique($matches[0])) . "\n");
}

printf("\n%d verificações, %d falha(s).\n", $checks, $failures);
exit($failures === 0 ? 0 : 1);
