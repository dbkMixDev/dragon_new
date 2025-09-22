<?php
use Midtrans\Config;
use Midtrans\Transaction;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../regist/payment/Midtrans.php';
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';

Config::$serverKey = 'Mid-server-DXD4mYC-7COHSlgYJH5cGfqa';
Config::$isSanitized = true;
Config::$is3ds = true;

$json = file_get_contents("php://input");
$data = json_decode($json, true);

$order_id = $data['order_id'] ?? '';
$transaction_status = $data['transaction_status'] ?? '';
$fraud_status = $data['fraud_status'] ?? '';

if (!$order_id || !$transaction_status) {
    http_response_code(400);
    exit("Invalid payload");
}

// Cek transaksi sukses
if (in_array($transaction_status, ['settlement', 'capture']) && $fraud_status !== 'challenge') {
    $stmt = $con->prepare("SELECT * FROM transactions WHERE order_id = ?");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $trx = $result->fetch_assoc();

    if ($trx && $trx['status'] !== 'success') {
        // Update status transaksi
        $update = $con->prepare("UPDATE transactions SET status = 'success', updated_at = NOW() WHERE order_id = ?");
        $update->bind_param("s", $order_id);
        $update->execute();

        $email = $trx['email'];
        $full_name = $trx['full_name'];
        $package = $trx['package'] ?? 'starter';
        $license = 'LIC-' . strtoupper(substr(sha1(microtime(true) . rand()), 0, 6));
        $password = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $username = strtolower($email);
        $license_exp = date('Y-m-d', strtotime('+1 year'));

        // Cek apakah user sudah ada
        $check = $con->prepare("SELECT email FROM userx WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            // Insert userx
            if (strpos($email, 'emailhook') !== false) {
                echo json_encode(['success' => false, 'message' => 'Email tidak diizinkan']);
                exit;
            }

            $insert = $con->prepare("INSERT INTO userx (username, email, pass, level, license, license_exp) VALUES (?, ?, ?, 'admin', ?, ?)");
            $insert->bind_param("sssss", $username, $email, $hashed_password, $license, $license_exp);

            if ($insert->execute()) {
                // Insert tb_package jika belum ada
                $packageUnits = ['starter' => 5, 'business' => 15, 'professional' => 25, 'enterprise' => 40];
                $unit = $packageUnits[$package] ?? 5;

                $checkPkg = $con->prepare("SELECT COUNT(*) FROM tb_package WHERE id_package = ? AND username = ?");
                $checkPkg->bind_param("ss", $package, $email);
                $checkPkg->execute();
                $checkPkg->bind_result($pkgCount);
                $checkPkg->fetch();
                $checkPkg->close();

                if ($pkgCount == 0) {
                    $insPkg = $con->prepare("INSERT INTO tb_package (id_package, username, unit) VALUES (?, ?, ?)");
                    $insPkg->bind_param("ssi", $package, $email, $unit);
                    $insPkg->execute();
                    $insPkg->close();
                }

                // ===== KIRIM EMAIL DENGAN OPTIMASI ANTI-SPAM =====
                $mail = new PHPMailer(true);
                try {
                    // Server settings (TLS untuk better deliverability)
                    $mail->isSMTP();
                    $mail->Host = 'mail.dragonplay.id';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@dragonplay.id';
                    $mail->Password = SMTP_PASSWORD;
                    $mail->SMTPSecure = 'tls'; // Ganti dari 'ssl' ke 'tls'
                    $mail->Port = 587; // Ganti dari 465 ke 587

                    // Recipients dengan nama yang proper
                    $mail->setFrom('noreply@dragonplay.id', 'DragonPlay Team'); // Ganti 'System' jadi 'Team'
                    $mail->addAddress($email, $full_name); // Tambah nama recipient
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
                    $package_name = ucfirst($package);
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
                                    <p style='margin: 0 0 20px 0; font-size: 16px;'>Halo <strong>$full_name</strong>,</p>
                                    
                                    <p style='margin: 0 0 20px 0; line-height: 1.6;'>
                                        Terima kasih telah mempercayai DragonPlay sebagai partner bisnis Anda. 
                                        Pembayaran telah berhasil diproses dan akun premium Anda telah aktif.
                                    </p>
                                    
                                    <!-- Account Details Box -->
                                    <table style='width: 100%; background-color: #f8f9fa; border-radius: 8px; margin: 25px 0; border: 1px solid #e9ecef;' cellpadding='20'>
                                        <tr>
                                            <td>
                                                <h3 style='margin: 0 0 15px 0; color: #667eea; font-size: 18px;'>Detail Akun Anda</h3>
                                                <table style='width: 100%;'>
                                                    <tr><td style='padding: 8px 0; font-weight: bold; width: 35%;'>Username:</td><td style='padding: 8px 0; color: #667eea; font-weight: bold;'>$username</td></tr>
                                                    <tr><td style='padding: 8px 0; font-weight: bold;'>Password:</td><td style='padding: 8px 0; color: #667eea; font-weight: bold;'>$password</td></tr>
                                                    <tr><td style='padding: 8px 0; font-weight: bold;'>Paket:</td><td style='padding: 8px 0;'>$package_name</td></tr>
                                                    <tr><td style='padding: 8px 0; font-weight: bold;'>License:</td><td style='padding: 8px 0;'>$license</td></tr>
                                                    <tr><td style='padding: 8px 0; font-weight: bold;'>Berlaku Hingga:</td><td style='padding: 8px 0;'>$license_exp</td></tr>
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
                                            <td style='padding: 5px 0; line-height: 1.6;'>Explore fitur-fitur premium yang tersedia untuk paket $package_name</td>
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

Halo $full_name,

Terima kasih telah mempercayai DragonPlay sebagai partner bisnis Anda. 
Pembayaran telah berhasil diproses dan akun premium Anda telah aktif.

DETAIL AKUN ANDA:
Username: $username
Password: $password
Paket: $package_name
License: $license
Berlaku Hingga: $license_exp

PENTING: Simpan informasi login ini dengan aman dan jangan bagikan kepada orang lain.

LANGKAH SELANJUTNYA:
1. Login ke sistem DragonPlay menggunakan kredensial di atas
2. Explore fitur-fitur premium yang tersedia untuk paket $package_name
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
                    error_log("✓ Email berhasil dikirim ke $email untuk order $order_id (Anti-Spam Optimized)");

                } catch (Exception $e) {
                    error_log("✗ Gagal kirim email ke $email untuk order $order_id: " . $e->getMessage());

                    // Coba kirim ulang dengan konfigurasi alternatif (SSL fallback)
                    try {
                        $mail->clearAddresses();
                        $mail->addAddress($email, $full_name);
                        $mail->SMTPSecure = 'ssl';
                        $mail->Port = 465;
                        $mail->send();
                        error_log("✓ Email berhasil dikirim pada percobaan kedua ke $email untuk order $order_id (SSL Fallback)");
                    } catch (Exception $e2) {
                        error_log("✗ Gagal kirim email pada percobaan kedua ke $email untuk order $order_id: " . $e2->getMessage());
                    }
                }
            } else {
                error_log("✗ Gagal insert user untuk order $order_id: " . $con->error);
            }

            $insert->close();
        } else {
            error_log("⚠️ User dengan email $email sudah ada untuk order $order_id");
        }

        $check->close();
    } else {
        if (!$trx) {
            error_log("✗ Transaksi tidak ditemukan untuk order $order_id");
        } else {
            error_log("ℹ️ Transaksi $order_id sudah berstatus success sebelumnya");
        }
    }

    $stmt->close();
} else {
    error_log("ℹ️ Transaksi $order_id tidak dalam status settlement/capture atau ada fraud challenge");
}

http_response_code(200);
echo 'OK';