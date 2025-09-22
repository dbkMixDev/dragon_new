<?php
header('Content-Type: application/json');
session_start();
$usernames = $_SESSION['username'] ?? '';
include '../include/config.php';

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

// Default start dan end jika kosong
if (!$start) {
    $start = date('Y-m-01');
}
if (!$end) {
    $end = date('Y-m-d');
}

$filter = "WHERE DATE(tanggal) BETWEEN '$start' AND '$end'";

$query = "
SELECT * FROM (
    -- Bagian 1: Rental (FIXED: menggunakan tb_trans_final.created_at)
    SELECT
        t.id AS id,
        'rental' AS source,
        tf.created_at AS tanggal,
        t.inv AS inv,
        CONCAT(
            'Rental (', COALESCE(p.type_ps, '[Unknown]'), ') ',
            CASE 
                WHEN t.end IS NULL THEN 'Open play' 
                ELSE 
                    CONCAT(t.durasi, ' Min ', 
                        CASE WHEN t.extra = 1 THEN 'Extra' ELSE 'Reg' END
                    )
            END,
            ' (#', t.id_ps, ')'
        ) AS details,
        COALESCE(t.harga, 0) AS total,
        'Rental' AS kategori,
        t.userx,
        CAST(NULL AS CHAR) AS total_out,
        COALESCE(tf.promo, 0) AS promo
    FROM tb_trans_final tf
    INNER JOIN tb_trans t ON tf.invoice = t.inv
    LEFT JOIN playstations p ON t.id_ps = p.no_ps AND p.userx = '$usernames'
    WHERE tf.userx = '$usernames' AND tf.invoice IS NOT NULL
      AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
      AND (t.is_deleted IS NULL OR t.is_deleted != 1)
      AND tf.id_trans NOT LIKE 'TRX-OUT%'

    UNION ALL

    -- Bagian 2: FnB (FIXED: menggunakan tb_trans_final.created_at)
    SELECT
        fnb.id AS id,
        'fnb' AS source,
        tf.created_at AS tanggal,
        fnb.inv AS inv,
        CONCAT(COALESCE(f.nama, '[Deleted Item]'), ' x', fnb.qty, ' (#', fnb.id_ps, ')') AS details,
        COALESCE(fnb.total, 0) AS total,
        fnb.type AS kategori,
        fnb.userx,
        CAST(NULL AS CHAR) AS total_out,
        NULL AS promo
    FROM tb_trans_final tf
    INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
    LEFT JOIN tb_fnb f ON fnb.id_fnb = f.id
    WHERE tf.userx = '$usernames' AND tf.invoice IS NOT NULL
      AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
      AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
      AND tf.id_trans NOT LIKE 'TRX-OUT%'

    UNION ALL

    -- Bagian 3: Spending (FIXED: menggunakan tb_trans_final.created_at dengan join ke tb_trans_out)
    SELECT
        tfo.id AS id,
        'spending' AS source,
        tf.created_at AS tanggal,
        CAST(NULL AS CHAR) AS inv,
        COALESCE(tfo.note, '[No Note]') AS details,
        CAST(NULL AS CHAR) AS total,
        'Spending' AS kategori,
        tf.userx,
        COALESCE(tf.grand_total, 0) AS total_out,
        NULL AS promo
    FROM tb_trans_final tf
    INNER JOIN tb_trans_out tfo ON tf.id_trans = tfo.id_trans
    WHERE tf.userx = '$usernames' AND tf.id_trans IS NOT NULL
      AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
      AND (tfo.is_deleted IS NULL OR tfo.is_deleted != 1)
      AND tf.id_trans LIKE 'TRX-OUT%'
) AS all_trans
$filter
ORDER BY tanggal ASC
";

$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode([
        "error" => mysqli_error($con),
        "data" => [],
        "debug" => [
            "start" => $start,
            "end" => $end,
            "filter" => $filter
        ]
    ]);
    exit;
}

$rawData = [];
$groupedData = [];
$promoPerInvoice = [];

while ($row = mysqli_fetch_assoc($result)) {
    $in = (float)$row['total'];
    $out = (float)$row['total_out'];
    $promox = (float)$row['promo'];
    $invoice = $row['inv'];
    $timestamp = strtotime($row['tanggal']);

    $formattedRow = [
        'timestamp' => $timestamp,
        'tanggal' => date('d-m-Y H:i:s', $timestamp),
        'details' => $row['details'] ?: '<i class="text-danger">Product has been deleted</i>',
        'in' => $in > 0 ? 'Rp ' . number_format($in, 0, ',', '.') : '-',
        'out' => $out > 0 ? '<i class="text-danger">Rp ' . number_format($out, 0, ',', '.') . '</i>' : '-',
        'saldo_raw_in' => $in,
        'saldo_raw_out' => $out,
        'promo_raw' => $promox,
        'invoice' => $invoice,
        'is_promo' => false
    ];

    $rawData[] = $formattedRow;

    if ($invoice) {
        $groupedData[$invoice][] = $formattedRow;
        if (!isset($promoPerInvoice[$invoice]) && $promox > 0) {
            $promoPerInvoice[$invoice] = $promox;
        }
    }
}

// Tambahkan entry promo untuk setiap invoice yang punya promo
foreach ($promoPerInvoice as $inv => $promoValue) {
    $lastTimestamp = 0;
    foreach ($groupedData[$inv] as $row) {
        if ($row['timestamp'] > $lastTimestamp) {
            $lastTimestamp = $row['timestamp'];
        }
    }

    $rawData[] = [
        'timestamp' => $lastTimestamp + 1,
        'tanggal' => date('d-m-Y H:i:s', $lastTimestamp),
        'details' => '<i class="text-muted">Promo Used</i>',
        'in' => '-',
        'out' => '<i class="text-warning">Rp ' . number_format($promoValue, 0, ',', '.') . '</i>',
        'saldo_raw_in' => 0,
        'saldo_raw_out' => $promoValue,
        'promo_raw' => $promoValue,
        'invoice' => $inv,
        'is_promo' => true
    ];
}

// Urutkan data berdasarkan timestamp
usort($rawData, function($a, $b) {
    return $a['timestamp'] <=> $b['timestamp'];
});

// Hitung saldo berjalan DAN tambahkan nomor urut
$data = [];
$saldo = 0;
$promo = 0;
$no_urut = 1; // TAMBAHKAN INI: counter untuk nomor urut

foreach ($rawData as $row) {
    $saldo += $row['saldo_raw_in'];
    $saldo -= $row['saldo_raw_out'];

    if ($row['is_promo']) {
        $promo += $row['promo_raw'];
    }

    // TAMBAHKAN INI: masukkan nomor urut ke dalam array
    $data[] = [
        'no_urut' => $no_urut++, // TAMBAHKAN BARIS INI
        'tanggal' => $row['tanggal'],
        'details' => $row['details'],
        'in' => $row['in'],
        'out' => $row['out'],
        'saldo' => 'Rp ' . number_format($saldo, 0, ',', '.'),
    ];
}

echo json_encode([
    "data" => $data,
    "totalPromo" => $promo > 0 ? 'Rp ' . number_format($promo, 0, ',', '.') : '-',
    "recordsTotal" => count($data),
    "recordsFiltered" => count($data)
]);
?>