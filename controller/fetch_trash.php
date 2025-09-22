<?php
session_start();
header('Content-Type: application/json');

include '../include/config.php';

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

// Default start dan end jika kosong
if (!$start) $start = date('Y-m-01');
if (!$end) $end = date('Y-m-d');

$username = $_SESSION['username'];

// =====================
// 1. Ambil PARENT (tb_trans_final) - INCOME ONLY
// Spending akan diambil terpisah dari tb_trans_out
// =====================
$queryParent = "
SELECT 
    tf.id,
    tf.invoice,
    tf.userdel,
    tf.datedel,
    tf.created_at,
    tf.metode_pembayaran,
    tf.bayar,
    tf.kembali,
    tf.promo,
    tf.grand_total,
    tf.userx,
    'income' as transaction_type
FROM tb_trans_final tf
WHERE tf.userx = '$username'
  AND DATE(tf.created_at) BETWEEN '$start' AND '$end'
  AND tf.invoice IS NOT NULL
  AND ( tf.is_deleted = 1)
  AND tf.id_trans NOT LIKE 'TRX-OUT%'
ORDER BY tf.created_at DESC
";

// =====================
// 2. Ambil SPENDING dari tb_trans_out (struktur lama)
// =====================
$querySpending = "
SELECT 
    tout.id,
    tout.invoice,
    tout.userdel,
    tout.datedel,
    tout.created_at,
    COALESCE(tout.metode_pembayaran, 'cash') as metode_pembayaran,
    0 as bayar,
    0 as kembali,
    0 as promo,
    tout.grand_total,
    tout.userx,
    'spending' as transaction_type,
    COALESCE(tout.note, '[No Note]') as spending_note
FROM tb_trans_out tout
WHERE tout.userx = '$username'
  AND DATE(tout.created_at) BETWEEN '$start' AND '$end'
  AND tout.invoice IS NOT NULL
  AND ( tout.is_deleted = 1)
ORDER BY tout.created_at DESC
";

// =====================
// 3. Ambil CHILD (rental, fnb) dari invoice yang sama
//    Menggunakan JOIN dengan tb_trans_final untuk konsistensi tanggal
// =====================
$queryChild = "
SELECT * FROM (
    -- RENTAL - JOIN dengan tb_trans_final untuk konsistensi tanggal
    SELECT
        t.id AS id,
        'rental' AS source,
        tf.created_at AS tanggal,
        t.inv AS inv,
        t.usercreate AS usercreate,
        CONCAT(
            'Rental (', COALESCE(p.type_ps, '[Unknown]'), ') ',
            CASE 
                WHEN t.end IS NULL THEN 'Open play' 
                ELSE CONCAT(t.durasi, ' Min ', 
                    CASE WHEN t.extra = 1 THEN 'Extra' ELSE 'Reg' END)
            END,
            ' (#', t.id_ps, ')'
        ) AS details,
        COALESCE(t.harga, 0) AS total,
        'Rental' AS kategori,
        t.userx
    FROM tb_trans_final tf
    INNER JOIN tb_trans t ON tf.invoice = t.inv
    LEFT JOIN playstations p ON t.id_ps = p.no_ps AND p.userx = '$username'
    WHERE tf.userx = '$username'
      AND DATE(tf.created_at) BETWEEN '$start' AND '$end'
      AND ( tf.is_deleted = 1)
      AND ( t.is_deleted = 1)
      AND tf.id_trans NOT LIKE 'TRX-OUT%'
      AND tf.invoice IS NOT NULL

    UNION ALL

    -- FnB - JOIN dengan tb_trans_final untuk konsistensi tanggal
    SELECT
        fnb.id AS id,
        'fnb' AS source,
        tf.created_at AS tanggal,
        fnb.inv AS inv,
        fnb.usercreate AS usercreate,
        CONCAT(COALESCE(f.nama, '[Deleted Item]'), ' x', fnb.qty, 
               CASE WHEN fnb.id_ps IS NOT NULL THEN CONCAT(' (#', fnb.id_ps, ')') ELSE '' END
        ) AS details,
        COALESCE(fnb.total, 0) AS total,
        fnb.type AS kategori,
        fnb.userx
    FROM tb_trans_final tf
    INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
    LEFT JOIN tb_fnb f ON fnb.id_fnb = f.id
    WHERE tf.userx = '$username'
      AND DATE(tf.created_at) BETWEEN '$start' AND '$end'
      AND ( tf.is_deleted = 1)
      AND (fnb.is_deleted = 1)
      AND tf.id_trans NOT LIKE 'TRX-OUT%'
      AND tf.invoice IS NOT NULL

) AS all_trans
ORDER BY tanggal DESC
";

// =====================
// 4. Eksekusi Query
// =====================
$resultParent = mysqli_query($con, $queryParent);
if (!$resultParent) {
    echo json_encode(["error" => "Parent query error: " . mysqli_error($con), "data" => []]);
    exit;
}

$resultSpending = mysqli_query($con, $querySpending);
if (!$resultSpending) {
    echo json_encode(["error" => "Spending query error: " . mysqli_error($con), "data" => []]);
    exit;
}

$resultChild = mysqli_query($con, $queryChild);
if (!$resultChild) {
    echo json_encode(["error" => "Child query error: " . mysqli_error($con), "data" => []]);
    exit;
}

// =====================
// 5. Kumpulkan Data Parent (Income)
// =====================
$parents = [];
while ($row = mysqli_fetch_assoc($resultParent)) {
    $parents[$row['invoice']] = $row;
}

// =====================
// 6. Kumpulkan Data Spending
// =====================
$spendings = [];
while ($row = mysqli_fetch_assoc($resultSpending)) {
    $spendings[] = $row;
}

// =====================
// 7. Kelompokkan Data Child berdasarkan invoice (hanya untuk income)
// =====================
$childrenByInvoice = [];
while ($row = mysqli_fetch_assoc($resultChild)) {
    $invoice = $row['inv'];
    if (!isset($childrenByInvoice[$invoice])) {
        $childrenByInvoice[$invoice] = [];
    }
    $childrenByInvoice[$invoice][] = $row;
}

// =====================
// 8. Build data akhir - INCOME TRANSACTIONS
// =====================
$data = [];
foreach ($parents as $parent) {
    $invoice = $parent['invoice'];

    if (!isset($childrenByInvoice[$invoice])) continue;

    $childrenTotal = 0;
    $childrenDetails = [];

    foreach ($childrenByInvoice[$invoice] as $child) {
        $childrenTotal += (float)$child['total'];

        $childDetail = date('H:i:s', strtotime($child['tanggal'])) . ' : ';
        $childDetail .= $child['details'] ?? '<i class="text-danger">Product has been deleted</i>';

        $childTotal = (float)$child['total'];
        $childDetail .= ' - Rp ' . number_format($childTotal, 0, ',', '.');

        $childrenDetails[] = $childDetail;
    }

    $totalMatch = abs($childrenTotal - (float)$parent['grand_total']) < 0.01
        ? ' <span class="badge badge-success"></span>'
        : ' <span class="badge badge-danger">(Child: Rp ' . number_format($childrenTotal, 0, ',', '.') . ')</span>';

    $paymentMethodRaw = $parent['metode_pembayaran'] ?? 'N/A';
    $paymentMethod = ($paymentMethodRaw === 'debit') ? 'Bank Transfer' : $paymentMethodRaw;

    $bayar = (float)($parent['bayar'] ?? 0);
    $kembali = (float)($parent['kembali'] ?? 0);
    $promo = $parent['promo'] ?? '';
   $userid = $parent['userdel'] . '<br>' . ($parent['datedel'] ?? '');


    // Info Pembayaran untuk Income
    $paymentInfo = '<br><small class="text-muted">';
    $paymentInfo .= 'Metode: <strong>' . $paymentMethod . '</strong>';
    if (!empty($promo) && $promo > 0) {
        $paymentInfo .= ' | <span class="text-warning">Promo: <strong>Rp ' . number_format($promo, 0, ',', '.') . '</strong></span>';
    }
    if ($bayar > 0) {
        $paymentInfo .= ' | Bayar: <strong>Rp ' . number_format($bayar, 0, ',', '.') . '</strong>';
    }
    if ($kembali > 0) {
        $paymentInfo .= ' | Kembali: <strong>Rp ' . number_format($kembali, 0, ',', '.') . '</strong>';
    }
    $paymentInfo .= '</small>';

    // Format tampilan akhir untuk frontend
    $completeDetails = '<strong>Invoice: ' . $invoice . '</strong>' . $totalMatch . $paymentInfo;
    if (!empty($childrenDetails)) {
        $completeDetails .= '<br><div style="margin-top: 8px; padding-left: 15px; border-left: 2px solid #ddd;">';
        $completeDetails .= '<small>' . implode('<br>', $childrenDetails) . '</small>';
        $completeDetails .= '</div>';
    }

    $data[] = [
        'id' => $parent['id'],
        'source' => 'parent',
        'tanggal' => date('d-m-Y H:i:s', strtotime($parent['created_at'])),
        'details' => $completeDetails,
        'total' => '<strong>Rp ' . number_format((float)$parent['grand_total'], 0, ',', '.') . '</strong>',
        'kategori' => 'Income',
        'inv' => $invoice,
        'userid' => $userid,
        'userx' => $parent['userx'],
        'is_parent' => true,
        'transaction_type' => 'income',
        'metode_pembayaran' => $paymentMethod,
        'bayar' => $bayar,
        'kembali' => $kembali,
        'promo' => $promo,
        'children_count' => count($childrenByInvoice[$invoice])
    ];
}

// =====================
// 9. Add SPENDING TRANSACTIONS
// =====================
foreach ($spendings as $spending) {
    $paymentMethodRaw = $spending['metode_pembayaran'] ?? 'cash';
    $paymentMethod = ($paymentMethodRaw === 'debit') ? 'Bank Transfer' : $paymentMethodRaw;
    
    $userid = $spending['usercreate'] ?? '';
    $invoice = $spending['invoice'];
 $userid = $spending['userdel'] . '<br>' . ($spending['datedel'] ?? '');

    // Format tampilan untuk spending
    $completeDetails = '<strong class="text-danger">Spending: ' . $invoice . '</strong>';
    
    // Info spending note
    if (!empty($spending['spending_note']) && $spending['spending_note'] !== '[No Note]') {
        $completeDetails .= '<br><small class="text-muted">Note: ' . $spending['spending_note'] . '</small>';
    }
    
    // Info Pembayaran untuk Spending
    $paymentInfo = '<br><small class="text-muted">';
    $paymentInfo .= 'Metode: <strong>' . $paymentMethod . '</strong>';
    $paymentInfo .= '</small>';
    
    $completeDetails .= $paymentInfo;

    $data[] = [
        'id' => $spending['id'],
        'source' => 'spending',
        'tanggal' => date('d-m-Y H:i:s', strtotime($spending['created_at'])),
        'details' => $completeDetails,
        'total' => '<strong class="text-danger">- Rp ' . number_format(abs((float)$spending['grand_total']), 0, ',', '.') . '</strong>',
        'kategori' => 'Spending',
        'inv' => $invoice,
        'userid' => $userid,
        'userx' => $spending['userx'],
        'is_parent' => true,
        'transaction_type' => 'spending',
        'metode_pembayaran' => $paymentMethod,
        'bayar' => 0,
        'kembali' => 0,
        'promo' => 0,
        'children_count' => 0
    ];
}

// =====================
// 10. Sort semua data berdasarkan tanggal (DESC)
// =====================
usort($data, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

// =====================
// 11. Kirim data JSON
// =====================
echo json_encode([
    "data" => $data,
    "recordsTotal" => count($data),
    "recordsFiltered" => count($data)
]);
?>