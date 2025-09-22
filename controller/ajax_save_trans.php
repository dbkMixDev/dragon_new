<?php
include '../include/config.php';
session_start();
$userid = $_SESSION['user_id'] ?? '';
$username = $_SESSION['username'] ?? '';
$resX = $con->prepare("SELECT timezone FROM userx WHERE username = ?");
$resX->bind_param("s", $userid);
$resX->execute();
$resXult = $resX->get_result();
if ($row = $resXult->fetch_assoc()) {
    $timezone = $row['timezone'] ?: $defaultTimezone;
}
$resX->close();

date_default_timezone_set($timezone);
$now = date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode = $_POST['metode'] ?? '';
    $bayar = intval($_POST['bayar'] ?? 0);
    $kembali = intval($_POST['kembali'] ?? 0);
    $promo = intval($_POST['promo'] ?? 0);
    $invoice = $_POST['invoice'] ?? 'INV' . time();
    $grandtotal = intval($_POST['grandtotal'] ?? 0);
    $id_trans = $_POST['id_trans'] ?? '';
    $userx = $_POST['userx'] ?? '';
    $ps = intval($_POST['ps'] ?? 0);

    // 1. Cek status playstation
    if ($ps == 0) {
        // Biarkan saja, skip pengecekan
    } else {
        $cek = $con->query("SELECT status,duration FROM playstations WHERE no_ps = '$ps' AND userx = '$username' LIMIT 1");
        if ($cek && $cek->num_rows > 0) {
            $row = $cek->fetch_assoc();
            if (strtolower($row['duration']) === 'open') {
                echo json_encode(['success' => false, 'error' => 'Mode Open Play!, Sesi belum berakhir.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Playstation tidak ditemukan.']);
            exit;
        }
    }

    // 2. Pastikan $id_trans terisi dengan benar
    if (!$id_trans) {
        $username_safe = mysqli_real_escape_string($con, $userx);
        $qTrans = mysqli_query($con, "SELECT id_trans FROM tb_trans WHERE inv IS NULL AND id_ps = $ps AND userx = '$username_safe' ORDER BY id_trans DESC LIMIT 1");
        if ($rowTrans = mysqli_fetch_assoc($qTrans)) {
            $id_trans = $rowTrans['id_trans'];
        } else {
            $qTransFnb = mysqli_query($con, "SELECT id_trans FROM tb_trans_fnb WHERE inv IS NULL AND id_ps = $ps AND userx = '$username_safe' ORDER BY id_trans DESC LIMIT 1");
            if ($rowTransFnb = mysqli_fetch_assoc($qTransFnb)) {
                $id_trans = $rowTransFnb['id_trans'];
            }
        }

        if (!$id_trans) {
            echo json_encode(['success' => false, 'error' => 'ID Transaksi tidak ditemukan. Tidak dapat memproses pembayaran.']);
            exit;
        }
    }

    // 3. Cek apakah id_trans sudah ada di tb_trans_final
    $checkStmt = $con->prepare("SELECT id_trans FROM tb_trans_final WHERE id_trans = ? LIMIT 1");
    $checkStmt->bind_param("s", $id_trans);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Transaksi sudah diproses sebelumnya.']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Mulai transaction untuk memastikan data consistency
    $con->begin_transaction();

    try {
        // 4. Simpan ke tb_trans_final
        $stmt = $con->prepare("INSERT INTO tb_trans_final 
            (metode_pembayaran, bayar, kembali, promo, invoice, id_trans, userx, grand_total, usercreate, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiisssiss", $metode, $bayar, $kembali, $promo, $invoice, $id_trans, $userx, $grandtotal, $userid, $now);

        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan transaksi final: ' . $stmt->error);
        }
        $stmt->close();

        // 5. Update invoice di tb_trans
        $updateTrans = $con->prepare("UPDATE tb_trans SET inv = ? WHERE id_trans = ?");
        $updateTrans->bind_param("ss", $invoice, $id_trans);
        if (!$updateTrans->execute()) {
            throw new Exception('Gagal update invoice tb_trans: ' . $updateTrans->error);
        }
        $updateTrans->close();

        // 6. Update invoice di tb_trans_fnb
        $updateTransFnb = $con->prepare("UPDATE tb_trans_fnb SET inv = ? WHERE id_trans = ?");
        $updateTransFnb->bind_param("ss", $invoice, $id_trans);
        if (!$updateTransFnb->execute()) {
            throw new Exception('Gagal update invoice tb_trans_fnb: ' . $updateTransFnb->error);
        }
        $updateTransFnb->close();

        // Commit transaction jika semua berhasil
        $con->commit();

        $autoQuery = $con->prepare("SELECT 1 FROM tb_feature WHERE userx = ? AND feature = 'autoprint' AND status = 1 LIMIT 1");
        $autoQuery->bind_param("s", $userx);
        $autoQuery->execute();
        $autoResult = $autoQuery->get_result();
        $hasAutopilot = $autoResult->num_rows > 0;

        echo json_encode([
            'success' => true,
            'inv' => $invoice,
            'username' => $userx,
            'autopilot' => $hasAutopilot
        ]);

        $autoQuery->close();

    } catch (Exception $e) {
        // Rollback jika ada error
        $con->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>