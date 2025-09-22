<?php
session_start();

require_once('include/config.php');
require_once 'include/crypto.php';
if (!isset($_SESSION['username'], $_SESSION['login_token'], $_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$userid = $_SESSION['user_id'];
$tokenx = $_SESSION['login_token'];
// Ambil data user
$res = mysqli_query($con, "SELECT login_token, last_log, host, merchand, address, level FROM userx WHERE username='$userid'");
$row = mysqli_fetch_assoc($res);

if (!$row) {
    // User tidak ditemukan
    session_destroy();
    session_start();
    $_SESSION['error_token'] = "Account not found.";
    header("Location: login.php");
    exit;
}

$level = $row['level'];
$tokenDB = $row['login_token'];

if ($tokenDB !== $tokenx) {
    // Kalo token beda, cek apakah level operator
    if ($level == 'operator') {
        $last_login_info = $row['last_log'] ?? 'Unknown';
        $last_host_info = $row['host'] ?? 'Unknown';

        session_destroy();
        session_start();
        $_SESSION['error_token'] = "Operator account only allows 1 active session. Last login at " . $last_login_info;
        
        header("Location: login.php");
        exit;
    } else {
        // Untuk selain operator, misal admin/supervisor bisa diatur policy lain (optional)
        // Kalau mau semua user strict, bisa hapus else ini
    }
}

// Simpan level user dalam session untuk penggunaan selanjutnya
$_SESSION['user_level'] = $row['level'];
$user_level = $row['level'];

// Cek jika merchant atau address masih kosong/null
if (empty($row['merchand'])) {
    header("Location: merchant_setup.php");
    exit;
}

$token = $_SESSION['username'] ?? null;
$encrypt = encrypt($token);
if (!$encrypt) {
    die("Token tidak valid atau rusak.");
}

$home = encrypt('home');
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/index.php';
$encryptedPage = $_GET['q'] ?? encrypt('home');
$page = decrypt($encryptedPage);

// Daftar halaman yang hanya bisa diakses oleh admin
$admin_only_pages = ['general', 'master'];

// Cek pembatasan akses untuk halaman admin
if (in_array($page, $admin_only_pages) && $user_level !== 'admin') {
    // Redirect ke halaman home dengan pesan error
    $_SESSION['access_denied'] = "Access denied. You don't have permission to access this page.";
    $home_encrypted = encrypt('home');
    header("Location: index.php?q=" . urlencode($home_encrypted));
    exit;
}

$res = $con->prepare("SELECT timezone FROM userx WHERE username = ?");
if (!$res) {
    die("Prepare failed: " . $con->error);
}

$res->bind_param("s", $userid);

if (!$res->execute()) {
    die("Execute failed: " . $res->error);
}

// Bind result ke variabel
$res->bind_result($timezone_from_db);

// Fetch hasilnya
if ($res->fetch()) {
    $timezone = $timezone_from_db ?: $defaultTimezone;
} else {
    $timezone = $defaultTimezone; // fallback jika tidak ada
}

$res->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Dashboard | Dragon Play - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/libs/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Disable Google Translate -->
    <meta name="google" content="notranslate">
    <meta name="google-translate-customization" content="(translations disabled)">
    <!-- Alternative/Additional -->
    <meta http-equiv="Content-Language" content="id">
    
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
</head>
<body data-sidebar="dark" data-layout-mode="light">
    <div id="layout-wrapper">
        <header id="page-topbar">
            <?php include 'include/headbar.php'; ?>
        </header>
        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <?php include 'include/sidebar.php'; ?>
            </div>
        </div>
        
        <div class="main-content">
            <?php
            // Tampilkan pesan access denied jika ada
            if (isset($_SESSION['access_denied'])) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Access Denied",
                        text: "' . $_SESSION['access_denied'] . '",
                        confirmButtonText: "OK"
                    });
                </script>';
                unset($_SESSION['access_denied']);
            }
            
            if ($page == null || $page == $home) {
                include('page/xaxaveria.php');
            } else if (file_exists('page/' . $page . '.php')) {
                include('page/' . $page . '.php');
            } else {
                include('page/xaxaveria.php');
            }
            ?>
        </div>
        
        <?php include 'include/footer.php'; ?>
    </div>
    
    <!-- Right Sidebar -->
    <div class="right-bar">
        <div data-simplebar class="h-100">
            <div class="rightbar-title d-flex align-items-center px-3 py-4">
                <h5 class="m-0 me-2">Settings</h5>
               
            </div>

            <!-- Settings -->
            <hr class="mt-0" />
            <h6 class="text-center mb-0">Choose Layouts</h6>

            <div class="p-4">
                <div class="mb-2">
                    <img src="assets/images/layouts/layout-1.jpg" class="img-thumbnail" alt="layout images">
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="light-mode-switch" checked>
                    <label class="form-check-label" for="light-mode-switch">Light Mode</label>
                </div>

                <div class="mb-2">
                    <img src="assets/images/layouts/layout-2.jpg" class="img-thumbnail" alt="layout images">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="dark-mode-switch">
                    <label class="form-check-label" for="dark-mode-switch">Dark Mode</label>
                </div>

                <div class="mb-2">
                    <img src="assets/images/layouts/layout-3.jpg" class="img-thumbnail" alt="layout images">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="rtl-mode-switch">
                    <label class="form-check-label" for="rtl-mode-switch">RTL Mode</label>
                </div>

                <div class="mb-2">
                    <img src="assets/images/layouts/layout-4.jpg" class="img-thumbnail" alt="layout images">
                </div>
                <div class="form-check form-switch mb-5">
                    <input class="form-check-input theme-choice" type="checkbox" id="dark-rtl-mode-switch">
                    <label class="form-check-label" for="dark-rtl-mode-switch">Dark RTL Mode</label>
                </div>
            </div>
        </div> <!-- end slimscroll-menu-->
    </div>
    <!-- /Right-bar -->


    <script>
    document.querySelectorAll('.btn-feature-disabled').forEach(btn => {
        btn.addEventListener('click', () => {
            Swal.fire({
                icon: 'info',
                title: 'Coming Soon',
                text: 'This feature is still under development.',
                confirmButtonText: 'OK'
            });
        });
    });
    </script>

    <script>
        let wakeLock = null;

        async function keepScreenAwake() {
            try {
                wakeLock = await navigator.wakeLock.request('screen');
                console.log('Screen will stay awake');
            } catch (err) {
                console.error('Wake Lock error:', err);
            }
        }

        document.addEventListener('visibilitychange', () => {
            if (wakeLock !== null && document.visibilityState === 'visible') {
                keepScreenAwake();
            }
        });

        keepScreenAwake();
    </script>

    <!-- Dark Mode Implementation Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get theme elements
        const lightModeSwitch = document.getElementById('light-mode-switch');
        const darkModeSwitch = document.getElementById('dark-mode-switch');
        const rtlModeSwitch = document.getElementById('rtl-mode-switch');
        const darkRtlModeSwitch = document.getElementById('dark-rtl-mode-switch');
        
        const body = document.body;
        const bootstrapStyle = document.getElementById('bootstrap-style');
        const appStyle = document.getElementById('app-style');
        
        // Load saved theme from localStorage
        const savedTheme = localStorage.getItem('theme') || 'light';
        const savedDirection = localStorage.getItem('direction') || 'ltr';
        
        // Apply saved theme on page load
        applyTheme(savedTheme, savedDirection);
        
        // Theme switch event listeners
        lightModeSwitch.addEventListener('change', function() {
            if (this.checked) {
                uncheckOtherSwitches('light');
                applyTheme('light', 'ltr');
                saveTheme('light', 'ltr');
            }
        });
        
        darkModeSwitch.addEventListener('change', function() {
            if (this.checked) {
                uncheckOtherSwitches('dark');
                applyTheme('dark', 'ltr');
                saveTheme('dark', 'ltr');
            }
        });
        
        rtlModeSwitch.addEventListener('change', function() {
            if (this.checked) {
                uncheckOtherSwitches('rtl');
                applyTheme('light', 'rtl');
                saveTheme('light', 'rtl');
            }
        });
        
        darkRtlModeSwitch.addEventListener('change', function() {
            if (this.checked) {
                uncheckOtherSwitches('dark-rtl');
                applyTheme('dark', 'rtl');
                saveTheme('dark', 'rtl');
            }
        });
        
        function uncheckOtherSwitches(activeTheme) {
            lightModeSwitch.checked = (activeTheme === 'light');
            darkModeSwitch.checked = (activeTheme === 'dark');
            rtlModeSwitch.checked = (activeTheme === 'rtl');
            darkRtlModeSwitch.checked = (activeTheme === 'dark-rtl');
        }
        
        function applyTheme(theme, direction) {
            // Remove existing theme classes
            body.classList.remove('dark-mode', 'rtl-mode');
            body.removeAttribute('dir');
            
            // Apply new theme
            if (theme === 'dark') {
                body.setAttribute('data-layout-mode', 'dark');
                body.setAttribute('data-sidebar', 'dark');
                body.setAttribute('data-topbar', 'dark');
            } else {
                body.setAttribute('data-layout-mode', 'light');
                body.setAttribute('data-sidebar', 'dark');
                body.removeAttribute('data-topbar');
            }
            
            // Apply RTL direction
            if (direction === 'rtl') {
                body.setAttribute('dir', 'rtl');
                body.classList.add('rtl-mode');
            } else {
                body.setAttribute('dir', 'ltr');
            }
            
            // Update switch states based on current theme
            updateSwitchStates(theme, direction);
        }
        
        function updateSwitchStates(theme, direction) {
            if (theme === 'light' && direction === 'ltr') {
                uncheckOtherSwitches('light');
            } else if (theme === 'dark' && direction === 'ltr') {
                uncheckOtherSwitches('dark');
            } else if (theme === 'light' && direction === 'rtl') {
                uncheckOtherSwitches('rtl');
            } else if (theme === 'dark' && direction === 'rtl') {
                uncheckOtherSwitches('dark-rtl');
            }
        }
        
        function saveTheme(theme, direction) {
            localStorage.setItem('theme', theme);
            localStorage.setItem('direction', direction);
        }
        
        // Additional CSS for dark mode
        if (!document.getElementById('dark-mode-styles')) {
            const darkModeStyles = document.createElement('style');
            darkModeStyles.id = 'dark-mode-styles';
            darkModeStyles.innerHTML = `
                [data-layout-mode="dark"] {
                    background-color: #2a3042;
                    color: #e9ecef;
                }
                
                [data-layout-mode="dark"] .card {
                    background-color: #363a4a;
                    border-color: #4b5262;
                }
                
                [data-layout-mode="dark"] .navbar-header {
                    background-color: #363a4a;
                    border-bottom: 1px solid #4b5262;
                }
                
                [data-layout-mode="dark"] .vertical-menu {
                    background-color: #2a3042;
                }
                
                [data-layout-mode="dark"] .main-content {
                    background-color: #2a3042;
                }
                
                [data-layout-mode="dark"] .page-content {
                    background-color: #2a3042;
                }
                
                [data-layout-mode="dark"] .text-muted {
                    color: #adb5bd !important;
                }
                
                [data-layout-mode="dark"] .table {
                    color: #e9ecef;
                }
                
                [data-layout-mode="dark"] .table-light {
                    background-color: #4b5262;
                    color: #e9ecef;
                }
                
                [data-layout-mode="dark"] .btn-secondary {
                    background-color: #4b5262;
                    border-color: #4b5262;
                    color: #e9ecef;
                }
                
                [data-layout-mode="dark"] .form-control {
                    background-color: #4b5262;
                    border-color: #5a6476;
                    color: #e9ecef;
                }
                
                [data-layout-mode="dark"] .form-control:focus {
                    background-color: #4b5262;
                    border-color: #7c869a;
                    color: #e9ecef;
                    box-shadow: 0 0 0 0.2rem rgba(124, 134, 154, 0.25);
                }
                
                [data-layout-mode="dark"] .dropdown-menu {
                    background-color: #363a4a;
                    border-color: #4b5262;
                }
                
                [data-layout-mode="dark"] .dropdown-item {
                    color: #e9ecef;
                }
                
                [data-layout-mode="dark"] .dropdown-item:hover {
                    background-color: #4b5262;
                    color: #e9ecef;
                }
                
                [data-layout-mode="dark"] .modal-content {
                    background-color: #363a4a;
                    color: #e9ecef;
                }
                
                [data-layout-mode="dark"] .modal-header {
                    border-bottom-color: #4b5262;
                }
                
                [data-layout-mode="dark"] .modal-footer {
                    border-top-color: #4b5262;
                }
                
                [data-layout-mode="dark"] .right-bar {
                    background-color: #363a4a;
                    color: #e9ecef;
                }
                
                /* RTL Styles */
                .rtl-mode {
                    direction: rtl;
                    text-align: right;
                }
                
                .rtl-mode .navbar-brand-box {
                    margin-left: auto;
                    margin-right: 0;
                }
                
                .rtl-mode .vertical-menu {
                    left: auto;
                    right: 0;
                }
                
                .rtl-mode .main-content {
                    margin-left: 0;
                    margin-right: 250px;
                }
                
                .rtl-mode .dropdown-menu {
                    left: auto !important;
                    right: 0 !important;
                }
            `;
            document.head.appendChild(darkModeStyles);
        }
    });
    </script>

</body>
</html>