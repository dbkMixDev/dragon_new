<?php

// Fungsi untuk generate random id_trans
function generateRandomCode($length = 10)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}
// Pastikan username tersedia di awal
$user_level = $_SESSION['level'] ?? 'user';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_POST['username']) ? $_POST['username'] : null);
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
// Handle Start Rental
if (isset($_POST['action']) && $_POST['action'] === 'start_rental') {
    // VALIDASI SESSION WAJIB - STOP JIKA TIDAK ADA
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        session_destroy();
        session_start();
        $_SESSION['error_token'] = "Session timeout, Please login again!";
        header("Location: login.php");
        exit;
    }

    $no_ps = $_POST['no_ps'];
    $type_ps = $_POST['type_ps'];
    $duration = $_POST['duration'];
    $now = date('Y-m-d H:i:s');
    $harga = isset($_POST['harga']) ? (int) $_POST['harga'] : null;
    $prepare = isset($_POST['prepare']) ? (int) $_POST['prepare'] : 0;

    // VALIDASI KEPEMILIKAN PS DAN STATUS
    $checkPS = mysqli_query($con, "SELECT status FROM playstations WHERE no_ps='" . mysqli_real_escape_string($con, $no_ps) . "' AND type_ps='" . mysqli_real_escape_string($con, $type_ps) . "' AND userx='" . mysqli_real_escape_string($con, $username) . "'");
    $psData = mysqli_fetch_assoc($checkPS);

    if (!$psData) {
        echo "<script>alert('PlayStation tidak ditemukan atau bukan milik Anda'); location.reload();</script>";
        exit();
    }
    if ($psData['status'] !== 'available') {
        echo "<script>alert('PlayStation sedang digunakan'); location.reload();</script>";
        exit();
    }

    if ($duration === 'open') {
        $end_time = null;
    } else {
        $end_time = date('Y-m-d H:i:s', strtotime("+$duration minutes"));
    }
    $status = 'occupied';

    // UPDATE DENGAN KONDISI STATUS AVAILABLE
    $q = "UPDATE playstations SET status='$status', start_time='$now', end_time=" . ($end_time ? "'$end_time'" : 'NULL') . ", duration='$duration' WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND status='available'";
    $result = mysqli_query($con, $q);

    // CEK APAKAH UPDATE BERHASIL
    if (!$result || mysqli_affected_rows($con) === 0) {
        echo "<script>alert('Gagal memulai rental - PlayStation mungkin sudah digunakan'); location.reload();</script>";
        exit();
    }

    // Insert ke tb_trans saat mulai rental
    $id_trans = generateRandomCode(10);
    $start_trans = $now;
    $mode_stop = NULL;
    $manual_stop = 'NULL';
    $date_trans = $now;
    $extra = 0;
    if ($duration === 'open') {
        $durasi_trans = NULL;
        $end_trans = NULL;
    } else {
        if ($prepare === 1 && is_numeric($duration) && $duration > 5) {
            $durasi_trans = $duration - 5;
        } else {
            $durasi_trans = $duration;
        }
        $end_trans = date('Y-m-d H:i:s', strtotime("$now +$durasi_trans minutes"));
    }
    $qTrans = "INSERT INTO tb_trans (id_trans, id_ps, start, end, durasi, mode_stop, manual_stop, harga, date_trans, extra, userx,usercreate) VALUES (
        '$id_trans', '$no_ps', '$start_trans', " . ($end_trans ? "'$end_trans'" : 'NULL') . ", " . ($durasi_trans !== NULL ? "'$durasi_trans'" : 'NULL') . ", " . ($mode_stop ? "'$mode_stop'" : 'NULL') . ", $manual_stop, " . ($harga !== null ? "'$harga'" : 'NULL') . ", '$date_trans', '$extra', " . ($username ? "'$username'" : 'NULL') . ",'$userid')";
    mysqli_query($con, $qTrans);
    error_log('DEBUG: Start Rental - no_ps: ' . $no_ps . ', type_ps: ' . $type_ps . ', duration: ' . $duration . ', harga: ' . $harga);
    echo "<script>location.reload();</script>";
}
// Handle Extra Time
if (isset($_POST['action']) && $_POST['action'] === 'extra_time') {
    // VALIDASI SESSION WAJIB - STOP JIKA TIDAK ADA
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        session_destroy();
        session_start();
        $_SESSION['error_token'] = "Session timeout, Please login again!";
        header("Location: login.php");
        exit;
    }

    $no_ps = $_POST['no_ps'];
    $type_ps = $_POST['type_ps'];
    $add_duration = $_POST['add_duration'];
    $harga = isset($_POST['harga']) ? (int) $_POST['harga'] : null;

    // VALIDASI KEPEMILIKAN PS DAN STATUS OCCUPIED
    $checkPS = mysqli_query($con, "SELECT end_time, duration, status FROM playstations WHERE no_ps='" . mysqli_real_escape_string($con, $no_ps) . "' AND type_ps='" . mysqli_real_escape_string($con, $type_ps) . "' AND userx='" . mysqli_real_escape_string($con, $username) . "'");
    $psData = mysqli_fetch_assoc($checkPS);

    if (!$psData) {
        echo "<script>alert('PlayStation tidak ditemukan atau bukan milik Anda'); location.reload();</script>";
        exit();
    }
    if ($psData['status'] !== 'occupied') {
        echo "<script>alert('PlayStation tidak sedang digunakan'); location.reload();</script>";
        exit();
    }

    // Get current end_time and duration
    $current_end = $psData['end_time'];
    $current_dur = $psData['duration'];

    if ($add_duration === 'open') {
        $new_end = null;
        $new_dur = 'open';
    } else {
        $new_end = date('Y-m-d H:i:s', strtotime("$current_end +$add_duration minutes"));
        $new_dur = is_numeric($current_dur) ? $current_dur + $add_duration : $add_duration;
    }

    // UPDATE DENGAN KONDISI STATUS OCCUPIED DAN KEPEMILIKAN
    $q = "UPDATE playstations SET end_time=" . ($new_end ? "'$new_end'" : 'NULL') . ", duration='$new_dur' WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND status='occupied'";
    $result = mysqli_query($con, $q);

    // CEK APAKAH UPDATE BERHASIL
    if (!$result || mysqli_affected_rows($con) === 0) {
        echo "<script>alert('Gagal menambah waktu - PlayStation mungkin sudah berhenti'); location.reload();</script>";
        exit();
    }

    // Ambil id_trans dari transaksi utama (pertama) untuk sesi ini
    // Cek id_trans aktif di tb_trans terlebih dahulu
    $resTrans = mysqli_query($con, "SELECT id_trans FROM tb_trans WHERE id_ps='$no_ps' AND inv IS NULL and userx='" . mysqli_real_escape_string($con, $username) . "' ORDER BY start ASC LIMIT 1");
    $rowTrans = mysqli_fetch_assoc($resTrans);

    // Jika tidak ada di tb_trans, cek di tb_trans_fnb
    if (!$rowTrans) {
        $resFnbTrans = mysqli_query($con, "SELECT DISTINCT tf.id_trans FROM tb_trans_fnb tf 
                                      LEFT JOIN tb_fnb f ON tf.id_fnb = f.id
                                      WHERE tf.id_ps = '$no_ps' 
                                      AND tf.inv IS NULL 
                                      AND f.userx = '" . mysqli_real_escape_string($con, $username) . "' 
                                      ORDER BY tf.id ASC LIMIT 1");
        $rowFnbTrans = mysqli_fetch_assoc($resFnbTrans);
        $id_trans = $rowFnbTrans ? $rowFnbTrans['id_trans'] : generateRandomCode(10);
    } else {
        $id_trans = $rowTrans['id_trans'];
    }
    $start_trans = $current_end; // start dari akhir end sebelumnya

    // Kolom end harus diisi (bukan NULL), yaitu start_trans + durasi_trans menit
    if ($add_duration === 'open') {
        $end_trans = NULL;
    } else {
        $end_trans = date('Y-m-d H:i:s', strtotime("$start_trans +$add_duration minutes"));
    }
    $mode_stop = NULL;
    $manual_stop = 'NULL';
    $date_trans = date('Y-m-d H:i:s');
    $extra = 1;
    $durasi_trans = $add_duration;

    $qTrans = "INSERT INTO tb_trans (id_trans, id_ps, start, end, durasi, mode_stop, manual_stop, harga, date_trans, extra, userx,usercreate) VALUES (
        '$id_trans', '$no_ps', '$start_trans', " . ($end_trans ? "'$end_trans'" : 'NULL') . ", '$durasi_trans', " . ($mode_stop ? "'$mode_stop'" : 'NULL') . ", $manual_stop, " . ($harga !== null ? "'$harga'" : 'NULL') . ", '$date_trans', '$extra', " . ($username ? "'$username'" : 'NULL') . ",'$userid')";
    mysqli_query($con, $qTrans);
    // echo "<script>location.reload();</script>";
}
// Handle Extra Time
if (isset($_POST['action']) && $_POST['action'] === 'extra_time_available') {
    // VALIDASI SESSION WAJIB - STOP JIKA TIDAK ADA
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        session_destroy();
        session_start();
        $_SESSION['error_token'] = "Session timeout, Please login again!";
        header("Location: login.php");
        exit;
    }

    $no_ps = $_POST['no_ps'];
    $type_ps = $_POST['type_ps'];
    $add_duration = $_POST['add_duration'];
    $harga = isset($_POST['harga']) ? (int) $_POST['harga'] : null;

    // VALIDASI KEPEMILIKAN PS DAN STATUS OCCUPIED
    $checkPS = mysqli_query($con, "SELECT end AS end FROM tb_trans WHERE id_ps='" . mysqli_real_escape_string($con, $no_ps) . "' 
    AND inv IS NULL AND userx='" . mysqli_real_escape_string($con, $username) . "'");
    $psData = mysqli_fetch_assoc($checkPS);

    if (!$psData) {
        echo "<script>alert('PlayStation tidak ditemukan atau bukan milik Anda'); </script>";
        exit();
    }
    // if ($psData['status'] !== 'occupied') {
    //     echo "<script>alert('PlayStation tidak sedang digunakan'); location.reload();</script>";
    //     exit();
    // }


    if ($add_duration === 'open') {
        $end_time = null;
    } else {
        $end_time = date('Y-m-d H:i:s', strtotime("+$add_duration minutes"));
    }
    $status = 'occupied';

    // UPDATE DENGAN KONDISI STATUS AVAILABLE
    $q = "UPDATE playstations SET status='$status', start_time='$now', end_time=" . ($end_time ? "'$end_time'" : 'NULL') . ", duration='$add_duration' WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND status='available'";
    $result = mysqli_query($con, $q);
    // CEK APAKAH UPDATE BERHASIL
    if (!$result || mysqli_affected_rows($con) === 0) {
        echo "<script>alert('Gagal menambah waktu - PlayStation mungkin masih tersewa'); location.reload();</script>";
        exit();
    }

    // Ambil id_trans dari transaksi utama (pertama) untuk sesi ini
    // Cek id_trans aktif di tb_trans terlebih dahulu
    $resTrans = mysqli_query($con, "SELECT id_trans FROM tb_trans WHERE id_ps='$no_ps' AND inv IS NULL and userx='" . mysqli_real_escape_string($con, $username) . "' ORDER BY start ASC LIMIT 1");
    $rowTrans = mysqli_fetch_assoc($resTrans);

    // Jika tidak ada di tb_trans, cek di tb_trans_fnb
    if (!$rowTrans) {
        $resFnbTrans = mysqli_query($con, "SELECT DISTINCT tf.id_trans FROM tb_trans_fnb tf 
                                      LEFT JOIN tb_fnb f ON tf.id_fnb = f.id
                                      WHERE tf.id_ps = '$no_ps' 
                                      AND tf.inv IS NULL 
                                      AND f.userx = '" . mysqli_real_escape_string($con, $username) . "' 
                                      ORDER BY tf.id ASC LIMIT 1");
        $rowFnbTrans = mysqli_fetch_assoc($resFnbTrans);
        $id_trans = $rowFnbTrans ? $rowFnbTrans['id_trans'] : generateRandomCode(10);
    } else {
        $id_trans = $rowTrans['id_trans'];
    }
    $start_trans = $now; // start dari akhir end sebelumnya

    // Kolom end harus diisi (bukan NULL), yaitu start_trans + durasi_trans menit
    if ($add_duration === 'open') {
        $end_trans = NULL;
    } else {
        $end_trans = date('Y-m-d H:i:s', strtotime("$start_trans +$add_duration minutes"));
    }
    $mode_stop = NULL;
    $manual_stop = 'NULL';
    $date_trans = date('Y-m-d H:i:s');
    $extra = 1;
    $durasi_trans = $add_duration;

    $qTrans = "INSERT INTO tb_trans (id_trans, id_ps, start, end, durasi, mode_stop, manual_stop, harga, date_trans, extra, userx,usercreate) VALUES (
        '$id_trans', '$no_ps', '$start_trans', " . ($end_trans ? "'$end_trans'" : 'NULL') . ", '$durasi_trans', " . ($mode_stop ? "'$mode_stop'" : 'NULL') . ", $manual_stop, " . ($harga !== null ? "'$harga'" : 'NULL') . ", '$date_trans', '$extra', " . ($username ? "'$username'" : 'NULL') . ",'$userid')";
    mysqli_query($con, $qTrans);
    // echo "<script>location.reload();</script>";
}
// Endpoint AJAX untuk membebaskan PS
if (isset($_POST['action']) && $_POST['action'] === 'free_ps') {
    // VALIDASI SESSION WAJIB
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        session_destroy();
        session_start();
        $_SESSION['error_token'] = "Session Timeout, Please login again!";
        header("Location: login.php");
        exit;
    }

    $no_ps = $_POST['no_ps'];
    $type_ps = $_POST['type_ps'];
    $mode_stop = 'AUTO';

    // VALIDASI KEPEMILIKAN PS
    $checkPS = mysqli_query($con, "SELECT status FROM playstations WHERE no_ps='" . mysqli_real_escape_string($con, $no_ps) . "' AND type_ps='" . mysqli_real_escape_string($con, $type_ps) . "' AND userx='" . mysqli_real_escape_string($con, $username) . "'");
    $psData = mysqli_fetch_assoc($checkPS);

    if (!$psData) {
        echo json_encode(['success' => false, 'error' => 'PlayStation tidak ditemukan atau bukan milik Anda']);
        exit;
    }

    $now = date('Y-m-d H:i:s');

    // UPDATE PS dengan validasi kepemilikan
    $q = "UPDATE playstations SET status='available', start_time=NULL, end_time=NULL, duration=NULL WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND status='occupied'";
    $result = mysqli_query($con, $q);

    if (!$result || mysqli_affected_rows($con) === 0) {
        echo json_encode(['success' => false, 'error' => 'Gagal membebaskan PS']);
        exit;
    }

    // Update transaksi
    $harga_update = '';
    if (isset($_POST['harga']) && is_numeric($_POST['harga'])) {
        $harga = (int) $_POST['harga'];
        $harga_update = ", harga='$harga'";
    }
    $q = "UPDATE tb_trans SET manual_stop='$now', mode_stop='$mode_stop' $harga_update WHERE mode_stop IS NULL AND id_ps = '$no_ps' and userx='" . mysqli_real_escape_string($con, $username) . "'";
    mysqli_query($con, $q);

    echo json_encode(['success' => true]);
    exit;
}

// Handle Pause
if (isset($_POST['action']) && $_POST['action'] === 'pause_ps') {
    // VALIDASI SESSION WAJIB
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        session_destroy();
        session_start();
        $_SESSION['error_token'] = "Session Timeout, Please login again!";
        header("Location: login.php");
        exit;
    }

    $no_ps = $_POST['no_ps'];
    $type_ps = $_POST['type_ps'];

    // VALIDASI KEPEMILIKAN PS DAN STATUS OCCUPIED
    $checkPS = mysqli_query($con, "SELECT status FROM playstations WHERE no_ps='" . mysqli_real_escape_string($con, $no_ps) . "' AND type_ps='" . mysqli_real_escape_string($con, $type_ps) . "' AND userx='" . mysqli_real_escape_string($con, $username) . "'");
    $psData = mysqli_fetch_assoc($checkPS);

    if (!$psData) {
        echo json_encode(['success' => false, 'error' => 'PlayStation tidak ditemukan atau bukan milik Anda']);
        exit;
    }
    if ($psData['status'] !== 'occupied') {
        echo json_encode(['success' => false, 'error' => 'PlayStation tidak sedang digunakan']);
        exit;
    }

    $pause_time = date('Y-m-d H:i:s');
    $q = "UPDATE playstations SET status='paused', pause_time='$pause_time' WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND status='occupied'";
    $result = mysqli_query($con, $q);

    if (!$result || mysqli_affected_rows($con) === 0) {
        echo json_encode(['success' => false, 'error' => 'Gagal pause PS']);
        exit;
    }

    echo json_encode(['success' => true, 'pause_time' => $pause_time]);
    exit;
}

// Handle Play (resume from pause)
if (isset($_POST['action']) && $_POST['action'] === 'play_ps') {
    // VALIDASI SESSION WAJIB
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        session_destroy();
        session_start();
        $_SESSION['error_token'] = "Session Timeout, Please login again!";
        header("Location: login.php");
        exit;
    }

    $no_ps = $_POST['no_ps'];
    $type_ps = $_POST['type_ps'];

    // VALIDASI KEPEMILIKAN PS DAN STATUS PAUSED
    $res = mysqli_query($con, "SELECT pause_time, end_time, duration, total_pause, start_time, status FROM playstations WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' LIMIT 1");
    $row = mysqli_fetch_assoc($res);

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'PlayStation tidak ditemukan atau bukan milik Anda']);
        exit;
    }
    if ($row['status'] !== 'paused') {
        echo json_encode(['success' => false, 'error' => 'PlayStation tidak dalam status pause']);
        exit;
    }

    $pause_time = $row['pause_time'];
    $now = date('Y-m-d H:i:s');

    if ($row['duration'] === 'open') {
        // Resume open play: start_time dimajukan sejumlah durasi pause
        $old_start = $row['start_time'];
        $pause_start = $row['pause_time'];
        $pause_seconds = strtotime($now) - strtotime($pause_start);
        // Hitung start_time baru: start_time_lama + selisih pause (detik)
        $new_start_timestamp = strtotime($old_start) + $pause_seconds;
        $new_start = date('Y-m-d H:i:s', $new_start_timestamp);
        $q = "UPDATE playstations SET status='occupied', pause_time=NULL, start_time='$new_start' WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND status='paused'";
    } else {
        // Mode biasa
        $end_time = $row['end_time'];
        $diff = strtotime($now) - strtotime($pause_time);
        $new_end_time = date('Y-m-d H:i:s', strtotime($end_time) + $diff);
        $q = "UPDATE playstations SET status='occupied', pause_time=NULL, end_time='$new_end_time' WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND status='paused'";
    }

    $result = mysqli_query($con, $q);

    if (!$result || mysqli_affected_rows($con) === 0) {
        echo json_encode(['success' => false, 'error' => 'Gagal resume PS']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

// Handle Stop
if (isset($_POST['action']) && $_POST['action'] === 'stop_ps') {
    // VALIDASI SESSION WAJIB
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        session_destroy();
        session_start();
        $_SESSION['error_token'] = "Session Timeout, Please login again!";
        header("Location: login.php");
        exit;
    }

    $no_ps = $_POST['no_ps'];
    $type_ps = $_POST['type_ps'];
    $username = $_SESSION['username']; // HANYA dari session

    // VALIDASI KEPEMILIKAN PS
    $checkPS = mysqli_query($con, "SELECT status FROM playstations WHERE no_ps='" . mysqli_real_escape_string($con, $no_ps) . "' AND type_ps='" . mysqli_real_escape_string($con, $type_ps) . "' AND userx='" . mysqli_real_escape_string($con, $username) . "'");
    $psData = mysqli_fetch_assoc($checkPS);

    if (!$psData) {
        echo json_encode(['success' => false, 'error' => 'PlayStation tidak ditemukan atau bukan milik Anda']);
        exit;
    }

    // Ambil transaksi terakhir untuk PS dan username ini yang belum di-stop
    $res = mysqli_query($con, "SELECT * FROM tb_trans WHERE id_ps='$no_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND mode_stop IS NULL ");
    $row = mysqli_fetch_assoc($res);

    if ($row) {
        $now = date('Y-m-d H:i:s');
        $mode_stop = 'AUTO';
        $manual_stop = "NULL";
        if (isset($_POST['manual']) && $_POST['manual'] == '1') {
            $mode_stop = 'MANUAL';
            $manual_stop = "'$now'";
        }

        // Jika open play (end NULL, durasi NULL), update end, durasi, harga, manual_stop
        if (is_null($row['end']) && (is_null($row['durasi']) || $row['durasi'] === '' || $row['durasi'] === 'open')) {
            // Hitung durasi berjalan
            $start_time = $row['start'];
            $end_time = $now;
            $diff_minutes = ceil((strtotime($end_time) - strtotime($start_time)) / 60);
            $harga = isset($_POST['harga']) && is_numeric($_POST['harga']) ? (int) $_POST['harga'] : 0;
            $q = "UPDATE tb_trans SET end='$end_time', durasi='$diff_minutes', harga='$harga', manual_stop=$manual_stop, mode_stop='$mode_stop' WHERE id_trans='{$row['id_trans']}' and userx='" . mysqli_real_escape_string($con, $username) . "'";
            mysqli_query($con, $q);
        } else {
            // Non open play, update manual_stop & mode_stop, update harga jika dikirim
            $harga_update = '';
            if (isset($_POST['harga']) && is_numeric($_POST['harga'])) {
                $harga = (int) $_POST['harga'];
                $harga_update = ", harga='$harga'";
            }
            $q = "UPDATE tb_trans SET manual_stop=$manual_stop, mode_stop='$mode_stop' $harga_update WHERE mode_stop IS NULL AND id_ps = '$no_ps' and userx='" . mysqli_real_escape_string($con, $username) . "'";
            mysqli_query($con, $q);
        }
    }

    // Update playstations ke available dengan validasi kepemilikan
    $q = "UPDATE playstations SET status='available', start_time=NULL, end_time=NULL, duration=NULL, pause_time=NULL WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND status IN ('occupied', 'paused')";
    $result = mysqli_query($con, $q);

    if (!$result || mysqli_affected_rows($con) === 0) {
        echo json_encode(['success' => false, 'error' => 'Gagal stop PS']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}
// Handle Move PS
if (isset($_POST['action']) && $_POST['action'] === 'move_ps') {
    // VALIDASI SESSION WAJIB
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        session_destroy();
        session_start();
        $_SESSION['error_token'] = "Session Timeout, Please login again!";
        header("Location: login.php");
        exit;
    }

    $from_no_ps = $_POST['from_no_ps'];
    $from_type_ps = $_POST['from_type_ps'];
    $to_no_ps = $_POST['to_no_ps'];
    $to_type_ps = $_POST['to_type_ps'];

    // VALIDASI KEPEMILIKAN PS ASAL
    $checkFromPS = mysqli_query($con, "SELECT status, start_time, end_time, duration, pause_time FROM playstations WHERE no_ps='" . mysqli_real_escape_string($con, $from_no_ps) . "' AND type_ps='" . mysqli_real_escape_string($con, $from_type_ps) . "' and userx='" . mysqli_real_escape_string($con, $username) . "'");
    $row = mysqli_fetch_assoc($checkFromPS);

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'PlayStation asal tidak ditemukan atau bukan milik Anda']);
        exit;
    }

    // PERBAIKAN: VALIDASI PS TUJUAN HARUS MILIK USER YANG SAMA DAN AVAILABLE
    $checkToPS = mysqli_query($con, "SELECT status FROM playstations WHERE no_ps='" . mysqli_real_escape_string($con, $to_no_ps) . "' AND type_ps='" . mysqli_real_escape_string($con, $to_type_ps) . "' AND userx='" . mysqli_real_escape_string($con, $username) . "'");
    $toData = mysqli_fetch_assoc($checkToPS);

    if (!$toData) {
        echo json_encode(['success' => false, 'error' => 'PlayStation tujuan tidak ditemukan atau bukan milik Anda']);
        exit;
    }
    if ($toData['status'] !== 'available') {
        echo json_encode(['success' => false, 'error' => 'PlayStation tujuan tidak tersedia']);
        exit;
    }

    // Update PS tujuan dengan validasi kepemilikan DAN status available
    $q1 = "UPDATE playstations SET status='{$row['status']}', start_time='{$row['start_time']}', end_time=" . ($row['end_time'] ? "'{$row['end_time']}'" : 'NULL') . ", duration=" . ($row['duration'] ? "'{$row['duration']}'" : 'NULL') . ", pause_time=" . ($row['pause_time'] ? "'{$row['pause_time']}'" : 'NULL') . " WHERE no_ps='$to_no_ps' AND type_ps='$to_type_ps' AND userx='" . mysqli_real_escape_string($con, $username) . "' AND status='available'";
    $result1 = mysqli_query($con, $q1);

    if (!$result1 || mysqli_affected_rows($con) === 0) {
        echo json_encode(['success' => false, 'error' => 'Gagal move ke PS tujuan - PS mungkin sudah digunakan']);
        exit;
    }

    // PATCH: Update id_ps di tb_trans juga saat move (khusus transaksi aktif)
    $qTrans = "UPDATE tb_trans SET id_ps='$to_no_ps' WHERE id_ps='$from_no_ps' AND mode_stop IS NULL AND userx ='" . mysqli_real_escape_string($con, $username) . "'";
    mysqli_query($con, $qTrans);

    // PATCH: Update id_ps di tb_trans_fnb juga saat move (khusus transaksi aktif/inv IS NULL)
    $qTransFnb = "UPDATE tb_trans_fnb SET id_ps='$to_no_ps' WHERE id_ps='$from_no_ps' AND inv IS NULL AND userx ='" . mysqli_real_escape_string($con, $username) . "'";
    mysqli_query($con, $qTransFnb);

    // Reset PS asal dengan validasi kepemilikan dan status
    $q2 = "UPDATE playstations SET status='available', start_time=NULL, end_time=NULL, duration=NULL, pause_time=NULL WHERE no_ps='$from_no_ps' AND type_ps='$from_type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "' AND status IN ('occupied', 'paused')";
    $result2 = mysqli_query($con, $q2);

    if (!$result2 || mysqli_affected_rows($con) === 0) {
        // Rollback: kembalikan PS tujuan ke available jika reset PS asal gagal
        $rollback = "UPDATE playstations SET status='available', start_time=NULL, end_time=NULL, duration=NULL, pause_time=NULL WHERE no_ps='$to_no_ps' AND type_ps='$to_type_ps' AND userx='" . mysqli_real_escape_string($con, $username) . "'";
        mysqli_query($con, $rollback);

        echo json_encode(['success' => false, 'error' => 'Gagal reset PS asal']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}
// Ambil semua PS available per type_ps untuk dropdown move
$availablePS = [];
$availQuery = "SELECT no_ps, type_ps FROM playstations WHERE status='available' and userx='" . mysqli_real_escape_string($con, $username) . "' ORDER BY type_ps, no_ps";
$availRes = $con->query($availQuery);
while ($r = $availRes->fetch_assoc()) {
    $no_ps = $r['no_ps'];
    $type_ps = $r['type_ps'];

    // Cek tb_trans dan tb_trans_fnb apakah masih ada inv NULL untuk PS ini
    $hasTrans = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM tb_trans WHERE id_ps='$no_ps' AND inv IS NULL  AND userx ='$username'"))['cnt'] ?? 0;
    $hasFnb = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM tb_trans_fnb WHERE id_ps='$no_ps' AND inv IS NULL  AND userx ='$username'"))['cnt'] ?? 0;

    if ($hasTrans == 0 && $hasFnb == 0) {
        $availablePS[$type_ps][] = $no_ps;
    }
}
?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Rentals Management </h4>

                    <!-- <span class="text-muted">Report date : <?= $dateNOW ?></span> -->


                    <div class="page-title-right d-flex align-items-center" style="gap: 10px;">
                        <?php
                        // Hitung jumlah PS available dan busy
                        $countAvailable = 0;
                        $countBusy = 0;
                        $countQuery = "SELECT status, COUNT(*) as total FROM playstations WHERE userx ='$username' GROUP BY status";
                        $countRes = $con->query($countQuery);
                        while ($row = $countRes->fetch_assoc()) {
                            if (strtolower($row['status']) === 'available') {
                                $countAvailable = $row['total'];
                            } elseif (strtolower($row['status']) === 'occupied' || strtolower($row['status']) === 'paused') {
                                $countBusy += $row['total'];
                            }
                        }
                        ?>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success p-1">
                                <i class="bx bx-check-square"></i> Available: <?= $countAvailable ?>
                            </span>
                            <span class="badge bg-danger p-1">
                                <i class="bx bx-no-entry"></i> Occupied: <?= $countBusy ?>
                            </span>
                        </div>



                    </div>

                </div>
            </div>
        </div>

        <!-- end page title -->

        <div class="row">
            <?php

            // Ambil data dari tabel playstations, urut berdasarkan no_ps
            if ($username) {
                $sql = "SELECT * FROM playstations WHERE userx = '" . mysqli_real_escape_string($con, $username) . "' ORDER BY no_ps ASC";
            } else {
                $sql = "SELECT * FROM playstations ORDER BY no_ps ASC";
            }
            $result = $con->query($sql);

            if (!$result) {
                die("Query gagal: " . $con->error);
            }

            // Loop data playstation
            while ($row = $result->fetch_assoc()):
                $status = ucfirst($row['status']); // Contoh: Available, Busy, etc.
                if ($status === 'Available') {
                    $badgeColor = 'success';
                } elseif ($status === 'Paused') {
                    $badgeColor = 'warning';
                } else {
                    $badgeColor = 'danger';
                }
                $device = $row['type_ps'];
                $psnumberx = $row['no_ps'];
                $tytyp = $row['type_ps'];
                $startTime = !empty($row['start_time']) ? $row['start_time'] : '-';
                $endTime = !empty($row['end_time']) && $row['end_time'] !== '0000-00-00 00:00:00' ? $row['end_time'] : '-';
                $duration = !empty($row['duration']) ? $row['duration'] : '-';
                // Query ke tb_pricelist untuk setiap PS (PASTIKAN INI DI ATAS BADGE HARGA)
                $querys = "SELECT duration, price FROM tb_pricelist WHERE type_ps = '$tytyp' and userx = '$username' AND price != 0 ORDER BY duration+0 ASC";
                $results = mysqli_query($con, $querys);
                $priceList = mysqli_fetch_all($results, MYSQLI_ASSOC);
                ?>

                <div class="col-xl-3 col-sm-6 mb-0">
                    <div class="card shadow-sm border 
        <?php
        if ($status === 'Available') {
            echo 'border-success';
        } elseif ($status === 'Paused') {
            echo 'border-warning';
        } else {
            echo 'border-danger';
        }
        ?> rounded-7">

                        <div class="card-body p-3">
                            <!-- Header -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <!-- Kiri: No PS + ikon -->
                                <div class="d-flex align-items-center">
                                    <h5 class="font-size-20 mb-0 fw-bold text-primary me-2">
                                        #<?= htmlspecialchars($row['no_ps']) ?>
                                        <?php
                                        $statusTime = strtotime($row['status_device']);
                                        $now = time();
                                        $diffMinutes = round(($now - $statusTime) / 60);

                                        if (!function_exists('formatTimeAgo')) {
                                            function formatTimeAgo($minutes)
                                            {
                                                if ($minutes < 60) {
                                                    return $minutes . ' minutes ago';
                                                } else {
                                                    $hours = floor($minutes / 60);
                                                    $remainingMinutes = $minutes % 60;
                                                    return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' '
                                                        . ($remainingMinutes > 0 ? $remainingMinutes . ' minutes ' : '') . 'ago';
                                                }
                                            }
                                        }

                                        $tooltipText = htmlspecialchars($row['id_usb']) . ' ';
                                        $tooltipText .= ($diffMinutes > 5)
                                            ? 'Disconnected (' . formatTimeAgo($diffMinutes) . ')'
                                            : 'Connected (' . formatTimeAgo($diffMinutes) . ')';

                                        if ($diffMinutes > 5) {
                                            echo '<span title="' . $tooltipText . '" class="badge bg-danger d-inline-flex align-items-center justify-content-center" style="width: 18px; height: 18px; font-size: 12px;">
            <i class="bx bx-no-entry"></i>
          </span>';
                                        } else {
                                            echo '<span title="' . $tooltipText . '" class="badge bg-success d-inline-flex align-items-center justify-content-center" style="width: 18px; height: 18px; font-size: 12px;">
            <i class="bx bx-check-square"></i>
          </span>';
                                        }
                                        ?>


                                    </h5>
                                    <!-- <ul class="list-inline mb-0 d-flex gap-1">
            <li class="list-inline-item p-0 m-0">
                <a href="javascript:void(0);" class="text-dark"><i class="mdi mdi-arrow-up-bold-circle"></i></a>
            </li>
            <li class="list-inline-item p-0 m-0">
                <a href="javascript:void(0);" class="text-dark"><i class="mdi mdi-arrow-down-bold-circle"></i></a>
            </li>
            <li class="list-inline-item p-0 m-0">
                <a href="javascript:void(0);" class="text-dark"><i class="mdi mdi-remote-desktop"></i></a>
            </li>
            <li class="list-inline-item p-0 m-0">
                <a href="javascript:void(0);" class="text-danger"><i class="mdi mdi-power"></i></a>
            </li>
        </ul> -->
                                </div>

                                <div class="d-flex align-items-center">
                                    <!-- Status badge -->
                                    <span class="badge bg-<?= $badgeColor ?> px-2 py-1">
                                        <i class="bx bx-info-circle me-0"></i><?= htmlspecialchars($status) ?>
                                    </span>

                                    <!-- Dropdown tombol titik tiga -->
                                    <div class="dropdown">
                                        <a class="btn btn-link text-muted p-0 ms-0" role="button" data-bs-toggle="dropdown"
                                            aria-haspopup="true">
                                            <i class="mdi mdi-dots-vertical font-size-14"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <!-- Trigger Modal -->
                                            <a href="#" class="dropdown-item btn-history" data-ps="<?= $psnumberx ?>"
                                                data-id="<?= $psnumberx ?>" data-bs-toggle="modal"
                                                data-bs-target="#historyModal<?= $psnumberx ?>">
                                                History Rent
                                            </a>

                                        </div>
                                    </div>

                                </div>

                            </div>


                            <!-- Device Name dan Countdown -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-dark mb-1 fw-semibold"><?= htmlspecialchars($device) ?></h6>
                                <h6 class="text-muted mb-1">
                                    <?php if (strtolower($row['status']) === 'occupied' && $duration === 'open' && $startTime !== '-'): ?>
                                        <span id="countdown<?= $row['no_ps'] ?>">00:00:00</span>
                                        <script>
                                            (function () {
                                                var start = new Date("<?= $startTime ?>").getTime();
                                                var priceList = window.priceList_<?= $psnumberx ?> || [];
                                                function getPriceByMinute(minute) {
                                                    var price = 0;
                                                    if (!priceList || priceList.length === 0) return price;
                                                    for (var i = 0; i < priceList.length; i++) {
                                                        if (parseInt(priceList[i].duration) >= minute) {
                                                            price = priceList[i].price;
                                                            return price;
                                                        }
                                                    }
                                                    // Jika menit berjalan lebih dari semua duration, ambil harga duration terbesar
                                                    price = priceList[priceList.length - 1].price;
                                                    return price;
                                                }
                                                var countupInterval<?= $row['no_ps'] ?> = setInterval(function () {
                                                    var now = new Date().getTime();
                                                    var diff = now - start;
                                                    if (diff < 0) diff = 0;
                                                    var h = String(Math.floor(diff / (1000 * 60 * 60))).padStart(2, '0');
                                                    var m = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                                                    var s = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
                                                    document.getElementById("countdown<?= $row['no_ps'] ?>").innerText = `${h}:${m}:${s}`;
                                                    // Update badge harga setiap menit
                                                    var totalMinute = Math.floor(diff / 60000) + 1;
                                                    var price = getPriceByMinute(totalMinute);
                                                    var badge = document.getElementById("badge-price-<?= $psnumberx ?>");
                                                    if (badge && price) {
                                                        badge.innerText = 'Rp. ' + price.toLocaleString('id-ID');
                                                    }
                                                }, 1000);
                                            })();
                                        </script>
                                    <?php elseif ($endTime !== '-' && strtolower($row['status']) === 'occupied' && $duration !== 'open'): ?>
                                        <span id="countdown<?= $row['no_ps'] ?>">Loading...</span>
                                        <script>
                                            const end<?= $row['no_ps'] ?> = new Date("<?= $endTime ?>").getTime();
                                            const interval<?= $row['no_ps'] ?> = setInterval(() => {
                                                const now = new Date().getTime();
                                                let distance = end<?= $row['no_ps'] ?> - now;
                                                if (distance < 0) distance = 0;
                                                const h = String(Math.floor(distance / (1000 * 60 * 60))).padStart(2, '0');
                                                const m = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                                                const s = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
                                                document.getElementById("countdown<?= $row['no_ps'] ?>").innerText = `${h}:${m}:${s}`;
                                                if (distance === 0) {
                                                    clearInterval(interval<?= $row['no_ps'] ?>);
                                                    // AJAX untuk membebaskan PS
                                                    $.ajax({
                                                        url: '',
                                                        type: 'POST',
                                                        data: {
                                                            action: 'free_ps',
                                                            no_ps: '<?= $row['no_ps'] ?>',
                                                            type_ps: '<?= $row['type_ps'] ?>'
                                                        },
                                                        success: function (res) {
                                                            location.reload();
                                                        }
                                                    });
                                                }
                                            }, 1000);
                                        </script>
                                    <?php elseif (strtolower($row['status']) === 'paused'): ?>
                                        <span class="badge bg-warning text-dark px-2 py-1">Paused</span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </h6>
                            </div>

                            <!-- Session Info -->
                            <div class="bg-light rounded p-1 mb-2">
                                <table class="w-100 text-muted" style="font-size: 12px;">
                                    <tr>
                                        <td style="width:28%;">Start</td>
                                        <td>: <span class="text-dark">
                                                <?php if ($startTime !== '-' && $startTime !== null && $startTime !== ''): ?>
                                                    <?= date('d-m-Y H:i:s', strtotime($startTime)) ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </span></td>
                                    </tr>
                                    <tr>
                                        <td>End</td>
                                        <td>: <span class="text-dark">
                                                <?php if ($endTime !== '-' && $endTime !== null && $endTime !== ''): ?>
                                                    <?= date('d-m-Y H:i:s', strtotime($endTime)) ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </span></td>
                                    </tr>
                                    <tr>
                                        <td>Duration</td>
                                        <td>: <span class="text-dark">
                                                <?php if ($duration === 'open'): ?>
                                                    Open Play
                                                    <?php if (!empty($priceList)):
                                                        $minPrice = min(array_column($priceList, 'price'));
                                                        ?>
                                                        <span class="badge bg-info text-light ms-1 price-badge"
                                                            id="priceBadge<?= $row['no_ps'] ?>">
                                                            Rp. <span
                                                                class="price-value "><?= number_format($minPrice, 0, ',', '.') ?></span>
                                                        </span>
                                                        <script>
                                                            window.priceList<?= $row['no_ps'] ?> = <?= json_encode($priceList) ?>;
                                                        </script>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($duration) ?>
                                                    <?= (is_numeric($duration) && $duration > 0) ? ' Minutes' : '' ?>
                                                <?php endif; ?>
                                            </span></td>
                                    </tr>
                                </table>
                            </div>
                            <?php
                            // Hitung jumlah transaksi rental (tb_trans)
                            $qInvRental = "
        SELECT COUNT(*) as cnt FROM tb_trans 
        WHERE id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "' 
        AND inv IS NULL AND userx = '" . mysqli_real_escape_string($con, $username) . "'";
                            $invCountRental = (int) (mysqli_fetch_assoc(mysqli_query($con, $qInvRental))['cnt'] ?? 0);

                            // Hitung jumlah transaksi FNB (qty) (tb_trans_fnb)
                            $qInvFnb = "
        SELECT SUM(qty) as cnt FROM tb_trans_fnb 
        WHERE id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "' 
        AND inv IS NULL AND userx = '" . mysqli_real_escape_string($con, $username) . "'";
                            $invCountFnb = (int) (mysqli_fetch_assoc(mysqli_query($con, $qInvFnb))['cnt'] ?? 0);

                            // Hitung total transaksi
                            $invCount = $invCountRental + $invCountFnb;

                            // Tombol order hanya disable jika PS 'available' dan belum ada transaksi
                            $disabled = (strtolower($status) === 'available' && $invCount === 0) ? 'disabled' : '';
                            ?>
                            <?php
                            // Query ke tb_pricelist
                            $querys = "SELECT duration, price FROM tb_pricelist WHERE type_ps = '$tytyp' and userx = '$username' ORDER BY duration+0 ASC";
                            $results = mysqli_query($con, $querys);

                            // Ambil semua data sekaligus ke array
                            $priceList = mysqli_fetch_all($results, MYSQLI_ASSOC);
                            // Tentukan nama field dan label berdasarkan status
                            $fieldName = ($status === 'Available') ? 'duration' : 'add_duration';
                            $labelText = ($status === 'Available' && $invCount == 0) ? '-- Select Duration --' : '-- Select Extra Duration --';

                            // Cek apakah ada data pricelist
                            $hasPriceList = !empty($priceList);
                            // var_dump($hasPriceList);
                            ?>
                            <!-- Duration Selection -->
                            <div class="mb-0 d-flex align-items-center justify-content-between gap-2">

                                <?php if ($status === 'Available'): ?>
                                    <select class="form-select form-select-sm w-85" name="duration">
                                        <option disabled selected><?= $labelText ?></option>

                                        <?php foreach ($priceList as $item): ?>
                                            <?php
                                            $dur = htmlspecialchars($item['duration']);
                                            $price = number_format($item['price'], 0, ',', '.');
                                            if ($dur == 'open') {
                                                $label = "Open Play";
                                            } elseif ((int) $dur <= 30) {
                                                $label = $dur . " Minutes";
                                            } else {
                                                $hours = $dur / 60;
                                                $label = rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.') .
                                                    ($hours == 1 ? " Hour" : " Hours");
                                            }
                                            ?>
                                            <option value="<?= $dur ?>"><?= $label ?> - Rp <?= $price ?></option>
                                        <?php endforeach; ?>
                                        <?php if ((!empty($hasPriceList)) && ($invCount == 0)): ?>
                                            <option value="open">Open Play</option>
                                        <?php endif; ?>

                                    </select>
                                <?php else: ?>
                                    <select class="form-select form-select-sm w-85" name="add_duration" <?php if ($duration === 'open')
                                        echo 'disabled'; ?>>
                                        <option disabled selected><?= $labelText ?></option>
                                        <?php foreach ($priceList as $item): ?>
                                            <?php
                                            $dur = htmlspecialchars($item['duration']);
                                            $price = number_format($item['price'], 0, ',', '.');
                                            if ($dur == 'open') {
                                                $label = "Open Play";
                                            } elseif ((int) $dur <= 30) {
                                                $label = $dur . " Minutes";
                                            } else {
                                                $hours = $dur / 60;
                                                $label = rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.') .
                                                    ($hours == 1 ? " Hour" : " Hours");
                                            }
                                            ?>
                                            <option value="<?= $dur ?>"><?= $label ?> - Rp <?= $price ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>

                                <button type="button" class="btn btn-outline-success btn-sm position-relative"
                                    data-bs-toggle="modal" data-bs-target="#modal<?= $psnumberx ?>" style="width: 15%;"
                                    <?= $disabled ?>>
                                    <i class="bx bx-cart"></i>
                                    <?php if ($invCount > 0): ?>
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                            style="font-size: 9px;">
                                            <?= $invCount ?>
                                        </span>
                                    <?php endif; ?>
                                </button>

                            </div>


                        </div>

                        <!-- Footer Buttons -->
                        <div class="card-footer bg-transparent border-top-0 pt-0 pb-2 px-3" style="margin-top:-10px">
                            <div class="row g-2">
                                <?php if ($status === 'Available'): ?>
                                    <div class="col-7">
                                        <button type="button" class="btn btn-primary w-100 btn-sm fw-semibold btn-start-rental"
                                            data-no_ps="<?= $psnumberx ?>" data-type_ps="<?= $tytyp ?>" <?= ($invCount > 0) ? 'disabled' : '' ?>>
                                            <i class="bx bx-play-circle me-1"></i>Start Rental
                                        </button>
                                    </div>

                                    <?php if ($invCount > 0): ?>
                                        <div class="col-5">
                                            <button type="button"
                                                class="btn btn-success w-100 btn-sm fw-semibold btn-extra-time-available"
                                                data-no_ps="<?= $psnumberx ?>" data-type_ps="<?= $tytyp ?>" <?php if ($duration === 'open')
                                                        echo 'disabled'; ?> data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Extra Time">
                                                <i class="bx bx-time"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="col-5">
                                            <input class="form-check-input" type="checkbox" id="tasklistCheck01<?= $psnumberx ?>">
                                            <label class="form-check-label " for="tasklistCheck01<?= $psnumberx ?>"
                                                style="font-size:10px"> with prepare</label>
                                        </div>
                                    <?php endif; ?>


                                <?php elseif ($status === 'Paused'): ?>
                                    <div class="col-5">
                                        <button type="button" class="btn btn-info w-100 btn-sm fw-semibold btn-play-ps"
                                            data-no_ps="<?= $psnumberx ?>" data-type_ps="<?= $tytyp ?>">
                                            <i class="bx bx-play-circle"></i>
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button type="button" class="btn btn-danger w-100 btn-sm fw-semibold btn-stop-ps"
                                            data-no_ps="<?= $psnumberx ?>" data-type_ps="<?= $tytyp ?>">
                                            <i class="bx bx-stop"></i>
                                        </button>
                                    </div>
                                    <div class="col-3 position-relative">
                                        <button type="button" class="btn btn-secondary w-100 btn-sm fw-semibold btn-move-ps"
                                            data-no_ps="<?= $psnumberx ?>" data-type_ps="<?= $tytyp ?>">
                                            <i class="bx bx-transfer"></i>
                                        </button>
                                        <div class="move-dropdown d-none position-absolute w-100 mt-1" style="z-index:10;">
                                            <select class="form-select form-select-sm move-select"
                                                data-from_no_ps="<?= $psnumberx ?>" data-from_type_ps="<?= $tytyp ?>">
                                                <option value="" disabled selected>Pilih PS tujuan...</option>
                                                <?php if (!empty($availablePS[$tytyp])):
                                                    foreach ($availablePS[$tytyp] as $availNo):
                                                        if ($availNo != $psnumberx): ?>
                                                            <option value="<?= $availNo ?>">PS #<?= $availNo ?></option>
                                                        <?php endif; endforeach; endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php elseif ($status === 'Occupied'): ?>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-success w-100 btn-sm fw-semibold btn-extra-time"
                                            data-no_ps="<?= $psnumberx ?>" data-type_ps="<?= $tytyp ?>" <?php if ($duration === 'open')
                                                    echo 'disabled'; ?> data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Extra Time">
                                            <i class="bx bx-time"></i>
                                        </button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-warning w-100 btn-sm fw-semibold btn-pause-ps"
                                            data-no_ps="<?= $psnumberx ?>" data-type_ps="<?= $tytyp ?>" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Pause">
                                            <i class="bx bx-pause"></i>
                                        </button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-danger w-100 btn-sm fw-semibold btn-stop-ps"
                                            data-no_ps="<?= $psnumberx ?>" data-type_ps="<?= $tytyp ?>" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Stop">
                                            <i class="bx bx-stop"></i>
                                        </button>
                                    </div>
                                    <div class="col-3 position-relative">
                                        <button type="button" class="btn btn-secondary w-100 btn-sm fw-semibold btn-move-ps"
                                            data-no_ps="<?= $psnumberx ?>" data-type_ps="<?= $tytyp ?>" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Move">
                                            <i class="bx bx-transfer"></i>
                                        </button>
                                        <div class="move-dropdown d-none position-absolute w-100 mt-1" style="z-index:10;">
                                            <select class="form-select form-select-sm move-select"
                                                data-from_no_ps="<?= $psnumberx ?>" data-from_type_ps="<?= $tytyp ?>">
                                                <option value="" disabled selected>Select PS...</option>
                                                <?php if (!empty($availablePS[$tytyp])):
                                                    foreach ($availablePS[$tytyp] as $availNo):
                                                        if ($availNo != $psnumberx): ?>
                                                            <option value="<?= $availNo ?>">#<?= $availNo ?> (<?= $tytyp ?>)</option>
                                                        <?php endif; endforeach; endif; ?>
                                            </select>
                                        </div>
                                    </div>


                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
                        session_destroy();
                        session_start();
                        $_SESSION['error_token'] = "Session Timeout, Please login again!";
                        header("Location: login.php");
                        exit;
                    }

                    // Ambil transaksi tb_trans untuk PS ini yang inv IS NULL dan userx sama
                    $qTransList = "SELECT * FROM tb_trans WHERE id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "' AND inv IS NULL AND userx = '" . mysqli_real_escape_string($con, $username) . "' ORDER BY start DESC";
                    $rTransList = mysqli_query($con, $qTransList);

                    // Ambil id_trans utama (pertama) untuk sesi ini
                    $mainTrans = mysqli_fetch_assoc(mysqli_query($con, "SELECT id_trans FROM tb_trans WHERE id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "' AND inv IS NULL AND userx = '" . mysqli_real_escape_string($con, $username) . "' ORDER BY start ASC LIMIT 1"));
                    $main_id_trans = $mainTrans ? $mainTrans['id_trans'] : null;

                    // Ambil produk FNB
                    $fnbList = [];
                    $qFnb = "SELECT * FROM tb_fnb where userx = '" . mysqli_real_escape_string($con, $username) . "' ORDER BY nama ASC";
                    $rFnb = mysqli_query($con, $qFnb);
                    while ($f = mysqli_fetch_assoc($rFnb)) {
                        $fnbList[] = $f;
                    }

                    // Gabungan data rental dan FNB
                    $allRows = [];
                    $totalRental = 0;
                    $totalFnb = 0;

                    // Rental rows
                    mysqli_data_seek($rTransList, 0);
                    while ($tr = mysqli_fetch_assoc($rTransList)) {
                        $harga = $tr['harga'] ? (int) $tr['harga'] : 0;
                        $totalRental += $harga;
                        $mode_stop = $tr['mode_stop'];
                        $durasi = $tr['durasi'] ? $tr['durasi'] : '-';
                        $namaRental = "Rental " . $durasi . " Min (" . $tytyp . ")";
                        $allRows[] = [
                            'id' => $tr['id'],
                            'nama' => $namaRental,
                            'qty' => 1,
                            'harga' => $harga,
                            'total' => $harga,
                            'tipe' => 'rental',
                            'modestop' => $mode_stop,
                        ];
                    }

                    // PERBAIKAN: FNB rows - ambil data FNB hanya sekali saja untuk setiap jenis yang sama
// Gunakan array untuk tracking FNB yang sudah ditampilkan
                    $displayedFnb = [];

                    // FNB rows dari tb_trans_fnb yang terkait dengan main_id_trans saja
                    if ($main_id_trans) {
                        $qFnbTrans = "SELECT tf.*, f.nama, f.harga FROM tb_trans_fnb tf 
                  LEFT JOIN tb_fnb f ON tf.id_fnb = f.id 
                  LEFT JOIN tb_trans t ON tf.id_trans = t.id_trans
                  WHERE tf.id_trans = '" . mysqli_real_escape_string($con, $main_id_trans) . "'
                  AND t.userx = '" . mysqli_real_escape_string($con, $username) . "'";
                        $rFnbTrans = mysqli_query($con, $qFnbTrans);
                        while ($ft = mysqli_fetch_assoc($rFnbTrans)) {
                            $fnbKey = $ft['id_fnb']; // atau bisa pakai nama jika ingin berdasarkan nama produk
                
                            // Cek apakah FNB ini sudah pernah ditampilkan
                            if (!isset($displayedFnb[$fnbKey])) {
                                $total = $ft['qty'] * $ft['harga'];
                                $totalFnb += $total;
                                $allRows[] = [
                                    'id' => $ft['id'],
                                    'nama' => $ft['nama'],
                                    'qty' => $ft['qty'],
                                    'harga' => $ft['harga'],
                                    'total' => $total,
                                    'tipe' => 'fnb'
                                ];

                                // Tandai FNB ini sudah ditampilkan
                                $displayedFnb[$fnbKey] = true;
                            }
                        }
                    }

                    // FNB rows dari tb_trans_fnb yang inv IS NULL tetapi id_trans tidak ada di tb_trans (orphans)
                    $qFnbOrphan = "
    SELECT tf.*, f.nama, f.harga 
    FROM tb_trans_fnb tf
    LEFT JOIN tb_fnb f ON tf.id_fnb = f.id
    LEFT JOIN tb_trans t ON tf.id_trans = t.id_trans
    WHERE tf.inv IS NULL 
      AND t.id_trans IS NULL
      AND tf.id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "'
      AND f.userx = '" . mysqli_real_escape_string($con, $username) . "'
";
                    $rFnbOrphan = mysqli_query($con, $qFnbOrphan);
                    while ($ft = mysqli_fetch_assoc($rFnbOrphan)) {
                        $fnbKey = $ft['id_fnb']; // atau bisa pakai nama jika ingin berdasarkan nama produk
                
                        // Cek apakah FNB ini sudah pernah ditampilkan
                        if (!isset($displayedFnb[$fnbKey])) {
                            $total = $ft['qty'] * $ft['harga'];
                            $totalFnb += $total;
                            $allRows[] = [
                                'id' => $ft['id'],
                                'nama' => $ft['nama'],
                                'qty' => $ft['qty'],
                                'harga' => $ft['harga'],
                                'total' => $total,
                                'tipe' => 'fnb'
                            ];

                            // Tandai FNB ini sudah ditampilkan
                            $displayedFnb[$fnbKey] = true;
                        }
                    }
                    ?>
                    <?php
                    // Check autoprint status
                    $autoDelEnabled = false;
                    $stmt = $con->prepare("SELECT status FROM tb_feature WHERE userx = ? AND feature = 'autodel'");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        // Bind hasil kolom 'status' ke variabel (meskipun kita tidak benar-benar pakai nilainya di sini)
                        $stmt->bind_result($status);
                        $stmt->fetch();

                        // Bisa juga cek nilai status, misalnya:
                        if ($status == 1) {
                            $autoDelEnabled = true;
                        }
                    }

                    $stmt->close();
                    ?>

                    <div class="modal fade" id="modal<?= $psnumberx ?>" tabindex="-1" role="dialog"
                        aria-labelledby="modalLabel<?= $psnumberx ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel<?= $psnumberx ?>">Order Details for PS
                                        #<?= $psnumberx ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                        onclick="location.reload();"></button>
                                </div>

                                <div class="modal-body">
                                    <!-- Form tambah FNB tetap -->
                                    <form id="formAddFnb<?= $psnumberx ?>" class="row g-2 align-items-end mb-2">
                                        <input type="hidden" name="id_trans"
                                            value="<?= htmlspecialchars($main_id_trans) ?>">
                                        <div class="col-8">

                                            <select class="form-select form-select-sm" name="id_fnb" required>
                                                <option value="" disabled selected>Select Product...</option>
                                                <?php foreach ($fnbList as $fnb): ?>
                                                    <option value="<?= $fnb['id'] ?>"><?= htmlspecialchars($fnb['nama']) ?> - Rp
                                                        <?= number_format($fnb['harga'], 0, ',', '.') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-2">
                                            <input type="number" min="1" class="form-control form-control-sm" name="qty"
                                                value="1" placeholder="Qty" required>
                                        </div>
                                        <div class="col-2">
                                            <button type="button" class="btn btn-success btn-sm w-100 btn-add-fnb"
                                                data-ps="<?= $psnumberx ?>">+</button>
                                        </div>
                                    </form>
                                    <div class="mb-0 fw-bold">Transaction</div>
                                    <!-- Change modal-dialog to modal-md for a narrower modal -->
                                    <div class="table-responsive mb-0" id="trxTable<?= $psnumberx ?>">
                                        <table id="fnb-table<?= $psnumberx ?>"
                                            class="table table-sm table-bordered align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Item Name</th>
                                                    <th>Qty</th>
                                                    <th>Unit Price</th>
                                                    <th>Subtotal</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="fnb-body<?= $psnumberx ?>">
                                                <?php if (count($allRows) > 0):
                                                    $no = 1;
                                                    foreach ($allRows as $row): ?>
                                                        <tr id="row-fnb-<?= $row['id'] ?>">

                                                            <td><?= $no++ ?></td>
                                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                                            <td><?= $row['qty'] ?></td>
                                                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                                            <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                                            <td>
                                                                <?php if (isset($row['tipe']) && $row['tipe'] === 'fnb'): ?>
                                                                    <button type="button" class="btn btn-danger btn-sm w-50 btn-del-fnb"
                                                                        data-ps="<?= $psnumberx ?>" data-id="<?= $row['id'] ?>"
                                                                        id="btnDelFnb<?= $row['id'] ?>">
                                                                        -
                                                                    </button>
                                                                <?php endif; ?>

                                                                <?php if ((isset($row['tipe']) && $row['tipe'] === 'rental' && $user_level == 'admin') || (isset($row['tipe']) && $row['tipe'] === 'rental' && $autoDelEnabled)): ?>

                                                                    <button type="button"
                                                                        class="btn btn-danger btn-sm w-50 btn-del-Rent"
                                                                        data-ps="<?= $psnumberx ?>" data-id="<?= $row['id'] ?>"
                                                                        id="btnDelRental<?= $row['id'] ?>">
                                                                        -
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>


                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">No transactions yet.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>

                                                <tr>
                                                    <th colspan="4" class="text-end">Total</th>
                                                    <th colspan="1" id="grand-total<?= $psnumberx ?>">Rp
                                                        <?= number_format($totalRental + $totalFnb, 0, ',', '.') ?>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <?php
                                    $grandTotal = ($totalRental ?? 0) + ($totalFnb ?? 0);
                                    ?>
                                    <hr>


                                    <div class="row mb-1" style="margin-top:-20px">
                                        <div class="col-6 text-end">Promo:</div>
                                        <div class="col-6">
                                            <select class="form-select form-select-sm promo-select"
                                                id="promo<?= $psnumberx ?>" name="promo" data-ps="<?= $psnumberx ?>">
                                                <option value="0">-- Select Promo --</option>
                                                <?php
                                                $qPromo = "SELECT * FROM tb_promo 
                                                    WHERE (type_rental = '$tytyp' OR type_rental = 'Rental') 
                                                    AND userx = '$username' 
                                                    AND status = 1";


                                                $rPromo = mysqli_query($con, $qPromo);
                                                while ($p = mysqli_fetch_assoc($rPromo)):
                                                    $type = $p['disc_type']; // nominal, perc, hours
                                                    $qty = $p['qty_potongan'];

                                                    // Label tampilannya
                                                    if ($type === 'nominal') {
                                                        $label = 'Rp ' . number_format($qty, 0, ',', '.');
                                                        $value = $qty;
                                                    } elseif ($type === 'perc') {
                                                        $label = $qty . '%';
                                                        $value = ($qty / 100) * $grandTotal; // akan dihitung di JS
                                                    } elseif ($type === 'hours') {
                                                        $durationminutes = intval($qty) * 60;
                                                        $q = mysqli_query($con, "SELECT price FROM tb_pricelist WHERE duration = '$durationminutes' AND type_ps = '$tytyp' AND userx='$username' LIMIT 1");
                                                        $data = mysqli_fetch_assoc($q);

                                                        $qty = $data['price'] ?? 0;
                                                        $label = 'Rp ' . number_format($qty, 0, ',', '.');
                                                        $value = $qty; // juga akan dihitung di JS
                                                    }
                                                    ?>
                                                    <option value="<?= $value ?>"
                                                        data-nama="<?= htmlspecialchars($p['nama_promo']) ?>"
                                                        data-type="<?= $type ?>" data-qty="<?= $qty ?>">
                                                        <?= htmlspecialchars($p['nama_promo']) ?> (<?= $label ?>)
                                                    </option>
                                                <?php endwhile; ?>

                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-1">
                                        <div class="col-6 text-end">Grand Total:</div>
                                        <div class="col-6 fw-bold" id="displayTotal<?= $psnumberx ?>">Rp
                                            <?= number_format($grandTotal, 0, ',', '.') ?>
                                        </div>
                                    </div>

                                    <!-- Tambahkan dropdown metode pembayaran -->
                                    <div class="row mb-1">
                                        <div class="col-6 text-end">Payment Method:</div>
                                        <div class="col-6">
                                            <select class="form-select form-select-sm" id="paymentMethod<?= $psnumberx ?>">
                                                <option value="cash">Cash</option>
                                                <option value="qris">QRIS</option>
                                                <option value="debit">Bank Transfer</option>
                                                <option value="ewalet">E-walet</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Kolom bayar & kembalian (hanya untuk tunai) -->
                                    <div class="row mb-1" id="tunaiFields<?= $psnumberx ?>">
                                        <div class="col-6 text-end">Pay:</div>
                                        <div class="col-6">
                                            <input type="number" min="0" class="form-control form-control-sm bayar-input"
                                                id="bayar<?= $psnumberx ?>" placeholder="Rp" data-ps="<?= $psnumberx ?>">
                                        </div>
                                        <div class="col-6 text-end mt-2">Change:</div>
                                        <div class="col-6 mt-2">
                                            <span class="form-control form-control-sm bg-light"
                                                id="kembali<?= $psnumberx ?>">Rp 0</span>
                                        </div>
                                    </div>



                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-info btnPayNow" data-ps="<?= $psnumberx ?>"
                                            data-id_trans="<?= $main_id_trans ?>" data-userx="<?= $username ?>" disabled>
                                            Pay Now
                                        </button>



                                    </div>

                                </div>
                            </div>

                        </div>
                        <!-- Modal -->

                        <!-- Modal Move PS -->
                        <div class="modal fade" id="moveModal<?= $psnumberx ?>" tabindex="-1"
                            aria-labelledby="moveModalLabel<?= $psnumberx ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="moveModalLabel<?= $psnumberx ?>">Pindahkan Sesi ke PS
                                            Lain</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="moveForm<?= $psnumberx ?>">
                                            <input type="hidden" name="from_no_ps" value="<?= $psnumberx ?>">
                                            <input type="hidden" name="from_type_ps" value="<?= $tytyp ?>">
                                            <div class="mb-3">
                                                <label for="to_no_ps<?= $psnumberx ?>" class="form-label">Pilih PS Tujuan
                                                    (<?= $tytyp ?> yang tersedia):</label>
                                                <select class="form-select" name="to_no_ps" id="to_no_ps<?= $psnumberx ?>"
                                                    required>
                                                    <option value="" disabled selected>-- Pilih PS --</option>
                                                    <?php if (!empty($availablePS[$tytyp])):
                                                        foreach ($availablePS[$tytyp] as $availNo):
                                                            if ($availNo != $psnumberx): ?>
                                                                <option value="<?= $availNo ?>">PS #<?= $availNo ?></option>
                                                            <?php endif; endforeach; endif; ?>
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="button" class="btn btn-primary btn-submit-move"
                                            data-from_no_ps="<?= $psnumberx ?>"
                                            data-from_type_ps="<?= $tytyp ?>">Pindahkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="modal fade" id="historyModal<?= $psnumberx ?>" tabindex="-1"
                        aria-labelledby="historyModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-lg">
                            <div class="modal-content">

                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h5 class="modal-title">History Rent #<?= $psnumberx ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Tutup"></button>
                                </div>

                                <!-- Date Filter Section -->
                                <div class="modal-body py-2 border-bottom">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-3">
                                            <label for="startDate<?= $psnumberx ?>"
                                                class="form-label small mb-1">Start:</label>
                                            <input type="date" class="form-control form-control-sm"
                                                id="startDate<?= $psnumberx ?>"
                                                value="<?= date('Y-m-d', strtotime('-7 days')) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="endDate<?= $psnumberx ?>" class="form-label small mb-1">End:</label>
                                            <input type="date" class="form-control form-control-sm"
                                                id="endDate<?= $psnumberx ?>" value="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    id="filterBtn<?= $psnumberx ?>">
                                                    <i class="bx bx-search"></i> Filter
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    id="resetBtn<?= $psnumberx ?>">
                                                    <i class="bx bx-refresh"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- History Content Section -->
                                <div class="modal-body pt-3" id="historyContent<?= $psnumberx ?>">
                                    <div class="text-center">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                        <p class="mb-0 ms-2">Loading history data...</p>
                                    </div>
                                </div>

                                <!-- Modal Footer (Optional) -->
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>




            <?php endwhile; ?>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <!-- Tampilkan data unit -->

                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-muted">Tidak ada unit rental ditemukan.</div>
            <?php endif; ?>


        </div>
    </div>
</div>
<!-- end row -->


<!-- end row -->

</div> <!-- container-fluid -->
</div>
<!-- End Page-content -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalElement = document.getElementById('modal<?= $psnumberx ?>');
        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function () {
                location.reload();
            });
        }
    });
    $(document).ready(function () {
        $('#modal<?= $psnumberx ?>').on('hidden.bs.modal', function () {
            location.reload();
        });



        // Start Rental
        $('.btn-start-rental').click(function () {
            var card = $(this).closest('.card');
            var no_ps = $(this).data('no_ps');
            var type_ps = $(this).data('type_ps');
            var duration = card.find('select[name="duration"]').val();
            var prepareChecked = card.find('input[type="checkbox"]').is(':checked');
            var prepare = prepareChecked ? 1 : 0;
            if (!duration) { Swal.fire('Pilih durasi!'); return; }
            if (prepareChecked && duration !== 'open') {
                duration = parseInt(duration) + 5;
            }
            // Ambil harga dari option terpilih, bukan badge
            var harga = 0;
            var select = card.find('select[name="duration"]');
            var selectedOption = select.find('option:selected');
            if (selectedOption.length && selectedOption.val() !== 'open') {
                var labelText = selectedOption.text();
                var match = labelText.match(/Rp\s*([0-9.]+)/);
                if (match) {
                    harga = parseInt(match[1].replace(/\./g, ''));
                }
            }
            Swal.fire({
                title: 'Mulai Rental?',
                text: 'Mulai rental untuk PS #' + no_ps + ' (' + type_ps + ')',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Mulai',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('', {
                        action: 'start_rental',
                        no_ps: no_ps,
                        type_ps: type_ps,
                        duration: duration,
                        harga: harga, // Kirim harga ke server
                        prepare: prepare // Kirim status prepare ke server
                    }, function (res) {
                        location.reload();
                    });
                }
            });
        });
        // Extra Time
        $('.btn-extra-time').click(function () {
            var card = $(this).closest('.card');
            var no_ps = $(this).data('no_ps');
            var type_ps = $(this).data('type_ps');
            var add_duration = card.find('select[name="add_duration"]').val();
            if (!add_duration) { Swal.fire('Pilih durasi tambahan!'); return; }
            // Ambil harga dari option terpilih, bukan badge
            var harga = 0;
            var select = card.find('select[name="add_duration"]');
            var selectedOption = select.find('option:selected');
            if (selectedOption.length && selectedOption.val() !== 'open') {
                var labelText = selectedOption.text();
                var match = labelText.match(/Rp\s*([0-9.]+)/);
                if (match) {
                    harga = parseInt(match[1].replace(/\./g, ''));
                }
            }
            Swal.fire({
                title: 'Tambah Waktu?',
                text: 'Tambah waktu untuk PS #' + no_ps + ' (' + type_ps + ')',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tambah',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('', {
                        action: 'extra_time',
                        no_ps: no_ps,
                        type_ps: type_ps,
                        add_duration: add_duration,
                        harga: harga // Kirim harga ke server
                    }, function (res) {
                        location.reload();
                    });
                }
            });
        });
        $('.btn-extra-time-available').click(function () {
            var card = $(this).closest('.card');
            var no_ps = $(this).data('no_ps');
            var type_ps = $(this).data('type_ps');
            var add_duration = card.find('select[name="duration"]').val();
            if (!add_duration) { Swal.fire('Pilih durasi tambahan!'); return; }
            // Ambil harga dari option terpilih, bukan badge
            var harga = 0;
            var select = card.find('select[name="duration"]');
            var selectedOption = select.find('option:selected');
            if (selectedOption.length && selectedOption.val() !== 'open') {
                var labelText = selectedOption.text();
                var match = labelText.match(/Rp\s*([0-9.]+)/);
                if (match) {
                    harga = parseInt(match[1].replace(/\./g, ''));
                }
            }
            Swal.fire({
                title: 'Tambah Waktu Lagi?',
                text: 'Tambah waktu untuk PS #' + no_ps + ' (' + type_ps + ')',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tambah',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('', {
                        action: 'extra_time_available',
                        no_ps: no_ps,
                        type_ps: type_ps,
                        add_duration: add_duration,
                        harga: harga // Kirim harga ke server
                    }, function (res) {
                        location.reload();
                    });
                }
            });
        });
        // Pause
        $('.btn-pause-ps').click(function () {
            var no_ps = $(this).data('no_ps');
            var type_ps = $(this).data('type_ps');
            var username = "<?= addslashes($username) ?>";
            Swal.fire({
                title: 'Pause Sesi?',
                text: 'Pause sesi PS #' + no_ps + ' (' + type_ps + ')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Pause',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('', {
                        action: 'pause_ps',
                        no_ps: no_ps,
                        type_ps: type_ps,
                    }, function (res) {
                        location.reload();
                    });
                }
            });
        });
        // Play (resume)
        $('.btn-play-ps').click(function () {
            var no_ps = $(this).data('no_ps');
            var type_ps = $(this).data('type_ps');
            var username = "<?= addslashes($username) ?>";
            var card = $(this).closest('.card');

            // Ambil pause_time, end_time dari tabel (span.text-dark di kolom End)
            var pauseTimeText = '';
            var endTimeText = '';
            card.find('tr').each(function () {
                var label = $(this).find('td').first().text().trim().toLowerCase();
                if (label === 'end') {
                    endTimeText = $(this).find('span.text-dark').text().trim();
                }
            });
            // Pause time tidak tampil di tabel, harus AJAX ke server
            $.post('controller/get_pause_info.php', {
                no_ps: no_ps,
                type_ps: type_ps,
                username: username
            }, function (res) {

                var sisaText = '';
                if (res && res.success) {
                    var pause_time = res.pause_time; // format: YYYY-MM-DD HH:mm:ss
                    var end_time = res.end_time;
                    var now = new Date();
                    // Hitung detik pause
                    var pauseDate = pause_time ? new Date(pause_time.replace(/-/g, '/')) : null;
                    var endDate = end_time ? new Date(end_time.replace(/-/g, '/')) : null;
                    var diffPause = 0;
                    if (pauseDate) {
                        diffPause = Math.floor((now - pauseDate) / 1000); // detik
                    }
                    // end_time baru = end_time + diffPause
                    var endBaru = endDate ? new Date(endDate.getTime() + diffPause * 1000) : null;
                    if (endBaru) {
                        var diffMs = endBaru - now;
                        if (diffMs > 0) {
                            var diffMin = Math.floor(diffMs / 60000);
                            var jam = Math.floor(diffMin / 60);
                            var menit = diffMin % 60;
                            sisaText = jam + ' jam ' + menit + ' menit lagi';
                        } else {
                            sisaText = 'Waktu sudah habis';
                        }
                    }
                }
                Swal.fire({
                    title: 'Lanjutkan Sesi?',
                    html: 'Lanjutkan sesi PS #' + no_ps + ' (' + type_ps + ')<br>' +
                        (sisaText ? '<b>Sisa waktu: ' + sisaText + '</b>' : ''),
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('', {
                            action: 'play_ps',
                            no_ps: no_ps,
                            type_ps: type_ps,
                        }, function (res) {
                            location.reload();
                        });
                    }
                });
            }, 'json');
        });


        // Stop
        $('.btn-stop-ps').click(function () {
            var no_ps = $(this).data('no_ps');
            var type_ps = $(this).data('type_ps');
            var card = $(this).closest('.card');
            var isOpenPlay = false;
            // Cek dari badge durasi
            var durasiText = card.find('td:contains("Durasi")').next().text().trim();
            if (durasiText.toLowerCase().indexOf('open play') !== -1) {
                isOpenPlay = true;
            }
            // Atau cek dari JS variable (lebih akurat)
            if (card.find('select[name="add_duration"]').is(':disabled')) {
                isOpenPlay = true;
            }
            // Atau cek dari data di DOM (jika ada)
            if (card.find('select[name="duration"]').val() === 'open') {
                isOpenPlay = true;
            }
            if (isOpenPlay) {
                // Ambil start_time dari badge/tabel
                var startTimeText = card.find('td:contains("Start")').next().text().trim();
                var startTime = startTimeText && startTimeText !== '-' ? startTimeText : null;
                var psnumberx = no_ps;
                var priceList = window['priceList' + psnumberx] || [];
                if (startTime && priceList.length > 0) {
                    // Format tanggal ke YYYY-MM-DD HH:mm:ss jika perlu
                    var start = new Date(startTime.replace(/(\d{2})-(\d{2})-(\d{4})/, '$3-$2-$1')).getTime();
                    var now = new Date().getTime();
                    var diffMs = now - start;
                    var totalMinute = Math.floor(diffMs / 60000) + 1;
                    // Cari harga sesuai menit berjalan
                    var price = 0;
                    for (var i = 0; i < priceList.length; i++) {
                        if (parseInt(priceList[i].duration) >= totalMinute) {
                            price = parseInt(priceList[i].price);
                            break;
                        }
                    }
                    if (price === 0 && priceList.length > 0) price = parseInt(priceList[priceList.length - 1].price);
                    Swal.fire({
                        title: 'Akhiri Sesi Open Play?',
                        html: 'Durasi berjalan: <b>' + totalMinute + ' menit</b><br>Harga saat ini: <b>Rp ' + price.toLocaleString('id-ID') + '</b><br>Lanjut akhiri sesi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Akhiri',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.post('', {
                                action: 'stop_ps',
                                no_ps: no_ps,
                                type_ps: type_ps,
                                harga: price, // Kirim harga ke server
                                manual: 1 // pastikan mode manual
                            }, function (res) {
                                location.reload();
                            });
                        }
                    });
                    return;
                }
            }
            // Default: non open play
            Swal.fire({
                title: 'Akhiri Sesi?',
                text: 'Akhiri sesi PS #' + no_ps + ' (' + type_ps + ')',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Ya, Akhiri',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ambil harga dari tabel jika ada (untuk non open play, biasanya sudah fix di tb_trans)
                    var harga = null;
                    // Jika ingin update harga juga untuk non open play, bisa ambil dari badge/tabel
                    $.post('', {
                        action: 'stop_ps',
                        no_ps: no_ps,
                        type_ps: type_ps,
                        harga: harga, // tetap kirim harga (null jika tidak ada)
                        manual: 1 // pastikan mode manual
                    }, function (res) {
                        location.reload();
                    });
                }
            });
        });
        // Move dropdown handler
        $('.btn-move-ps').click(function (e) {
            e.stopPropagation();
            var parent = $(this).closest('.position-relative');
            $('.move-dropdown').not(parent.find('.move-dropdown')).addClass('d-none');
            parent.find('.move-dropdown').toggleClass('d-none');
        });
        // Hide dropdown on click outside
        $(document).click(function () {
            $('.move-dropdown').addClass('d-none');
        });
        $('.move-dropdown').click(function (e) { e.stopPropagation(); });
        // Submit move
        $('.move-select').change(function () {
            var from_no_ps = $(this).data('from_no_ps');
            var from_type_ps = $(this).data('from_type_ps');
            var to_no_ps = $(this).val();
            if (!to_no_ps) return;
            Swal.fire({
                title: 'Pindahkan Sesi?',
                text: 'Pindahkan sesi ke PS #' + to_no_ps,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Pindahkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('', {
                        action: 'move_ps',
                        from_no_ps: from_no_ps,
                        from_type_ps: from_type_ps,
                        to_no_ps: to_no_ps,
                        to_type_ps: from_type_ps
                    }, function (res) {
                        location.reload();
                    });
                } else {
                    $(this).val('');
                }
            });
        });
        // PATCH: Disable select add_duration & extra time button saat losstime berjalan (JS)
        $('.card').each(function () {
            var card = $(this);
            var dur = card.find('select[name="add_duration"]').val();
            var isLosstime = card.find('select[name="add_duration"]').is(':disabled');
            if (isLosstime) {
                card.find('select[name="add_duration"]').prop('disabled', true);
                card.find('.btn-extra-time').prop('disabled', true);
            }
        });
        // PATCH: Update badge harga saat losstime berjalan
        <?php
        // Loop ulang untuk inject script per PS yang losstime
        $result->data_seek(0); // reset pointer
        while ($row2 = $result->fetch_assoc()):
            $no_ps2 = $row2['no_ps'];
            $duration2 = !empty($row2['duration']) ? $row2['duration'] : '-';
            $status2 = strtolower($row2['status']);
            $startTime2 = !empty($row2['start_time']) ? $row2['start_time'] : '-';
            if ($status2 === 'occupied' && $duration2 === 'open' && $startTime2 !== '-'):
                ?>
                    (function () {
                        var start = new Date("<?= $startTime2 ?>").getTime();
                        var badge = document.getElementById('priceBadge<?= $no_ps2 ?>').querySelector('.price-value');
                        var priceList = window.priceList<?= $no_ps2 ?>;
                        setInterval(function () {
                            var now = new Date().getTime();
                            var diff = now - start;
                            var totalMin = Math.ceil(diff / 60000);
                            var price = 0;
                            if (Array.isArray(priceList)) {
                                for (var i = 0; i < priceList.length; i++) {
                                    if (parseInt(priceList[i].duration) >= totalMin) {
                                        price = parseInt(priceList[i].price);
                                        break;
                                    }
                                }
                                if (price === 0 && priceList.length > 0) price = parseInt(priceList[priceList.length - 1].price);
                            }
                            if (badge) badge.textContent = price.toLocaleString('id-ID');
                        }, 1000);
                    })();
            <?php endif; endwhile; ?>

        // Disable/enable 'with prepare' checkbox if 'Open Play' is selected
        $('.card').each(function () {
            var card = $(this);
            var select = card.find('select[name="duration"]');
            var checkbox = card.find('input[type="checkbox"]');
            if (select.length && checkbox.length) {
                function updatePrepareCheckbox() {
                    var val = select.val();
                    if (val === 'open') {
                        checkbox.prop('checked', false).prop('disabled', true);
                    } else {
                        checkbox.prop('disabled', false);
                    }
                }
                select.on('change', updatePrepareCheckbox);
                updatePrepareCheckbox(); // initial state
            }
        });
    });
    function formatRupiah(angka) {
        return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    // UPDATED: Add FNB dengan promo integration
    $('.btn-add-fnb').on('click', function () {
        const ps = $(this).data('ps');
        const form = $(this).closest('form');
        const id_trans = form.find('input[name="id_trans"]').val();
        const id_fnb = parseInt(form.find('select[name="id_fnb"]').val());
        const qty = parseInt(form.find('input[name="qty"]').val());

        if (!id_fnb || qty < 1) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Pilih produk dan isi qty minimal 1!',
            });
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'controller/ajax_add_fnb.php',
            data: { id_trans, id_fnb, qty, ps },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const tbody = $('#trxTable' + ps).find('tbody');
                    let found = false;

                    tbody.find('tr').each(function () {
                        const row = $(this);
                        const namaItem = row.find('td').eq(1).text().trim();
                        if (namaItem === res.data.nama) {
                            const currentQty = parseInt(row.find('td').eq(2).text());
                            const newQty = currentQty + qty;
                            const newTotal = newQty * res.data.harga;

                            row.find('td').eq(2).text(newQty);
                            row.find('td').eq(4).text('Rp ' + newTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));

                            row.attr('id', `row-fnb-${res.data.real_db_id}`);
                            row.attr('data-id', res.data.real_db_id);
                            row.find('.btn-del-fnb').attr('data-id', res.data.real_db_id);

                            found = true;
                            return false;
                        }
                    });

                    if (!found) {
                        tbody.find('tr').each(function () {
                            if ($(this).find('td').length === 1 && $(this).find('td').text().includes('No transactions yet')) {
                                $(this).remove();
                            }
                        });
                        const no = tbody.find('tr').length + 1;
                        const newRow = `
                         <tr id="row-fnb-${res.data.real_db_id}" data-id="${res.data.real_db_id}">
                            <td>${no}</td>
                            <td>${res.data.nama}</td>
                            <td>${res.data.qty}</td>
                            <td>Rp ${res.data.harga.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}</td>
                            <td>Rp ${res.data.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-danger btn-sm w-50 btn-del-fnb" 
                                        data-ps="${ps}" 
                                        data-id="${res.data.real_db_id}">
                                    -
                                </button>
                            </td>
                        </tr>
                    `;
                        tbody.append(newRow);
                    }

                    // Update nomor urut
                    tbody.find('tr').each(function (idx) {
                        $(this).find('td').eq(0).text(idx + 1);
                    });

                    // UPDATED: Update total dengan promo consideration
                    updateTotalWithPromo(ps, res.grand_total);

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: res.error || 'Gagal menambahkan produk.',
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan pada server.',
                });
            }
        });
    });

    // UPDATED: Delete FNB dengan promo integration
    $('body').on('click', '.btn-del-Rent', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const ps = $(this).data('ps');

        if (!id) {
            Swal.fire('Error', 'ID tidak ditemukan.', 'error');
            return;
        }

        Swal.fire({
            title: 'Hapus item?',
            text: 'Yakin ingin menghapus item ini dari transaksi?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'controller/ajax_del_rent.php',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.success) {
                            $(`#row-fnb-${id}`).remove();

                            // Update nomor urut
                            const tbody = $('#trxTable' + ps).find('tbody');
                            tbody.find('tr').each(function (idx) {
                                $(this).find('td').eq(0).text(idx + 1);
                            });

                            // UPDATED: Update total dengan promo consideration
                            updateTotalWithPromo(ps, res.grand_total);

                            Swal.fire('Berhasil', 'Item berhasil dihapus.', 'success');
                        } else {
                            Swal.fire('Gagal', res.error || 'Gagal menghapus item.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        Swal.fire('Error', 'Gagal menghubungi server: ' + error, 'error');
                    }
                });
            }
        });
    });
    // UPDATED: Delete FNB dengan promo integration
    $('body').on('click', '.btn-del-fnb', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const ps = $(this).data('ps');

        if (!id) {
            Swal.fire('Error', 'ID tidak ditemukan.', 'error');
            return;
        }

        Swal.fire({
            title: 'Hapus item?',
            text: 'Yakin ingin menghapus item ini dari transaksi?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'controller/ajax_del_fnb.php',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.success) {
                            $(`#row-fnb-${id}`).remove();

                            // Update nomor urut
                            const tbody = $('#trxTable' + ps).find('tbody');
                            tbody.find('tr').each(function (idx) {
                                $(this).find('td').eq(0).text(idx + 1);
                            });

                            // UPDATED: Update total dengan promo consideration
                            updateTotalWithPromo(ps, res.grand_total);

                            Swal.fire('Berhasil', 'Item berhasil dihapus.', 'success');
                        } else {
                            Swal.fire('Gagal', res.error || 'Gagal menghapus item.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        Swal.fire('Error', 'Gagal menghubungi server: ' + error, 'error');
                    }
                });
            }
        });
    });
    function calculateGrandTotalWithPromo(ps) {
        const totalRental = parseInt($('#trxTable' + ps).find('[data-rental-total]').text().replace(/[^0-9]/g, '')) || 0;
        const totalFnb = parseInt($('#trxTable' + ps).find('#grand-total' + ps).attr('data-fnb-total')) || 0; // ✅ ambil dari atribut
        const promoDiscount = parseInt($('#promo' + ps).val()) || 0;

        const grandTotal = (totalRental + totalFnb) - promoDiscount;
        const finalTotal = Math.max(0, grandTotal);

        $('#displayTotal' + ps).text('Rp ' + finalTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
        $('#grand-total' + ps).text('Rp ' + totalFnb.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')); // biar konsisten

        $('#bayar' + ps).val('');
        $('#kembali' + ps).text('Rp 0');

        return finalTotal;
    }
    // Event handler untuk perubahan promo
    $(document).on('change', '.promo-select', function () {
        const ps = $(this).data('ps');
        // Ambil total dari tabel (tfoot grand-total)
        let tableTotalText = $('#trxTable' + ps).find('tfoot #grand-total' + ps).text().replace(/[^0-9]/g, '');
        let tableTotal = parseInt(tableTotalText) || 0;

        // Set grand total = total tabel (tanpa promo)
        $('#displayTotal' + ps).text('Rp ' + tableTotal.toLocaleString('id-ID'));

        // Jika promo dipilih, kurangi qty_potongan
        const promoValue = parseInt($(this).val()) || 0;
        if (promoValue > 0) {
            let afterPromo = Math.max(0, tableTotal - promoValue);
            $('#displayTotal' + ps).text('Rp ' + afterPromo.toLocaleString('id-ID'));
            // Optional: notifikasi
            const promoName = $(this).find('option:selected').data('nama') || '';
            Swal.fire({
                icon: 'success',
                title: 'Promo Applied!',
                text: promoName + ' - Diskon Rp ' + promoValue.toLocaleString('id-ID'),
                timer: 1500,
                showConfirmButton: false
            });
        }

        // Reset bayar & kembalian
        $('#bayar' + ps).val('');
        $('#kembali' + ps).text('Rp 0');
    });

    // Payment method: show/hide bayar & kembalian
    $(document).on('change', '[id^="paymentMethod"]', function () {
        const ps = $(this).attr('id').replace('paymentMethod', '');
        const metode = $(this).val();
        const grandTotal = parseInt($('#displayTotal' + ps).text().replace(/[^0-9]/g, '')) || 0;

        const btn = $('.btnPayNow[data-ps="' + ps + '"]');

        if (metode === 'cash') {
            $('#tunaiFields' + ps).show();
            $('#bayar' + ps).focus();
            btn.prop('disabled', true); // butuh input bayar
        } else {
            $('#tunaiFields' + ps).hide();
            $('#bayar' + ps).val('');
            $('#kembali' + ps).text('Rp 0');
            btn.prop('disabled', false); // langsung bisa klik
        }
    });

    $(document).on('input', '.bayar-input', function () {
        const ps = $(this).data('ps');
        const bayar = parseInt($(this).val()) || 0;
        const grandTotal = parseInt($('#displayTotal' + ps).text().replace(/[^0-9]/g, '')) || 0;

        const kembali = Math.max(0, bayar - grandTotal);
        $('#kembali' + ps).text('Rp ' + kembali.toLocaleString('id-ID'));

        const btn = $('.btnPayNow[data-ps="' + ps + '"]');

        if (bayar >= grandTotal) {
            btn.prop('disabled', false);
        } else {
            btn.prop('disabled', true);
            if (bayar > 0) {
                $('#kembali' + ps).html('<span class="text-danger">Kurang Rp ' +
                    (grandTotal - bayar).toLocaleString('id-ID') + '</span>');
            }
        }
    });
    $(document).on('change', '[id^="paymentMethod"]', function () {
        const ps = $(this).attr('id').replace('paymentMethod', '');
        const metode = $(this).val();
        const btn = $('.btnPayNow[data-ps="' + ps + '"]');

        if (metode === 'cash') {
            $('#tunaiFields' + ps).show();
            $('#bayar' + ps).focus();
            btn.prop('disabled', true); // karena butuh input bayar
        } else {
            $('#tunaiFields' + ps).hide();
            $('#bayar' + ps).val('');
            $('#kembali' + ps).text('Rp 0');
            btn.prop('disabled', false); // langsung bisa klik
        }
    });

    $(document).on('input', '.bayar-input', function () {
        const ps = $(this).data('ps');
        const bayar = parseInt($(this).val()) || 0;
        const grandTotal = parseInt($('#displayTotal' + ps).text().replace(/[^0-9]/g, '')) || 0;

        const kembali = Math.max(0, bayar - grandTotal);
        $('#kembali' + ps).text('Rp ' + kembali.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));

        const btn = $('.btnPayNow[data-ps="' + ps + '"]');

        if (bayar >= grandTotal) {
            btn.prop('disabled', false);
        } else {
            btn.prop('disabled', true);
            if (bayar > 0) {
                $('#kembali' + ps).html('<span class="text-danger">Kurang Rp ' +
                    (grandTotal - bayar).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '</span>');
            }
        }
    });



    // Inisialisasi saat halaman load
    $(document).ready(function () {
        // Sembunyikan semua field tunai di awal
        $('[id^="tunaiFields"]').hide();

        // Set default payment method ke tunai dan tampilkan field
        $('[id^="paymentMethod"]').each(function () {
            const ps = $(this).attr('id').replace('paymentMethod', '');
            $(this).val('cash');
            $('#tunaiFields' + ps).show();
        });
    });

    function updateTotalWithPromo(ps, newGrandTotal) {
        // Update base total tampilan
        $('#trxTable' + ps).find('#grand-total' + ps)
            .text('Rp ' + newGrandTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'))
            .attr('data-fnb-total', newGrandTotal); // ✅ Simpan nilai total FNB untuk perhitungan promo

        // Ambil nilai promo
        const promoDiscount = parseInt($('#promo' + ps).val()) || 0;
        const finalTotal = Math.max(0, newGrandTotal - promoDiscount);

        // Update tampilan total akhir
        $('#displayTotal' + ps).text('Rp ' + finalTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));

        // Reset bayar dan kembali
        $('#bayar' + ps).val('');
        $('#kembali' + ps).text('Rp 0');
    }
    $(document).on('click', '.btnPayNow', function () {
        console.log('Pay Now clicked');

        // Prevent double click - disable button immediately
        const $btn = $(this);
        const originalText = $btn.html();
        const originalClass = $btn.attr('class');

        // Disable button and change appearance
        $btn.prop('disabled', true)
            .html('<i class="spinner-border spinner-border-sm me-2"></i>Processing...')
            .removeClass('btn-primary btn-success')
            .addClass('btn-secondary');

        const ps = $(this).data('ps');
        const id_trans = $(this).data('id_trans');
        const userx = $(this).data('userx');
        const grandtotal = parseInt($('#displayTotal' + ps).text().replace(/[^0-9]/g, '')) || 0;

        const metode = $('#paymentMethod' + ps).val();
        const bayar = parseInt($('#bayar' + ps).val()) || 0;
        const kembali = parseInt($('#kembali' + ps).text().replace(/[^0-9]/g, '')) || 0;
        const promo = parseInt($('#promo' + ps).val()) || 0;
        const invoice = 'INV' + Date.now();

        $.ajax({
            url: 'controller/ajax_save_trans.php',
            method: 'POST',
            dataType: 'json',
            data: { metode, bayar, kembali, promo, invoice, id_trans, userx, ps, grandtotal },

            success: function (res) {
                if (res.success) {
                    Swal.fire('Sukses', 'Transaksi berhasil disimpan!', 'success').then(() => {
                        $('#modal' + ps).modal('hide');
                        if (res.autopilot) {
                            window.open(`controller/print_struk.php?inv=${res.inv}`, 'strukWindow', 'width=500,height=800,top=100,left=300');
                        }
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.error || 'Gagal menyimpan transaksi.', 'error');
                    // Re-enable button on error
                    $btn.prop('disabled', false)
                        .html(originalText)
                        .attr('class', originalClass);
                }
            },

            error: function () {
                Swal.fire('Error', 'Terjadi error koneksi.', 'error');
                // Re-enable button on error
                $btn.prop('disabled', false)
                    .html(originalText)
                    .attr('class', originalClass);
            }
        });
    });
    $(document).ready(function () {

        //   // Test function to verify API response
        //   function testAPIResponse() {
        //     // Sample data from your API response
        //     const sampleResponse = {
        //       "data": [
        //         {
        //           "id": "5",
        //           "start": "17-06-2025 07:19",
        //           "end": "17-06-2025 07:27",
        //           "type": "Reguler",
        //           "duration": "9 Min",
        //           "actual_duration": "8 Min",
        //           "actual_stop": "(M) 17-06-2025 07:27",
        //           "price": "Rp 3.000",
        //           "raw_price": "3000",
        //           "raw_duration": 8,
        //           "date_only": "17-06-2025"
        //         }
        //       ],
        //       "total_records": 1,
        //       "date_range": {"start": "", "end": ""}
        //     };

        //     console.log('Testing with sample data:', sampleResponse);
        //     return sampleResponse;
        //   }

        // Function to load history data
        function loadHistoryData(psId, psNumber, startDate = null, endDate = null) {
            console.log('🔍 loadHistoryData called with:', { psId, psNumber, startDate, endDate });

            // Find modal content element
            const modalBody = $('#historyContent' + psNumber);
            console.log('📋 Modal element search result:', {
                selector: '#historyContent' + psNumber,
                found: modalBody.length > 0,
                element: modalBody[0]
            });

            if (modalBody.length === 0) {
                console.error('❌ Modal body element not found!');
                alert('Error: Modal element #historyContent' + psNumber + ' tidak ditemukan!');
                return;
            }

            // Show loading
            modalBody.html(`
      <div class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="mb-0 mt-2">Loading data...</p>
      </div>
    `);

            // Prepare AJAX data
            let ajaxData = { id_ps: psId };
            if (startDate && endDate) {
                ajaxData.start_date = startDate;
                ajaxData.end_date = endDate;
            }

            console.log('📤 AJAX request data:', ajaxData);
            console.log('🌐 Request URL: controller/get_history.php');

            // Make AJAX request
            $.ajax({
                url: 'controller/get_history.php',
                type: 'GET',
                data: ajaxData,
                dataType: 'json',
                timeout: 10000, // 10 second timeout

                beforeSend: function () {
                    console.log('📡 AJAX request started...');
                },

                success: function (response) {
                    console.log('✅ AJAX Success! Raw response:', response);
                    processHistoryData(response, modalBody, startDate, endDate);
                },

                error: function (xhr, status, error) {
                    console.error('❌ AJAX Error:', {
                        status: status,
                        error: error,
                        statusCode: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        readyState: xhr.readyState
                    });

                    // Show error message
                    let errorMsg = `
          <div class="alert alert-danger">
            <h6><i class="bx bx-error"></i> Gagal Memuat Data</h6>
            <p class="mb-2">Status: ${xhr.status} - ${error}</p>
            <small class="text-muted">URL: controller/get_history.php</small>
        `;

                    if (xhr.responseText) {
                        errorMsg += `<br><small class="text-muted">Response: ${xhr.responseText}</small>`;
                    }

                    errorMsg += `</div>`;
                    modalBody.html(errorMsg);

                    // Test with sample data as fallback
                    console.log('🧪 Testing with sample data as fallback...');
                    setTimeout(() => {
                        const sampleData = testAPIResponse();
                        processHistoryData(sampleData, modalBody, startDate, endDate);
                    }, 2000);
                },

                complete: function () {
                    console.log('🏁 AJAX request completed');
                }
            });
        }

        // Function to process and display history data
        function processHistoryData(response, modalBody, startDate, endDate) {
            console.log('🔄 Processing history data:', response);

            try {
                // Extract data from response
                let data, totalRecords;

                if (response && response.data && Array.isArray(response.data)) {
                    data = response.data;
                    totalRecords = response.total_records || data.length;
                    console.log('📊 Using response.data format');
                } else if (Array.isArray(response)) {
                    data = response;
                    totalRecords = data.length;
                    console.log('📊 Using direct array format');
                } else {
                    throw new Error('Invalid response format');
                }

                console.log('📈 Data extracted:', {
                    dataLength: data.length,
                    totalRecords: totalRecords,
                    firstItem: data[0]
                });

                // Check if data is empty
                if (!data || data.length === 0) {
                    let message = `
          <div class="alert alert-info">
            <h6><i class="bx bx-info-circle"></i> Tidak Ada Data</h6>
            <p class="mb-0">Tidak ada histori untuk unit ini`;

                    if (startDate && endDate) {
                        message += ` pada periode ${formatDate(startDate)} - ${formatDate(endDate)}`;
                    }

                    message += `.</p></div>`;
                    modalBody.html(message);
                    return;
                }

                // Process data and create table
                let totalDurasi = 0;
                let totalHarga = 0;

                // Create header info
                let headerInfo = `
        <div class="alert alert-primary mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <strong><i class="bx bx-calendar me-1"></i>Total Records: ${totalRecords}</strong>
      `;

                if (startDate && endDate) {
                    headerInfo += `<small>Periode: ${formatDate(startDate)} - ${formatDate(endDate)}</small>`;
                }

                headerInfo += `</div></div>`;

                // Create table
                let table = `
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0 table-sm" style="font-size: 0.8rem;">
            <thead class="table-light">
              <tr>
                <th style="font-size: 0.75rem;">Tanggal</th>
                <th style="font-size: 0.75rem;">Start</th>
                <th style="font-size: 0.75rem;">End</th>
                <th style="font-size: 0.75rem;">Type</th>
                <th style="font-size: 0.75rem;">Durasi</th>
                <th style="font-size: 0.75rem;">Actual Stop</th>
                <th class="text-end" style="font-size: 0.75rem;">Harga</th>
              </tr>
            </thead>
            <tbody>
      `;

                // Process each row
                data.forEach((row, index) => {
                    console.log(`🔍 Processing row ${index + 1}:`, row);

                    // Safe duration parsing
                    const durasiInt = parseInt(row.raw_duration || row.duration?.replace(/\D/g, '') || '0') || 0;
                    const hargaInt = parseInt(row.raw_price || row.price?.replace(/\D/g, '') || '0') || 0;

                    totalDurasi += durasiInt;
                    totalHarga += hargaInt;

                    // Safe field extraction
                    const dateOnly = row.date_only || row.start?.split(' ')[0] || 'N/A';
                    const usercreate = row.usercreate || 'N/A';
                    const startTime = row.start || 'N/A';
                    const endTime = row.end || 'N/A';
                    const type = row.type || 'Unknown';
                    const actualDuration = row.duration || row.duration || 'N/A';
                    const actualStop = row.actual_stop || 'N/A';
                    const price = row.price || 'Rp 0';

                    // Row styling
                    const rowClass = type === 'Extra' ? 'table-warning' : '';
                    const badgeClass = type === 'Extra' ? 'bg-warning text-dark' : 'bg-primary';

                    table += `
  <tr class="${rowClass}" style="font-size: 0.8rem;">
    <td>${dateOnly}</td>
    <td title="Created by: ${usercreate}">${startTime}</td>
    <td>${endTime}</td>
    <td><span class="badge ${badgeClass}" style="font-size: 0.7rem;">${type}</span></td>
    <td>${actualDuration}</td>
    <td>${actualStop}</td>
    <td class="text-end">${price}</td>
  </tr>
`;

                });

                // Add footer
                table += `
            </tbody>
            <tfoot class="table-light">
              <tr style="font-size: 0.8rem;">
                <td colspan="4" class="text-end fw-bold">TOTAL</td>
                <td class="fw-bold">${totalDurasi} Min</td>
                <td></td>
                <td class="text-end fw-bold">Rp ${totalHarga.toLocaleString('id-ID')}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      `;

                // Display the final result
                const finalHTML = headerInfo + table;
                modalBody.html(finalHTML);

                console.log('✅ Data processed successfully!');

            } catch (error) {
                console.error('❌ Error processing data:', error);
                modalBody.html(`
        <div class="alert alert-danger">
          <h6><i class="bx bx-error"></i> Error Processing Data</h6>
          <p class="mb-0">${error.message}</p>
        </div>
      `);
            }
        }

        // Function to format date for display
        function formatDate(dateString) {
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return dateString;
                }
                return date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            } catch (error) {
                console.error('Error formatting date:', error);
                return dateString;
            }
        }

        // Main history button click handler
        $('.btn-history').click(function () {
            console.log('🖱️ History button clicked');

            const psId = $(this).data('id');
            const psNumber = $(this).data('ps');

            console.log('📋 Button data:', { psId, psNumber });

            if (!psId || !psNumber) {
                console.error('❌ Missing PS data!');
                alert('Error: Data PS tidak lengkap (ID atau Number kosong)');
                return;
            }

            // Store data in modal for filter use
            const modal = $('#historyModal' + psNumber);
            console.log('🎯 Modal search:', {
                selector: '#historyModal' + psNumber,
                found: modal.length > 0
            });

            if (modal.length === 0) {
                console.error('❌ Modal not found!');
                alert('Error: Modal #historyModal' + psNumber + ' tidak ditemukan');
                return;
            }

            modal.data('ps-id', psId);
            modal.data('ps-number', psNumber);

            // Set default dates (from PHP default values or current date range)
            const startDateInput = $('#startDate' + psNumber);
            const endDateInput = $('#endDate' + psNumber);

            let startDate = startDateInput.val();
            let endDate = endDateInput.val();

            console.log('📅 Date inputs:', { startDate, endDate });

            // Load history data
            loadHistoryData(psId, psNumber, startDate, endDate);
        });

        // Filter button click handler
        $(document).on('click', '[id^="filterBtn"]', function () {
            console.log('🔍 Filter button clicked');

            const psNumber = $(this).attr('id').replace('filterBtn', '');
            const modal = $('#historyModal' + psNumber);
            const psId = modal.data('ps-id');
            const startDate = $('#startDate' + psNumber).val();
            const endDate = $('#endDate' + psNumber).val();

            console.log('🔄 Filter data:', { psNumber, psId, startDate, endDate });

            // Validate dates
            if (!startDate || !endDate) {
                alert('Silakan pilih tanggal mulai dan tanggal akhir');
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                return;
            }

            loadHistoryData(psId, psNumber, startDate, endDate);
        });

        // Reset button click handler
        $(document).on('click', '[id^="resetBtn"]', function () {
            console.log('🔄 Reset button clicked');

            const psNumber = $(this).attr('id').replace('resetBtn', '');
            const modal = $('#historyModal' + psNumber);
            const psId = modal.data('ps-id');

            // Reset to default range (7 days back to today - matching PHP defaults)
            const today = new Date();
            const sevenDaysAgo = new Date(today.getTime() - (7 * 24 * 60 * 60 * 1000));

            const startDate = sevenDaysAgo.toISOString().split('T')[0];
            const endDate = today.toISOString().split('T')[0];

            $('#startDate' + psNumber).val(startDate);
            $('#endDate' + psNumber).val(endDate);

            console.log('📅 Reset dates:', { startDate, endDate });

            loadHistoryData(psId, psNumber, startDate, endDate);
        });

        // Enter key handler for date inputs
        $(document).on('keypress', '[id^="startDate"], [id^="endDate"]', function (e) {
            if (e.which === 13) { // Enter key
                const psNumber = $(this).attr('id').replace(/\D/g, '');
                $('#filterBtn' + psNumber).click();
            }
        });

        console.log('🚀 History loader initialized successfully!');

    });
</script>



<!-- JAVASCRIPT -->
<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- dashboard init -->
<script src="assets/js/pages/dashboard.init.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>