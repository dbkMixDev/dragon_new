<?php
session_start();
include 'include/config.php';

// Generate UUID dan hostname
function generate_uuid_v4_with_hostname()
{
    $uuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );

    return [
        'uuid' => $uuid,
        'hostname' => gethostname()
    ];
}

$data = generate_uuid_v4_with_hostname();
$uuiid = $data['uuid'];
$host = $data['hostname'];

$diff = 0;
$show_error = false;
$remaining_time = 0;

$stmt = $con->prepare("SELECT attempts, UNIX_TIMESTAMP(last_attempt) as last_attempt_ts FROM login_attempts WHERE hostname = ?");
$stmt->bind_param("s", $host);
$stmt->execute();
$stmt->store_result();

$attempt = null;
if ($stmt->num_rows > 0) {
    $stmt->bind_result($attempts, $last_attempt_ts);
    $stmt->fetch();
    $attempt = ['attempts' => $attempts, 'last_attempt_ts' => $last_attempt_ts];
}
$stmt->close();

if ($attempt && $attempt['attempts'] >= 5) {
    $last_attempt = $attempt['last_attempt_ts'];
    $current_time = time();
    $diff = $current_time - $last_attempt;
    $remaining_time = 120 - $diff;

    if ($remaining_time > 0) {
        $minutes = ceil($remaining_time / 60);
        $_SESSION['error'] = "Terlalu banyak percobaan login. Silakan coba lagi setelah {$minutes} menit.";
        $show_error = true;
    } else {
        $stmt = $con->prepare("UPDATE login_attempts SET attempts = 0 WHERE hostname = ?");
        $stmt->bind_param("s", $host);
        $stmt->execute();
        $stmt->close();
    }
}

if (!empty($_SESSION['username'])) {
    header("location:index.php");
    exit;
}
?>
<?php
// Fungsi ambil IP user
function getUserIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$ip = getUserIP();

// Panggil API ip-api untuk fallback lokasi
$url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,lat,lon,query";
$response = @file_get_contents($url);
$data = json_decode($response, true);

// Jika gagal ambil data, siapkan default
if (!$data || $data['status'] !== 'success') {
    $data = [
        'query' => $ip,
        'country' => 'Tidak diketahui',
        'regionName' => '-',
        'city' => '-',
        'lat' => '-',
        'lon' => '-'
    ];
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Login | Dragon Play - Admin Panel</title>
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
                    <div class="card overflow-hidden">
                        <div class="bg-primary bg-soft">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-primary p-4">
                                        <h5 class="text-primary">Welcome Back !</h5>
                                        <p>Dragon Play Billing V2.1</p>
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="assets/images/profile-img.png" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="auth-logo">
                                <a href="./" class="auth-logo-light">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="assets/images/logo-light.svg" alt="" class="rounded-circle"
                                                height="34">
                                        </span>
                                    </div>
                                </a>
                                <a href="./" class="auth-logo-dark">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="assets/images/logo.svg" alt="" class="rounded-circle" height="34">
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2">
                                <form class="form-horizontal" method="POST" action="controller/loginx.php">
                                    <?php if ($show_error || isset($_SESSION['error'])): ?>
                                        <div class="alert alert-danger">
                                            <?= htmlspecialchars($_SESSION['error'] ?? '') ?>
                                            <?php unset($_SESSION['error']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['error_token'])): ?>
                                        <div class="alert alert-danger">
                                            <?= htmlspecialchars($_SESSION['error_token']);
                                            unset($_SESSION['error_token']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username/Email</label>
                                        <input type="text" class="form-control" id="username" name="namex"
                                            placeholder="Enter username/email" required
                                            oninput="this.value = this.value.replace(/\s/g, '')">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" name="passx" id="password" class="form-control"
                                                required placeholder="Enter password"
                                                oninput="this.value = this.value.replace(/\s/g, '')">
                                            <button class="btn btn-light" type="button" id="togglePassword">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" id="user_info" name="user_info"
                                        value="IP: <?= $data['query'] ?> | Lokasi: <?= $data['city'] . ', ' . $data['regionName'] . " (" . $data['lat'] . ", " . $data['lon'] . ")" ?>">



                                    <!-- <p><b>Lokasi (GPS jika diizinkan, IP jika ditolak):</b> <span id="lokasi"> -->

                                    <!-- </span></p> -->

                                    <script>
                                        if (navigator.geolocation) {
                                            navigator.geolocation.getCurrentPosition(
                                                function (pos) {
                                                    let lat = pos.coords.latitude.toFixed(6);
                                                    let lng = pos.coords.longitude.toFixed(6);
                                                    let ip = "<?= $data['query'] ?>";

                                                    // Update hidden input user_info
                                                    document.getElementById("user_info").value =
                                                        `IP: ${ip} | Lokasi: Koordinat GPS (${lat}, ${lng})`;

                                                    // Enable tombol bayar
                                                    $('#pay-button').prop('disabled', false);
                                                },
                                                function (error) {
                                                    console.log("GPS ditolak / error, fallback pakai lokasi IP.");
                                                    // Tetap pakai default dari PHP di hidden input
                                                    $('#pay-button').prop('disabled', false); // optional enable
                                                }
                                            );
                                        } else {
                                            console.log("Browser tidak mendukung Geolocation, pakai lokasi IP default.");
                                            $('#pay-button').prop('disabled', false); // optional enable
                                        }
                                    </script>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember-check">
                                        <label class="form-check-label" for="remember-check">Remember me</label>
                                    </div>

                                    <div class="mt-3 d-grid">
                                        <?php if ($show_error && $remaining_time > 0): ?>
                                            <button class="btn btn-primary waves-effect waves-light" type="button" disabled
                                                id="login-button">
                                                Coba lagi dalam <span id="countdown"><?= $remaining_time ?></span> detik
                                            </button>
                                            <script>
                                                let secondsLeft = <?= $remaining_time ?>;
                                                const countdownElement = document.getElementById('countdown');
                                                const countdownInterval = setInterval(() => {
                                                    secondsLeft--;
                                                    countdownElement.textContent = secondsLeft;
                                                    if (secondsLeft <= 0) {
                                                        clearInterval(countdownInterval);
                                                        window.location.reload();
                                                    }
                                                }, 1000);
                                            </script>
                                        <?php else: ?>
                                            <button class="btn btn-primary waves-effect waves-light" type="submit">Log
                                                In</button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <a href="auth-recoverpw.php?lk=<?= htmlspecialchars($uuiid) ?>&h=<?= htmlspecialchars($host) ?>"
                                            class="text-muted">
                                            <i class="mdi mdi-lock me-1"></i> Forgot your password?
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="mt-5 text-center">
                            <p>Don't have an account? <a href="regist/">Order here</a></p>
                            <p>©
                                <script>document.write(new Date().getFullYear())</script> Dragon Play. Best Project <i
                                    class="mdi mdi-heart text-danger"></i> by dbk.dev
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.querySelector('input[name="passx"]');
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    this.innerHTML = isPassword
                        ? '<i class="mdi mdi-eye-off-outline"></i>'
                        : '<i class="mdi mdi-eye-outline"></i>';
                });
            }
        });
    </script>
    <script src="assets/js/app.js"></script>
</body>

</html>