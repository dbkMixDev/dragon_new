<?php
require_once 'include/crypto.php';
session_start();
$token = $_GET['q'] ?? null;

$decryptedEmail = decrypt($token);
if (!$decryptedEmail) {
    die("Token tidak valid atau rusak.");
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Reset Password | Dragon Play - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/icons.min.css" rel="stylesheet" />
    <link href="assets/css/app.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/sweetalert2@11"></script>


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
                                    <i class="bx bx-lock-alt h1 mb-0 text-primary"></i>
                                </div>
                            </div>

                            <div class="p-2 mt-4">
                                <h4>Reset your password <?= isset($_SESSION['error4']) ? ' - ' . $_SESSION['error4'] : '' ?></h4>
                                <p class="mb-4">for <span class="fw-semibold"><?= htmlspecialchars($decryptedEmail) ?></span></p>

                               <form id="resetForm" action="controller/reset-password.php" method="POST" novalidate>
    <div class="mb-3 text-start">
    <label for="password" class="form-label">New Password</label>
    <div class="input-group auth-pass-inputgroup">
        <input type="password" name="password" id="password" class="form-control" required
               aria-label="New Password" aria-describedby="togglePassword">
        <button class="btn btn-light" type="button" id="togglePassword">
            <i class="mdi mdi-eye-outline"></i>
        </button>
    </div>
    <small id="passwordHelp" class="form-text text-muted d-block mt-1">
        Minimum 6 characters ❌, 1 uppercase ❌, 1 number ❌, 1 symbol ❌
    </small>
</div>

<div class="mb-3 text-start">
    <label for="confirm" class="form-label">Confirm Password</label>
    <div class="input-group auth-pass-inputgroup">
        <input type="password"  name="confirm"  id="confirm" class="form-control" required
               aria-label="Confirm Password" aria-describedby="toggleConfirm">
        <button class="btn btn-light" type="button" id="toggleConfirm">
            <i class="mdi mdi-eye-outline"></i>
        </button>
    </div>
    <div id="matchMessage" class="text-danger small mt-1" style="display:none;"></div>
</div>

    <input type="hidden" name="email" value="<?= htmlspecialchars($decryptedEmail) ?>">

    <div class="mt-4">
        <button class="btn btn-primary w-100" type="submit" id="submitBtn" disabled>Reset Password</button>
    </div>
</form>

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
 <!-- Password show/hide -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="mdi mdi-eye-off-outline"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="mdi mdi-eye-outline"></i>';
            }
        });

         document.getElementById('toggleConfirm').addEventListener('click', function() {
            const passwordInput = document.querySelector('input[name="confirm"]');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="mdi mdi-eye-off-outline"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="mdi mdi-eye-outline"></i>';
            }
        });
    </script>
<script>
const passwordInput = document.getElementById("password");
const confirmInput = document.getElementById("confirm");
const passwordHelp = document.getElementById("passwordHelp");
const matchMessage = document.getElementById("matchMessage");
const submitBtn = document.getElementById("submitBtn");

// Cek syarat password
function checkPasswordRequirements(password) {
    return {
        length: password.length >= 6,
        uppercase: /[A-Z]/.test(password),
        number: /\d/.test(password),
        symbol: /[^A-Za-z0-9]/.test(password)
    };
}

// Update tampilan syarat password
function updatePasswordHelp(password) {
    const checks = checkPasswordRequirements(password);

    passwordHelp.innerHTML =
        `Minimum 6 characters ${checks.length ? '✅' : '❌'}, ` +
        `1 uppercase ${checks.uppercase ? '✅' : '❌'}, ` +
        `1 number ${checks.number ? '✅' : '❌'}, ` +
        `1 symbol ${checks.symbol ? '✅' : '❌'}`;

    return Object.values(checks).every(v => v === true); // true jika semua terpenuhi
}

// Cek apakah password dan confirm sama
function checkPasswordsMatch(password, confirm) {
    return password === confirm;
}

// Update status tombol submit & pesan error match
function validateForm() {
    const password = passwordInput.value;
    const confirm = confirmInput.value;

    const isPasswordValid = updatePasswordHelp(password);
    const isMatch = checkPasswordsMatch(password, confirm);

    if (!isMatch && confirm.length > 0) {
        matchMessage.style.display = "block";
        matchMessage.textContent = "Passwords do not match.";
    } else {
        matchMessage.style.display = "none";
        matchMessage.textContent = "";
    }

    submitBtn.disabled = !(isPasswordValid && isMatch);
}

// Event listener input password dan confirm
passwordInput.addEventListener("input", validateForm);
confirmInput.addEventListener("input", validateForm);
</script>

<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_SESSION['error4'])): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops!',
        text: '<?= $_SESSION['error4'] ?>',
    });
</script>
<?php unset($_SESSION['error4']); endif; ?>
<?php if (isset($_SESSION['success_redirect'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= $_SESSION['success4'] ?>',
        timer: 2000,
        showConfirmButton: false
    }).then(() => {
        window.location.href = "login.php";
    });
</script>
<?php unset($_SESSION['success_redirect'], $_SESSION['success4']); endif; ?>
</div>
</body>
</html>
