<?php
include('../include/config.php');
  // WIB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ps_id = intval($_POST['ps_id']);
    $duration = intval($_POST['duration']);
    $device_id = isset($_POST['device_id']) ? escapeshellarg($_POST['device_id']) : null;
    $start_time = date('Y-m-d H:i:s'); 
    $end_time = date('Y-m-d H:i:s', strtotime("+$duration minutes"));

    if (!$ps_id || !$duration) {
        echo json_encode(["status" => "error", "message" => "Data tidak lengkap!"]);
        exit();
    }

    // Ambil harga dari tb_harga berdasarkan durasi & tipe PS
    $stmt_harga = $conn->prepare("SELECT harga FROM tb_harga WHERE type_ps = (SELECT type_ps FROM playstations WHERE id = ?) AND durasi = ?");
    $stmt_harga->bind_param("ii", $ps_id, $duration);
    $stmt_harga->execute();
    $stmt_harga->bind_result($harga);
    $stmt_harga->fetch();
    $stmt_harga->close();

    if ($harga === null) {
        $harga = 0;
        
    }

    // Insert ke tb_trans
    $stmt2 = $conn->prepare("INSERT INTO tb_trans (id_ps, start, end, durasi, harga, date_trans, start_akum) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("issiiss", $ps_id, $start_time, $end_time, $duration, $harga, $start_time, $start_time);
    $stmt2->execute();
    $stmt2->close();

    // Update status PlayStation
    $stmt = $conn->prepare("UPDATE playstations SET status='occupied', start_time=?, update_on=?, end_time=?, duration=? WHERE id=?");
    $stmt->bind_param("sssii", $start_time, $start_time, $end_time, $duration, $ps_id);
    $stmt->execute();
    $stmt->close();

  
    
    // Jalankan ADB hanya jika `device_id` tidak kosong
if (!empty($device_id)) {
    $command = "\"$adb_path\" -s $device_id shell input keyevent 224"; // Matikan layar
    
    try {
        $output = @shell_exec($command . " 2>&1");
        // Bisa log $output jika perlu debug, tapi tidak ditampilkan ke user
    } catch (Exception $e) {
        // Abaikan error
    }
}
       
  // Kirim pesan ke Telegram
$message = "Transaksi baru: \n"
. "PS ID: $ps_id\n"
. "Durasi: $duration menit\n"
. "Harga: $harga\n"
. "Waktu mulai: $start_time\n"
. "Waktu selesai: $end_time";
if($harga > '0'){
// Mengencode URL dengan parameter yang diperlukan
// $telegramApiUrl = "https://api.telegram.org/bot7313031235:AAG8MM28X_uX-9qmz8G7IzH3RFhanqN8G9I/sendMessage?chat_id=-1002519355568&text=" . urlencode($message);

// Kirim request ke Telegram API
// file_get_contents($telegramApiUrl);
}


    echo json_encode(["status" => "success"]);
}
?>
