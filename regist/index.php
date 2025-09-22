<?php
session_start();
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
    <title>Register | Dragon Play - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Register for Dragon Play Premium Membership" name="description" />
    <meta content="Dragon Play" name="author" />

    <!-- Midtrans Snap -->
    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="Mid-client-SqCQO-tyEQfQqU4t"></script>

    <!-- App favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico">

    <!-- Bootstrap Css -->
    <link href="../assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="../assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .register-card {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
            border: none;
            overflow: hidden;
            max-width: 1200px;
            margin: 0 auto;
        }

        .register-header {
            background: linear-gradient(135deg, rgb(80, 115, 151), rgb(48, 106, 165));
            color: white;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .btn-register {
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .price-tag {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }

        .price-tag.premium {
            background: linear-gradient(135deg, #fd7e14, #ffc107);
        }

        .price-tag.business {
            background: linear-gradient(135deg, #6f42c1, #e83e8c);
        }

        .price-tag.enterprise {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
        }

        .price-tag.ultimate {
            background: linear-gradient(135deg, #212529, #495057);
        }

        .package-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
        }

        .package-card:hover {
            border-color: #007bff;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.1);
            transform: translateY(-2px);
        }

        .package-card.selected {
            border-color: #007bff;
            background-color: #f8f9ff;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.15);
        }

        .package-card .badge-popular {
            position: absolute;
            top: -10px;
            right: 15px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
        }

        .package-card .badge-recommended {
            position: absolute;
            top: -10px;
            right: 15px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
        }

        .feature-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            margin: 6px 0;
            font-size: 13px;
            line-height: 1.4;
        }

        .feature-icon {
            color: #28a745;
            margin-right: 8px;
            font-size: 12px;
            min-width: 14px;
            margin-top: 2px;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .spinner-custom {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .package-price {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .package-duration {
            font-size: 12px;
            color: #6c757d;
        }

        .package-highlight {
            background: linear-gradient(135deg, rgb(50, 76, 114), rgb(69, 102, 180));
            color: white;
            text-align: center;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
        }

        .package-scroll {
            max-height: none;
            overflow-y: visible;
        }

        .package-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .package-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .package-scroll::-webkit-scrollbar-thumb {
            background: #007bff;
            border-radius: 10px;
        }

        @media (max-width: 992px) {
            .package-scroll {
                max-height: none;
            }
        }

        @media (max-width: 768px) {
            .package-card {
                margin: 10px 0;
                padding: 15px;
            }

            .package-price {
                font-size: 22px;
            }

            .feature-item {
                font-size: 13px;
            }

            .register-card {
                margin: 0 15px;
            }

            .feature-list {
                padding: 12px;
                margin: 12px 0;
            }

            .package-highlight {
                font-size: 13px;
                padding: 6px;
            }
        }

        @media (max-width: 576px) {
            .package-card {
                margin: 15px 0;
                padding: 18px;
            }

            .package-price {
                font-size: 24px;
            }

            .feature-item {
                font-size: 14px;
                margin: 8px 0;
            }

            .badge-popular,
            .badge-recommended {
                right: 10px;
                font-size: 10px;
                padding: 3px 10px;
            }

            .container-fluid {
                padding: 0 10px;
            }
        }
    </style>
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner-custom"></div>
            <h5>Memproses Pembayaran...</h5>
            <p class="text-muted mb-0">Mohon tunggu, jangan tutup halaman ini</p>
        </div>
    </div>

    <div class="account-pages my-5 pt-sm-5">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card register-card overflow-hidden">
                        <div class="register-header">
                            <div class="row">
                                <div class="col-8">
                                    <div class="text-white p-4">
                                        <h4 class="text-white mb-2">
                                            <i class="fas fa-crown me-2"></i>
                                            Pilih Paket Membership
                                        </h4>
                                        <p class="mb-2">Dragon Play Billing</p>
                                        <div id="selectedPrice" class="price-tag">
                                            <i class="fas fa-tag me-1"></i>
                                            Pilih Paket
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 align-self-end">
                                    <img src="../assets/images/profile-img.png" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <div class="text-center mt-4 mb-4">
                                <a href="index.php">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="../assets/images/logo.svg" alt="" class="rounded-circle"
                                                height="34">
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- Package Selection -->
                            <div class="package-selection mb-4">
                                <h5 class="text-center mb-4">
                                    <i class="fas fa-gift text-primary me-2"></i>
                                    Pilih Paket yang Sesuai dengan Bisnis Anda
                                </h5>

                                <div class="package-scroll">
                                    <div class="row g-3">
                                        <!-- Basic Package -->


                                        <!-- Starter Package -->
                                        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12">
                                            <div class="package-card" data-package="starter" data-price="350000">
                                                <div class="badge-recommended">POPULER</div>
                                                <div class="text-center">
                                                    <h6 class="mb-2">
                                                        <i class="fas fa-rocket text-success me-1"></i>
                                                        Starter
                                                    </h6>
                                                    <div class="package-price text-success">350K <span
                                                            style="font-size:12px">/ tahun</span></div>

                                                </div>

                                                <div class="package-highlight">
                                                    Android/Google TV Max 5 Units
                                                </div>

                                                <div class="feature-list">
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>SmartTV unlimited (Beli Modul)</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Billiard unlimited (Beli Modul)</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Portal Web & Booking</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>3 Akun karyawan</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Forum Komunitas</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>QR Code Interaktif</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-building feature-icon"></i>
                                                        <span>1 Cabang</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Business Package -->
                                        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12">
                                            <div class="package-card" data-package="business" data-price="500000">
                                                <div class="text-center">
                                                    <h6 class="mb-2">
                                                        <i class="fas fa-briefcase text-primary me-1"></i>
                                                        Business
                                                    </h6>
                                                    <div class="package-price text-primary">500K<span
                                                            style="font-size:12px">/ tahun</span></div>

                                                </div>

                                                <div class="package-highlight">
                                                    Android/Google TV: Max 15 Units
                                                </div>

                                                <div class="feature-list">
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>SmartTV unlimited (Beli Modul)</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Billiard unlimited (Beli Modul)</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Portal Web & Booking</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>3 Akun karyawan</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Forum Komunitas</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>QR Code Interaktif</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-building feature-icon"></i>
                                                        <span>1 Cabang</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Professional Package -->
                                        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12">
                                            <div class="package-card" data-package="professional" data-price="750000">
                                                <div class="text-center">
                                                    <h6 class="mb-2">
                                                        <i class="fas fa-crown text-warning me-1"></i>
                                                        Professional
                                                    </h6>
                                                    <div class="package-price text-warning">750K<span
                                                            style="font-size:12px">/ tahun</span></div>

                                                </div>

                                                <div class="package-highlight">
                                                    Android/Google TV: Max 25 Units
                                                </div>

                                                <div class="feature-list">
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>SmartTV unlimited (Beli Modul)</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Billiard unlimited (Beli Modul)</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Portal Web & Booking</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>3 Akun karyawan</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Forum Komunitas</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>QR Code Interaktif</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-building feature-icon"></i>
                                                        <span>1 Cabang</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Enterprise Package -->
                                        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12">
                                            <div class="package-card" data-package="enterprise" data-price="1000000">
                                                <div class="badge-popular">TERLENGKAP</div>
                                                <div class="text-center">
                                                    <h6 class="mb-2">
                                                        <i class="fas fa-gem text-danger me-1"></i>
                                                        Enterprise
                                                    </h6>
                                                    <div class="package-price text-danger">1JT<span
                                                            style="font-size:12px">/ tahun</span></div>

                                                </div>

                                                <div class="package-highlight">
                                                    Android/Google TV: Max 40 Units
                                                </div>

                                                <div class="feature-list">
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>SmartTV unlimited (Beli Modul)</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Billiard unlimited (Beli Modul)</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Portal Web & Booking</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>3 Akun karyawan</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>Forum Komunitas</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-check feature-icon"></i>
                                                        <span>QR Code Interaktif</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-building feature-icon"></i>
                                                        <span>1 Cabang</span>
                                                    </div>
                                                    <div class="feature-item">
                                                        <i class="fas fa-star feature-icon" style="color: #ffc107;"></i>
                                                        <span>Priority Support</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Multi-Branch Note -->
                                        <div class="col-12">
                                            <div class="alert alert-info mt-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Multi-Cabang:</strong> Dashboard owner GRATIS untuk pemilik
                                                lebih dari 1 cabang.
                                                Hubungi support untuk upgrade multi-cabang.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="p-3">
                                        <form id="payment-form" class="form-horizontal needs-validation" novalidate>
                                            <!-- Hidden input untuk package -->
                                            <input type="hidden" id="selected_package" name="package" value="">
                                            <input type="hidden" id="selected_amount" name="amount" value="">

                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-12">
                                                    <div class="mb-3">
                                                        <label for="useremail" class="form-label">
                                                            <i class="fas fa-envelope me-2"></i>Email
                                                        </label>
                                                        <input type="email" class="form-control" id="useremail"
                                                            name="email" placeholder="Masukkan email aktif Anda"
                                                            required maxlength="45">

                                                        <div class="invalid-feedback">Mohon masukkan email yang valid.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-6 col-12">
                                                    <div class="mb-3">
                                                        <label for="notlp" class="form-label">
                                                            <i class="fas fa-phone me-2"></i>No Handphone
                                                        </label>
                                                        <input type="number" class="form-control" id="notlp"
                                                            name="notlp" placeholder="Masukan no handphone" required
                                                            pattern="[0-9+\-\s()]+">
                                                        <div class="invalid-feedback">Mohon masukkan nomor telepon yang
                                                            valid.</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="username" class="form-label">
                                                    <i class="fas fa-user me-2"></i>Nama Lengkap
                                                </label>
                                                <input type="text" class="form-control" id="username" name="username"
                                                    placeholder="Masukkan nama lengkap Anda" required minlength="3">
                                                <div class="invalid-feedback">Nama minimal 3 karakter.</div>
                                            </div>
                                            <!-- <h2>Info Lokasi Pengguna</h2> -->

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
                                            <!-- Terms and Conditions -->
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                                    <label class="form-check-label small" for="terms">
                                                        Saya setuju dengan
                                                        <a href="#" class="text-decoration-none">Syarat & Ketentuan</a>
                                                        dan
                                                        <a href="#" class="text-decoration-none">Kebijakan Privasi</a>
                                                    </label>
                                                    <div class="invalid-feedback">Anda harus menyetujui syarat dan
                                                        ketentuan.</div>
                                                </div>
                                            </div>

                                            <div class="d-grid">
                                                <button
                                                    class="btn btn-primary btn-lg btn-register d-flex align-items-center justify-content-center gap-2"
                                                    type="submit" id="pay-button" disabled>
                                                    <span class="spinner-border spinner-border-sm d-none"
                                                        id="spinner"></span>
                                                    <i class="fas fa-credit-card me-2" id="btnIcon"></i>
                                                    <span id="btnText">Pilih Paket Terlebih Dahulu</span>
                                                </button>
                                            </div>

                                            <div class="text-center mt-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-shield-alt me-1"></i>
                                                    Pembayaran aman dengan enkripsi SSL
                                                </small>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back to Home -->
                    <div class="mt-4 text-center">
                        <a href="../index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Beranda
                        </a>
                    </div>

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
    <script src="../assets/libs/jquery/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="../assets/libs/simplebar/simplebar.min.js"></script>
    <script src="../assets/libs/node-waves/waves.min.js"></script>
    <script src="../assets/js/app.js"></script>

    <script>
        // Package data
        const packageData = {
            // basic: { name: 'Basic', price: 50000, units: 'Personal' },
            starter: { name: 'Starter', price: 350000, units: '5 Android TV' },
            business: { name: 'Business', price: 500000, units: '15 Android TV' },
            professional: { name: 'Professional', price: 750000, units: '25 Android TV' },
            enterprise: { name: 'Enterprise', price: 1000000, units: '40 Android TV' }
        };

        // Package selection logic
        $('.package-card').on('click', function () {
            // Remove selected class from all cards
            $('.package-card').removeClass('selected');

            // Add selected class to clicked card
            $(this).addClass('selected');

            // Get package data
            const packageType = $(this).data('package');
            const packagePrice = $(this).data('price');
            const packageInfo = packageData[packageType];

            // Update hidden inputs
            $('#selected_package').val(packageType);
            $('#selected_amount').val(packagePrice);

            // Update price display in header
            const formattedPrice = 'Rp ' + parseInt(packagePrice).toLocaleString('id-ID');
            $('#selectedPrice').html('<i class="fas fa-tag me-1"></i>' + formattedPrice);

            // Update button
            const buttonText = 'Bayar Sekarang - ' + formattedPrice;
            $('#btnText').text(buttonText);
            $('#pay-button').prop('disabled', false);

            // Add appropriate class to price tag
            $('#selectedPrice').removeClass('premium business enterprise ultimate');
            if (packageType === 'starter') {
                $('#selectedPrice').addClass('premium');
            } else if (packageType === 'business') {
                $('#selectedPrice').addClass('business');
            } else if (packageType === 'professional') {
                $('#selectedPrice').addClass('enterprise');
            } else if (packageType === 'enterprise') {
                $('#selectedPrice').addClass('ultimate');
            }
        });

        // Form submission
        $('#payment-form').on('submit', function (e) {
            e.preventDefault();

            // Check if package is selected
            if (!$('#selected_package').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Paket',
                    text: 'Silakan pilih paket membership terlebih dahulu',
                    confirmButtonColor: '#007bff'
                });
                return;
            }

            // Bootstrap form validation
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            // Show loading state
            showLoading(true);
            setButtonLoading(true);

            // AJAX request to save data
            $.ajax({
                type: 'POST',
                url: 'tokenmid.php',
                data: $(this).serialize(),
                dataType: 'json',
                timeout: 30000,

                success: function (response) {
                    console.log('Response:', response);

                    if (response.error) {
                        showError('Error!', response.error);
                        return;
                    }

                    if (!response.success || !response.order_id) {
                        showError('Error!', 'Gagal menyimpan data transaksi. Silakan coba lagi.');
                        return;
                    }

                    // Show success message dan redirect ke checkout
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Berhasil Disimpan!',
                        text: 'Mengalihkan ke halaman pembayaran...',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        // Redirect ke checkout page
                        window.location.href = response.redirect_url;
                    });
                },

                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Status:", status);
                    console.error("Response:", xhr.responseText);

                    let errorMessage = 'Gagal memproses transaksi. Email sudah terdaftar.';

                    if (status === 'timeout') {
                        errorMessage = 'Permintaan timeout. Periksa koneksi internet Anda dan coba lagi.';
                    } else if (xhr.status === 0) {
                        errorMessage = 'Tidak ada koneksi internet. Periksa jaringan Anda.';
                    } else if (xhr.status >= 500) {
                        errorMessage = 'Server bermasalah. Silakan coba lagi nanti.';
                    } else if (xhr.status === 400) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.error || errorMessage;
                        } catch (e) {
                            // Use default message
                        }
                    }

                    showError('Kesalahan Koneksi', errorMessage);
                },

                complete: function () {
                    // Reset loading state
                    showLoading(false);
                    setButtonLoading(false);
                }
            });
        });

        // Helper functions
        function showLoading(show) {
            if (show) {
                $('#loadingOverlay').css('display', 'flex');
            } else {
                $('#loadingOverlay').hide();
            }
        }

        function setButtonLoading(loading) {
            const button = $('#pay-button');
            const spinner = $('#spinner');
            const icon = $('#btnIcon');
            const text = $('#btnText');

            if (loading) {
                button.attr('disabled', true);
                spinner.removeClass('d-none');
                icon.addClass('d-none');
                text.text('Memproses...');
            } else {
                button.attr('disabled', false);
                spinner.addClass('d-none');
                icon.removeClass('d-none');

                // Restore original text based on selected package
                const packagePrice = $('#selected_amount').val();
                if (packagePrice) {
                    const formattedPrice = 'Rp ' + parseInt(packagePrice).toLocaleString('id-ID');
                    text.text('Bayar Sekarang - ' + formattedPrice);
                } else {
                    text.text('Pilih Paket Terlebih Dahulu');
                }
            }
        }

        function showError(title, message) {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Tutup'
            });
        }
    </script>
</body>

</html>