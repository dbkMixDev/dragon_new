<?php
session_start(); // Pastikan session start di paling atas

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../include/config.php';
require_once '../include/crypto.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error2'] = "Email Format Invalid!.";
        header("Location: ../login.php");
        exit;
    }

    // Enkripsi email untuk URL
    $encrypted = encrypt($email);
    $urlSafeEncrypted = urlencode($encrypted);

    // Cek apakah email ada di database tanpa get_result()
    $stmt = $con->prepare("SELECT * FROM userx WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error2'] = "Email Not Found! Your Not Member.";
        header("Location: ../auth-recoverpw.php");
        exit;
    }

    $token = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT); // 4 digit OTP

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'mail.dragonplay.id';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@dragonplay.id';
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('noreply@dragonplay.id', 'DragonPlay System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Kode Verifikasi Anda';
        $mail->Body    = "Halo,<br><br>Kode verifikasi Anda adalah: <b>$token</b><br><br>Jangan bagikan kepada siapa pun.<br><br>- DragonPlay";

        $mail->send();

        // Simpan token ke database dengan ON DUPLICATE KEY UPDATE
        $stmt = $con->prepare("
            INSERT INTO recoverpw (email, token, `update`) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE 
                token = VALUES(token), 
                `update` = NOW()
        ");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();

        // Redirect ke halaman token dengan parameter terenkripsi
        echo "<script>window.location.href = '../auth-token.php?q=$urlSafeEncrypted';</script>";
        exit;

    } catch (Exception $e) {
        $_SESSION['error2'] = "Error, Send Email Failed.";
        header("Location: ../login.php");
        exit;
    }
}
?>
