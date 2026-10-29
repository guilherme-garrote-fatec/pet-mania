<?php
declare(strict_types=1);

function render_services(PDO $pdo, array $user): string
{
    $services = all_services($pdo);
    ob_start();
    ?>
    <?= page_header('CUIDADO E BEM-ESTAR', 'Serviços', 'Configure os cuidados oferecidos pela Pet Mania.', button_link('services', 'service', 'Novo serviço')) ?>
    <div class="service-grid">
      <?php if (!$services): ?><article class="panel empty-panel"><?= table_empty(1, 'Cadastre o primeiro serviço.') ?></article><?php endif; ?>
      <?php foreach ($services as $index => $item): $active = (int) $item['active'] === 1; ?>
        <article class="service-card<?= $active ? '' : ' service-card--inactive' ?>"><div class="service-card-head"><span><?= icon($index % 2 ? 'scissors' : 'paw', 21) ?></span><span class="status-badge status-badge--<?= $active ? 'active' : 'muted' ?>"><?= $active ? 'Ativo' : 'Inativo' ?></span></div><h2><?= e($item['name']) ?></h2><p>Atendimento realizado com produtos selecionados e equipe especializada.</p><div class="service-price"><strong><?= e(money($item['price'])) ?></strong><span><?= icon('calendar', 14) ?> <?= e($item['duration']) ?> min</span></div><div class="service-metrics"><div><span>Custo estimado</span><strong><?= e(money($item['cost'])) ?></strong></div><div><span>Margem</span><strong><?= margin_percent($item['price'], $item['cost']) ?>%</strong></div></div><div class="service-actions"><a class="button button--secondary" href="<?= e(page_url('services', ['modal' => 'service', 'id' => $item['id']])) ?>"><?= icon('edit', 14) ?> Editar</a><?= post_button('toggle_service', 'services', $active ? 'Desativar' : 'Ativar', 'link-button', ['id' => $item['id']]) ?></div></article>
      <?php endforeach; ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

function render_service_form(PDO $pdo, ?array $record): string
{
    ob_start();
    ?>
    <label class="field"><span>Nome do serviço <b>*</b></span><input name="name" value="<?= e($record['name'] ?? '') ?>" maxlength="120" required></label>
    <div class="form-grid form-grid--three"><label class="field"><span>Preço <b>*</b></span><input type="number" name="price" min="0.01" max="<?= e(MAX_AMOUNT) ?>" step="0.01" value="<?= e($record['price'] ?? '') ?>" required></label><label class="field"><span>Custo <b>*</b></span><input type="number" name="cost" min="0" max="<?= e(MAX_AMOUNT) ?>" step="0.01" value="<?= e($record['cost'] ?? '') ?>" required></label><label class="field"><span>Duração (min) <b>*</b></span><input type="number" name="duration" min="1" max="<?= e(MAX_SERVICE_DURATION) ?>" step="1" value="<?= e($record['duration'] ?? '') ?>" required></label></div>
    <?php
    return (string) ob_get_clean();
}
