<?php
declare(strict_types=1);

function page_url(string $page, array $params = []): string
{
    return '?' . http_build_query(['page' => $page] + $params);
}

function redirect_to(string $page, array $params = []): never
{
    header('Location: ' . page_url($page, $params), true, 303);
    exit;
}

/**
 * Lê um parâmetro textual da query string, ignorando valores que não sejam texto (ex.: `?q[]=x`).
 */
function query_value(string $key, string $default = ''): string
{
    $value = $_GET[$key] ?? null;
    return is_string($value) ? trim($value) : $default;
}

function query_choice(string $key, array $allowed, string $default): string
{
    $value = query_value($key, $default);
    return in_array($value, $allowed, true) ? $value : $default;
}

function query_id(string $key): ?int
{
    $id = filter_var(query_value($key), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function query_date(string $key, string $default): string
{
    $value = query_value($key);
    return valid_date($value) ? $value : $default;
}
