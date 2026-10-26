<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function text_length(string $value): int
{
    $length = preg_match_all('/./us', $value, $matches);
    return $length === false ? strlen($value) : $length;
}

/**
 * Primeiros caracteres de um texto UTF-8, em maiúsculas, sem quebrar letras acentuadas.
 */
function text_prefix(string $value, int $length): string
{
    preg_match_all('/./us', $value, $matches);
    $prefix = implode('', array_slice($matches[0], 0, $length));
    return function_exists('mb_strtoupper') ? mb_strtoupper($prefix, 'UTF-8') : strtoupper($prefix);
}

function truncate_text(string $value, int $length): string
{
    preg_match_all('/./us', $value, $matches);
    return count($matches[0]) > $length ? implode('', array_slice($matches[0], 0, $length - 1)) . '…' : $value;
}

function initials(string $name): string
{
    $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (!$words) {
        return '';
    }
    $initials = text_prefix($words[0], 1);
    if (count($words) > 1) {
        $initials .= text_prefix($words[1], 1);
    }
    return $initials;
}

function first_name(string $name): string
{
    return explode(' ', trim($name))[0];
}

function money(float|int|string $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function margin_percent(float|int|string $price, float|int|string $cost): int
{
    return (float) $price > 0 ? (int) round(((float) $price - (float) $cost) / (float) $price * 100) : 0;
}

function short_time(?string $value): string
{
    return substr((string) $value, 0, 5);
}

function date_br(?string $value): string
{
    if (!$value) {
        return '—';
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', substr($value, 0, 10));
    return $date ? $date->format('d/m/Y') : '—';
}

function date_heading_pt(?string $value = null): string
{
    $date = new DateTimeImmutable($value ?: 'today');
    $weekdays = [1 => 'SEGUNDA-FEIRA', 'TERÇA-FEIRA', 'QUARTA-FEIRA', 'QUINTA-FEIRA', 'SEXTA-FEIRA', 'SÁBADO', 'DOMINGO'];
    $months = [1 => 'JANEIRO', 'FEVEREIRO', 'MARÇO', 'ABRIL', 'MAIO', 'JUNHO', 'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO'];
    return $weekdays[(int) $date->format('N')] . ', ' . $date->format('d') . ' DE ' . $months[(int) $date->format('n')];
}

function month_abbr_pt(DateTimeInterface $date): string
{
    $months = [1 => 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    return $months[(int) $date->format('n')];
}
