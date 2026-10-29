<?php
declare(strict_types=1);

/** Maior valor aceito pelas colunas DECIMAL(10,2). */
const MAX_AMOUNT = 99999999.99;
/** Limite para quantidades inteiras (estoque, itens vendidos). */
const MAX_QUANTITY = 1000000;

function action_error(string $message, string $page, array $params = []): never
{
    set_flash($message, 'error');
    redirect_to($page, $params);
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
    $id = optional_id($key);
    if ($id === null) {
        action_error("Selecione {$label}.", $page);
    }
    return $id;
}

function optional_id(string $key): ?int
{
    $id = filter_var(post_value($key), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function valid_date(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function valid_time(string $value): bool
{
    return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) === 1;
}

/**
 * Valor monetário positivo (ou zero, se permitido) até MAX_AMOUNT; devolve null quando inválido.
 */
function post_amount(string $key, bool $allowZero = false): ?float
{
    $value = filter_var(post_value($key), FILTER_VALIDATE_FLOAT);
    if ($value === false || $value < 0 || ($value == 0 && !$allowZero) || $value > MAX_AMOUNT) {
        return null;
    }
    return round($value, 2);
}

function post_integer(string $key, int $min, int $max = MAX_QUANTITY): ?int
{
    $value = filter_var(post_value($key), FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
    return $value === false ? null : $value;
}

/**
 * @param array{0: string, 1: int} ...$fields Pares [valor, tamanho máximo].
 */
function within_lengths(array ...$fields): bool
{
    foreach ($fields as [$value, $max]) {
        if (text_length($value) > $max) {
            return false;
        }
    }
    return true;
}
