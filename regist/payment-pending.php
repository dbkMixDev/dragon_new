<?php
session_start();
include "../include/config.php";

$order_id = $_GET['order_id'] ?? '';

// Get transaction details
$query = "SELECT * FROM transactions WHERE order_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaction = mysqli_fetch_assoc($result);

if (!$transaction) {
    header("Location: register.php");
    exit();
}

$nama = $transaction['full_name'];
$email = $transaction['email'];
$biaya = $transaction['amount'];
$package = $transaction['package'] ?? 'starter';
$payment_type = strtoupper(str_replace('_va', '', $transaction['payment_type']));
$va_number = $transaction['va_number'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Tertunda - Dragon Play</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
      <meta content="Register for Dragon Play Premium Membership" name="description" />
    <meta content="Dragon Play" name="author" />
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <style>
        body {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .status-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
            background: white;
            overflow: hidden;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .status-header {
            background: linear-gradient(135deg, #ff9a56, #ffad56);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .status-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .status-body {
            padding: 40px;
        }
        
        .info-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .transaction-details {
            background: #f8f9fa;
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
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #ff9a56, #ffad56);
            border: none;
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 154, 86, 0.4);
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
        
        .countdown-timer {
            font-size: 24px;
            font-weight: bold;
            color: #ff9a56;
            text-align: center;
            margin: 20px 0;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .payment-method {
            text-align: center;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: #ff9a56;
            background: #fff8f0;
        }
        
        .steps-list {
            counter-reset: step-counter;
            list-style: none;
            padding: 0;
        }
        
        .steps-list li {
            counter-increment: step-counter;
            margin: 15px 0;
            padding-left: 40px;
            position: relative;
        }
        
        .steps-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            background: #ff9a56;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="row justify-content-center w-100">
            <div class="col-12">
                <div class="card status-card">
                    <div class="status-header">
                        <div class="status-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h2 class="mb-3">Pembayaran Tertunda</h2>
                        <p class="mb-0">Menunggu Konfirmasi Pembayaran</p>
                    </div>
                    
                    <div class="status-body">
                        <div class="info-box">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle text-warning fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-1">Pembayaran Anda Sedang Diproses</h6>
                                    <p class="mb-0">Silakan selesaikan pembayaran sesuai metode yang Anda pilih. Status akan diperbarui otomatis setelah pembayaran dikonfirmasi.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="transaction-details">
                            <h6 class="mb-3"><i class="fas fa-receipt me-2"></i>Detail Transaksi</h6>

                            <div class="row mb-2">
                                <div class="col-6"><strong>Order ID:</strong></div>
                                <div class="col-6"><?php echo $order_id; ?></div>
                            </div>
                             <div class="row mb-2">
                                <div class="col-6"><strong>Bank:</strong></div>
                                <div class="col-6"><?php echo $payment_type; ?></div>
                            </div>
                             <div class="row mb-2">
                                <div class="col-6"><strong>No. VA:</strong></div>
                                <div class="col-6"><?php echo $va_number; ?></div>
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
                                <div class="col-6"><?php echo ucfirst($package); ?></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6"><strong>Total Bayar:</strong></div>
                                <div class="col-6"><strong class="text-primary">Rp <?php echo number_format($biaya, 0, ',', '.'); ?></strong></div>
                            </div>
                        </div>
                        
                        <div class="countdown-timer">
                            <i class="fas fa-hourglass-half me-2"></i>
                            <span id="countdown">24:00:00</span>
                        </div>
                        <p class="text-center text-muted">Batas waktu pembayaran</p>
                        
                        <div class="info-box">
                            <h6 class="mb-3"><i class="fas fa-list-check me-2"></i>Langkah Selanjutnya:</h6>
                            <ol class="steps-list">
                                <li>Selesaikan pembayaran sesuai metode yang dipilih</li>
                                <li>Simpan bukti pembayaran/screenshot</li>
                                <li>Tunggu konfirmasi otomatis (1-24 jam)</li>
                                <li>Akses premium akan aktif setelah konfirmasi</li>
                            </ol>
                        </div>
                        
                        <div class="payment-methods">
                            <div class="payment-method">
                                <i class="fas fa-university text-primary fs-3"></i>
                                <p class="mt-2 mb-0 small">Bank Transfer</p>
                            </div>
                            <div class="payment-method">
                                <i class="fas fa-credit-card text-success fs-3"></i>
                                <p class="mt-2 mb-0 small">Credit Card</p>
                            </div>
                            <div class="payment-method">
                                <i class="fas fa-mobile-alt text-info fs-3"></i>
                                <p class="mt-2 mb-0 small">E-Wallet</p>
                            </div>
                            <div class="payment-method">
                                <i class="fas fa-store text-warning fs-3"></i>
                                <p class="mt-2 mb-0 small">Convenience Store</p>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button class="btn btn-primary-custom btn-lg" onclick="checkPaymentStatus()">
                                <i class="fas fa-sync me-2"></i>
                                Cek Status Pembayaran
                            </button>
                            <button class="btn btn-secondary-custom btn-lg" onclick="window.location.href='../'">
                                <i class="fas fa-arrow-left me-2"></i>
                                Kembali ke Beranda
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p class="text-muted small">
                                <i class="fas fa-headset me-1"></i>
                                Butuh bantuan? <a href="https://wa.me/628984390348?text=Halo%20admin,%20saya%20ada%20pertanyaan%20mengenai%20pembayaran%20membership%20Dragon%20Play." 
   class="text-decoration-none" 
   target="_blank">
   Hubungi Admin via WhatsApp
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
        // Countdown timer (24 hours)
        let timeLeft = 24 * 60 * 60; // 24 hours in seconds
        
        function updateCountdown() {
            const hours = Math.floor(timeLeft / 3600);
            const minutes = Math.floor((timeLeft % 3600) / 60);
            const seconds = timeLeft % 60;
            
            document.getElementById('countdown').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft > 0) {
                timeLeft--;
                setTimeout(updateCountdown, 1000);
            } else {
                document.getElementById('countdown').textContent = 'EXPIRED';
                document.getElementById('countdown').style.color = '#dc3545';
            }
        }
        
        // Start countdown
        updateCountdown();
        
        // Check payment status
        function checkPaymentStatus() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengecek...';
            button.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // In real implementation, you would make an AJAX call to check status
                fetch(`check-payment-status.php?order_id=<?php echo $order_id; ?>`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.href = `payment-success.php?order_id=<?php echo $order_id; ?>`;
                        } else if (data.status === 'failed') {
                            window.location.href = `payment-failed.php?order_id=<?php echo $order_id; ?>`;
                        } else {
                            // Still pending
                            alert('Pembayaran masih dalam proses. Silakan coba lagi nanti.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mengecek status. Silakan coba lagi.');
                    })
                    .finally(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    });
            }, 2000);
        }
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            fetch(`check-payment-status.php?order_id=<?php echo $order_id; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = `payment-success.php?order_id=<?php echo $order_id; ?>`;
                    } else if (data.status === 'failed') {
                        window.location.href = `payment-failed.php?order_id=<?php echo $order_id; ?>`;
                    }
                })
                .catch(error => console.error('Auto-check error:', error));
        }, 30000); // Check every 30 seconds
    </script>
</body>
</html>