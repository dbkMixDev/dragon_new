<?php
include '../include/config.php';
header('Content-Type: application/json');
session_start();
$username = $_SESSION['username'];
$userid = $_SESSION['user_id'];

$resX = $con->prepare("SELECT timezone FROM userx WHERE username = ?");
$resX->bind_param("s", $userid);
$resX->execute();
$resXult = $resX->get_result();
if ($row = $resXult->fetch_assoc()) {
    $timezone = $row['timezone'] ?: $defaultTimezone;
}
$resX->close();

date_default_timezone_set($timezone);
$now = date('Y-m-d H:i:s'); // Contoh: 2025-06-14 08:15:00

$id_trans = isset($_POST['id_trans']) ? trim($_POST['id_trans']) : '';
$id_fnb = isset($_POST['id_fnb']) ? trim($_POST['id_fnb']) : '';
$qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;

$ps_id = isset($_POST['ps']) ? (int) $_POST['ps'] : 0;
// Cek apakah sudah ada transaksi FNB dengan inv NULL, userx dan id_ps sama
if (!$id_trans) {
    $username_safe = mysqli_real_escape_string($con, $username);
    $cekFnb = mysqli_query($con, "SELECT tf.id_trans FROM tb_trans_fnb tf 
        JOIN tb_trans t ON tf.id_trans = t.id_trans 
        WHERE t.inv IS NULL AND t.userx = '$username_safe' AND t.id_ps = $ps_id 
        ORDER BY tf.id_trans DESC LIMIT 1");
    if ($rowFnb = mysqli_fetch_assoc($cekFnb)) {
        $id_trans = $rowFnb['id_trans'];
    } else {
        // Cek tb_trans dengan inv NULL
        $qTrans = mysqli_query($con, "SELECT id_trans FROM tb_trans_fnb WHERE inv IS NULL AND id_ps = $ps_id AND userx = '$username_safe' ORDER BY id_trans DESC LIMIT 1");
        if ($rowTrans = mysqli_fetch_assoc($qTrans)) {
            $id_trans = $rowTrans['id_trans'];
        } else {
            // Fungsi generateRandomCode
            function generateRandomCode($length = 10)
            {
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $code = '';
                for ($i = 0; $i < $length; $i++) {
                    $code .= $chars[random_int(0, strlen($chars) - 1)];
                }
                return $code;
            }
            $id_trans = generateRandomCode(10);
        }
    }
}

if (!$id_trans || !$id_fnb || $qty < 1) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit;
}

// Cek produk FNB valid
$qFnb = mysqli_query($con, "SELECT * FROM tb_fnb WHERE id='" . mysqli_real_escape_string($con, $id_fnb) . "' LIMIT 1");
if (!$qFnb || mysqli_num_rows($qFnb) == 0) {
    echo json_encode(['success' => false, 'error' => 'Produk tidak ditemukan']);
    exit;
}
$fnb = mysqli_fetch_assoc($qFnb);
$harga = (int) $fnb['harga'];
$type = $fnb['type_fnb']; // Ambil type_fnb, default jika tidak ada
$real_db_id = null;
$final_qty = 0;

// Cek apakah sudah ada dalam transaksi
$cek = mysqli_query($con, "SELECT id, qty FROM tb_trans_fnb WHERE id_trans='$id_trans' AND id_fnb='$id_fnb' AND userx='$username' ");
if ($row = mysqli_fetch_assoc($cek)) {
    // Update existing
    $new_qty = $row['qty'] + $qty;
    $new_total = $new_qty * $harga;
    mysqli_query($con, "UPDATE tb_trans_fnb SET qty='$new_qty', total='$new_total' WHERE id='{$row['id']}'  AND userx='$username' ");
    $final_qty = $new_qty;
    $real_db_id = $row['id']; // ID yang sudah ada
} else {
    // Insert new
    $new_total = $qty * $harga;
    mysqli_query($con, "INSERT INTO tb_trans_fnb (id_trans, id_fnb, qty,userx,id_ps, total,type,created_at,usercreate)
     VALUES ('$id_trans', '$id_fnb', '$qty', '$username','$ps_id','$new_total','$type', '$now','$userid')");
    $final_qty = $qty;
    $real_db_id = mysqli_insert_id($con); // ID baru dari database
}

// Hitung total FNB
$total_fnb = 0;
$qTotalFnb = mysqli_query($con, "SELECT SUM(tf.qty * f.harga) AS total FROM tb_trans_fnb tf
    JOIN tb_fnb f ON tf.id_fnb = f.id
    WHERE tf.id_trans = '$id_trans'  AND tf.userx='$username' ");
if ($r = mysqli_fetch_assoc($qTotalFnb)) {
    $total_fnb = (int) $r['total'];
}

// PERBAIKAN: Total rental - ambil SEMUA rental untuk PS ini yang belum di-invoice
$total_rental = 0;
$username_safe = mysqli_real_escape_string($con, $username);
$qRentalAll = mysqli_query($con, "SELECT SUM(harga) AS total_rental FROM tb_trans 
    WHERE id_ps = '$ps_id' AND inv IS NULL AND userx = '$username_safe'");
if ($r = mysqli_fetch_assoc($qRentalAll)) {
    $total_rental = (int) ($r['total_rental'] ?? 0);
}

// Final response - TAMBAHKAN real_db_id
echo json_encode([
    'success' => true,
    'data' => [
        'id' => $id_fnb,
        'real_db_id' => $real_db_id, // ID sebenarnya di database
        'nama' => $fnb['nama'],
        'qty' => $final_qty,
        'harga' => $harga,
        'total' => $final_qty * $harga,
    ],
    'total_fnb' => $total_fnb,
    'total_rental' => $total_rental,
    'grand_total' => $total_fnb + $total_rental
]);
?>