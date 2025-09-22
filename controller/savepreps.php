<?php
// controller/savepreps.php
session_start();
require_once '../include/config.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get form data
    $merchant = trim($_POST['merchant'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Basic validation
    if (strlen($merchant) < 3) {
        echo json_encode(['success' => false, 'message' => 'Merchant name minimal 3 karakter']);
        exit;
    }

    if (strlen($address) < 10) {
        echo json_encode(['success' => false, 'message' => 'Address minimal 10 karakter']);
        exit;
    }

    // Handle logo upload
    $logoPath = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logoPath = handleLogoUpload($_FILES['logo']);
        if ($logoPath === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload logo']);
            exit;
        }
    }

    // Update database
    $success = updateMerchantInfo($merchant, $address, $logoPath);

    if ($success) {
        // Set session data if needed
        $_SESSION['merchant_setup'] = true;
        $_SESSION['merchant_name'] = $merchant;

        echo json_encode([
            'success' => true,
            'message' => 'Merchant information saved successfully!',
            'redirect' => 'index.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save merchant information']);
    }

} catch (Exception $e) {
    error_log("Savepreps Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

/**
 * Handle logo upload - DISESUAIKAN DENGAN PATH DRAGON
 */
function handleLogoUpload($file) {
    // Validation
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    if ($file['size'] > $maxSize) {
        return false;
    }

    // SESUAI DENGAN LOKASI DRAGON: C:\xampp\htdocs\dragon\assets\images\logos
    $uploadDir = '../assets/images/logos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'logo_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Return relative path for database storage - SESUAI PATH DRAGON
        return 'assets/images/logos/' . $filename;
    }

    return false;
}

/**
 * Update merchant information in database
 */
function updateMerchantInfo($merchant, $address, $logoPath = null) {
    global $con;

    try {
        if (isset($_SESSION['username'])) {
            // Update existing user
            $username = $_SESSION['username'];

            if ($logoPath) {
                // Hapus logo lama dulu (optional)
                deleteOldLogo($username);

                $stmt = $con->prepare("
                    UPDATE userx 
                    SET merchand = ?, address = ?, logox = ?, updated_at = NOW() 
                    WHERE username = ?
                ");
                $stmt->bind_param("ssss", $merchant, $address, $logoPath, $username);
            } else {
                $stmt = $con->prepare("
                    UPDATE userx 
                    SET merchand = ?, address = ?, updated_at = NOW() 
                    WHERE username = ?
                ");
                $stmt->bind_param("sss", $merchant, $address, $username);
            }
        } else {
            // Setup pertama kali, insert admin baru
            $defaultUsername = 'admin_' . uniqid();
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);

            if ($logoPath) {
                $stmt = $con->prepare("
                    INSERT INTO userx (username, pass, merchand, address, logox, level, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'admin', 1, NOW())
                ");
                $stmt->bind_param("sssss", $defaultUsername, $defaultPassword, $merchant, $address, $logoPath);
            } else {
                $stmt = $con->prepare("
                    INSERT INTO userx (username, pass, merchand, address, level, status, created_at) 
                    VALUES (?, ?, ?, ?, 'admin', 1, NOW())
                ");
                $stmt->bind_param("ssss", $defaultUsername, $defaultPassword, $merchant, $address);
            }

            $_SESSION['username'] = $defaultUsername;
            $_SESSION['level'] = 'admin';
        }

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            logActivity('UPDATE_MERCHANT', "Updated merchant: $merchant");

            // Notifikasi selamat datang
            $username = $_SESSION['username'] ?? $defaultUsername ?? '';
            if ($username) {
                $stmtNotif = $con->prepare("
                    INSERT INTO notifications (userx, title, message, icon, created_at) 
                    VALUES (?, 'dragonplay.id', 'Selamat bergabung dan mulailah menyiapkan data untuk menggunakan sistem ini.', 'bx bx-info-circle', NOW())
                ");
                $stmtNotif->bind_param("s", $username);
                $stmtNotif->execute();
                $stmtNotif->close();
            }

            return true;
        }

        return false;

    } catch (Exception $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Simple activity logger
 */
function logActivity($action, $details) {
    $username = $_SESSION['username'] ?? 'system';
    $logFile = '../logs/merchant_' . date('Y-m') . '.log';

    // Create logs directory if not exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logEntry = date('Y-m-d H:i:s') . " | $username | $action | $details\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Get merchant info (helper function untuk digunakan di halaman lain)
 */
function getMerchantInfo($username) {
    global $con;

    try {
        $stmt = $con->prepare("SELECT merchand, address, logox FROM userx WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $stmt->close();
            return null;
        }

        $stmt->bind_result($merchand, $address, $logox);
        $stmt->fetch();
        $stmt->close();

        return [
            'merchand' => $merchand,
            'address' => $address,
            'logox' => $logox
        ];

    } catch (Exception $e) {
        error_log("Get Merchant Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if merchant setup is complete
 */
function isMerchantSetupComplete($username) {
    $merchantInfo = getMerchantInfo($username);

    return $merchantInfo &&
        !empty($merchantInfo['merchand']) &&
        !empty($merchantInfo['address']);
}

/**
 * Get full path untuk display logo - HELPER FUNCTION TAMBAHAN
 */
function getLogoUrl($logoPath) {
    if (empty($logoPath)) {
        return 'assets/images/default-logo.png'; // default logo
    }

    if (file_exists('../' . $logoPath)) {
        return $logoPath;
    }

    return 'assets/images/default-logo.png';
}

/**
 * Delete old logo when updating - HELPER FUNCTION TAMBAHAN
 */
function deleteOldLogo($username) {
    $merchantInfo = getMerchantInfo($username);

    if ($merchantInfo && !empty($merchantInfo['logox'])) {
        $oldLogoPath = '../' . $merchantInfo['logox'];
        if (file_exists($oldLogoPath)) {
            unlink($oldLogoPath);
        }
    }
}
