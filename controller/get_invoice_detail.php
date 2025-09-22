<?php
if (isset($_GET['invoice'])) {
    require_once '../include/config.php';
    $invoice = $con->real_escape_string($_GET['invoice']);
    $result = [
        'trans' => [],
        'fnb' => [],
        'spending' => [],
        'promo' => null  // Tambahkan promo di sini
    ];

    // Cek apakah id_trans mengandung 'TRX-OUT' dari tabel tb_trans_final
    $q_check_trans = $con->query("SELECT id_trans, promo FROM tb_trans_final WHERE invoice = '$invoice'");
    $is_trx_out = false;
    if ($row_check = $q_check_trans->fetch_assoc()) {
        // Cek TRX-OUT
        if (strpos($row_check['id_trans'], 'TRX-OUT') !== false) {
            $is_trx_out = true;
        }

        // Simpan promo jika ada
        if (!empty($row_check['promo'])) {
            $result['promo'] = $row_check['promo'];
        }
    }

    // Menambahkan data transaksi rental jika ada
    $transFound = false; // Flag untuk memeriksa jika ada transaksi rental
    if (!$is_trx_out) {
        // Rental (tb_trans)
        $q_trans = $con->query("SELECT 
            t.id_ps, 
            p.type_ps, 
            t.extra,
            t.harga, 
            t.durasi,
            1 AS qty
        FROM 
            tb_trans t
        LEFT JOIN 
            playstations p ON t.id_ps = p.id
        WHERE 
            t.inv = '$invoice' ORDER BY t.id ASC");
        while ($row = $q_trans->fetch_assoc()) {
            $result['trans'][] = $row;
            $transFound = true;
        }

        // FnB (tb_trans_fnb)
        $q_fnb = $con->query("SELECT t.qty, t.total, f.nama FROM tb_trans_fnb t LEFT JOIN tb_fnb f ON t.id_fnb=f.id WHERE t.inv='$invoice'");
        while ($row = $q_fnb->fetch_assoc()) {
            $result['fnb'][] = $row;
            $transFound = true;
        }
    }

    // Jika tidak ada transaksi rental/FnB, tampilkan spending
    if (!$transFound) {
        $q_spending = $con->query("SELECT note, grand_total FROM tb_trans_out WHERE invoice = '$invoice'");
        while ($row = $q_spending->fetch_assoc()) {
            $result['spending'][] = $row;
        }
    }

    // Kirim JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

?>
