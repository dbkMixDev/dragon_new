<?php
// This is just for very basic implementation reference, in production, you should validate the incoming requests and implement your backend more securely.
// Please refer to this docs for snap popup:
// https://docs.midtrans.com/en/snap/integration-guide?id=integration-steps-overview

namespace Midtrans;

require_once dirname(__FILE__) . '/payment/Midtrans.php';
// Set Your server key
// can find in Merchant Portal -> Settings -> Access keys
Config::$serverKey = 'Mid-server-DXD4mYC-7COHSlgYJH5cGfqa';
Config::$clientKey = 'Mid-client-SqCQO-tyEQfQqU4t';

// non-relevant function only used for demo/example purpose
printExampleWarningMessage();

// Uncomment for production environment
Config::$isProduction = true;
Config::$isSanitized = Config::$is3ds = true;

// Required

include "../include/config.php";
$order_id = $_GET['order_id'];

 
$query = "SELECT * FROM transactions WHERE order_id = ? AND status = 'pending'";
$stmt = mysqli_prepare($con, $query);
if (!$stmt) {
    // Handle error
    die("Prepare failed: " . mysqli_error($con));
}
mysqli_stmt_bind_param($stmt, "s", $order_id);

if (!mysqli_stmt_execute($stmt)) {
    // Handle execute error
    die("Execute failed: " . mysqli_stmt_error($stmt));
}

// Dapatkan metadata hasil
$meta = mysqli_stmt_result_metadata($stmt);
if (!$meta) {
    die("Failed to get metadata: " . mysqli_stmt_error($stmt));
}

// Buat array untuk menampung kolom dan nilai
$fields = [];
$row = [];
while ($field = mysqli_fetch_field($meta)) {
    $fields[] = &$row[$field->name];
}

// Bind hasil ke variabel di $row
call_user_func_array([$stmt, 'bind_result'], $fields);

// Fetch satu baris hasil
if (mysqli_stmt_fetch($stmt)) {
    // $row sekarang berisi associative array dari hasil query
    $transaction = [];
    foreach($row as $key => $val) {
        $transaction[$key] = $val;
    }
} else {
    $transaction = null; // Tidak ada hasil
}

mysqli_stmt_close($stmt);


$nama = $transaction['full_name'];
$email = $transaction['email'];
$biaya = $transaction['amount'];

$transaction_details = array(
    'order_id' => $order_id,
    'gross_amount' =>  $biaya, // no decimal allowed for creditcard
);

// Optional
$item_details = array (
    array(
        'id' => 'a1',
        'price' => $biaya,
        'quantity' => 1,
        'name' => "DRAGON PLAY MEMBERSHIP PREMIUM"
    ),
);

// Optional
$customer_details = array(
    'first_name'    => "$nama",
    'last_name'     => "",
    'email'         => "$email",
    'phone'         => ""
);

// Fill transaction details
$transaction = array(
    'transaction_details' => $transaction_details,
    'customer_details' => $customer_details,
    'item_details' => $item_details,
);

$snap_token = '';
try {
    $snap_token = Snap::getSnapToken($transaction);
}
catch (\Exception $e) {
    echo $e->getMessage();
}

function printExampleWarningMessage() {
    if (strpos(Config::$serverKey, 'your ') != false ) {
        echo "<code>";
        echo "<h4>Please set your server key from sandbox</h4>";
        echo "In file: " . __FILE__;
        echo "<br>";
        echo "<br>";
        echo htmlspecialchars('Config::$serverKey = \'<server key>\';');
        die();
    } 
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dragon Play - Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
      <meta content="Register for Dragon Play Premium Membership" name="description" />
    <meta content="Dragon Play" name="author" />
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .payment-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
            background: white;
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .payment-body {
            padding: 40px;
            text-align: center;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-payment {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .btn-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .payment-info {
            background: #f8f9ff;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .order-details {
            text-align: left;
            margin: 20px 0;
        }
        
        .order-details .row {
            margin-bottom: 10px;
        }
        
        .amount-highlight {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .auto-redirect-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #0066cc;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
        <div class="row justify-content-center w-100">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card payment-card">
                    <div class="payment-header">
                        <i class="fas fa-crown fa-3x mb-3"></i>
                        <h3 class="mb-2">Dragon Play Premium</h3>
                        <p class="mb-0">Pembayaran Membership</p>
                    </div>
                    
                    <div class="payment-body">
                        <div id="loading-section">
                            <div class="loading-spinner"></div>
                            <h5>Menyiapkan Pembayaran...</h5>
                            <p class="text-muted">Popup pembayaran akan muncul secara otomatis</p>
                            
                            <div class="auto-redirect-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Otomatis:</strong> Jendela pembayaran akan terbuka dalam 3 detik
                            </div>
                        </div>
                        
                        <div class="payment-info">
                            <div class="order-details">
                                <div class="row">
                                    <div class="col-6"><strong>Order ID:</strong></div>
                                    <div class="col-6"><?php echo $order_id; ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-6"><strong>Nama:</strong></div>
                                    <div class="col-6"><?php echo $nama; ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-6"><strong>Email:</strong></div>
                                    <div class="col-6"><?php echo $email; ?></div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6"><strong>Total Bayar:</strong></div>
                                    <div class="col-6 amount-highlight">Rp <?php echo number_format($biaya, 0, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <button id="pay-button" class="btn btn-payment btn-lg w-100">
                            <i class="fas fa-credit-card me-2"></i>
                            Buka Pembayaran Manual
                        </button>
                        
                        <p class="text-muted mt-3 small">
                            <i class="fas fa-shield-alt me-1"></i>
                            Pembayaran aman dengan enkripsi SSL 256-bit
                        </p>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="register.php" class="text-white text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali ke Pendaftaran
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Midtrans Snap JS -->
    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="<?php echo Config::$clientKey;?>"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    
    <script type="text/javascript">
        // Countdown untuk auto-open payment
        let countdown = 3;
        let autoPaymentTriggered = false;
        
        function updateLoadingText() {
            const loadingSection = document.getElementById('loading-section');
            if (countdown > 0) {
                loadingSection.querySelector('h5').textContent = `Menyiapkan Pembayaran... (${countdown})`;
                countdown--;
                setTimeout(updateLoadingText, 1000);
            } else if (!autoPaymentTriggered) {
                triggerPayment();
            }
        }
        
        function triggerPayment() {
            autoPaymentTriggered = true;
            
            // Hide loading, show manual button
            document.getElementById('loading-section').querySelector('h5').textContent = 'Membuka Jendela Pembayaran...';
            
            // Open Midtrans Snap
            snap.pay('<?php echo $snap_token?>', {
               onSuccess: function(result) {
    console.log('Payment success:', result);
    
    // Simpan VA ke server
    fetch('save_va.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(result)
    });

    // Redirect
    window.location.href = 'payment-success.php?order_id=<?php echo $order_id; ?>';
},
onPending: function(result) {
    console.log('Payment pending:', result);

    fetch('save_va.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(result)
    });

    window.location.href = 'payment-pending.php?order_id=<?php echo $order_id; ?>';
},
                onError: function(result) {
                    console.log('Payment error:', result);
                    alert('Terjadi kesalahan dalam pembayaran. Silakan coba lagi.');
                    document.getElementById('loading-section').querySelector('h5').textContent = 'Gagal membuka pembayaran. Gunakan tombol manual.';
                },
                onClose: function() {
                    console.log('Payment popup closed');
                    document.getElementById('loading-section').querySelector('h5').textContent = 'Pembayaran dibatalkan. Klik tombol untuk mencoba lagi.';
                }
            });
        }
        
        // Manual payment button
        document.getElementById('pay-button').onclick = function(){
            triggerPayment();
        };
        
        // Start countdown when page loads
        window.addEventListener('load', function() {
            setTimeout(updateLoadingText, 1000);
        });
        
        // Fallback: if Snap is not loaded properly
        window.addEventListener('load', function() {
            if (typeof snap === 'undefined') {
                setTimeout(function() {
                    if (typeof snap === 'undefined') {
                        document.getElementById('loading-section').querySelector('h5').textContent = 'Gagal memuat pembayaran. Refresh halaman atau gunakan tombol manual.';
                    }
                }, 5000);
            }
        });
    </script>
</body>
</html>