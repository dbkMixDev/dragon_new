<?php
require_once('../include/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $whatsapp = $_POST['whatsapp'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $category = $_POST['category'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $waktu = $_POST['waktu'] ?? '';
    $userx = $_POST['userx'] ?? '';
    $price = $_POST['price'] ?? 0;

    if (empty($whatsapp) || empty($nama) || empty($category) || empty($duration) || empty($tanggal) || empty($waktu) || empty($userx)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        exit;
    }

    $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);
    if (substr($whatsapp, 0, 2) === '62') {
        $whatsapp = substr($whatsapp, 2);
    } elseif (substr($whatsapp, 0, 1) === '0') {
        $whatsapp = substr($whatsapp, 1);
    }

    $timestamp = date('ymd');
    $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    $booking_code = "DRGBOOK-{$timestamp}-{$random}";

    $datetime_start = $tanggal . ' ' . $waktu . ':00';
    $end_time = date('Y-m-d H:i:s', strtotime($datetime_start . " + {$duration} minutes"));

    $tip = "Durasi: {$duration} menit | Kategori: {$category} | Harga: Rp " . number_format($price, 0, ',', '.');
    $status = 'pending';
    $date_time = date('Y-m-d H:i:s');
    $acc_admin = 'pending';
    $no_ps = '62' . $whatsapp;

    $time_start = $waktu . ':00';
    $time_end = date('H:i:s', strtotime($time_start . " +{$duration} minutes"));

    // Pakai mysqli_query biasa
    $sql = "INSERT INTO bookings (id, date, time_start, time_end, name, tip, status, date_time, acc_admin, no_ps, userx,duration,type_ps)
            VALUES ('$booking_code', '$tanggal', '$time_start', '$time_end', '$nama', '$tip', '$status', '$date_time', '$acc_admin', '$no_ps', '$userx','$duration','$category')";

    if (mysqli_query($con, $sql)) {
        // Ambil info merchant
        $merchant_info = "";
        $address_info = "";

        $merchant_query = mysqli_query($con, "SELECT merchand, address FROM userx WHERE email = '$userx'");
        if ($row = mysqli_fetch_assoc($merchant_query)) {
            $merchant_info = $row['merchand'];
            $address_info = $row['address'];
        }
        $merchant_query = mysqli_query($con, "SELECT phone FROM transactions WHERE status = 'success' AND email='$userx' LIMIT 1");
        if ($row = mysqli_fetch_assoc($merchant_query)) {
            $phoneadmo = $row['phone'];

        }
        $message = "🎮 *BOOKING PLAYSTATION* 🎮\n\n";
        $message .= "📋 *Detail Booking:*\n";
        $message .= "• Kode Booking: `{$booking_code}`\n";
        $message .= "• Nama: {$nama}\n";
        $message .= "• Kategori: {$category}\n";
        $message .= "• Durasi: {$duration} menit\n";
        $message .= "• Tanggal: " . date('d M Y', strtotime($tanggal)) . "\n";
        $message .= "• Waktu: {$waktu} - " . date('H:i', strtotime($waktu . " + {$duration} minutes")) . "\n";
        $message .= "• Estimasi Harga: Rp " . number_format($price, 0, ',', '.') . "\n\n";

        if ($merchant_info) {
            $message .= "🏪 *{$merchant_info}*\n";
            if ($address_info) {
                $message .= "📍 {$address_info}\n";
            }
        }

        // Ambil data ketentuan dari database
        $sql = "SELECT name FROM tb_ketentuan where userx='$userx'";
        $result = $con->query($sql);

        $message .= "\n📝 *Ketentuan:*\n";

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $message .= "• " . $row['name'] . "\n";
            }
            $message .= "⏰ *Status:* Menunggu konfirmasi admin\n\n";
            $message .= "Terima kasih telah booking! Admin akan segera menghubungi Anda untuk konfirmasi.";
        } else {
            $message .= "• Tidak ada ketentuan\n";
        }

        $message .= "\n";



        $whatsapp_url = "https://wa.me/$phoneadmo?text=" . urlencode($message);

        echo json_encode([
            'success' => true,
            'message' => 'Booking berhasil disimpan',
            'booking_code' => $booking_code,
            'whatsapp_url' => $whatsapp_url
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan booking: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>