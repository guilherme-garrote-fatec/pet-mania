<?php
declare(strict_types=1);

const TRANSACTION_TYPES = ['Receita', 'Custo', 'Comissão'];

/**
 * Soma dos lançamentos por tipo no intervalo [$start, $end).
 *
 * @return array{Receita: float, Custo: float, Comissão: float}
 */
function transaction_totals(PDO $pdo, string $start, string $end): array
{
    $totals = array_fill_keys(TRANSACTION_TYPES, 0.0);
    $rows = fetch_all($pdo, 'SELECT type, COALESCE(SUM(value), 0) AS total FROM transactions WHERE transaction_date >= ? AND transaction_date < ? GROUP BY type', [$start, $end]);
    foreach ($rows as $row) {
        if (isset($totals[$row['type']])) {
            $totals[$row['type']] = (float) $row['total'];
        }
    }
    return $totals;
}

/**
 * Totais do mês corrente.
 */
function current_month_totals(PDO $pdo): array
{
    return transaction_totals($pdo, date('Y-m-01'), date('Y-m-01', strtotime('first day of next month')));
}

function net_profit(array $totals): float
{
    return $totals['Receita'] - $totals['Custo'] - $totals['Comissão'];
}

/**
 * Receitas e despesas (custos + comissões) dos últimos $months meses, do mais antigo ao atual.
 *
 * @return list<array{label: string, revenue: float, expense: float}>
 */
function monthly_cash_flow(PDO $pdo, int $months): array
{
    $firstMonth = new DateTimeImmutable('first day of this month -' . ($months - 1) . ' months');
    $end = (new DateTimeImmutable('first day of next month'))->format('Y-m-d');
    $rows = fetch_all(
        $pdo,
        'SELECT SUBSTR(transaction_date, 1, 7) AS month, type, SUM(value) AS total FROM transactions WHERE transaction_date >= ? AND transaction_date < ? GROUP BY SUBSTR(transaction_date, 1, 7), type',
        [$firstMonth->format('Y-m-d'), $end],
    );
    $sums = [];
    foreach ($rows as $row) {
        $sums[$row['month']][$row['type']] = (float) $row['total'];
    }

    $flow = [];
    for ($offset = 0; $offset < $months; $offset++) {
        $month = $firstMonth->modify("+{$offset} months");
        $monthSums = $sums[$month->format('Y-m')] ?? [];
        $flow[] = [
            'label' => month_abbr_pt($month),
            'revenue' => $monthSums['Receita'] ?? 0.0,
            'expense' => ($monthSums['Custo'] ?? 0.0) + ($monthSums['Comissão'] ?? 0.0),
        ];
    }
    return $flow;
}

function recent_transactions(PDO $pdo, int $limit): array
{
    return fetch_all($pdo, 'SELECT * FROM transactions ORDER BY transaction_date DESC, id DESC LIMIT ' . $limit);
}

/**
 * Lançamentos do intervalo [$start, $end), opcionalmente filtrados por tipo.
 */
function transactions_between(PDO $pdo, string $start, string $end, ?string $type = null, ?int $limit = null): array
{
    $sql = 'SELECT transaction_date, description, type, value FROM transactions WHERE transaction_date >= ? AND transaction_date < ?';
    $params = [$start, $end];
    if ($type !== null) {
        $sql .= ' AND type = ?';
        $params[] = $type;
    }
    $sql .= ' ORDER BY transaction_date DESC, id DESC' . ($limit !== null ? ' LIMIT ' . $limit : '');
    return fetch_all($pdo, $sql, $params);
}

function find_manual_transaction(PDO $pdo, int $id): ?array
{
    return fetch_one($pdo, 'SELECT * FROM transactions WHERE id = ? AND is_manual = 1', [$id]);
}

/**
 * Lançamento gerado pelo sistema (vendas e atendimentos); não pode ser editado pela tela financeira.
 */
function record_automatic_transaction(PDO $pdo, string $date, string $description, string $type, float $value): void
{
    execute($pdo, 'INSERT INTO transactions (transaction_date, description, type, value, is_manual) VALUES (?, ?, ?, ?, 0)', [$date, truncate_text($description, 180), $type, round($value, 2)]);
}
