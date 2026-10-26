<?php
declare(strict_types=1);

function save_client_action(PDO $pdo): never
{
    $id = optional_id('id');
    $name = required_value('name', 'o nome do tutor', 'clients');
    $phone = required_value('phone', 'o telefone', 'clients');
    $email = post_value('email');
    $petName = post_value('pet_name');
    $breed = post_value('breed');
    $sex = post_value('sex') ?: 'Não informado';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        action_error('Informe um e-mail válido.', 'clients');
    }
    if (!within_lengths([$name, 120], [$phone, 40], [$email, 160], [$petName, 100], [$breed, 100])) {
        action_error('Um dos campos ultrapassou o tamanho permitido.', 'clients');
    }
    if (!in_array($sex, PET_SEXES, true)) {
        action_error('Selecione um sexo válido para o pet.', 'clients');
    }
    $addPet = $petName !== '' || $breed !== '';
    if (($addPet || !$id) && ($petName === '' || $breed === '')) {
        action_error($id ? 'Para cadastrar um pet, informe o nome e a raça.' : 'Informe o nome e a raça do primeiro pet.', 'clients');
    }

    if ($id && find_client($pdo, $id) === null) {
        action_error('Cliente não encontrado.', 'clients');
    }

    $pdo->beginTransaction();
    if ($id) {
        execute($pdo, 'UPDATE clients SET name = ?, phone = ?, email = ? WHERE id = ?', [$name, $phone, $email ?: null, $id]);
        $clientId = $id;
    } else {
        execute($pdo, 'INSERT INTO clients (name, phone, email) VALUES (?, ?, ?)', [$name, $phone, $email ?: null]);
        $clientId = (int) $pdo->lastInsertId();
    }
    if ($addPet) {
        insert_pet($pdo, $clientId, $petName, $breed, $sex);
    }
    $pdo->commit();

    set_flash($id ? 'Cadastro do cliente atualizado.' : 'Cliente e pet cadastrados com sucesso.');
    redirect_to('clients', ['selected' => $clientId]);
}
