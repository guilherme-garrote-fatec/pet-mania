<?php
declare(strict_types=1);

const REPORT_PERIODS = ['month' => 'Este mês', 'quarter' => 'Últimos 3 meses', 'year' => 'Este ano'];
const REPORT_TYPES = ['all' => 'Visão completa', 'Receita' => 'Somente receitas', 'Custo' => 'Somente custos'];

/**
 * Período selecionado em ?period=, como [chave, início, fim exclusivo].
 *
 * @return array{0: string, 1: string, 2: string}
 */
function report_period(): array
{
    $period = query_choice('period', array_keys(REPORT_PERIODS), 'month');
    $start = match ($period) {
        'quarter' => date('Y-m-01', strtotime('-2 months', strtotime(date('Y-m-01')))),
        'year' => date('Y-01-01'),
        default => date('Y-m-01'),
    };
    return [$period, $start, date('Y-m-d', strtotime('tomorrow'))];
}

function report_type(): string
{
    return query_choice('type', array_keys(REPORT_TYPES), 'all');
}

/**
 * Prefixa textos que planilhas interpretariam como fórmula (injeção de CSV).
 */
function csv_safe(string $value): string
{
    return preg_match('/^[\t\r\n ]*[=+\-@]/', $value) === 1 ? "'" . $value : $value;
}

function export_report_csv(PDO $pdo): never
{
    [$period, $start, $end] = report_period();
    $type = report_type();
    $rows = transactions_between($pdo, $start, $end, $type === 'all' ? null : $type);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="pet-mania-relatorio-' . $period . '.csv"');
    $output = fopen('php://output', 'wb');
    if ($output === false) {
        throw new RuntimeException('Não foi possível gerar o relatório.');
    }
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['Data', 'Descrição', 'Tipo', 'Valor'], ';');
    foreach ($rows as $row) {
        fputcsv($output, [$row['transaction_date'], csv_safe((string) $row['description']), $row['type'], number_format((float) $row['value'], 2, '.', '')], ';');
    }
    fclose($output);
    exit;
}
