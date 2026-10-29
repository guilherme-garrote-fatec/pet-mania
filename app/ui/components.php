<?php
declare(strict_types=1);

function page_header(string $eyebrow, string $title, string $description, string $action = ''): string
{
    return '<div class="page-header"><div><span>' . e($eyebrow) . '</span><h1>' . e($title) . '</h1><p>' . e($description) . '</p></div>' . $action . '</div>';
}

function button_link(string $page, string $modal, string $label, array $params = []): string
{
    return '<a class="button button--primary" href="' . e(page_url($page, ['modal' => $modal] + $params)) . '">' . icon('plus', 17) . e($label) . '</a>';
}

function stat_card(string $iconName, string $tone, string $label, string $value, string $detail): string
{
    return '<article class="stat-card"><span class="stat-icon stat-icon--' . e($tone) . '">' . icon($iconName, 20) . '</span><div class="stat-copy"><span>' . e($label) . '</span><strong>' . e($value) . '</strong><small>' . e($detail) . '</small></div></article>';
}

/**
 * Formulário POST de um único botão. $label é HTML confiável (normalmente um ícone).
 */
function post_button(string $action, string $page, string $label, string $class = 'button button--secondary', array $hidden = [], string $confirm = ''): string
{
    $html = '<form method="post" class="inline-form"' . ($confirm !== '' ? ' data-confirm="' . e($confirm) . '"' : '') . '>' . csrf_field() .
        '<input type="hidden" name="action" value="' . e($action) . '">' .
        '<input type="hidden" name="_page" value="' . e($page) . '">';
    foreach ($hidden as $key => $value) {
        $html .= '<input type="hidden" name="' . e($key) . '" value="' . e($value) . '">';
    }
    return $html . '<button class="' . e($class) . '" type="submit">' . $label . '</button></form>';
}

function table_empty(int $columns, string $message): string
{
    return '<div class="table-empty" style="grid-column:span ' . $columns . '">' . icon('paw', 25) . '<strong>Nenhum registro encontrado</strong><span>' . e($message) . '</span></div>';
}

function status_class(string $status): string
{
    return match ($status) {
        'Agendado' => 'scheduled',
        'Em andamento' => 'progress',
        'Concluído' => 'complete',
        'Cancelado' => 'cancelled',
        'Ativo' => 'active',
        default => 'muted',
    };
}

function status_badge(string $status): string
{
    return '<span class="status-badge status-badge--' . e(status_class($status)) . '">' . e($status) . '</span>';
}

/**
 * Opções de <select>; $selected é comparado como texto.
 *
 * @param array<int|string, string> $options Mapa valor => rótulo.
 */
function select_options(array $options, ?string $selected): string
{
    $html = '';
    foreach ($options as $value => $label) {
        $html .= '<option value="' . e($value) . '"' . ((string) $value === $selected ? ' selected' : '') . '>' . e($label) . '</option>';
    }
    return $html;
}
