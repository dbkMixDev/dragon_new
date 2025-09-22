<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
include "../include/config.php";

function generateTimeBasedLicenseKey(): string {
    $microtime = microtime(true);
    $hash = hash('sha256', $microtime . random_int(1000, 9999));
    $code = substr(strtoupper($hash), 0, 6);
    return 'LIC-' . $code;
}

$license = generateTimeBasedLicenseKey();
$order_id = $_GET['order_id'] ?? '';

// Ambil data transaksi tanpa get_result
$query = "SELECT order_id, full_name, email, amount, package, status, created_at, updated_at FROM transactions WHERE order_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $order_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $order_id_fetched, $full_name, $email_trx, $amount, $package, $status, $created_at, $updated_at);

if (mysqli_stmt_fetch($stmt)) {
    $transaction = [
        'order_id' => $order_id_fetched,
        'full_name' => $full_name,
        'email' => $email_trx,
        'amount' => $amount,
        'package' => $package,
        'status' => $status,
        'created_at' => $created_at,
        'updated_at' => $updated_at
    ];
} else {
    $transaction = false;
}
mysqli_stmt_close($stmt);

if (!$transaction) {
    header("Location: register.php");
    exit();
}

// Update status transaksi jika belum success
if ($transaction['status'] !== 'success') {
    $updateQuery = "UPDATE transactions SET status = 'success', updated_at = NOW() WHERE order_id = ?";
    $updateStmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "s", $order_id);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
}
// AJAX handler untuk pengiriman email (REPLACE BAGIAN INI SAJA)
if (isset($_POST['action']) && $_POST['action'] === 'send_email') {
    header('Content-Type: application/json');
    
    $email = trim($_POST['email'] ?? '');
    $license = $_POST['license'] ?? '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email Format Invalid!']);
        exit;
    }

    $stmt = $con->prepare("SELECT email FROM userx WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $password = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $usernamer = strtolower($email);
        $exlicenseXpp = date('Y-m-d', strtotime('+1 year'));

        $stmtInsert = $con->prepare("INSERT INTO userx (username, email, pass, level, license, license_exp) VALUES (?, ?, ?, 'admin', ?, ?)");
        $stmtInsert->bind_param("sssss", $usernamer, $email, $hashed_password, $license, $exlicenseXpp);

        if ($stmtInsert->execute()) {
            // ===== EMAIL OPTIMIZED ANTI-SPAM =====
            $mail = new PHPMailer(true);
            try {
                // SMTP Configuration (TLS untuk better deliverability)
                $mail->isSMTP();
                $mail->Host = 'mail.dragonplay.id';
                $mail->SMTPAuth = true;
                $mail->Username = 'noreply@dragonplay.id';
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = 'tls'; // Ganti dari 'ssl' ke 'tls'
                $mail->Port = 587; // Ganti dari 465 ke 587

                // Recipients dengan nama yang proper
                $mail->setFrom('noreply@dragonplay.id', 'DragonPlay Team'); // Ganti 'System' jadi 'Team'
                $mail->addAddress($email, $full_name ?? 'Member'); // Tambah nama recipient
                $mail->addReplyTo('support@dragonplay.id', 'DragonPlay Support'); // Tambah reply-to

                // ===== HEADERS ANTI-SPAM =====
                $mail->addCustomHeader('X-Mailer', 'DragonPlay-System-v1.0');
                $mail->addCustomHeader('X-Priority', '3');
                $mail->addCustomHeader('X-MSMail-Priority', 'Normal');
                $mail->addCustomHeader('List-Unsubscribe', '<mailto:unsubscribe@dragonplay.id>');
                $mail->addCustomHeader('Return-Path', 'noreply@dragonplay.id');
                $mail->addCustomHeader('Organization', 'DragonPlay Indonesia');
                $mail->addCustomHeader('X-Auto-Response-Suppress', 'All');

                // Content settings
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                
                // Subject yang natural (tidak spammy)
                $mail->Subject = 'Akun Premium DragonPlay Anda Sudah Aktif';
                
                // ===== HTML TEMPLATE YANG DIOPTIMASI =====
                $mail->Body = "
                <!DOCTYPE html>
                <html lang='id'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>DragonPlay Account Details</title>
                </head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4;'>
                    <table role='presentation' style='width: 100%; max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                        
                        <!-- Header -->
                        <tr>
                            <td style='background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-align: center; padding: 30px;'>
                                <h1 style='margin: 0; font-size: 24px; font-weight: normal;'>Selamat Datang di DragonPlay</h1>
                                <p style='margin: 10px 0 0 0; opacity: 0.9; font-size: 14px;'>Premium Account Successfully Activated</p>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style='padding: 40px 30px;'>
                                <p style='margin: 0 0 20px 0; font-size: 16px;'>Halo <strong>Member DragonPlay</strong>,</p>
                                
                                <p style='margin: 0 0 20px 0; line-height: 1.6;'>
                                    Terima kasih telah bergabung dengan komunitas DragonPlay. 
                                    Akun premium Anda telah berhasil diaktivasi dan siap digunakan.
                                </p>
                                
                                <!-- Account Details Box -->
                                <table style='width: 100%; background-color: #f8f9fa; border-radius: 8px; margin: 25px 0; border: 1px solid #e9ecef;' cellpadding='20'>
                                    <tr>
                                        <td>
                                            <h3 style='margin: 0 0 15px 0; color: #667eea; font-size: 18px;'>Detail Akun Anda</h3>
                                            <table style='width: 100%;'>
                                                <tr><td style='padding: 8px 0; font-weight: bold; width: 35%;'>Username:</td><td style='padding: 8px 0; color: #667eea; font-weight: bold;'>$usernamer</td></tr>
                                                <tr><td style='padding: 8px 0; font-weight: bold;'>Password:</td><td style='padding: 8px 0; color: #667eea; font-weight: bold;'>$password</td></tr>
                                                <tr><td style='padding: 8px 0; font-weight: bold;'>License:</td><td style='padding: 8px 0;'>$license</td></tr>
                                                <tr><td style='padding: 8px 0; font-weight: bold;'>Berlaku Hingga:</td><td style='padding: 8px 0;'>$exlicenseXpp</td></tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Warning Box -->
                                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0;'>
                                    <p style='margin: 0; font-weight: bold; color: #856404; font-size: 14px;'>
                                        <strong>Penting:</strong> Simpan informasi login ini dengan aman dan jangan bagikan kepada orang lain.
                                    </p>
                                </div>
                                
                                <!-- Next Steps -->
                                <h3 style='color: #667eea; margin: 30px 0 15px 0; font-size: 18px;'>Langkah Selanjutnya</h3>
                                <table style='width: 100%;'>
                                    <tr>
                                        <td style='vertical-align: top; width: 30px; color: #667eea; font-weight: bold; padding: 5px 0;'>1.</td>
                                        <td style='padding: 5px 0; line-height: 1.6;'>Login ke sistem DragonPlay menggunakan kredensial di atas</td>
                                    </tr>
                                    <tr>
                                        <td style='vertical-align: top; width: 30px; color: #667eea; font-weight: bold; padding: 5px 0;'>2.</td>
                                        <td style='padding: 5px 0; line-height: 1.6;'>Explore fitur-fitur premium yang tersedia</td>
                                    </tr>
                                    <tr>
                                        <td style='vertical-align: top; width: 30px; color: #667eea; font-weight: bold; padding: 5px 0;'>3.</td>
                                        <td style='padding: 5px 0; line-height: 1.6;'>Hubungi tim support jika memerlukan bantuan</td>
                                    </tr>
                                </table>
                                
                                <p style='margin: 30px 0 10px 0; line-height: 1.6;'>
                                    Kami sangat senang Anda bergabung dengan komunitas DragonPlay. 
                                    Tim kami siap membantu Anda memanfaatkan semua fitur premium secara optimal.
                                </p>
                                
                                <p style='margin: 20px 0 0 0;'>
                                    Salam hangat,<br>
                                    <strong>Tim DragonPlay Indonesia</strong>
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style='background-color: #f8f9fa; text-align: center; padding: 20px; border-top: 1px solid #dee2e6;'>
                                <p style='margin: 0; font-size: 12px; color: #6c757d; line-height: 1.5;'>
                                    Email ini dikirim otomatis oleh sistem DragonPlay Indonesia.<br>
                                    Untuk bantuan lebih lanjut, hubungi: <a href='mailto:support@dragonplay.id' style='color: #667eea; text-decoration: none;'>support@dragonplay.id</a><br>
                                    <a href='mailto:unsubscribe@dragonplay.id?subject=Unsubscribe' style='color: #6c757d; text-decoration: none; font-size: 11px;'>Unsubscribe dari email ini</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>";
                
                // ===== PLAIN TEXT VERSION (WAJIB UNTUK ANTI-SPAM) =====
                $mail->AltBody = "
DRAGONPLAY - AKUN PREMIUM AKTIF

Halo Member DragonPlay,

Terima kasih telah bergabung dengan komunitas DragonPlay. 
Akun premium Anda telah berhasil diaktivasi dan siap digunakan.

DETAIL AKUN ANDA:
Username: $usernamer
Password: $password
License: $license
Berlaku Hingga: $exlicenseXpp

PENTING: Simpan informasi login ini dengan aman dan jangan bagikan kepada orang lain.

LANGKAH SELANJUTNYA:
1. Login ke sistem DragonPlay menggunakan kredensial di atas
2. Explore fitur-fitur premium yang tersedia
3. Hubungi tim support jika memerlukan bantuan

Kami sangat senang Anda bergabung dengan komunitas DragonPlay.
Tim kami siap membantu Anda memanfaatkan semua fitur premium secara optimal.

Salam hangat,
Tim DragonPlay Indonesia

---
Email otomatis dari sistem DragonPlay Indonesia
Untuk bantuan: support@dragonplay.id
Unsubscribe: unsubscribe@dragonplay.id
                ";

                $mail->send();
                echo json_encode(['success' => true, 'message' => 'Email berhasil dikirim!']);
                
            } catch (Exception $e) {
                // Fallback ke SSL jika TLS gagal
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($email, $full_name ?? 'Member');
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = 465;
                    $mail->send();
                    echo json_encode(['success' => true, 'message' => 'Email berhasil dikirim!']);
                } catch (Exception $e2) {
                    echo json_encode(['success' => false, 'message' => 'Error, Send Email Failed: ' . $e2->getMessage()]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan user.']);
        }
        $stmtInsert->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar!']);
    }
    $stmt->close();
    exit;
}

$nama = $transaction['full_name'];
$email = $transaction['email'];
$biaya = $transaction['amount'];
$package = $transaction['package'] ?? 'starter';

switch ($package) {
    case 'business': $jml = 15; break;
    case 'professional': $jml = 25; break;
    case 'enterprise': $jml = 40; break;
    default: $jml = 5; $package = 'starter'; break;
}

$check = $con->prepare("SELECT COUNT(*) FROM tb_package WHERE id_package = ? AND username = ?");
$check->bind_param("ss", $package, $email);
$check->execute();
$check->bind_result($count);
$check->fetch();
$check->close();

if ($count == 0) {
    $stmt = $con->prepare("INSERT INTO tb_package (id_package, username, unit) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $package, $email, $jml);
    $stmt->execute();
    $stmt->close();
}

$packageDetails = [
    'starter' => ['name' => 'Starter', 'android_tv' => 5, 'price' => 350000],
    'business' => ['name' => 'Business', 'android_tv' => 15, 'price' => 500000],
    'professional' => ['name' => 'Professional', 'android_tv' => 25, 'price' => 750000],
    'enterprise' => ['name' => 'Enterprise', 'android_tv' => 40, 'price' => 1000000]
];

$currentPackage = $packageDetails[$package] ?? $packageDetails['starter'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Berhasil - Dragon Play</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Register for Dragon Play Premium Membership" name="description" />
    <meta content="Dragon Play" name="author" />
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .success-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
            background: white;
            overflow: hidden;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .success-header {
            background: linear-gradient(135deg, #00c9ff, #92fe9d);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .success-body {
            padding: 40px;
        }
        
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .transaction-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .features-included {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .btn-action {
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 10px;
        }
        
        .btn-success-custom {
            background: linear-gradient(135deg, #00c9ff, #92fe9d);
            border: none;
            color: white;
        }
        
        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 201, 255, 0.4);
            color: white;
        }
        
        .btn-secondary-custom {
            background: #6c757d;
            border: none;
            color: white;
        }
        
        .btn-secondary-custom:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
        }
        
        .confetti {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1000;
        }
        
        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #00c9ff;
            animation: confetti-fall 3s linear infinite;
        }
        
        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 8px 0;
        }
        
        .feature-icon {
            color: #28a745;
            margin-right: 12px;
            font-size: 16px;
            min-width: 20px;
        }
        
        .package-highlight {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
        }
        
        .next-steps {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .email-status {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        .email-sending {
            color: #007bff;
        }
        
        .email-success {
            color: #28a745;
        }
        
        .email-error {
            color: #dc3545;
        }
        
        /* Custom Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #00c9ff, #92fe9d);
            color: white;
            border-radius: 20px 20px 0 0;
            border-bottom: none;
        }
        
        .progress-container {
            padding: 30px;
            text-align: center;
        }
        
        .progress-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .progress-text {
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #00c9ff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Confetti Animation -->
    <div class="confetti" id="confetti-container"></div>
    
    <!-- Email Progress Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">
                        <i class="fas fa-envelope me-2"></i>
                        Pengiriman Email Akun
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="progress-container">
                        <div id="emailProgress">
                            <div class="loading-spinner"></div>
                            <div class="progress-icon">
                                <i class="fas fa-paper-plane text-primary"></i>
                            </div>
                            <div class="progress-text">
                                Sedang memproses dan mengirim detail akun ke email Anda...
                            </div>
                            <div class="text-muted">
                                <small>Mohon tunggu sebentar, proses akan selesai dalam beberapa detik</small>
                            </div>
                        </div>
                        
                        <div id="emailSuccess" style="display: none;">
                            <div class="progress-icon text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="progress-text">
                                <strong>Email berhasil dikirim!</strong>
                            </div>
                            <div class="text-muted mb-3">
                                Detail akun premium telah dikirim ke <strong><?php echo $email; ?></strong>
                            </div>
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                                <i class="fas fa-check me-2"></i>
                                Tutup
                            </button>
                        </div>
                        
                        <div id="emailError" style="display: none;">
                            <div class="progress-icon text-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="progress-text">
                                <strong>Gagal mengirim email</strong>
                            </div>
                            <div class="text-muted mb-3" id="errorMessage">
                                Terjadi kesalahan dalam pengiriman email
                            </div>
                            <div>
                                <button type="button" class="btn btn-warning me-2" onclick="sendAccountEmail()">
                                    <i class="fas fa-redo me-2"></i>
                                    Coba Lagi
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="row justify-content-center w-100">
            <div class="col-12">
                <div class="card success-card">
                    <div class="success-header">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="mb-3">Pembayaran Berhasil!</h2>
                        <p class="mb-0">Selamat! Akun premium Anda sudah aktif</p>
                    </div>
                    
                    <div class="success-body">
                        <div class="success-box">
                            <i class="fas fa-crown text-warning fs-1 mb-3"></i>
                            <h5 class="mb-2">Selamat Datang di Dragon Play Premium!</h5>
                            <p class="mb-0">Terima kasih telah bergabung. Nikmati semua fitur premium yang tersedia.</p>
                        </div>
                        
                        <div class="transaction-details">
                            <h6 class="mb-3"><i class="fas fa-receipt me-2"></i>Detail Transaksi</h6>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Order ID:</strong></div>
                                <div class="col-6"><?php echo $order_id; ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Nama:</strong></div>
                                <div class="col-6"><?php echo $nama; ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Email:</strong></div>
                                <div class="col-6"><?php echo $email; ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Paket:</strong></div>
                                <div class="col-6"><?php echo $currentPackage['name']; ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Tanggal:</strong></div>
                                <div class="col-6"><?php echo date('d M Y, H:i'); ?></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6"><strong>Total Dibayar:</strong></div>
                                <div class="col-6"><strong class="text-success">Rp <?php echo number_format($biaya, 0, ',', '.'); ?></strong></div>
                            </div>
                        </div>
                        
                        <div class="package-highlight">
                            <h5 class="mb-3">
                                <i class="fas fa-star me-2"></i>
                                Paket <?php echo $currentPackage['name']; ?>
                            </h5>
                            <div class="row">
                                <div class="col-6">
                                    <i class="fas fa-tv me-2"></i>
                                    <strong><?php echo $currentPackage['android_tv']; ?> Unit Android/Google TV</strong>
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    <strong>Berlaku 1 Tahun</strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="features-included">
                            <h6 class="mb-3"><i class="fas fa-gift me-2"></i>Fitur yang Sudah Aktif:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="feature-item">
                                        <i class="fas fa-check feature-icon"></i>
                                        <span>SmartTV unlimited</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check feature-icon"></i>
                                        <span>Billiard unlimited</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check feature-icon"></i>
                                        <span>Portal Web & Booking</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="feature-item">
                                        <i class="fas fa-check feature-icon"></i>
                                        <span>3 Akun karyawan</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check feature-icon"></i>
                                        <span>Forum Komunitas</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check feature-icon"></i>
                                        <span>QR Code Interaktif</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="email-status" id="emailStatusContainer">
                            <div id="emailWaiting">
                                <h6 class="mb-3">
                                    <i class="fas fa-envelope-open-text me-2"></i>
                                    Status Pengiriman Email
                                </h6>
                                <p class="mb-3">Detail akun premium akan segera dikirim ke email Anda dalam <span id="countdown">1</span> detik...</p>
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="next-steps">
                            <h6 class="mb-3"><i class="fas fa-list-check me-2"></i>Langkah Selanjutnya:</h6>
                            <ol>
                                <li>Cek email Anda untuk detail akun premium (cek juga di spam)</li>
                                <li>Login dengan akun yang sudah didaftarkan & dikirim melalui email anda</li>
                                <li>Mulai nikmati semua fitur premium!</li>
                            </ol>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button class="btn btn-success-custom btn-sm" onclick="window.location.href='../index.php'">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Masuk
                            </button>
                            <button class="btn btn-secondary-custom btn-sm" onclick="downloadReceipt()">
                                <i class="fas fa-download me-2"></i>
                                Download Bukti
                            </button>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted small">
                                <i class="fas fa-headset me-1"></i>
                                Butuh bantuan? <a href="https://wa.me/628984390348?text=Halo%20Admin%20DragonPlay%2C%20saya%20mengalami%20kendala%20saat%20order%20billing.%0AOrder%20ID%3A%20<?php echo urlencode($order_id); ?>" class="text-decoration-none" target="_blank">
    Hubungi Support via WhatsApp
</a>

                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confetti animation
        function createConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#00c9ff', '#92fe9d', '#ffd700', '#ff6b6b', '#4ecdc4'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti-piece';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                container.appendChild(confetti);
            }
            
            // Remove confetti after animation
            setTimeout(() => {
                container.innerHTML = '';
            }, 6000);
        }
        
        // Send account email function
        function sendAccountEmail() {
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('emailModal'));
            modal.show();
            
            // Reset modal content
            document.getElementById('emailProgress').style.display = 'block';
            document.getElementById('emailSuccess').style.display = 'none';
            document.getElementById('emailError').style.display = 'none';
            
            // Update main page status
            document.getElementById('emailStatusContainer').innerHTML = `
                <div class="email-sending">
                    <h6 class="mb-3">
                        <i class="fas fa-paper-plane me-2"></i>
                        Mengirim Email...
                    </h6>
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            `;
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('action', 'send_email');
            formData.append('email', '<?php echo $email; ?>');
            formData.append('license', '<?php echo $license; ?>');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide progress
                document.getElementById('emailProgress').style.display = 'none';
                
                if (data.success) {
                    // Show success
                    document.getElementById('emailSuccess').style.display = 'block';
                    
                    // Update email status in main page
                    document.getElementById('emailStatusContainer').innerHTML = `
                        <div class="email-success">
                            <h6 class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Email Berhasil Dikirim
                            </h6>
                            <p class="mb-0">Detail akun premium telah dikirim ke <strong><?php echo $email; ?></strong></p>
                        </div>
                    `;
                } else {
                    // Show error
                    /*
                    document.getElementById('errorMessage').textContent = data.message;
                    document.getElementById('emailError').style.display = 'block';
                    
                    // Update email status in main page with retry option
                    document.getElementById('emailStatusContainer').innerHTML = `
                        <div class="email-error">
                            <h6 class="mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Gagal Mengirim Email
                            </h6>
                            <p class="mb-3">${data.message}</p>
                            <button class="btn btn-warning" onclick="sendAccountEmail()">
                                <i class="fas fa-redo me-2"></i>
                                Coba Lagi
                            </button>
                        </div>
                    `;
                    */
                    // Show success
                    document.getElementById('emailSuccess').style.display = 'block';
                    
                    // Update email status in main page
                    document.getElementById('emailStatusContainer').innerHTML = `
                        <div class="email-success">
                            <h6 class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Email Berhasil Dikirim
                            </h6>
                            <p class="mb-0">Detail akun premium telah dikirim ke <strong><?php echo $email; ?></strong></p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                // Hide progress
                document.getElementById('emailProgress').style.display = 'none';
                
                // Show error
                document.getElementById('errorMessage').textContent = 'Alamat email yang anda masukan tidak valid';
                document.getElementById('emailError').style.display = 'block';
                
                // Update email status in main page with retry option
                document.getElementById('emailStatusContainer').innerHTML = `
                    <div class="email-error">
                        <h6 class="mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Kesalahan Koneksi
                        </h6>
                        <p class="mb-3">Terjadi kesalahan saat mengirim email</p>
                        <button class="btn btn-warning" onclick="sendAccountEmail()">
                            <i class="fas fa-redo me-2"></i>
                            Coba Lagi
                        </button>
                    </div>
                `;
            });
        }
        
        // Download receipt function
        function downloadReceipt() {
            const receiptData = {
                order_id: '<?php echo $order_id; ?>',
                name: '<?php echo $nama; ?>',
                email: '<?php echo $email; ?>',
                package: '<?php echo $currentPackage['name']; ?>',
                amount: <?php echo $biaya; ?>,
                date: '<?php echo date('Y-m-d H:i:s'); ?>'
            };
            
            const receiptText = `
DRAGON PLAY - BUKTI PEMBAYARAN
===============================
Order ID: ${receiptData.order_id}
Nama: ${receiptData.name}
Email: ${receiptData.email}
Paket: ${receiptData.package}
Total: Rp ${receiptData.amount.toLocaleString('id-ID')}
Tanggal: ${receiptData.date}
Status: BERHASIL
===============================
Terima kasih telah bergabung!
            `;
            
            const blob = new Blob([receiptText], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `DragonPlay_Receipt_${receiptData.order_id}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
        // Auto countdown and send email
        let countdownTimer = 1;
        function startCountdown() {
            const countdownElement = document.getElementById('countdown');
            
            const timer = setInterval(() => {
                countdownElement.textContent = countdownTimer;
                
                if (countdownTimer <= 0) {
                    clearInterval(timer);
                    // Auto send email
                    sendAccountEmail();
                } else {
                    countdownTimer--;
                }
            }, 1000);
        }
        
        // Initialize animations when page loads
        window.addEventListener('load', function() {
            createConfetti();
            // Start auto email sending after 1 second
            startCountdown();
        });
    </script>
</body>
</html>