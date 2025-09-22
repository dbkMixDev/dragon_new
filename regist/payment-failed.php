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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Gagal - Dragon Play</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .failed-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
            background: white;
            overflow: hidden;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .failed-header {
            background: linear-gradient(135deg, #fc466b, #3f5efb);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .failed-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: shake 2s infinite;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
            20%, 40%, 60%, 80% { transform: translateX(10px); }
        }
        
        .failed-body {
            padding: 40px;
        }
        
        .error-box {
            background: #f8d7da;
            border: 1px solid #f1aeb5;
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
        
        .retry-options {
            background: #e2e3e5;
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
        
        .btn-danger-custom {
            background: linear-gradient(135deg, #fc466b, #3f5efb);
            border: none;
            color: white;
        }
        
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(252, 70, 107, 0.4);
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
        
        .btn-success-custom {
            background: #28a745;
            border: none;
            color: white;
        }
        
        .btn-success-custom:hover {
            background: #218838;
            transform: translateY(-2px);
            color: white;
        }
        
        .common-issues {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .issue-item {
            display: flex;
            align-items: flex-start;
            margin: 10px 0;
            padding: 8px 0;
        }
        
        .issue-icon {
            color: #ffc107;
            margin-right: 12px;
            font-size: 16px;
            min-width: 20px;
            margin-top: 3px;
        }
        
        .support-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
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
            cursor: pointer;
        }
        
        .payment-method:hover {
            border-color: #fc466b;
            background: #fff5f5;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="row justify-content-center w-100">
            <div class="col-12">
                <div class="card failed-card">
                    <div class="failed-header">
                        <div class="failed-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h2 class="mb-3">Pembayaran Gagal</h2>
                        <p class="mb-0">Maaf, transaksi Anda tidak dapat diproses</p>
                    </div>
                    
                    <div class="failed-body">
                        <div class="error-box">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle text-danger fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-1">Transaksi Tidak Berhasil</h6>
                                    <p class="mb-0">Pembayaran Anda mengalami kendala. Silakan coba lagi atau gunakan metode pembayaran lain.</p>
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
                                <div class="col-6"><strong>Total:</strong></div>
                                <div class="col-6"><strong class="text-danger">Rp <?php echo number_format($biaya, 0, ',', '.'); ?></strong></div>
                            </div>
                        </div>
                        
                        <div class="common-issues">
                            <h6 class="mb-3"><i class="fas fa-question-circle me-2"></i>Kemungkinan Penyebab:</h6>
                            <div class="issue-item">
                                <i class="fas fa-credit-card issue-icon"></i>
                                <span>Saldo kartu tidak mencukupi</span>
                            </div>
                            <div class="issue-item">
                                <i class="fas fa-lock issue-icon"></i>
                                <span>Kartu diblokir oleh bank</span>
                            </div>
                            <div class="issue-item">
                                <i class="fas fa-wifi issue-icon"></i>
                                <span>Koneksi internet tidak stabil</span>
                            </div>
                            <div class="issue-item">
                                <i class="fas fa-clock issue-icon"></i>
                                <span>Sesi pembayaran habis (timeout)</span>
                            </div>
                            <div class="issue-item">
                                <i class="fas fa-ban issue-icon"></i>
                                <span>Pembayaran dibatalkan</span>
                            </div>
                        </div>
                        
                        <div class="retry-options">
                            <h6 class="mb-3"><i class="fas fa-redo me-2"></i>Opsi Pembayaran Ulang:</h6>
                            <div class="payment-methods">
                                <div class="payment-method" onclick="retryPayment('bank_transfer')">
                                    <i class="fas fa-university text-primary fs-3"></i>
                                    <p class="mt-2 mb-0 small">Bank Transfer</p>
                                </div>
                                <div class="payment-method" onclick="retryPayment('credit_card')">
                                    <i class="fas fa-credit-card text-success fs-3"></i>
                                    <p class="mt-2 mb-0 small">Credit Card</p>
                                </div>
                                <div class="payment-method" onclick="retryPayment('e_wallet')">
                                    <i class="fas fa-mobile-alt text-info fs-3"></i>
                                    <p class="mt-2 mb-0 small">E-Wallet</p>
                                </div>
                                <div class="payment-method" onclick="retryPayment('convenience_store')">
                                    <i class="fas fa-store text-warning fs-3"></i>
                                    <p class="mt-2 mb-0 small">Convenience Store</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button class="btn btn-danger-custom btn-lg" onclick="retryPayment()">
                                <i class="fas fa-redo me-2"></i>
                                Coba Bayar Lagi
                            </button>
                            <button class="btn btn-success-custom btn-lg" onclick="window.location.href='register.php'">
                                <i class="fas fa-plus me-2"></i>
                                Daftar Ulang
                            </button>
                            <button class="btn btn-secondary-custom btn-lg" onclick="window.location.href='../index.php'">
                                <i class="fas fa-home me-2"></i>
                                Ke Beranda
                            </button>
                        </div>
                        
                        <div class="support-box">
                            <i class="fas fa-headset text-info fs-2 mb-3"></i>
                            <h6 class="mb-2">Butuh Bantuan?</h6>
                            <p class="mb-3">Tim support kami siap membantu Anda menyelesaikan masalah pembayaran.</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="mailto:support@dragonplay.com" class="btn btn-outline-info btn-sm w-100 mb-2">
                                        <i class="fas fa-envelope me-2"></i>
                                        Email Support
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="https://wa.me/628984390348" class="btn btn-outline-success btn-sm w-100 mb-2" target="_blank">
                                        <i class="fab fa-whatsapp me-2"></i>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p class="text-muted small">
                                <i class="fas fa-shield-alt me-1"></i>
                                Data transaksi Anda aman dan terlindungi
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function retryPayment(method = '') {
            // Show loading
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            button.disabled = true;
            
            // Simulate processing
            setTimeout(() => {
                // Redirect to payment page with the same order ID
                const retryUrl = `checkout.php?order_id=<?php echo $order_id; ?>`;
                if (method) {
                    window.location.href = retryUrl + '&preferred_method=' + method;
                } else {
                    window.location.href = retryUrl;
                }
            }, 1500);
        }
        
        // Auto-suggest retry after 10 seconds
        setTimeout(() => {
            if (confirm('Ingin mencoba pembayaran lagi dengan metode yang berbeda?')) {
                retryPayment();
            }
        }, 10000);
        
        // Track failed payment for analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'payment_failed', {
                'transaction_id': '<?php echo $order_id; ?>',
                'value': <?php echo $biaya; ?>,
                'currency': 'IDR'
            });
        }
        
        // Add click tracking for payment methods
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove previous selections
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.style.borderColor = '#e9ecef';
                    m.style.background = 'white';
                });
                
                // Highlight selected method
                this.style.borderColor = '#fc466b';
                this.style.background = '#fff5f5';
            });
        });
    </script>
</body>
</html>