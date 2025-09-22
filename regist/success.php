<?php
session_start();
require_once 'config/midtrans.php';

// Ambil order_id dari parameter
$order_id = $_GET['order_id'] ?? '';
$payment_status = $_GET['payment'] ?? '';

// Jika tidak ada order_id, redirect ke home
if (empty($order_id)) {
    header('Location: index.php?error=invalid_access');
    exit;
}

// Inisialisasi variabel
$transaction = null;
$user_data = null;
$error_message = '';
$success_message = '';

try {
    // Koneksi database
    $pdo = getDbConnection();
    if (!$pdo) {
        throw new Exception('Koneksi database gagal');
    }

    // Ambil data transaksi
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$order_id]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        // Cek di session sebagai fallback
        if (
            isset($_SESSION['pending_registration']) &&
            $_SESSION['pending_registration']['order_id'] === $order_id
        ) {
            $sessionData = $_SESSION['pending_registration'];
            $user_data = [
                'email' => $sessionData['email'],
                'full_name' => $sessionData['username'],
                'phone' => $sessionData['notlp']
            ];
        } else {
            throw new Exception('Data transaksi tidak ditemukan');
        }
    } else {
        $user_data = [
            'email' => $transaction['email'],
            'full_name' => $transaction['full_name'],
            'phone' => $transaction['phone']
        ];
    }

    // Verifikasi status pembayaran dengan Midtrans jika diperlukan
    if ($transaction && $transaction['status'] === 'pending') {
        $verification_result = verifyPaymentStatus($order_id);
        if ($verification_result['status'] === 'success') {
            // Update status di database
            $stmt = $pdo->prepare("UPDATE transactions SET status = 'success', updated_at = NOW() WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $transaction['status'] = 'success';
        }
    }

    // Cek apakah user sudah terdaftar
    if ($transaction && $transaction['status'] === 'success') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$transaction['email']]);
        $user = $stmt->fetch();

        // if (!$user) {
        //     // Buat user baru jika belum ada
        //     $password = generateRandomPassword();
        //     $hashedPassword = hashPassword($password);

        //     $stmt = $pdo->prepare("
        //         INSERT INTO users (email, phone, full_name, password, membership_status, membership_expired, created_at) 
        //         VALUES (?, ?, ?, ?, 'active', DATE_ADD(NOW(), INTERVAL 1 YEAR), NOW())
        //     ");
        //     $stmt->execute([
        //         $transaction['email'], 
        //         $transaction['phone'], 
        //         $transaction['full_name'],
        //         $hashedPassword
        //     ]);

        //     $user_data['password'] = $password;
        //     $user_data['is_new_account'] = true;
        // } else {
        //     $user_data['is_new_account'] = false;
        // }
    }

    // Set success message
    if ($transaction) {
        switch ($transaction['status']) {
            case 'success':
                $success_message = 'Pembayaran berhasil! Membership Anda telah aktif.';
                break;
            case 'pending':
                $success_message = 'Pembayaran sedang diproses. Kami akan mengkonfirmasi dalam beberapa menit.';
                break;
            case 'failed':
                $error_message = 'Pembayaran gagal. Silakan coba lagi atau hubungi support.';
                break;
            default:
                $error_message = 'Status pembayaran tidak diketahui.';
        }
    }

    // Clear session data jika pembayaran berhasil
    if ($transaction && $transaction['status'] === 'success') {
        unset($_SESSION['pending_registration']);
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
    logError("Error in success.php", ['error' => $e->getMessage(), 'order_id' => $order_id]);
}

// Function untuk verifikasi status pembayaran ke Midtrans
function verifyPaymentStatus($order_id)
{
    try {
        $url = MidtransConfig::getBaseUrl() . '/status/' . $order_id;
        $serverKey = MidtransConfig::SERVER_KEY;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':')
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return [
                'status' => ($result['transaction_status'] === 'settlement' || $result['transaction_status'] === 'capture') ? 'success' : 'pending',
                'data' => $result
            ];
        }

        return ['status' => 'pending', 'data' => null];
    } catch (Exception $e) {
        return ['status' => 'error', 'data' => null];
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Payment Status | Dragon Play</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Payment status - Dragon Play membership" name="description" />
    <meta content="Dragon Play" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .success-animation {
            animation: bounceIn 1s ease-in-out;
        }

        .error-animation {
            animation: shake 0.82s cubic-bezier(.36, .07, .19, .97) both;
        }

        @keyframes bounceIn {

            0%,
            20%,
            40%,
            60%,
            80%,
            to {
                animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
            }

            0% {
                opacity: 0;
                transform: scale3d(0.3, 0.3, 0.3);
            }

            20% {
                transform: scale3d(1.1, 1.1, 1.1);
            }

            40% {
                transform: scale3d(0.9, 0.9, 0.9);
            }

            60% {
                opacity: 1;
                transform: scale3d(1.03, 1.03, 1.03);
            }

            80% {
                transform: scale3d(0.97, 0.97, 0.97);
            }

            to {
                opacity: 1;
                transform: scale3d(1, 1, 1);
            }
        }

        @keyframes shake {

            10%,
            90% {
                transform: translate3d(-1px, 0, 0);
            }

            20%,
            80% {
                transform: translate3d(2px, 0, 0);
            }

            30%,
            50%,
            70% {
                transform: translate3d(-4px, 0, 0);
            }

            40%,
            60% {
                transform: translate3d(4px, 0, 0);
            }
        }

        .status-card {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
            border: none;
            overflow: hidden;
        }

        .status-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .success-icon {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .error-icon {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        .pending-icon {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .info-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            border: none;
        }

        .feature-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-5px);
        }

        .countdown {
            font-family: 'Courier New', monospace;
            font-size: 1.2em;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>

<body>
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-6">

                    <?php if (!empty($success_message)): ?>
                        <!-- Success Card -->
                        <div class="card status-card success-animation">
                            <div class="card-body text-center p-5">
                                <div class="status-icon success-icon mb-4">
                                    <i class="fas fa-check text-white" style="font-size: 3rem;"></i>
                                </div>

                                <h2 class="text-success mb-4">
                                    <?php echo $transaction['status'] === 'success' ? '🎉 Pembayaran Berhasil!' : '⏳ Pembayaran Diproses'; ?>
                                </h2>

                                <div class="alert alert-success" role="alert">
                                    <h5 class="alert-heading">
                                        <?php echo $transaction['status'] === 'success' ? 'Selamat!' : 'Mohon Tunggu'; ?>
                                    </h5>
                                    <p class="mb-0"><?php echo htmlspecialchars($success_message); ?></p>
                                </div>

                                <!-- Transaction Details -->
                                <div class="info-card p-4 mb-4">
                                    <h5 class="text-dark mb-3">📋 Detail Transaksi</h5>
                                    <div class="row text-start">
                                        <div class="col-sm-6">
                                            <p class="mb-2"><strong>Order ID:</strong></p>
                                            <p class="mb-2"><strong>Email:</strong></p>
                                            <p class="mb-2"><strong>Nama:</strong></p>
                                            <p class="mb-0"><strong>Status:</strong></p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p class="mb-2"><code><?php echo htmlspecialchars($order_id); ?></code></p>
                                            <p class="mb-2"><?php echo htmlspecialchars($user_data['email']); ?></p>
                                            <p class="mb-2"><?php echo htmlspecialchars($user_data['full_name']); ?></p>
                                            <p class="mb-0">
                                                <span
                                                    class="badge bg-<?php echo $transaction['status'] === 'success' ? 'success' : 'warning'; ?> fs-6">
                                                    <?php echo ucfirst($transaction['status']); ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($transaction['status'] === 'success'): ?>
                                    <!-- Success Features -->
                                    <div class="row mb-4">
                                        <div class="col-4">
                                            <div class="feature-item">
                                                <i class="fas fa-crown text-warning mb-2" style="font-size: 2rem;"></i>
                                                <h6>Premium Access</h6>
                                                <small class="text-muted">Akses semua fitur premium</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="feature-item">
                                                <i class="fas fa-gamepad text-primary mb-2" style="font-size: 2rem;"></i>
                                                <h6>Full Games</h6>
                                                <small class="text-muted">Semua game tersedia</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="feature-item">
                                                <i class="fas fa-headset text-info mb-2" style="font-size: 2rem;"></i>
                                                <h6>24/7 Support</h6>
                                                <small class="text-muted">Dukungan penuh</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Email Info -->
                                    <div class="alert alert-info" role="alert">
                                        <h6 class="alert-heading">📧 Cek Email Anda</h6>
                                        <p class="mb-0">
                                            Detail akun dan password login telah dikirim ke
                                            <strong><?php echo htmlspecialchars($user_data['email']); ?></strong>
                                        </p>
                                        <small class="d-block mt-2 text-muted">
                                            Tidak ada email? Cek folder spam atau tunggu beberapa menit
                                        </small>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-grid gap-2">
                                        <a href="login.php" class="btn btn-success btn-lg">
                                            <i class="fas fa-sign-in-alt me-2"></i>
                                            Login ke Akun Anda
                                        </a>
                                        <a href="dashboard.php" class="btn btn-primary">
                                            <i class="fas fa-tachometer-alt me-2"></i>
                                            Ke Dashboard
                                        </a>
                                        <a href="index.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-home me-2"></i>
                                            Kembali ke Beranda
                                        </a>
                                    </div>

                                <?php else: ?>
                                    <!-- Pending Status -->
                                    <div class="alert alert-warning" role="alert">
                                        <h6 class="alert-heading">⏳ Status: Menunggu Konfirmasi</h6>
                                        <p class="mb-2">Pembayaran Anda sedang diverifikasi. Proses ini biasanya memakan waktu
                                            1-5 menit.</p>
                                        <div class="countdown" id="countdown">Refresh otomatis dalam: <span id="timer">30</span>
                                            detik</div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button class="btn btn-warning btn-lg" onclick="location.reload()">
                                            <i class="fas fa-sync me-2"></i>
                                            Refresh Status
                                        </button>
                                        <a href="index.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-home me-2"></i>
                                            Kembali ke Beranda
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php elseif (!empty($error_message)): ?>
                        <!-- Error Card -->
                        <div class="card status-card error-animation">
                            <div class="card-body text-center p-5">
                                <div class="status-icon error-icon mb-4">
                                    <i class="fas fa-times text-white" style="font-size: 3rem;"></i>
                                </div>

                                <h2 class="text-danger mb-4">❌ Terjadi Kesalahan</h2>

                                <div class="alert alert-danger" role="alert">
                                    <h5 class="alert-heading">Maaf!</h5>
                                    <p class="mb-0"><?php echo htmlspecialchars($error_message); ?></p>
                                </div>

                                <div class="d-grid gap-2">
                                    <a href="register.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-redo me-2"></i>
                                        Coba Lagi
                                    </a>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-home me-2"></i>
                                        Kembali ke Beranda
                                    </a>
                                    <a href="mailto:support@dragonplay.com" class="btn btn-outline-info">
                                        <i class="fas fa-envelope me-2"></i>
                                        Hubungi Support
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Support Info -->
                    <div class="mt-4 text-center">
                        <p class="text-muted">
                            Butuh bantuan?
                            <a href="mailto:support@dragonplay.com" class="text-decoration-none">
                                <i class="fas fa-envelope me-1"></i>
                                Hubungi Support
                            </a>
                            |
                            <a href="https://wa.me/6281234567890" class="text-decoration-none" target="_blank">
                                <i class="fab fa-whatsapp me-1"></i>
                                WhatsApp
                            </a>
                        </p>
                    </div>

                    <!-- Footer -->
                    <div class="mt-3 text-center">
                        <p class="text-muted">
                            ©
                            <script>document.write(new Date().getFullYear())</script>
                            Dragon Play. Best Project <i class="mdi mdi-heart text-danger"></i> by dbk.dev
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
        $(document).ready(function () {
            // Auto refresh untuk status pending
            <?php if ($transaction && $transaction['status'] === 'pending'): ?>
                let countdown = 30;
                const timer = setInterval(function () {
                    countdown--;
                    $('#timer').text(countdown);

                    if (countdown <= 0) {
                        clearInterval(timer);
                        location.reload();
                    }
                }, 1000);

                // Check status setiap 10 detik
                const statusCheck = setInterval(function () {
                    $.ajax({
                        url: 'controller/check_status.php',
                        method: 'POST',
                        data: { order_id: '<?php echo htmlspecialchars($order_id); ?>' },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                clearInterval(statusCheck);
                                clearInterval(timer);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Pembayaran Berhasil!',
                                    text: 'Membership Anda telah aktif!',
                                    showConfirmButton: false,
                                    timer: 2000
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        }
                    });
                }, 10000);
            <?php endif; ?>

            // Animate elements
            setTimeout(function () {
                $('.status-card').addClass('animate__animated animate__fadeInUp');
            }, 200);
        });
    </script>
</body>

</html>