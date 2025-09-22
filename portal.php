<?php
require_once('include/config.php');
require_once 'include/crypto.php';
$license = isset($_GET['q']) ? decrypt($_GET['q']) : null;
// echo "<script>console.log('Merchand ID: $merchand');</script>";
$r = $con->query("SELECT * FROM userx WHERE license = '$license'");
foreach ($r as $rr) {
    $merchand = $rr['merchand'];
    $username = $rr['email'];
    $license = $rr['license'];
    $exp = $rr['license_exp'];
    $cabang = $rr['cabang'];
    $logox = $rr['logox'];
    $address = $rr['address'];
}

// Ambil kategori global (userx=ALL)
$sql_all = "SELECT id_category, name, status FROM tb_category WHERE userx='ALL' ORDER BY name ASC";
$result_all = $con->query($sql_all);

// Ambil kategori milik user (override status)
$sql_user = "SELECT name, status FROM tb_category WHERE userx='$username'";
$result_user = $con->query($sql_user);

// Buat array override status
$user_override = [];
if ($result_user && $result_user->num_rows > 0) {
    while ($row = $result_user->fetch_assoc()) {
        $user_override[strtolower($row['name'])] = strtolower($row['status']);
    }
}
if ($result_all->num_rows === 0) {
    die("Tidak ada data kategori.");
}

$montNOW = date("M Y");
$montNOWS = date("Y-m");
$monthNOWsimple = date("m/Y");
$dateNOW = date("d M Y");
$dateNOW2 = date("d M Y H:i:s");

$datting = date('Y-m-d');
$yesterNOW = date('d M Y', strtotime("-1 days"));
$dateNOWsimple = date("d/m/Y");
$yearNOW = date("Y");
$yearNOWsimple = date("Y");
?>
<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Dragonplay | <?= $merchand ?> - Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- owl.carousel css -->
    <link rel="stylesheet" href="assets/libs/owl.carousel/assets/owl.carousel.min.css">

    <link rel="stylesheet" href="assets/libs/owl.carousel/assets/owl.theme.default.min.css">

    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
    <style>
        .bg-overlay {
            background-color: transparent !important;
            /* hilangkan warna overlay */
            background-image: url('assets/images/background-portal.jpg') !important;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: rgba(255, 255, 255, 0.5);
            /* Putih transparan */
            font-size: 25px;
            font-weight: bold;
            white-space: nowrap;
            text-transform: uppercase;
            pointer-events: none;
            /* Biar gak ganggu klik */
            user-select: none;
            /* Tidak bisa di-select */
        }

        /* Enhanced Booking Card Animation */
        .booking-card {
            transition: all 0.4s ease;
        }

        .booking-card.highlight {
            border: 3px solid #007bff !important;
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        }

        .booking-card.highlight .card-header {
            background: linear-gradient(45deg, #007bff, #0056b3) !important;
            color: white !important;
        }

        /* Smooth focus animation for WhatsApp input */
        #whatsapp {
            transition: all 0.3s ease;
        }

        #whatsapp:focus {
            border-color: #007bff;
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.3);
        }

        /* Game Popup Styles */
        .game-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            backdrop-filter: blur(5px);
        }

        .game-popup.show {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeInPopup 0.4s ease-out;
        }

        .popup-content {
            background: white;
            border-radius: 15px;
            padding: 0;
            max-width: 800px;
            max-height: 80vh;
            width: 90%;
            overflow: hidden;
            position: relative;
            transform: scale(0.7);
            animation: popupSlideIn 0.4s ease-out forwards;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .popup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .popup-close {
            position: absolute;
            top: 15px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .popup-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .popup-body {
            padding: 20px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .game-item {
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .game-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
            background: linear-gradient(145deg, #fff, #f8f9fa);
        }

        .game-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: block;
        }

        .game-title {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .game-category {
            font-size: 12px;
            color: #666;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 10px;
            display: inline-block;
        }

        .gallery-image-container {
            position: relative;
            width: 100%;
            height: 250px;
            overflow: hidden;
            border-radius: 15px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .gallery-image-container:hover {
            transform: scale(1.02);
        }

        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 15px;
        }


        .blog-box {
            background: #fff;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .blog-box:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .no-gallery-state {
            text-align: center;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 15px;
            margin: 20px 0;
        }

        .modal-content {
            background: transparent;
            border: none;
        }

        .modal-body {
            padding: 0;
            position: relative;
        }

        .modal-image {
            width: 100%;
            height: auto;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .btn-close-custom {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.8);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-close-custom:hover {
            background: rgba(0, 0, 0, 0.9);
            color: white;
        }

        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.8);
        }

        @keyframes fadeInPopup {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes popupSlideIn {
            from {
                transform: scale(0.7) translateY(-50px);
                opacity: 0;
            }

            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .popup-content {
                width: 95%;
                max-height: 85vh;
            }

            .game-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }

            .game-item {
                padding: 10px;
            }

            .game-icon {
                font-size: 2rem;
            }
        }

        /* Pulse animation for highlight */
        @keyframes pulse {
            0% {
                border-color: #007bff;
                box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
            }

            50% {
                border-color: #0056b3;
                box-shadow: 0 0 40px rgba(0, 123, 255, 0.6);
            }

            100% {
                border-color: #007bff;
                box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
            }
        }

        .booking-card.highlight.pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>

<body data-bs-spy="scroll" data-bs-target="#topnav-menu" data-bs-offset="60">

    <nav class="navbar navbar-expand-lg navigation fixed-top sticky">
        <div class="container">
            <a class="navbar-logo d-flex align-items-center justify-content-center" href="#" style="height:70px;">
                <h4 class="logo logo-dark text-dark fw-semibold m-0">
                    <img src="<?= $logox ?>" width="40" height="40"
                        style="width:35px; height:35px; border-radius: 50%;">
                    <?= $merchand ?>
                </h4>
                <h4 class="logo logo-light text-white fw-semibold m-0">
                    <img src="<?= $logox ?>" width="40" height="40"
                        style="width:35px; height:35px; border-radius: 50%;">
                    <?= $merchand ?>
                </h4>
            </a>

            <button type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item waves-effect waves-light"
                data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav ms-auto" id="topnav-menu">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#facility">Facility</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#galery">Galery</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- hero section start -->
    <section class="section hero-section" id="home">
        <div class="bg-overlay bg-success"></div>
        <div class="container">
            <div class="row align-items-lg-center align-items-start">
                <div class="col-lg-5">
                    <div class="text-white-50">
                        <h1 class="text-white fw-semibold mb-0 hero-title">
                            Yuk, Booking PlayStation!
                        </h1>
                        <p class="font-size-14">
                            Biar kamu & temen-temen gak kehabisan slot, langsung amankan jadwal mainmu di sini!<br>
                            Main game favorit jadi gampang dan asik. Pilih unit, booking, dan langsung siap untuk
                            seru-seruan tanpa ribet!
                        </p>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <a href="javascript:void(0);" class="btn btn-success" id="bookNowBtn">Pesan Sekarang</a>
                            <!-- <a href="javascript:void(0);" class="btn btn-light" id="gameListBtn">Koleksi Game?</a> -->
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 col-md-8 col-sm-10 ms-lg-auto align-self-start">
                    <div class="card overflow-hidden mb-0 mt-0 mt-lg-0 booking-card" id="bookingCard">
                        <div class="card-header text-center text-dark">
                            <h5 class="mb-0">🎮 Booking Online</h5>
                        </div>
                        <div class="card-body">
                            <form id="bookingForm">
                                <div class="mb-1">
                                    <label class="form-label fw-semibold">📱 Nomor WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+62</span>
                                        <input type="tel" class="form-control" id="whatsapp" placeholder="812-3456-7890"
                                            maxlength="15" oninput="removeLeadingZero(this)" required>
                                    </div>

                                    <script>
                                        function removeLeadingZero(input) {
                                            if (input.value.startsWith('0')) {
                                                input.value = input.value.substring(1);
                                            }
                                        }
                                    </script>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label fw-semibold">👤 Nama</label>
                                    <input type="text" class="form-control" id="nama" placeholder="Nama lengkap"
                                        required>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label fw-semibold">🎯 Kategori Rental</label>
                                    <select class="form-select" id="category" required onchange="getPriceList()">
                                        <option value="">Pilih kategori</option>
                                        <?php
                                        if ($result_all && $result_all->num_rows > 0) {
                                            while ($cat = $result_all->fetch_assoc()) {
                                                $cat_name = strtolower($cat['name']);
                                                if (isset($user_override[$cat_name]) && $user_override[$cat_name] === 'disable')
                                                    continue;
                                                echo '<option value="' . htmlspecialchars($cat['name']) . '">' . htmlspecialchars($cat['name']) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label fw-semibold">⏱️ Durasi Sewa</label>
                                    <select class="form-select" id="duration" required disabled>
                                        <option value="">Pilih kategori terlebih dahulu</option>
                                    </select>
                                </div>

                                <div class="row mb-1">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">📅 Tanggal</label>
                                        <input type="date" class="form-control" id="tanggal" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">🕐 Waktu Mulai</label>
                                        <input type="time" class="form-control" id="waktu" required>
                                    </div>
                                </div>

                                <div class="total-price text-center mb-3" id="priceCard" style="display: none;">
                                    <h5 class="mb-1">💰 Total Harga</h5>
                                    <h3 class="mb-0" id="totalPrice">Rp 0</h3>
                                </div>

                                <button type="submit" class="btn whatsapp-btn w-100 py-2">
                                    📱 Booking Sekarang
                                </button>

                                <div class="mt-3 p-2 bg-light rounded small">
                                    <?php
                                    $message = "";
                                    // Ambil data ketentuan dari database
                                    $sql = "SELECT name FROM tb_ketentuan where userx='$username'";
                                    $result = $con->query($sql);

                                    $message .= "Ketentuan : ";

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $message .= "• " . $row['name'];
                                        }

                                    } else {
                                        $message .= "• Tidak ada ketentuan\n";
                                    }




                                    echo $message;
                                    ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Game List Popup -->
    <div class="game-popup" id="gamePopup">
        <div class="popup-content">
            <div class="popup-header">
                <h3>🎮 Koleksi Game PlayStation</h3>
                <p class="mb-0">Pilihan game terlengkap untuk seru-seruan!</p>
                <button class="popup-close" id="closePopup">&times;</button>
            </div>
            <div class="popup-body">
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-center mb-3">🏆 Game Populer</h5>
                        <div class="game-grid">
                            <div class="game-item">
                                <div class="game-icon">🥊</div>
                                <div class="game-title">Tekken 8</div>
                                <div class="game-category">Fighting</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">⚽</div>
                                <div class="game-title">FIFA 24</div>
                                <div class="game-category">Sports</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">🏎️</div>
                                <div class="game-title">Gran Turismo 7</div>
                                <div class="game-category">Racing</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">🎯</div>
                                <div class="game-title">Call of Duty</div>
                                <div class="game-category">Action</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">⚔️</div>
                                <div class="game-title">God of War</div>
                                <div class="game-category">Adventure</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">🕷️</div>
                                <div class="game-title">Spider-Man 2</div>
                                <div class="game-category">Action</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">🦄</div>
                                <div class="game-title">Hogwarts Legacy</div>
                                <div class="game-category">RPG</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">💀</div>
                                <div class="game-title">Mortal Kombat 1</div>
                                <div class="game-category">Fighting</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">🏀</div>
                                <div class="game-title">NBA 2K24</div>
                                <div class="game-category">Sports</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">🤖</div>
                                <div class="game-title">Horizon</div>
                                <div class="game-category">Adventure</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">🎸</div>
                                <div class="game-title">Guitar Hero</div>
                                <div class="game-category">Music</div>
                            </div>
                            <div class="game-item">
                                <div class="game-icon">🧟</div>
                                <div class="game-title">Resident Evil 4</div>
                                <div class="game-category">Horror</div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <p class="text-muted"><em>Dan masih banyak game seru lainnya!</em></p>
                            <button class="btn btn-success" id="bookFromPopup">
                                🎮 Booking Sekarang Juga!
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- currency price section start -->
    <section class="section bg-white p-0" id="facility">
        <div class="container">
            <div class="currency-price">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="container">
                                        <div class="row">

                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-lg-6">
                                                <div class="text-muted">
                                                    <h4><?= $merchand ?></h4>
                                                    <p><?= $address ?></p>

                                                    <?php
                                                    // Ambil total unit per type_ps
                                                    $totalUnitsQuery = $con->query("
            SELECT type_ps, COUNT(*) AS total_unit
            FROM playstations
            WHERE userx = '$username'
            GROUP BY type_ps
        ");

                                                    $totalUnits = [];
                                                    while ($row = $totalUnitsQuery->fetch_assoc()) {
                                                        $totalUnits[$row['type_ps']] = $row['total_unit'];
                                                    }

                                                    // Ambil available unit per type_ps
                                                    $availableUnitsQuery = $con->query("
            SELECT type_ps, COUNT(*) AS available
            FROM playstations
            WHERE status = 'available' AND userx = '$username'
            GROUP BY type_ps
        ");

                                                    $availableUnits = [];
                                                    while ($row = $availableUnitsQuery->fetch_assoc()) {
                                                        $availableUnits[$row['type_ps']] = $row['available'];
                                                    }

                                                    echo '<div class="mt-4">';
                                                    echo '<h4 class="fw-semibold mb-3">🎮 Ketersediaan Unit</h4>';

                                                    if (!empty($totalUnits)) {
                                                        foreach ($totalUnits as $type => $total) {
                                                            // Jika tidak ada di available units, berarti available = 0
                                                            $available = isset($availableUnits[$type]) ? $availableUnits[$type] : 0;

                                                            echo '<div class="p-3 mb-3 border rounded shadow-sm bg-light d-flex justify-content-between align-items-center">';
                                                            echo '<div><h5 class="mb-1">' . htmlspecialchars($type) . '</h5>';

                                                            // Status text yang lebih informatif
                                                            if ($available == 0) {
                                                                echo '<small class="text-danger fw-bold">Semua Unit Sedang Digunakan </small></div>';
                                                            } elseif ($available < $total) {
                                                                echo '<small class="text-warning fw-bold">Tersedia ' . $available . ' dari ' . $total . ' Unit</small></div>';
                                                            } else {
                                                                echo '<small class="text-success fw-bold">Tersedia ' . $available . ' dari ' . $total . ' Unit</small></div>';
                                                            }

                                                            // Badge warna dinamis
                                                            if ($available == 0) {
                                                                $badgeClass = 'bg-danger';
                                                                $badgeText = 'Full Booked';
                                                            } elseif ($available < $total) {
                                                                $badgeClass = 'bg-warning text-dark';
                                                                $badgeText = $available . ' / ' . $total;
                                                            } else {
                                                                $badgeClass = 'bg-success';
                                                                $badgeText = 'AVAILABLE';
                                                            }

                                                            echo '<span class="badge ' . $badgeClass . ' fs-6">' . $badgeText . '</span>';
                                                            echo '</div>';
                                                        }
                                                    } else {
                                                        echo '<div class="alert alert-info">Belum ada unit PlayStation yang terdaftar.</div>';
                                                    }

                                                    echo '</div>';
                                                    ?>
                                                </div>
                                            </div>

                                            <div class="col-lg-6 ms-auto">
                                                <div class="mt-4 mt-lg-0">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="card border">
                                                                <div class="card-body">


                                                                    <div class="text-muted">
                                                                        <h4>Fasilitas</h4>
                                                                        <div class="text-muted">
                                                                            <?php
                                                                            $facility = $con->query("SELECT name FROM tb_facility WHERE userx = '$username' ORDER BY name ASC");

                                                                            if ($facility->num_rows > 0) {
                                                                                while ($f = $facility->fetch_assoc()) {
                                                                                    echo '<p class="mb-1"><i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> ' . htmlspecialchars($f['name']) . '</p>';
                                                                                }
                                                                            } else {
                                                                                echo '<p class="mb-0 text-danger"><i class="mdi mdi-alert-circle-outline align-middle me-1"></i> Tidak ada fasilitas terdaftar.</p>';
                                                                            }
                                                                            ?>
                                                                        </div>



                                                                    </div>

                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog start -->
    <section class="section bg-white" id="galery">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mb-5">
                        <h4>Galeri </h4>
                    </div>
                </div>
            </div>
            <?php
            // Query untuk ambil data dari tabel tb_galery
            $query = "SELECT name, date, pic FROM tb_galery WHERE userx = '$username'";
            $result = mysqli_query($con, $query);
            ?>


            <div class="row">
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <?php while ($row = mysqli_fetch_array($result)) { ?>
                        <div class="col-xl-4 col-md-6 col-sm-12">
                            <div class="blog-box mb-4">
                                <div class="gallery-image-container"
                                    onclick="openImageModal('<?php echo htmlspecialchars($row['pic']); ?>', '<?php echo htmlspecialchars($row['name']); ?>')">
                                    <img src="<?php echo htmlspecialchars($row['pic']); ?>"
                                        alt="<?php echo htmlspecialchars($row['name']); ?>" class="gallery-image">
                                    <div class="watermark"><?php echo htmlspecialchars($merchand); ?></div>
                                </div>

                                <div class="p-3">
                                    <div class="text-muted">
                                        <p class="mb-2">
                                            <i class="bx bx-calendar me-1"></i>
                                            <?php echo date('d M, Y', strtotime($row['date'])); ?>
                                        </p>
                                        <h5 class="mb-0 text-dark"><?php echo htmlspecialchars($row['name']); ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="col-12">
                        <div class="no-gallery-state">
                            <i class="bx bx-image-alt" style="font-size: 4rem; color: #ccc;"></i>
                            <h4 class="mt-3 text-muted">Tidak ada galeri</h4>
                            <p class="text-muted mb-0">Belum ada gambar yang tersedia untuk ditampilkan.</p>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- Modal untuk menampilkan gambar besar -->
            <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="btn-close-custom" onclick="closeImageModal()">
                                <i class="bx bx-x"></i>
                            </button>
                            <img src="" alt="" class="modal-image" id="modalImage">
                            <div class="watermark"><?php echo htmlspecialchars($merchand); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function openImageModal(imageSrc, imageAlt) {
                    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                    const modalImage = document.getElementById('modalImage');

                    modalImage.src = imageSrc;
                    modalImage.alt = imageAlt;

                    modal.show();
                }

                function closeImageModal() {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('imageModal'));
                    if (modal) {
                        modal.hide();
                    }
                }

                // Close modal when clicking on backdrop
                document.getElementById('imageModal').addEventListener('click', function (e) {
                    if (e.target === this) {
                        closeImageModal();
                    }
                });

                // Close modal with Escape key
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        closeImageModal();
                    }
                });
            </script>
        </div>
        </div>
    </section>

    <!-- Footer start -->
    <footer class="landing-footer">
        <div class="container">

            <hr class="footer-border my-0">

            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-0">
                        <img src="assets/images/logo-light.png" alt="" height="20">
                    </div>

                    <p class="mb-2">
                        <script>document.write(new Date().getFullYear())</script> © dragonplay.id Design & Develop by
                        dbk.dev
                    </p>

                </div>

            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>

    <script src="assets/libs/jquery.easing/jquery.easing.min.js"></script>

    <!-- Plugins js-->
    <script src="assets/libs/jquery-countdown/jquery.countdown.min.js"></script>

    <!-- owl.carousel js -->
    <script src="assets/libs/owl.carousel/owl.carousel.min.js"></script>

    <!-- ICO landing init -->
    <script src="assets/js/pages/ico-landing.init.js"></script>

    <script src="assets/js/app.js"></script>

    <script>
        // Enhanced JavaScript functionality
        document.addEventListener('DOMContentLoaded', function () {
            const bookNowBtn = document.getElementById('bookNowBtn');
            const gameListBtn = document.getElementById('gameListBtn');
            const bookingCard = document.getElementById('bookingCard');
            const whatsappInput = document.getElementById('whatsapp');
            const gamePopup = document.getElementById('gamePopup');
            const closePopup = document.getElementById('closePopup');
            const bookFromPopup = document.getElementById('bookFromPopup');

            // Book Now Button Functionality
            bookNowBtn.addEventListener('click', function () {
                // Add highlight animation to booking card
                bookingCard.classList.add('highlight', 'pulse');

                // Smooth scroll to booking card
                bookingCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Focus on WhatsApp input after animation
                setTimeout(() => {
                    whatsappInput.focus();
                    whatsappInput.select();
                }, 500);

                // Remove highlight after 5 seconds
                setTimeout(() => {
                    bookingCard.classList.remove('highlight', 'pulse');
                }, 50);
            });

            // Game List Button Functionality
            gameListBtn.addEventListener('click', function () {
                gamePopup.classList.add('show');
                document.body.style.overflow = 'hidden'; // Prevent background scroll
            });

            // Close popup functionality
            function closeGamePopup() {
                gamePopup.classList.remove('show');
                document.body.style.overflow = 'auto'; // Restore scroll
            }

            closePopup.addEventListener('click', closeGamePopup);

            // Close popup when clicking outside
            gamePopup.addEventListener('click', function (e) {
                if (e.target === gamePopup) {
                    closeGamePopup();
                }
            });

            // Book from popup functionality
            bookFromPopup.addEventListener('click', function () {
                closeGamePopup();

                // Trigger book now functionality
                setTimeout(() => {
                    bookNowBtn.click();
                }, 300);
            });

            // Close popup with Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && gamePopup.classList.contains('show')) {
                    closeGamePopup();
                }
            });

            // Add hover effects to game items
            const gameItems = document.querySelectorAll('.game-item');
            gameItems.forEach(item => {
                item.addEventListener('mouseenter', function () {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });

                item.addEventListener('mouseleave', function () {
                    this.style.transform = 'translateY(-5px) scale(1)';
                });
            });
        });

        // Original getPriceList function
        function getPriceList() {
            const category = document.getElementById('category').value;
            const durationSelect = document.getElementById('duration');

            if (!category) {
                durationSelect.innerHTML = '<option value="">Pilih kategori terlebih dahulu</option>';
                durationSelect.disabled = true;
                return;
            }

            const formData = new FormData();
            formData.append('type_ps', category);
            formData.append('userx', '<?php echo $username; ?>');

            fetch('controller/get_price.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        durationSelect.innerHTML = '<option value="">Harga belum tersedia</option>';
                        durationSelect.disabled = true;
                        return;
                    }

                    durationSelect.innerHTML = '<option value="">Pilih durasi</option>';

                    data.forEach(item => {
                        let text = `${item.duration} Menit - Rp ${new Intl.NumberFormat('id-ID').format(item.price)}`;
                        durationSelect.innerHTML += `<option value="${item.duration}" data-price="${item.price}">${text}</option>`;
                    });

                    durationSelect.disabled = false;
                })
                .catch(err => {
                    console.error(err);
                    durationSelect.innerHTML = '<option value="">Gagal load harga</option>';
                    durationSelect.disabled = true;
                });
        }

        // Duration change handler for price calculation
        document.addEventListener('DOMContentLoaded', function () {
            const durationSelect = document.getElementById('duration');
            const priceCard = document.getElementById('priceCard');
            const totalPrice = document.getElementById('totalPrice');

            durationSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.dataset.price) {
                    const price = parseInt(selectedOption.dataset.price);
                    totalPrice.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(price)}`;
                    priceCard.style.display = 'block';
                } else {
                    priceCard.style.display = 'none';
                }
            });
        });
        // Form submission handler - Tambahkan script ini ke file utama Anda
        document.addEventListener('DOMContentLoaded', function () {
            const bookingForm = document.getElementById('bookingForm');
            const submitBtn = bookingForm.querySelector('button[type="submit"]');

            bookingForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // Ambil semua data form
                const whatsapp = document.getElementById('whatsapp').value.trim();
                const nama = document.getElementById('nama').value.trim();
                const category = document.getElementById('category').value;
                const duration = document.getElementById('duration').value;
                const tanggal = document.getElementById('tanggal').value;
                const waktu = document.getElementById('waktu').value;

                // Validasi form
                if (!whatsapp || !nama || !category || !duration || !tanggal || !waktu) {
                    alert('Semua field harus diisi!');
                    return;
                }

                // Validasi nomor WhatsApp
                const phoneRegex = /^[0-9]{10,13}$/;
                const cleanPhone = whatsapp.replace(/[^0-9]/g, '');
                if (!phoneRegex.test(cleanPhone)) {
                    alert('Nomor WhatsApp tidak valid! Gunakan format: 812-3456-7890');
                    document.getElementById('whatsapp').focus();
                    return;
                }

                // Validasi tanggal tidak boleh kemarin
                const selectedDate = new Date(tanggal);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (selectedDate < today) {
                    alert('Tanggal booking tidak boleh kemarin atau hari sebelumnya!');
                    document.getElementById('tanggal').focus();
                    return;
                }


                // Ambil harga dari option yang dipilih
                const durationSelect = document.getElementById('duration');
                const selectedOption = durationSelect.options[durationSelect.selectedIndex];
                const price = selectedOption.dataset.price || 0;

                // Disable button dan ubah text
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Memproses booking...';

                // Siapkan form data
                const formData = new FormData();
                formData.append('whatsapp', whatsapp);
                formData.append('nama', nama);
                formData.append('category', category);
                formData.append('duration', duration);
                formData.append('tanggal', tanggal);
                formData.append('waktu', waktu);
                formData.append('userx', '<?php echo $username; ?>'); // Dari PHP
                formData.append('price', price);

                // Kirim ke server
                fetch('controller/save_booking.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Tampilkan pesan sukses
                            alert('🎉 Booking berhasil!\n\nKode Booking: ' + data.booking_code + '\n\nAnda akan diarahkan ke WhatsApp untuk konfirmasi.');

                            // Redirect ke WhatsApp
                            window.open(data.whatsapp_url, '_blank');

                            // Reset form
                            bookingForm.reset();
                            document.getElementById('duration').disabled = true;
                            document.getElementById('duration').innerHTML = '<option value="">Pilih kategori terlebih dahulu</option>';
                            document.getElementById('priceCard').style.display = 'none';

                            // Remove highlight dari card
                            document.getElementById('bookingCard').classList.remove('highlight', 'pulse');

                        } else {
                            alert('❌ Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ Terjadi kesalahan saat memproses booking. Silakan coba lagi.');
                    })
                    .finally(() => {
                        // Restore button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Format input nomor WhatsApp secara real-time
            const whatsappInput = document.getElementById('whatsapp');
            whatsappInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/[^0-9]/g, '');

                // Format dengan tanda hubung untuk kemudahan baca
                if (value.length > 3 && value.length <= 7) {
                    value = value.replace(/(\d{3})(\d{1,4})/, '$1-$2');
                } else if (value.length > 7) {
                    value = value.replace(/(\d{3})(\d{4})(\d{1,4})/, '$1-$2-$3');
                }

                e.target.value = value;
            });

            // Set minimum date ke hari ini
            const dateInput = document.getElementById('tanggal');
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
            dateInput.value = today; // Set default ke hari ini

            // Set default waktu ke jam berikutnya
            const timeInput = document.getElementById('waktu');
            const now = new Date();
            const nextHour = new Date(now.getTime() + 60 * 60 * 1000); // +1 jam
            timeInput.value = nextHour.toTimeString().slice(0, 5);
        });

        // Tambahan: Auto-calculate total saat durasi berubah
        document.addEventListener('DOMContentLoaded', function () {
            const durationSelect = document.getElementById('duration');
            const priceCard = document.getElementById('priceCard');
            const totalPrice = document.getElementById('totalPrice');

            durationSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.dataset.price) {
                    const price = parseInt(selectedOption.dataset.price);
                    totalPrice.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(price)}`;
                    priceCard.style.display = 'block';

                    // Animasi highlight untuk price card
                    priceCard.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        priceCard.style.transform = 'scale(1)';
                    }, 200);
                } else {
                    priceCard.style.display = 'none';
                }
            });
        });
    </script>

</body>

</html>