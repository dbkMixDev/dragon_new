<?php
session_start();
require_once '../include/config.php'; // Sesuaikan path

// Set header untuk JSON response
header('Content-Type: application/json');

try {
    // Cek apakah user sudah login
    if (!isset($_SESSION['username'])) {
        throw new Exception('User tidak terautentikasi');
    }

    $username = $_SESSION['username'];
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_merchant_name':
            updateMerchantName($con, $username);
            break;
            
        case 'update_address':
            updateAddress($con, $username);
            break;
            
        case 'update_logo':
            updateLogo($con, $username);
            break;
            
        case 'update_timezone':
            updateTimezone($con, $username);
            break;
            
        default:
            throw new Exception('Aksi tidak valid');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function updateMerchantName($con, $username) {
    $merchantName = trim($_POST['merchant_name'] ?? '');
    
    if (empty($merchantName)) {
        throw new Exception('Nama merchant tidak boleh kosong');
    }
    
    if (strlen($merchantName) < 3) {
        throw new Exception('Nama merchant minimal 3 karakter');
    }
    
    $stmt = $con->prepare("UPDATE userx SET merchand = ? WHERE username = ?");
    if (!$stmt) {
        throw new Exception('Database error: ' . $con->error);
    }
    
    $stmt->bind_param("ss", $merchantName, $username);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Nama merchant berhasil diperbarui'
        ]);
    } else {
        throw new Exception('Gagal memperbarui nama merchant: ' . $stmt->error);
    }
    
    $stmt->close();
}

function updateAddress($con, $username) {
    $address = trim($_POST['address'] ?? '');
    
    if (empty($address)) {
        throw new Exception('Alamat tidak boleh kosong');
    }
    
    if (strlen($address) < 10) {
        throw new Exception('Alamat minimal 10 karakter');
    }
    
    $stmt = $con->prepare("UPDATE userx SET address = ? WHERE username = ?");
    if (!$stmt) {
        throw new Exception('Database error: ' . $con->error);
    }
    
    $stmt->bind_param("ss", $address, $username);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Alamat berhasil diperbarui'
        ]);
    } else {
        throw new Exception('Gagal memperbarui alamat: ' . $stmt->error);
    }
    
    $stmt->close();
}

function updateLogo($con, $username) {
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Tidak ada file yang diupload atau terjadi error saat upload');
    }
    
    $file = $_FILES['logo'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, atau GIF');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('Ukuran file terlalu besar. Maksimal 2MB');
    }
    
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new Exception('File yang diupload bukan gambar yang valid');
    }
    
    $uploadDir = '../assets/images/logos/'; // Sesuaikan path
    
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new Exception('Gagal membuat direktori upload');
    }
    
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = $username . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    $relativeFilePath = './assets/images/logos/' . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Gagal memindahkan file upload');
    }
    
    // Ambil logo lama tanpa get_result()
    $stmt = $con->prepare("SELECT logox FROM userx WHERE email = ?");
    if (!$stmt) {
        // Hapus file baru kalau gagal
        unlink($filePath);
        throw new Exception('Database error: ' . $con->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    $oldLogo = '';
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($oldLogo);
        $stmt->fetch();
    }
    $stmt->close();

    if (!empty($oldLogo)) {
        $oldFilePath = '../' . ltrim($oldLogo, './');
        if (file_exists($oldFilePath) && strpos($oldLogo, 'logos/') !== false) {
            unlink($oldFilePath);
        }
    }
    
    // Update logo di DB (ingat ganti ke username untuk email di bind_param)
    $stmt = $con->prepare("UPDATE userx SET logox = ? WHERE email = ?");
    if (!$stmt) {
        unlink($filePath);
        throw new Exception('Database error: ' . $con->error);
    }
    
    $stmt->bind_param("ss", $relativeFilePath, $username);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Logo berhasil diperbarui',
            'logo_url' => $relativeFilePath
        ]);
    } else {
        unlink($filePath);
        throw new Exception('Gagal memperbarui logo di database: ' . $stmt->error);
    }
    
    $stmt->close();
}

function updateTimezone($con, $username) {
    $timezone = trim($_POST['timezone'] ?? '');
    
    if (empty($timezone)) {
        throw new Exception('Timezone tidak boleh kosong');
    }
    
    $validTimezones = DateTimeZone::listIdentifiers();
    if (!in_array($timezone, $validTimezones)) {
        throw new Exception('Timezone tidak valid');
    }
    
    // Ambil timezone sekarang tanpa get_result()
    $stmt = $con->prepare("SELECT timezone FROM userx WHERE username = ?");
    if (!$stmt) {
        throw new Exception('Database error: ' . $con->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    $currentTimezone = null;
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($currentTimezone);
        $stmt->fetch();
    }
    $stmt->close();

    if ($currentTimezone === $timezone) {
        echo json_encode([
            'success' => true,
            'message' => 'Timezone sudah sama, tidak perlu diperbarui',
            'timezone' => $timezone
        ]);
        return;
    }
    
    $stmt = $con->prepare("UPDATE userx SET timezone = ? WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database error: ' . $con->error);
    }
    
    $stmt->bind_param("ss", $timezone, $username);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Timezone berhasil diperbarui',
                'timezone' => $timezone,
                'username' => $username
            ]);
        } else {
            throw new Exception('User tidak ditemukan atau timezone sama');
        }
    } else {
        throw new Exception('Gagal memperbarui timezone: ' . $stmt->error);
    }
    
    $stmt->close();
}
?>
