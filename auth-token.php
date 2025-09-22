<?php
session_start();
require_once 'include/crypto.php'; // Ini sudah otomatis dapat key dari config.php

$token = $_GET['q'] ?? null;

$decryptedEmail = decrypt($token);

if (!$decryptedEmail) {
    die("Token tidak valid atau rusak.");
}

// echo "Email: " . htmlspecialchars($decryptedEmail);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Two Step Verification | Dragon Play - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/icons.min.css" rel="stylesheet" />
    <link href="assets/css/app.min.css" rel="stylesheet" />
</head>

<body>
<div class="account-pages my-5 pt-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="avatar-md mx-auto">
                                <div class="avatar-title rounded-circle bg-light">
                                    <i class="bx bxs-envelope h1 mb-0 text-primary"></i>
                                </div>
                            </div>
                            <div class="p-2 mt-4">

                                <h4>Verify your email</h4>
                    <?php if (isset($_SESSION['error3'])): ?>
    <div class="alert alert-danger text-center mb-4" role="alert">
        <?php 
        $errorMsg = htmlspecialchars($_SESSION['error3']);
        echo $errorMsg;
        if ($_SESSION['error3'] === "Token sudah kedaluwarsa. Silakan minta ulang.") {
            echo ' <a href="#" id="resendBtn" onclick="handleResend()" class="fw-medium text-primary">Resend</a>';
        }
        unset($_SESSION['error3']);
        ?>
    </div>
<?php else: ?>
    <p class="mb-4">
        Please enter the 4 digit code sent to 
        <span class="fw-semibold"><?= htmlspecialchars($decryptedEmail) ?></span>
    </p>
<?php endif; ?>


                                <!-- FORM -->
                                <form id="verifyForm" action="controller/verify-token.php" method="POST">
                                    <div class="row">
                                        <?php for ($i = 1; $i <= 4; $i++): ?>
                                            <div class="col-3">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control form-control-lg text-center two-step"
                                                        maxlength="1" inputmode="numeric" pattern="[0-9]*" required>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                    <input type="hidden" name="email" value="<?= htmlspecialchars($decryptedEmail) ?>">
                                      <input type="hidden" name="email2" value="<?= htmlspecialchars($token) ?>">
                                    <input type="hidden" name="token" id="combinedToken">

                                    <div class="mt-4">
                                        <button class="btn btn-primary w-100" type="submit">Confirm</button>
                                    </div>
                                </form>

                                <!-- RESEND -->
                                <form id="resendForm" action="controller/gettoken.php" method="POST" style="display:none;">
                                    <input type="hidden" name="email" value="<?= htmlspecialchars($decryptedEmail) ?>">
                                </form>

                                <div class="mt-4 text-center">
                                    <p id="resendText">
                                        Didn't receive a code?
                                        <a href="#" id="resendBtn" onclick="handleResend()" class="fw-medium text-primary">Resend</a>
                                    </p>
                                    <p id="countdownText" class="text-muted" style="display: none;"></p>
                                </div>

                                <p class="mt-5 text-center">
                                    © <script>document.write(new Date().getFullYear())</script> Dragon Play. Best Project <i class="mdi mdi-heart text-danger"></i> by dbk.dev
                                </p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
    // Gabungkan digit token ke hidden input saat submit
    document.getElementById('verifyForm').addEventListener('submit', function(e) {
        const digits = [...document.querySelectorAll('.two-step')].map(input => input.value.trim()).join('');
        document.getElementById('combinedToken').value = digits;

        if (digits.length < 4) {
            e.preventDefault();
            alert("Kode verifikasi harus 4 digit!");
        }
    });

    // Auto fokus dan pindah input
    document.addEventListener("DOMContentLoaded", function () {
        const inputs = document.querySelectorAll('.two-step');
        inputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = e.clipboardData.getData('text').slice(0, 4);
                paste.split('').forEach((char, i) => {
                    if (inputs[i]) inputs[i].value = char;
                });
                if (inputs[paste.length - 1]) {
                    inputs[paste.length - 1].focus();
                }
            });
        });
    });

    // Resend Logic
    const RESEND_KEY = "resend_timer";
    const RESEND_INTERVAL = 300;

    function handleResend() {
        const now = Math.floor(Date.now() / 1000);
        localStorage.setItem(RESEND_KEY, now);
        document.getElementById('resendForm').submit();
    }

    function updateCountdown() {
        const last = parseInt(localStorage.getItem(RESEND_KEY) || "0");
        const now = Math.floor(Date.now() / 1000);
        const elapsed = now - last;
        const remaining = RESEND_INTERVAL - elapsed;

        if (remaining > 0) {
            document.getElementById('resendBtn').style.pointerEvents = 'none';
            document.getElementById('resendBtn').style.color = 'gray';
            document.getElementById('countdownText').style.display = 'block';
            document.getElementById('countdownText').innerText =
                `Please wait ${remaining} seconds before resending.`;
            setTimeout(updateCountdown, 1000);
        } else {
            document.getElementById('resendBtn').style.pointerEvents = 'auto';
            document.getElementById('resendBtn').style.color = '';
            document.getElementById('countdownText').style.display = 'none';
        }
    }

    updateCountdown();
    </script>

    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>
