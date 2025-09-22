<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');
//  
session_start();
require '../include/config.php';

// Enhanced error handling function
function sendError($message, $code = 400)
{
    http_response_code($code);
    echo json_encode(['error' => $message, 'code' => $code]);
    exit;
}

// Enhanced success response function
function sendSuccess($data)
{
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Validate session
$username = $_SESSION['username'] ?? null;
if (!$username) {
    sendError('Unauthorized access', 401);
}

// Sanitize and validate input parameters
$date_start = filter_input(INPUT_GET, 'date_start', FILTER_SANITIZE_STRING) ?: date('Y-m-01');
$date_end = filter_input(INPUT_GET, 'date_end', FILTER_SANITIZE_STRING) ?: date('Y-m-d');

// Enhanced date validation
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

if (!validateDate($date_start) || !validateDate($date_end)) {
    sendError('Invalid date format. Use YYYY-MM-DD format');
}

// Validate date range
$start_timestamp = strtotime($date_start);
$end_timestamp = strtotime($date_end);

if ($start_timestamp > $end_timestamp) {
    sendError('Start date cannot be greater than end date');
}

// Limit date range to prevent excessive data processing
$max_days = 365;
$date_diff = ($end_timestamp - $start_timestamp) / (60 * 60 * 24);
if ($date_diff > $max_days) {
    sendError("Date range cannot exceed {$max_days} days");
}

// Escape username for SQL safety
$username_escaped = mysqli_real_escape_string($con, $username);
$date_start_escaped = mysqli_real_escape_string($con, $date_start);
$date_end_escaped = mysqli_real_escape_string($con, $date_end);

try {
    // ---------------------------------------------
    // 1. PlayStation Data dengan logika filter yang responsive

    // Cek apakah ada filter tanggal yang spesifik (bukan default)
    $has_date_filter = !($date_start === date('Y-m-01') && $date_end === date('Y-m-d'));

    if ($has_date_filter) {
        // ADA FILTER: Hanya PlayStation yang aktif di range tanggal
        $query_ps = "
            SELECT 
                ps.id AS console_id,
                ps.no_ps,
                ps.type_ps,
                CONCAT(' (#', ps.no_ps, ') ', ps.type_ps) AS console_name,
                COUNT(t.id) AS total_rentals,
                ROUND(COALESCE(SUM(t.durasi), 0) / 60, 1) AS total_hours,
                ROUND(COALESCE(AVG(t.durasi), 0) / 60, 1) AS avg_duration,
                COUNT(DISTINCT DATE(tf.created_at)) AS active_days,
                ROUND(
                    COALESCE(SUM(t.durasi), 0) / 60 / 
                    NULLIF(COUNT(DISTINCT DATE(tf.created_at)), 0), 1
                ) AS avg_hours_per_day,
                COALESCE(SUM(t.harga), 0) AS revenue,
                ROUND(
                    COALESCE(SUM(t.harga), 0) / 
                    NULLIF(COALESCE(SUM(t.durasi), 0) / 60, 0), 0
                ) AS revenue_per_hour
            FROM 
                playstations ps
            INNER JOIN 
                tb_trans t ON ps.no_ps = t.id_ps 
                AND ps.userx = t.userx
                AND (t.is_deleted IS NULL OR t.is_deleted != 1)
            INNER JOIN 
                tb_trans_final tf ON t.inv = tf.invoice
                AND tf.userx = ps.userx
                AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
                AND tf.id_trans NOT LIKE 'TRX-OUT%'
            WHERE 
                ps.userx = ?
                AND DATE(tf.created_at) BETWEEN ? AND ?
            GROUP BY 
                ps.id, ps.type_ps, ps.no_ps
            ORDER BY 
                ps.no_ps
        ";

        $stmt_ps = mysqli_prepare($con, $query_ps);
        if (!$stmt_ps) {
            throw new Exception('Failed to prepare PlayStation filtered query: ' . mysqli_error($con));
        }
        mysqli_stmt_bind_param($stmt_ps, 'sss', $username_escaped, $date_start_escaped, $date_end_escaped);

    } else {
        // TIDAK ADA FILTER: Tampilkan semua PlayStation dengan data range default
        $query_ps = "
            SELECT 
                ps.id AS console_id,
                ps.no_ps,
                ps.type_ps,
                CONCAT(' (#', ps.no_ps, ') ', ps.type_ps) AS console_name,
                COUNT(CASE WHEN tf.created_at IS NOT NULL THEN t.id END) AS total_rentals,
                ROUND(COALESCE(SUM(CASE WHEN tf.created_at IS NOT NULL THEN t.durasi END), 0) / 60, 1) AS total_hours,
                ROUND(COALESCE(AVG(CASE WHEN tf.created_at IS NOT NULL THEN t.durasi END), 0) / 60, 1) AS avg_duration,
                COUNT(DISTINCT CASE WHEN tf.created_at IS NOT NULL THEN DATE(tf.created_at) END) AS active_days,
                ROUND(
                    COALESCE(SUM(CASE WHEN tf.created_at IS NOT NULL THEN t.durasi END), 0) / 60 / 
                    NULLIF(COUNT(DISTINCT CASE WHEN tf.created_at IS NOT NULL THEN DATE(tf.created_at) END), 0), 1
                ) AS avg_hours_per_day,
                COALESCE(SUM(CASE WHEN tf.created_at IS NOT NULL THEN t.harga END), 0) AS revenue,
                ROUND(
                    COALESCE(SUM(CASE WHEN tf.created_at IS NOT NULL THEN t.harga END), 0) / 
                    NULLIF(COALESCE(SUM(CASE WHEN tf.created_at IS NOT NULL THEN t.durasi END), 0) / 60, 0), 0
                ) AS revenue_per_hour
            FROM 
                playstations ps
            LEFT JOIN 
                tb_trans t ON ps.no_ps = t.id_ps 
                AND ps.userx = t.userx
                AND (t.is_deleted IS NULL OR t.is_deleted != 1)
            LEFT JOIN 
                tb_trans_final tf ON t.inv = tf.invoice
                AND tf.userx = ps.userx
                AND DATE(tf.created_at) BETWEEN ? AND ?
                AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
                AND tf.id_trans NOT LIKE 'TRX-OUT%'
            WHERE 
                ps.userx = ?
            GROUP BY 
                ps.id, ps.type_ps, ps.no_ps
            ORDER BY 
                ps.no_ps
        ";

        $stmt_ps = mysqli_prepare($con, $query_ps);
        if (!$stmt_ps) {
            throw new Exception('Failed to prepare PlayStation all query: ' . mysqli_error($con));
        }
        mysqli_stmt_bind_param($stmt_ps, 'sss', $date_start_escaped, $date_end_escaped, $username_escaped);
    }

    // Execute PlayStation query
    mysqli_stmt_execute($stmt_ps);
    $result_ps = mysqli_stmt_get_result($stmt_ps);

    $ps_data = [];
    $ps_totals = [
        'total_hours' => 0,
        'total_rentals' => 0,
        'total_revenue' => 0,
        'active_days' => 0,
        'avg_hours_per_day' => 0,
        'avg_revenue_per_hour' => 0,
        'total_consoles' => 0,
        'active_consoles' => 0
    ];

    $total_calendar_days = $date_diff + 1;
    $total_revenue_for_avg = 0;
    $total_hours_for_avg = 0;

    while ($row = mysqli_fetch_assoc($result_ps)) {
        // Convert numeric values
        $row['total_rentals'] = intval($row['total_rentals']);
        $row['total_hours'] = floatval($row['total_hours']);
        $row['avg_duration'] = floatval($row['avg_duration']);
        $row['active_days'] = intval($row['active_days']);
        $row['avg_hours_per_day'] = floatval($row['avg_hours_per_day']);
        $row['revenue'] = floatval($row['revenue']);
        $row['revenue_per_hour'] = floatval($row['revenue_per_hour']);

        // Add utilization percentage
        $row['utilization_rate'] = $total_calendar_days > 0
            ? round(($row['active_days'] / $total_calendar_days) * 100, 1)
            : 0;

        // Calculate totals
        $ps_totals['total_hours'] += $row['total_hours'];
        $ps_totals['total_rentals'] += $row['total_rentals'];
        $ps_totals['total_revenue'] += $row['revenue'];
        $ps_totals['active_days'] = max($ps_totals['active_days'], $row['active_days']);
        $ps_totals['total_consoles']++;

        if ($row['total_rentals'] > 0) {
            $ps_totals['active_consoles']++;
        }

        $total_revenue_for_avg += $row['revenue'];
        $total_hours_for_avg += $row['total_hours'];

        $ps_data[] = $row;
    }

    // Calculate aggregated averages
    $ps_totals['avg_hours_per_day'] = $ps_totals['active_days'] > 0
        ? round($ps_totals['total_hours'] / $ps_totals['active_days'], 1)
        : 0;

    $ps_totals['avg_revenue_per_hour'] = $total_hours_for_avg > 0
        ? round($total_revenue_for_avg / $total_hours_for_avg, 0)
        : 0;

    $ps_totals['utilization_rate'] = $ps_totals['total_consoles'] > 0 && $total_calendar_days > 0
        ? round(($ps_totals['active_days'] / ($ps_totals['total_consoles'] * $total_calendar_days)) * 100, 1)
        : 0;

    $ps_totals['avg_rentals_per_console'] = $ps_totals['total_consoles'] > 0
        ? round($ps_totals['total_rentals'] / $ps_totals['total_consoles'], 1)
        : 0;

    mysqli_stmt_close($stmt_ps);

    // ---------------------------------------------
    // 2. Payment Method Summary with Income AND Spending

    // Income query
    $query_payment_income = "
      SELECT 
    COALESCE(f.metode_pembayaran, 'Tidak Diketahui') AS payment_method,
    COUNT(*) AS transaction_count,
    COALESCE(SUM(f.grand_total), 0) AS total_amount,
    COALESCE(SUM(f.promo), 0) AS total_promo,
    COALESCE(AVG(f.grand_total), 0) AS avg_amount,
    MIN(f.grand_total) AS min_amount,
    MAX(f.grand_total) AS max_amount
FROM tb_trans_final f
WHERE DATE(f.created_at) BETWEEN ? AND ?
  AND f.userx = ?
  AND (f.is_deleted IS NULL OR f.is_deleted != 1)
  AND f.id_trans NOT LIKE 'TRX-OUT%'
  AND (
        EXISTS (SELECT 1 FROM tb_trans t WHERE t.inv = f.invoice)
        OR
        EXISTS (SELECT 1 FROM tb_trans_fnb fnb WHERE fnb.inv = f.invoice)
      )
GROUP BY f.metode_pembayaran
ORDER BY total_amount DESC;

    ";

    // Spending query
    $query_payment_spending = "
        SELECT 
            COALESCE(metode_pembayaran, 'cash') AS payment_method,
            COUNT(*) AS spending_count,
            COALESCE(SUM(grand_total), 0) AS total_spending
        FROM 
            tb_trans_final
        WHERE 
            DATE(created_at) BETWEEN ? AND ?
            AND userx = ?
            AND (is_deleted IS NULL OR is_deleted != 1)
            AND id_trans LIKE 'TRX-OUT%'
        GROUP BY 
            metode_pembayaran
        ORDER BY 
            total_spending DESC
    ";

    // Execute income query
    $stmt_payment_income = mysqli_prepare($con, $query_payment_income);
    if (!$stmt_payment_income) {
        throw new Exception('Failed to prepare payment income query: ' . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt_payment_income, 'sss', $date_start_escaped, $date_end_escaped, $username_escaped);
    mysqli_stmt_execute($stmt_payment_income);
    $result_payment_income = mysqli_stmt_get_result($stmt_payment_income);

    $payment_income_data = [];
    while ($row = mysqli_fetch_assoc($result_payment_income)) {
        $payment_income_data[$row['payment_method']] = [
            'transaction_count' => intval($row['transaction_count']),
            'total_amount' => floatval($row['total_amount']),
            'total_promo' => floatval($row['total_promo']),
            'avg_amount' => round(floatval($row['avg_amount']), 0),
            'min_amount' => floatval($row['min_amount']),
            'max_amount' => floatval($row['max_amount'])
        ];
    }
    mysqli_stmt_close($stmt_payment_income);

    // Execute spending query
    $stmt_payment_spending = mysqli_prepare($con, $query_payment_spending);
    if (!$stmt_payment_spending) {
        throw new Exception('Failed to prepare payment spending query: ' . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt_payment_spending, 'sss', $date_start_escaped, $date_end_escaped, $username_escaped);
    mysqli_stmt_execute($stmt_payment_spending);
    $result_payment_spending = mysqli_stmt_get_result($stmt_payment_spending);

    $payment_spending_data = [];
    while ($row = mysqli_fetch_assoc($result_payment_spending)) {
        $payment_spending_data[$row['payment_method']] = [
            'spending_count' => intval($row['spending_count']),
            'total_spending' => floatval($row['total_spending'])
        ];
    }
    mysqli_stmt_close($stmt_payment_spending);

    // Combine income and spending data
    $all_payment_methods = ['cash', 'ewalet', 'debit', 'qris'];
    $payment_data = [];
    $payment_totals = [
        'total_transactions' => 0,
        'total_amount' => 0,
        'total_promo' => 0,
        'total_spending' => 0,
        'total_net' => 0,
        'total_methods' => 0,
        'avg_transaction_amount' => 0
    ];

    foreach ($all_payment_methods as $method) {
        // Get income data
        $income = $payment_income_data[$method] ?? [
            'transaction_count' => 0,
            'total_amount' => 0,
            'total_promo' => 0,
            'avg_amount' => 0,
            'min_amount' => 0,
            'max_amount' => 0
        ];

        // Get spending data
        $spending = $payment_spending_data[$method] ?? [
            'spending_count' => 0,
            'total_spending' => 0
        ];

        // grand_total sudah net (setelah promo)
        $net_amount = $income['total_amount'] - $spending['total_spending'];

        $payment_item = [
            'payment_method' => $method,
            'transaction_count' => $income['transaction_count'],
            'spending_count' => $spending['spending_count'],
            'total_amount' => $income['total_amount'] + $income['total_promo'], // Gross untuk display
            'total_promo' => $income['total_promo'],
            'total_spending' => $spending['total_spending'],
            'net_amount' => $net_amount, // Net amount (grand_total sudah setelah promo) - spending
            'avg_amount' => $income['avg_amount'],
            'min_amount' => $income['min_amount'],
            'max_amount' => $income['max_amount']
        ];

        // Only include if there's any activity (income or spending)
        if ($income['total_amount'] > 0 || $spending['total_spending'] > 0) {
            // Hitung totals
            $payment_totals['total_transactions'] += $income['transaction_count'];
            $payment_totals['total_amount'] += $income['total_amount'] + $income['total_promo']; // Gross untuk display
            $payment_totals['total_promo'] += $income['total_promo'];
            $payment_totals['total_spending'] += $spending['total_spending'];
            $payment_totals['total_net'] += $net_amount; // gunakan grand_total (net) untuk total
            $payment_totals['total_methods']++;

            $payment_data[] = $payment_item;
        }
    }

    // Calculate percentages for each payment method
    foreach ($payment_data as &$row) {
        $row['percentage_of_total'] = $payment_totals['total_amount'] > 0
            ? round(($row['total_amount'] / $payment_totals['total_amount']) * 100, 1)
            : 0;
        $row['transaction_percentage'] = $payment_totals['total_transactions'] > 0
            ? round(($row['transaction_count'] / $payment_totals['total_transactions']) * 100, 1)
            : 0;
        $row['spending_percentage'] = $payment_totals['total_spending'] > 0
            ? round(($row['total_spending'] / $payment_totals['total_spending']) * 100, 1)
            : 0;
    }

    $payment_totals['avg_transaction_amount'] = $payment_totals['total_transactions'] > 0
        ? round($payment_totals['total_amount'] / $payment_totals['total_transactions'], 0)
        : 0;

    // ---------------------------------------------
    // 3. Financial data
    $data_financial = [];
    $totals = ['rent' => 0, 'fnb' => 0, 'others' => 0, 'spending' => 0, 'net' => 0, 'promo' => 0];
    $max_values = ['rent' => 0, 'fnb' => 0, 'others' => 0, 'spending' => 0, 'net' => null, 'promo' => 0];
    $min_values = ['rent' => PHP_INT_MAX, 'fnb' => PHP_INT_MAX, 'others' => PHP_INT_MAX, 'spending' => PHP_INT_MAX, 'net' => null, 'promo' => PHP_INT_MAX];
    $max_dates = ['rent' => null, 'fnb' => null, 'others' => null, 'spending' => null, 'net' => null, 'promo' => null];
    $min_dates = ['rent' => null, 'fnb' => null, 'others' => null, 'spending' => null, 'net' => null, 'promo' => null];

    $statistics = [
        'total_days' => 0,
        'work_days' => 0,
        'total_transactions' => 0
    ];

    // JOIN approach untuk GROSS amounts
    $rent_stmt = mysqli_prepare($con, "
        SELECT 
            COALESCE(SUM(t.harga), 0) AS total, 
            COUNT(t.id) AS count_rent
        FROM tb_trans_final tf
        INNER JOIN tb_trans t ON tf.invoice = t.inv
        WHERE DATE(tf.created_at) = ? 
          AND tf.userx = ? 
          AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
          AND (t.is_deleted IS NULL OR t.is_deleted != 1)
          AND tf.id_trans NOT LIKE 'TRX-OUT%'
    ");

    $fnb_stmt = mysqli_prepare($con, "
        SELECT 
            COALESCE(SUM(CASE WHEN fnb.type = 'FnB' THEN fnb.total ELSE 0 END), 0) AS fnb_total,
            COALESCE(SUM(CASE WHEN fnb.type = 'Others' THEN fnb.total ELSE 0 END), 0) AS others_total,
            COUNT(fnb.id) AS count_fnb
        FROM tb_trans_final tf
        INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
        WHERE DATE(tf.created_at) = ? 
          AND tf.userx = ? 
          AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
          AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
          AND tf.id_trans NOT LIKE 'TRX-OUT%'
    ");

    $spending_stmt = mysqli_prepare($con, "
        SELECT 
            COALESCE(SUM(tf.grand_total), 0) AS total, 
            COUNT(tf.id) AS count_spending
        FROM tb_trans_final tf
        WHERE DATE(tf.created_at) = ? 
          AND tf.userx = ? 
          AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
          AND tf.id_trans LIKE 'TRX-OUT%'
    ");

    $promo_stmt = mysqli_prepare($con, "
        SELECT COALESCE(SUM(promo), 0) AS total_promo
        FROM tb_trans_final
        WHERE DATE(created_at) = ? 
          AND userx = ? 
          AND (is_deleted IS NULL OR is_deleted != 1) 
          AND id_trans NOT LIKE 'TRX-OUT%'
    ");

    if (!$rent_stmt || !$fnb_stmt || !$spending_stmt || !$promo_stmt) {
        throw new Exception('Failed to prepare financial queries');
    }

    // Process each day in the date range
    $start_date = new DateTime($date_start);
    $end_date = new DateTime($date_end);
    $end_date->modify('+1 day');
    $interval = new DateInterval('P1D');
    $date_range = new DatePeriod($start_date, $interval, $end_date);

    foreach ($date_range as $date) {
        $current_date = $date->format('Y-m-d');
        $date_label = $date->format('d M');

        $statistics['total_days']++;

        // Get rent data - GROSS amounts dari tb_trans
        mysqli_stmt_bind_param($rent_stmt, 'ss', $current_date, $username_escaped);
        mysqli_stmt_execute($rent_stmt);
        $rent_result = mysqli_stmt_get_result($rent_stmt);
        $rent_row = mysqli_fetch_assoc($rent_result);
        $rent = intval($rent_row['total'] ?? 0);
        $count_rent = intval($rent_row['count_rent'] ?? 0);

        // Get FnB and Others data - GROSS amounts dari tb_trans_fnb
        mysqli_stmt_bind_param($fnb_stmt, 'ss', $current_date, $username_escaped);
        mysqli_stmt_execute($fnb_stmt);
        $fnb_result = mysqli_stmt_get_result($fnb_stmt);
        $fnb_row = mysqli_fetch_assoc($fnb_result);
        $fnb = intval($fnb_row['fnb_total'] ?? 0);
        $others = intval($fnb_row['others_total'] ?? 0);
        $count_fnb = intval($fnb_row['count_fnb'] ?? 0);

        // Get spending data
        mysqli_stmt_bind_param($spending_stmt, 'ss', $current_date, $username_escaped);
        mysqli_stmt_execute($spending_stmt);
        $spending_result = mysqli_stmt_get_result($spending_stmt);
        $spending_row = mysqli_fetch_assoc($spending_result);
        $spending = intval($spending_row['total'] ?? 0);
        $count_spending = intval($spending_row['count_spending'] ?? 0);

        // Get promo data
        mysqli_stmt_bind_param($promo_stmt, 'ss', $current_date, $username_escaped);
        mysqli_stmt_execute($promo_stmt);
        $promo_result = mysqli_stmt_get_result($promo_stmt);
        $promo_row = mysqli_fetch_assoc($promo_result);
        $promo = intval($promo_row['total_promo'] ?? 0);

        // net = gross - spending (TIDAK kurangi promo di sini)
        $net = ($rent + $fnb + $others) - $spending;
        $daily_transactions = $count_rent + $count_fnb + $count_spending;

        // Update statistics
        $statistics['total_transactions'] += $daily_transactions;
        if ($rent > 0 || $fnb > 0 || $others > 0 || $spending > 0 || $promo > 0) {
            $statistics['work_days']++;
        }

        // Update totals - GROSS amounts
        $totals['rent'] += $rent;
        $totals['fnb'] += $fnb;
        $totals['others'] += $others;
        $totals['spending'] += $spending;
        $totals['promo'] += $promo;
        $totals['net'] += $net;

        // Update max/min tracking
        if ($rent > $max_values['rent']) {
            $max_values['rent'] = $rent;
            $max_dates['rent'] = $date_label;
        }
        if ($fnb > $max_values['fnb']) {
            $max_values['fnb'] = $fnb;
            $max_dates['fnb'] = $date_label;
        }
        if ($others > $max_values['others']) {
            $max_values['others'] = $others;
            $max_dates['others'] = $date_label;
        }
        if ($spending > $max_values['spending']) {
            $max_values['spending'] = $spending;
            $max_dates['spending'] = $date_label;
        }
        if ($promo > $max_values['promo']) {
            $max_values['promo'] = $promo;
            $max_dates['promo'] = $date_label;
        }
        if ($max_values['net'] === null || $net > $max_values['net']) {
            $max_values['net'] = $net;
            $max_dates['net'] = $date_label;
        }

        // Update min values (only for non-zero values)
        if ($rent > 0 && $rent < $min_values['rent']) {
            $min_values['rent'] = $rent;
            $min_dates['rent'] = $date_label;
        }
        if ($fnb > 0 && $fnb < $min_values['fnb']) {
            $min_values['fnb'] = $fnb;
            $min_dates['fnb'] = $date_label;
        }
        if ($others > 0 && $others < $min_values['others']) {
            $min_values['others'] = $others;
            $min_dates['others'] = $date_label;
        }
        if ($spending > 0 && $spending < $min_values['spending']) {
            $min_values['spending'] = $spending;
            $min_dates['spending'] = $date_label;
        }
        if ($promo > 0 && $promo < $min_values['promo']) {
            $min_values['promo'] = $promo;
            $min_dates['promo'] = $date_label;
        }
        if ($min_values['net'] === null || ($net > 0 && $net < $min_values['net'])) {
            $min_values['net'] = $net;
            $min_dates['net'] = $date_label;
        }

        $data_financial[] = [
            'date' => $date_label,
            'rent' => $rent,
            'fnb' => $fnb,
            'others' => $others,
            'spending' => $spending,
            'promo' => $promo,
            'net' => $net,
            'transactions' => $daily_transactions
        ];
    }

    // Clean up statements
    mysqli_stmt_close($rent_stmt);
    mysqli_stmt_close($fnb_stmt);
    mysqli_stmt_close($spending_stmt);
    mysqli_stmt_close($promo_stmt);

    // Format min/max data
    $formatted_max = [];
    $formatted_min = [];

    foreach (['rent', 'fnb', 'others', 'spending', 'net', 'promo'] as $key) {
        $formatted_max[$key] = [
            'value' => $max_values[$key] ?? 0,
            'date' => $max_dates[$key]
        ];

        $min_val = $min_values[$key];
        if ($min_val === PHP_INT_MAX)
            $min_val = 0;

        $formatted_min[$key] = [
            'value' => $min_val,
            'date' => $min_dates[$key]
        ];
    }

    // ---------------------------------------------
    // Additional statistics for PlayStation
    $ps_statistics = [
        'date_range' => [
            'start' => $date_start,
            'end' => $date_end,
            'total_days' => $total_calendar_days
        ],
        'performance' => [
            'most_used_console' => null,
            'highest_revenue_console' => null,
            'best_utilization_console' => null,
            'least_used_console' => null
        ]
    ];

    // Find performance metrics
    if (!empty($ps_data)) {
        // Most used console (by hours)
        $most_used = array_reduce($ps_data, function ($carry, $item) {
            return (!$carry || $item['total_hours'] > $carry['total_hours']) ? $item : $carry;
        });
        $ps_statistics['performance']['most_used_console'] = [
            'console_name' => $most_used['console_name'],
            'total_hours' => $most_used['total_hours']
        ];

        // Highest revenue console
        $highest_revenue = array_reduce($ps_data, function ($carry, $item) {
            return (!$carry || $item['revenue'] > $carry['revenue']) ? $item : $carry;
        });
        $ps_statistics['performance']['highest_revenue_console'] = [
            'console_name' => $highest_revenue['console_name'],
            'revenue' => $highest_revenue['revenue']
        ];

        // Best utilization console
        $best_utilization = array_reduce($ps_data, function ($carry, $item) {
            return (!$carry || $item['utilization_rate'] > $carry['utilization_rate']) ? $item : $carry;
        });
        $ps_statistics['performance']['best_utilization_console'] = [
            'console_name' => $best_utilization['console_name'],
            'utilization_rate' => $best_utilization['utilization_rate']
        ];

        // Least used console (among active ones)
        $active_consoles = array_filter($ps_data, function ($item) {
            return $item['total_rentals'] > 0;
        });
        if (!empty($active_consoles)) {
            $least_used = array_reduce($active_consoles, function ($carry, $item) {
                return (!$carry || $item['total_hours'] < $carry['total_hours']) ? $item : $carry;
            });
            $ps_statistics['performance']['least_used_console'] = [
                'console_name' => $least_used['console_name'],
                'total_hours' => $least_used['total_hours']
            ];
        }
    }

    // DEBUG INFO: Investigasi selisih PlayStation vs Financial
    $missing_revenue_query = "
        SELECT 
            t.id_ps,
            COUNT(t.id) as count_trans,
            SUM(t.harga) as missing_revenue
        FROM tb_trans_final tf
        INNER JOIN tb_trans t ON tf.invoice = t.inv
        WHERE DATE(tf.created_at) BETWEEN ? AND ?
          AND tf.userx = ?
          AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
          AND (t.is_deleted IS NULL OR t.is_deleted != 1)
          AND tf.id_trans NOT LIKE 'TRX-OUT%'
          AND t.id_ps NOT IN (
              SELECT DISTINCT no_ps 
              FROM playstations 
              WHERE userx = ?
          )
        GROUP BY t.id_ps
    ";

    $missing_stmt = mysqli_prepare($con, $missing_revenue_query);
    $missing_revenue_data = [];

    if ($missing_stmt) {
        mysqli_stmt_bind_param($missing_stmt, 'ssss', $date_start_escaped, $date_end_escaped, $username_escaped, $username_escaped);
        mysqli_stmt_execute($missing_stmt);
        $missing_result = mysqli_stmt_get_result($missing_stmt);

        while ($missing_row = mysqli_fetch_assoc($missing_result)) {
            $missing_revenue_data[] = [
                'console_id' => intval($missing_row['id_ps']),
                'transaction_count' => intval($missing_row['count_trans']),
                'missing_revenue' => floatval($missing_row['missing_revenue'])
            ];
        }
        mysqli_stmt_close($missing_stmt);
    }

    $ps_vs_financial_diff = $totals['rent'] - $ps_totals['total_revenue'];

    // Tambahkan debug info ke response
    $debug_info = [
        'has_date_filter' => $has_date_filter,
        'ps_revenue_total' => $ps_totals['total_revenue'],
        'financial_rent_total' => $totals['rent'],
        'difference' => $ps_vs_financial_diff,
        'missing_revenue_details' => $missing_revenue_data,
        'note' => $ps_vs_financial_diff > 0 ?
            "Ada rental {$ps_vs_financial_diff} yang tidak ter-link ke playstations table" :
            "PlayStation dan Financial data sudah sinkron"
    ];

    // Hitung ulang payment totals berdasarkan financial data
    $financial_total_gross = $totals['rent'] + $totals['fnb'] + $totals['others'];
    $financial_total_net = $financial_total_gross - $totals['promo'] - $totals['spending'];

    // Override payment totals dengan financial calculation yang benar
    $payment_totals['total_net'] = $financial_total_net;

    // Send successful response with data dan debug info
    sendSuccess([
        // Financial data
        'data' => $data_financial,
        'summary' => [
            'total' => $totals,
            'max' => $formatted_max,
            'min' => $formatted_min
        ],
        'statistics' => $statistics,
        'date_range' => [
            'start' => $date_start,
            'end' => $date_end,
            'days' => $statistics['total_days']
        ],
        // PlayStation data
        'ps_data' => [
            'rows' => $ps_data,
            'totals' => $ps_totals,
            'statistics' => $ps_statistics
        ],
        // Payment method data
        'payment_methods' => [
            'data' => $payment_data,
            'totals' => $payment_totals,
            'summary' => [
                'most_popular_method' => !empty($payment_data) ? $payment_data[0]['payment_method'] : null,
                'highest_amount_method' => !empty($payment_data) ? $payment_data[0]['payment_method'] : null,
                'total_methods_used' => $payment_totals['total_methods'],
                'net_profit' => $payment_totals['total_net']
            ]
        ],
        // DEBUG: Investigasi selisih data
        'debug_info' => $debug_info
    ]);

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendError('Internal server error occurred', 500);
} finally {
    // Clean up any remaining resources
    if (isset($con) && $con) {
        mysqli_close($con);
    }
}
?>