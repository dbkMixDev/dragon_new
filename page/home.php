<!-- JAVASCRIPT -->
<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- dashboard init -->
<script src="assets/js/pages/dashboard.init.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>
<?php
// session_start();

require_once './include/config.php'; // koneksi database
require_once './include/crypto.php'; // jika password ingin di-enkripsi, tapi gunakan password_hash lebih baik
$username = $_SESSION['username'];
$level = $_SESSION['level'];
$r = $con->query("SELECT * FROM userx WHERE username = '$username'");
foreach ($r as $rr) {
    $merchand = $rr['merchand'];

    $license = $rr['license'];
    $exp = $rr['license_exp'];
    $cabang = $rr['cabang'];
    $address = $rr['address'];
    $logox = $rr['logox'];
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
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Dashboard</h4>

                    <span class="text-muted">Report date : <?= $dateNOW ?></span>




                </div>
            </div>
        </div>
        <!-- end page title -->
        <?php

        $r = $con->query("SELECT
    -- Bulan sekarang
    COALESCE(final_now.total_final, 0) AS total_final_now,
    COALESCE(final_now.total_promo, 0) AS total_promo_now,
    COALESCE(out_now.total_out, 0) AS total_out_now,
    COALESCE(final_now.total_final, 0) - COALESCE(out_now.total_out, 0) AS selisih_now,

    COALESCE(fnb_now.total_fnb, 0) AS total_fnb_now,
    COALESCE(rent_now.total_rent, 0) AS total_rent_now,
    COALESCE(ps_now.total_ps, 0) AS total_ps_now,

    -- Bulan sebelumnya
    COALESCE(final_prev.total_final, 0) AS total_final_prev,
    COALESCE(final_prev.total_promo, 0) AS total_promo_prev,
    COALESCE(out_prev.total_out, 0) AS total_out_prev,
    COALESCE(final_prev.total_final, 0) - COALESCE(out_prev.total_out, 0) AS selisih_prev,

    COALESCE(fnb_prev.total_fnb, 0) AS total_fnb_prev,
    COALESCE(rent_prev.total_rent, 0) AS total_rent_prev,
    COALESCE(ps_prev.total_ps, 0) AS total_ps_prev,

    -- Hari ini
    COALESCE(final_today.total_final, 0) AS total_final_today,
    COALESCE(final_today.total_promo, 0) AS total_promo_today,
    COALESCE(out_today.total_out, 0) AS total_out_today,
    COALESCE(fnb_today.total_fnb, 0) AS total_fnb_today,
    COALESCE(rent_today.total_rent, 0) AS total_rent_today,
    COALESCE(ps_today.total_ps, 0) AS total_ps_today,

    -- Kemarin
    COALESCE(final_yesterday.total_final, 0) AS total_final_yesterday,
    COALESCE(final_yesterday.total_promo, 0) AS total_promo_yesterday,
    COALESCE(out_yesterday.total_out, 0) AS total_out_yesterday,
    COALESCE(fnb_yesterday.total_fnb, 0) AS total_fnb_yesterday,
    COALESCE(rent_yesterday.total_rent, 0) AS total_rent_yesterday,
    COALESCE(ps_yesterday.total_ps, 0) AS total_ps_yesterday

FROM
-- ==============================================
-- BULAN INI - FIXED: Gunakan DATE(tf.created_at) dan JOIN approach
-- ==============================================
(SELECT 
    SUM(tf.grand_total) AS total_final, 
    SUM(tf.promo) AS total_promo 
 FROM tb_trans_final tf
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1) 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS final_now

CROSS JOIN
-- FIXED: Gunakan tb_trans_final dengan filter TRX-OUT%, bukan tb_trans_out
(SELECT 
    SUM(tf.grand_total) AS total_out 
 FROM tb_trans_final tf
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1) 
   AND tf.id_trans LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS out_now

CROSS JOIN
-- FIXED: JOIN dengan tb_trans_final untuk konsistensi tanggal
(SELECT 
    SUM(fnb.total) AS total_fnb 
 FROM tb_trans_final tf
 INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
   AND fnb.type = 'FnB' 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS fnb_now

CROSS JOIN
-- FIXED: JOIN dengan tb_trans_final untuk konsistensi tanggal
(SELECT 
    SUM(t.harga) AS total_rent 
 FROM tb_trans_final tf
 INNER JOIN tb_trans t ON tf.invoice = t.inv
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (t.is_deleted IS NULL OR t.is_deleted != 1)
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS rent_now

CROSS JOIN
-- FIXED: JOIN dengan tb_trans_final untuk konsistensi tanggal
(SELECT 
    SUM(fnb.total) AS total_ps 
 FROM tb_trans_final tf
 INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
   AND fnb.type = 'Others' 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS ps_now

-- ==============================================
-- BULAN SEBELUMNYA - FIXED: Same logic as above
-- ==============================================
CROSS JOIN
(SELECT 
    SUM(tf.grand_total) AS total_final, 
    SUM(tf.promo) AS total_promo 
 FROM tb_trans_final tf
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1) 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS final_prev

CROSS JOIN
(SELECT 
    SUM(tf.grand_total) AS total_out 
 FROM tb_trans_final tf
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1) 
   AND tf.id_trans LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS out_prev

CROSS JOIN
(SELECT 
    SUM(fnb.total) AS total_fnb 
 FROM tb_trans_final tf
 INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
   AND fnb.type = 'FnB' 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS fnb_prev

CROSS JOIN
(SELECT 
    SUM(t.harga) AS total_rent 
 FROM tb_trans_final tf
 INNER JOIN tb_trans t ON tf.invoice = t.inv
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (t.is_deleted IS NULL OR t.is_deleted != 1)
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS rent_prev

CROSS JOIN
(SELECT 
    SUM(fnb.total) AS total_ps 
 FROM tb_trans_final tf
 INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
 WHERE DATE_FORMAT(tf.created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m') 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
   AND fnb.type = 'Others' 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS ps_prev

-- ==============================================
-- HARI INI - FIXED: Same logic as above
-- ==============================================
CROSS JOIN
(SELECT 
    SUM(tf.grand_total) AS total_final, 
    SUM(tf.promo) AS total_promo 
 FROM tb_trans_final tf
 WHERE DATE(tf.created_at) = CURDATE() 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1) 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS final_today

CROSS JOIN
(SELECT 
    SUM(tf.grand_total) AS total_out 
 FROM tb_trans_final tf
 WHERE DATE(tf.created_at) = CURDATE() 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1) 
   AND tf.id_trans LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS out_today

CROSS JOIN
(SELECT 
    SUM(fnb.total) AS total_fnb 
 FROM tb_trans_final tf
 INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
 WHERE DATE(tf.created_at) = CURDATE() 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
   AND fnb.type = 'FnB' 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS fnb_today

CROSS JOIN
(SELECT 
    SUM(t.harga) AS total_rent 
 FROM tb_trans_final tf
 INNER JOIN tb_trans t ON tf.invoice = t.inv
 WHERE DATE(tf.created_at) = CURDATE() 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (t.is_deleted IS NULL OR t.is_deleted != 1)
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS rent_today

CROSS JOIN
(SELECT 
    SUM(fnb.total) AS total_ps 
 FROM tb_trans_final tf
 INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
 WHERE DATE(tf.created_at) = CURDATE() 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
   AND fnb.type = 'Others' 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS ps_today

-- ==============================================
-- KEMARIN - FIXED: Same logic as above
-- ==============================================
CROSS JOIN
(SELECT 
    SUM(tf.grand_total) AS total_final, 
    SUM(tf.promo) AS total_promo 
 FROM tb_trans_final tf
 WHERE DATE(tf.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1) 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS final_yesterday

CROSS JOIN
(SELECT 
    SUM(tf.grand_total) AS total_out 
 FROM tb_trans_final tf
 WHERE DATE(tf.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1) 
   AND tf.id_trans LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS out_yesterday

CROSS JOIN
(SELECT 
    SUM(fnb.total) AS total_fnb 
 FROM tb_trans_final tf
 INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
 WHERE DATE(tf.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
   AND fnb.type = 'FnB' 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS fnb_yesterday

CROSS JOIN
(SELECT 
    SUM(t.harga) AS total_rent 
 FROM tb_trans_final tf
 INNER JOIN tb_trans t ON tf.invoice = t.inv
 WHERE DATE(tf.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (t.is_deleted IS NULL OR t.is_deleted != 1)
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS rent_yesterday

CROSS JOIN
(SELECT 
    SUM(fnb.total) AS total_ps 
 FROM tb_trans_final tf
 INNER JOIN tb_trans_fnb fnb ON tf.invoice = fnb.inv
 WHERE DATE(tf.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
   AND (tf.is_deleted IS NULL OR tf.is_deleted != 1)
   AND (fnb.is_deleted IS NULL OR fnb.is_deleted != 1)
   AND fnb.type = 'Others' 
   AND tf.id_trans NOT LIKE 'TRX-OUT%'
   AND tf.userx = '$username') AS ps_yesterday;
");

        foreach ($r as $rr) {
            $vlmonth = $rr['selisih_now'];

            $vlmonthkotorPromo = $rr['total_promo_now'];
            $vlmonthkeluar = $rr['total_out_now'];
            $vlmonth_prev = $rr['selisih_prev'];
            $vlmonthkotor_prev = $rr['total_final_prev'];
            $vlmonthkeluar_prev = $rr['total_final_prev'];
            $rentnow = $rr['total_rent_now'];
            $fnbnow = $rr['total_fnb_now'];
            $psnow = $rr['total_ps_now'];
            $rentnowtoday = $rr['total_rent_today'];
            $fnbnowtoday = $rr['total_fnb_today'];
            $psnowtoday = $rr['total_ps_today'];
            $outtoday = $rr['total_out_today'];
            $promotoday = $rr['total_promo_today'];
            $vlmonthkotor = $rentnow + $psnow + $fnbnow - $vlmonthkotorPromo;
        }

        $r = $con->query("SELECT count(*) AS totalunit
FROM playstations 
WHERE userx ='$username'");

        foreach ($r as $rr) {
            $unit = $rr['totalunit'];
        }


        ?>
        <div class="row">
            <div class="col-xl-4">
                <div class="card overflow-hidden">
                    <div class="bg-primary bg-soft">
                        <div class="row">
                            <div class="col-7">
                                <div class="text-primary p-3">
                                    <h5 class="text-primary">Welcome Back !</h5>
                                    <p><?= $merchand ?></p>
                                </div>
                            </div>
                            <div class="col-5 align-self-end">
                                <img src="assets/images/profile-img.png" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="avatar-md profile-user-wid mb-4">
                                    <img src="<?= $logox ?>" alt="" class="img-thumbnail rounded-circle">
                                </div>
                                <h5 class="font-size-15 text-truncate"><?= $level ?></h5>
                                <p class="text-muted mb-0 text-truncate"><?= $userid ?></p>
                            </div>

                            <div class="col-sm-8">
                                <div class="pt-4">

                                    <div class="row">
                                        <div class="col-4">
                                            <h5 class="font-size-15"><?= $unit ?></h5>
                                            <p class="text-muted mb-0">Unit</p>
                                        </div>
                                        <div class="col-8">
                                            <h5 class="font-size-15">License</h5>
                                            <p class="text-muted mb-0"><?= $license ?></p>
                                            <?php
                                            // Format tanggal exp jadi "06 Jun 2026"
                                            $exp_formatted = '-';
                                            if (!empty($exp) && $exp !== '0000-00-00') {
                                                $exp_formatted = date('d M Y', strtotime($exp));
                                            }
                                            ?>
                                            <span style="font-size:10px"
                                                class="text-muted mb-0"><?= $exp_formatted ?></span>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <?php
                                        // Format Rp. dengan number_format
                                        $vlmonth_formatted = $fnbnowtoday + $psnowtoday + $rentnowtoday - $outtoday;
                                        ?>
                                        <span class="btn btn-primary waves-effect waves-light btn-xl">
                                            <i class="mdi mdi-wallet"></i>
                                            <b>Rp.
                                                <?= number_format($vlmonth_formatted - $promotoday, 0, ',', '.') ?></b>
                                            <span style="font-size:10px">Net Profit</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Monthly Net Income <?= $montNOW ?></h4>
                        <div class="row">
                            <div class="col-sm-6">

                                <h3><span style="font-size:10px">Rp.</span>
                                    <?= number_format($vlmonthkotor - $vlmonthkeluar, 0, ',', '.') ?></h3>

                                <p class="text-muted"><span class="text-success me-2">
                                        <?php
                                        if ($vlmonthkotor_prev == 0) {
                                            if ($vlmonthkotor == 0) {
                                                $growth = 0;
                                                echo '0%';
                                            } else {
                                                $growth = 100; // Jika bulan sebelumnya 0, anggap pertumbuhan 100%
                                                echo '∞%';
                                                // Atau echo '+100%'; jika kamu ingin menyederhanakan
                                            }
                                        } else {
                                            $growth = (($vlmonthkotor - $vlmonthkotor_prev) / $vlmonthkotor_prev) * 100;
                                            echo number_format($growth, 2) . '%';
                                        }
                                        ?>
                                        <i class="mdi mdi-arrow-up"></i>
                                    </span> From previous period</p>

                                <div class="mt-4">
                                    <!-- <a href="javascript: void(0);" class="btn btn-primary waves-effect waves-light btn-sm">View More <i class="mdi mdi-arrow-right ms-1"></i></a> -->
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mt-4 mt-sm-0">
                                    <div id="radialBar-chart2" data-colors='["--bs-primary"]' class="apex-charts"></div>

                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-4">
                                    <div>
                                        <p class="text-muted mb-2">Gross Profit</p>
                                        <h5 class="mb-0 font-size-13"><span class="font-size-10 text-muted">Rp
                                            </span><?= number_format($vlmonthkotor + $vlmonthkotorPromo - $vlmonthkeluar, 0, ',', '.') ?>
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div>
                                        <p class="text-muted mb-2">Promo Used</p>
                                        <h5 class="mb-0 font-size-13 text-warning">(<span
                                                class="font-size-10 text-warning">Rp
                                            </span><?= number_format($vlmonthkotorPromo, 0, ',', '.') ?>)</h5>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div>
                                        <p class="text-muted mb-2">Net Profit</p>
                                        <h5 class="mb-0 font-size-13"><span class="font-size-10 text-muted">Rp
                                            </span></span><?= number_format($vlmonthkotor - $vlmonthkeluar, 0, ',', '.') ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="row">

                    <div class="col-sm-4">
                        <div class="card">
                            <div class="card-body">

                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-xs me-3">
                                        <span
                                            class="avatar-title rounded-circle bg-primary bg-soft text-primary font-size-18">
                                            <i class="bx bx-archive-in"></i>
                                        </span>
                                    </div>
                                    <h5 class="font-size-14 mb-0">Rental Profit</h5>
                                </div>
                                <div class="text-muted mt-4">
                                    <h4><span style="font-size:10px">Rp.</span>
                                        <?= number_format($rentnowtoday, 0, ',', '.') ?> <i
                                            class="mdi mdi-chevron-up ms-1 text-success"></i></h4>
                                    <div class="d-flex">
                                        <?php
                                        $rentnowyesterday = isset($rr['total_rent_yesterday']) ? $rr['total_rent_yesterday'] : 0;
                                        if ($rentnowyesterday == 0) {
                                            if ($rentnowtoday == 0) {
                                                $rent_growth = 0;
                                            } else {
                                                $rent_growth = 100;
                                            }
                                        } else {
                                            $rent_growth = ($rentnowtoday / $rentnowyesterday) * 100;
                                        }
                                        ?>
                                        <span class="badge badge-soft-success font-size-12"> + <?= $rent_growth ?>%
                                        </span> <span class="ms-2 text-truncate">From previous period</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-xs me-3">
                                        <span
                                            class="avatar-title rounded-circle bg-warning bg-soft text-warning font-size-18">
                                            <i class="bx bx-restaurant"></i><i class="bx bx-basket"></i>
                                        </span>
                                    </div>
                                    <h4 class=" mb-0"><span style="font-size:10px">Rp.</span>
                                        <?= number_format($psnowtoday + $fnbnowtoday, 0, ',', '.') ?></h4>
                                </div>
                                <div class="text-muted mt-4">
                                    <?php
                                    $fnbnowyesterday = isset($rr['total_fnb_yesterday']) ? $rr['total_fnb_yesterday'] : 0;
                                    if ($fnbnowyesterday == 0) {
                                        if ($fnbnowtoday == 0) {
                                            $fnb_growth = 0;
                                        } else {
                                            $fnb_growth = 100;
                                        }
                                    } else {
                                        $fnb_growth = ($fnbnowtoday / $fnbnowyesterday) * 100;
                                    }
                                    ?>
                                    <!-- <h5>
                                                        <span style="font-size:10px">Rp.</span> <?= number_format($fnbnowtoday, 0, ',', '.') ?>
                                                         <span style="font-size:10px">FnB Profit</span>
                                                    </h5> -->
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge badge-soft-info font-size-12"> FnB Profit </span>
                                        <span class="text-truncate">Rp.
                                            <?= number_format($fnbnowtoday, 0, ',', '.') ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="badge badge-soft-success font-size-12"> Others Profit </span>
                                        <span class="text-truncate">Rp.
                                            <?= number_format($psnowtoday, 0, ',', '.') ?></span>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-xs me-3">
                                        <span
                                            class="avatar-title rounded-circle bg-danger bg-soft text-danger font-size-18">
                                            <i class="bx bx-archive-out"></i>
                                        </span>
                                    </div>
                                    <h5 class="font-size-14 mb-0">Spending</h5>
                                </div>
                                <div class="text-muted mt-4">
                                    <h4>(<span style="font-size:10px">Rp.</span>
                                        <?= number_format($outtoday, 0, ',', '.') ?>) <i
                                            class="mdi mdi-chevron-up ms-1 text-success"></i></h4>

                                    <div class="d-flex">
                                        <span class="badge badge-soft-warning font-size-12">( Rp.
                                            <?= number_format($promotoday, 0, ',', '.') ?>)</span> <span
                                            class="ms-2 text-truncate">Promo Today</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="card">
                    <div class="card-body">
                        <div class="d-sm-flex flex-wrap">
                            <h4 class="card-title mb-4">Weekly Net Profit</h4>
                            <div class="ms-auto">
                                <p class="mb-0">
                                    <span id="highest-summary" class="badge badge-soft-success me-2" title="">
                                        <i class="bx bx-trending-up align-bottom me-1"></i> Rp.0 highest
                                    </span>
                                    <span id="lowest-summary" class="badge badge-soft-danger" title="">
                                        <i class="bx bx-trending-down align-bottom me-1"></i> Rp.0 lowest
                                    </span>
                                </p>


                            </div>


                        </div>

                        <div id="stacked-column-charts" class="apex-charts"
                            data-colors='["--bs-primary", "--bs-success", "--bs-danger"]'>
                        </div>

                        <script>
                            function formatRupiahSingkat(val) {
                                if (val >= 1000000) {
                                    return (val / 1000000).toFixed(1) + 'M';
                                } else if (val >= 1000) {
                                    return (val / 1000).toFixed(1) + 'K';
                                } else {
                                    return val;
                                }
                            }

                            function getChartColorsArray(chartId) {
                                if (document.getElementById(chartId)) {
                                    var colors = document.getElementById(chartId).getAttribute("data-colors");
                                    if (colors) {
                                        colors = JSON.parse(colors);
                                        return colors.map(function (value) {
                                            var color = value.replace(" ", "");
                                            if (color.indexOf(",") === -1) {
                                                var cssColor = getComputedStyle(document.documentElement).getPropertyValue(color);
                                                return cssColor.trim() || color;
                                            }
                                            var parts = color.split(",");
                                            if (parts.length !== 2) return color;
                                            return "rgba(" + getComputedStyle(document.documentElement).getPropertyValue(parts[0]) + "," + parts[1] + ")";
                                        });
                                    }
                                }
                                return ["#008FFB", "#FEB019", "#FF4560", "#00E396"]; // fallback + net color
                            }

                            const chartColors = getChartColorsArray("stacked-column-charts");

                            fetch("./xapi/chart-dummy-api.php")
                                .then(res => res.json())
                                .then(response => {
                                    const data = response.data; // sesuai struktur baru
                                    const summary = response.summary; // sesuai struktur baru

                                    const categories = data.map(item => item.date);
                                    const rentData = data.map(item => item.rent);
                                    const fnbData = data.map(item => item.fnb);
                                    const spendingData = data.map(item => item.spending);

                                    // 🔥 Update summary highest & lowest NET
                                    const highest = summary.max.net;
                                    const lowest = summary.min.net;
                                    document.getElementById("highest-summary").innerHTML = `
            <i class="bx bx-trending-up align-bottom me-1"></i> Rp.${highest.value.toLocaleString()} highest
        `;
                                    document.getElementById("highest-summary").title = highest.date;

                                    document.getElementById("lowest-summary").innerHTML = `
            <i class="bx bx-trending-down align-bottom me-1"></i> Rp.${lowest.value.toLocaleString()} lowest
        `;
                                    document.getElementById("lowest-summary").title = lowest.date;


                                    const options = {
                                        chart: {
                                            height: 360,
                                            type: "bar",
                                            stacked: true,
                                            toolbar: { show: false },
                                            zoom: { enabled: true }
                                        },
                                        plotOptions: {
                                            bar: {
                                                horizontal: false,
                                                columnWidth: "15%",
                                                endingShape: "rounded"
                                            }
                                        },
                                        dataLabels: {
                                            enabled: true,
                                            style: {
                                                fontSize: '10px',
                                                colors: ['#000'],
                                            },
                                            formatter: formatRupiahSingkat,
                                        },
                                        tooltip: {
                                            y: {
                                                formatter: formatRupiahSingkat
                                            }
                                        },
                                        series: [
                                            { name: "Rent", data: rentData },
                                            { name: "FnB", data: fnbData },
                                            { name: "Spending", data: spendingData },

                                        ],
                                        xaxis: {
                                            categories: categories,
                                            labels: {
                                                style: {
                                                    fontSize: '10px'
                                                }
                                            }
                                        },
                                        colors: chartColors,
                                        yaxis: {
                                            labels: {
                                                style: {
                                                    fontSize: '10px'
                                                }
                                            }
                                        },
                                        legend: {
                                            position: "bottom"
                                        },
                                        fill: {
                                            opacity: 1
                                        }
                                    };

                                    new ApexCharts(document.querySelector("#stacked-column-charts"), options).render();

                                });
                        </script>



                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->
        <?php
        $r = $con->query("SELECT 
    fnb.id,
    fnb.nama,
    SUM(t.qty) AS total_qty,
    SUM(t.total) AS total_harga
FROM 
    tb_trans_fnb t
LEFT JOIN 
    tb_fnb fnb ON t.id_fnb = fnb.id
WHERE 
    t.userx = '$username'  AND t.inv IS NOT NULL
  AND (t.is_deleted IS NULL OR t.is_deleted != 1) 
    
GROUP BY 
    fnb.id, fnb.nama
ORDER BY 
    total_qty DESC
     LIMIT 5;
");
        $fnb_top = [];
        foreach ($r as $row) {
            $fnb_top[] = $row;
        }
        ?>

        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-end">
                                <div class="input-group input-group-sm">

                                </div>
                            </div>
                            <h4 class="card-title mb-4">Top 5 Selling Product</h4>
                        </div>

                        <?php if (!empty($fnb_top)): ?>
                            <div class="text-muted text-center">
                                <p class="mb-2">#1 <?= htmlspecialchars($fnb_top[0]['nama']) ?>
                                    (<?= $fnb_top[0]['total_qty'] ?>)</p>
                                <h4 class="mb-0">Rp. <?= number_format($fnb_top[0]['total_harga'], 0, ',', '.') ?></h4>
                                <!-- Growth badge bisa diisi jika ada data bulan sebelumnya -->

                            </div>
                        <?php else: ?>
                            <div class="text-muted text-center">
                                <p class="mb-2">No FnB sales this month.</p>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive mt-4">
                            <table class="table align-middle mb-0">
                                <tbody>
                                    <?php
                                    // Mulai dari index 1 karena index 0 sudah ditampilkan di atas
                                    for ($i = 1; $i < count($fnb_top); $i++):
                                        $rank = $i + 1;
                                        ?>
                                        <tr>
                                            <td>
                                                <h5 class="font-size-14 mb-1">#<?= $rank ?></h5>
                                                <p class="text-muted mb-0"><?= htmlspecialchars($fnb_top[$i]['nama']) ?>
                                                    (<?= $fnb_top[$i]['total_qty'] ?>)</p>
                                            </td>
                                            <td>
                                                <p class="text-muted mb-1">Total Sales</p>
                                                <h5 class="mb-0">Rp.
                                                    <?= number_format($fnb_top[$i]['total_harga'], 0, ',', '.') ?>
                                                </h5>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                    <?php if (count($fnb_top) < 2): ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Belum ada data lain.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
                // Query: Top 3 Popular Rentals (by total duration this month)
                $r = $con->query("
            SELECT 
                t.id_ps,
                p.type_ps,
                SUM(t.durasi) AS total_durasi
            FROM 
                tb_trans t
            LEFT JOIN 
                playstations p ON t.id_ps = p.id
            WHERE
                t.userx = '$username'  AND t.inv IS NOT NULL
  AND (t.is_deleted IS NULL OR t.is_deleted != 1) 
                AND p.type_rental != 'Playbox'
            GROUP BY 
                t.id_ps, p.type_ps
            ORDER BY 
                total_durasi DESC
            LIMIT 3
        ");
                $popular_rentals = [];
                foreach ($r as $row) {
                    $popular_rentals[] = $row;
                }
                ?>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Top 3 Popular Rentals</h4>
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="bx bx-map-pin text-primary display-4"></i>
                            </div>
                            <?php if (!empty($popular_rentals)): ?>
                                <h3>#<?= $popular_rentals[0]['id_ps'] ?> <span
                                        style="font-size:12px"><?= htmlspecialchars($popular_rentals[0]['type_ps']) ?></span>
                                </h3>
                                <p class="mb-0">(1st) <?= number_format($popular_rentals[0]['total_durasi']) ?> Hours</p>
                            <?php else: ?>
                                <p class="mb-0 text-muted">No rental data this month.</p>
                            <?php endif; ?>
                        </div>
                        <div class="table-responsive mt-4">
                            <table class="table align-middle table-nowrap">
                                <tbody>
                                    <?php for ($i = 1; $i < count($popular_rentals); $i++): ?>
                                        <tr>
                                            <td style="width: 30%">
                                                <p class="mb-0">(<?= $i + 1 ?><?= $i == 1 ? 'nd' : 'rd' ?>)</p>
                                            </td>
                                            <td style="width: 25%">
                                                <h5 class="mb-0">#<?= $popular_rentals[$i]['id_ps'] ?> <span
                                                        style="font-size:12px"><?= htmlspecialchars($popular_rentals[$i]['type_ps']) ?></span>
                                                </h5>
                                            </td>
                                            <td>
                                                <p class="mb-0"><?= number_format($popular_rentals[$i]['total_durasi']) ?>
                                                    Hours</p>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                    <?php if (count($popular_rentals) < 2): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No other data.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Status Rental</h4>

                        <ul class="nav nav-pills bg-light rounded" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#transactions-all-tab"
                                    role="tab">ALL</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-success" data-bs-toggle="tab" href="#transactions-buy-tab"
                                    role="tab">
                                    <i class="bx bx-check-square"></i> Available
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-danger" data-bs-toggle="tab" href="#transactions-sell-tab"
                                    role="tab">
                                    <i class="bx bx-no-entry"></i> Busy
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-4">
                            <!-- TAB ALL -->
                            <div class="tab-pane active" id="transactions-all-tab" role="tabpanel">
                                <div class="table-responsive" data-simplebar style="max-height: 330px;">
                                    <table class="table align-middle table-nowrap">
                                        <tbody class="rent-table-all"></tbody> <!-- khusus data all -->
                                    </table>
                                </div>
                            </div>

                            <!-- TAB AVAILABLE -->
                            <div class="tab-pane" id="transactions-buy-tab" role="tabpanel">
                                <div class="table-responsive" data-simplebar style="max-height: 330px;">
                                    <table class="table align-middle table-nowrap">
                                        <tbody class="rent-table-available"></tbody> <!-- khusus data available -->
                                    </table>
                                </div>
                            </div>

                            <!-- TAB BUSY -->
                            <div class="tab-pane" id="transactions-sell-tab" role="tabpanel">
                                <div class="table-responsive" data-simplebar style="max-height: 300px;">
                                    <table class="table align-middle table-nowrap">
                                        <tbody class="rent-table-busy"></tbody> <!-- khusus data busy -->
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            let countdownInterval = null;
            let activeCountdowns = [];

            function parseDateCustom(str) {
                // Mendukung format "DD-MM-YYYY HH:mm:ss" dan juga "YYYY-MM-DD HH:mm:ss"
                if (!str || typeof str !== 'string') return null;

                if (str.includes('-') && str.includes(' ')) {
                    // Coba format DD-MM-YYYY
                    let parts = str.split(' ');
                    if (!parts[0].startsWith('20')) { // asumsi kalau bukan format ISO
                        const [dd, mm, yyyy] = parts[0].split('-').map(Number);
                        const [HH, MM, SS] = parts[1].split(':').map(Number);
                        return new Date(yyyy, mm - 1, dd, HH, MM, SS);
                    } else {
                        // Format ISO-like "YYYY-MM-DD HH:mm:ss"
                        return new Date(str.replace(' ', 'T'));
                    }
                }
                return null;
            }

            function formatHourDecimal(minutes) {
                if (minutes === 'open') return 'Open Play';
                const hours = minutes / 60;
                return Number.isInteger(hours) ? `${hours} h` : `${hours.toFixed(1)} h`;
            }

            function loadRentStatus(status = 'all') {
                fetch(`./xapi/getstatusrent.php?status=${status}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '';
                        let countdowns = [];

                        // Hitung total untuk semua status (all)
                        // Karena API dipanggil per status, kita perlu data lengkap untuk total All dan Available/Busy
                        // Jika API tidak mendukung return all sekaligus, maka bisa simpan total di tempat lain
                        // Tapi jika status='all', kita dapat total all di sini.
                        if (status === 'all') {
                            // Hitung available dan busy dari data all
                            const availableCount = data.filter(i => i.status === 'available').length;
                            const busyCount = data.filter(i => i.status !== 'available').length;

                            // Update teks tab atau elemen total
                            document.querySelector('[href="#transactions-all-tab"]').textContent = `All (${data.length})`;
                            document.querySelector('[href="#transactions-buy-tab"]').textContent = `Available (${availableCount})`;
                            document.querySelector('[href="#transactions-sell-tab"]').textContent = `Busy (${busyCount})`;
                        } else if (status === '1') {
                            document.querySelector('[href="#transactions-buy-tab"]').textContent = `Available (${data.length})`;
                        } else if (status === '0') {
                            document.querySelector('[href="#transactions-sell-tab"]').textContent = `Busy (${data.length})`;
                        }

                        data.forEach((item) => {
                            const isAvailable = item.status === "available";
                            const start = item.start_time ? item.start_time.substr(11, 5) : '-';
                            const end = item.end_time ? item.end_time.substr(11, 5) : '-';

                            const countdownId = `countdown-${status}-${item.no_ps}-${item.end_time.replace(/[^a-zA-Z0-9]/g, '')}`;

                            let endTime = null;
                            if (item.end_time) {
                                endTime = parseDateCustom(item.end_time);
                                if (!endTime || isNaN(endTime.getTime())) {
                                    console.warn('Invalid date:', item.end_time);
                                    endTime = null;
                                }
                            }

                            html += `   
                    <tr>
                        <td style="width: 50px;">
                            <div class="font-size-18 ${isAvailable ? 'text-success' : 'text-danger'}">
                                <i class="bx ${isAvailable ? 'bx-check-square' : 'bx-no-entry'}"></i>
                            </div>
                        </td>
                        <td>
                            <div>
                                <h5 class="font-size-12 mb-1">#${item.no_ps} (${item.type_ps}) </h5>
                                <p class="text-muted mb-0">${start} - ${end}</p>
                            </div>
                        </td>
                        <td>
                            <div class="text-end">
                             <h5 class="font-size-12 mb-0">${formatHourDecimal(item.duration)}</h5>
                                <span id="${countdownId}">${endTime ? 'Loading...' : '-'}</span>
                            </div>
                        </td>
                    </tr>
                `;

                            if (item.duration === 'open') {
                                // Count up dari start_time
                                const startTime = parseDateCustom(item.start_time);
                                if (startTime && !isNaN(startTime.getTime())) {
                                    countdowns.push({ id: countdownId, time: startTime, type: 'up' });
                                }
                            } else if (endTime) {
                                countdowns.push({ id: countdownId, time: endTime, type: 'down' });
                            }
                        });

                        let targetTbody;
                        if (status === 'all') targetTbody = document.querySelector('.rent-table-all');
                        else if (status === '1') targetTbody = document.querySelector('.rent-table-available');
                        else if (status === '0') targetTbody = document.querySelector('.rent-table-busy');

                        if (targetTbody) targetTbody.innerHTML = html;

                        startCountdowns(countdowns);
                    })
                    .catch(err => {
                        console.error('Error loading rent status:', err);
                    });
            }


            function startCountdowns(countdowns) {
                if (countdownInterval) clearInterval(countdownInterval);

                activeCountdowns = countdowns;
                function updateCountdown() {
                    const now = new Date();
                    activeCountdowns.forEach(({ id, time, type }) => {
                        const el = document.getElementById(id);
                        if (!el) return;

                        let diff = Math.floor((type === 'down' ? time - now : now - time) / 1000);
                        if (diff <= 0 && type === 'down') {
                            el.textContent = 'Time is up';
                        } else {
                            const h = Math.floor(diff / 3600);
                            const m = Math.floor((diff % 3600) / 60);
                            const s = diff % 60;
                            el.textContent = `${type === 'up' ? '+' : ''}${h}h ${m}m ${s}s`;
                        }
                    });
                }


                updateCountdown();
                countdownInterval = setInterval(updateCountdown, 1000);
            }

            // Initial load all data
            loadRentStatus('all');

            // Auto reload data tiap 1 menit sekali (60000 ms)
            setInterval(() => {
                // Bisa modif ini jika mau reload tab yang sedang aktif
                loadRentStatus('all');
            }, 60000);

            // Event binding tab clicks supaya reload sesuai tab
            document.querySelector('[href="#transactions-all-tab"]').addEventListener('click', () => loadRentStatus('all'));
            document.querySelector('[href="#transactions-buy-tab"]').addEventListener('click', () => loadRentStatus('1'));
            document.querySelector('[href="#transactions-sell-tab"]').addEventListener('click', () => loadRentStatus('0'));
        </script>




        <div class="row">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Top 10 Recent Transactions</h4>
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check font-size-16 align-middle">
                                                <input class="form-check-input" type="checkbox" id="transactionCheck01">
                                                <label class="form-check-label" for="transactionCheck01"></label>
                                            </div>
                                        </th>
                                        <th class="align-middle">Trans ID</th>

                                        <th class="align-middle">Date</th>
                                        <th class="align-middle">Total</th>
                                        <th class="align-middle">View Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Ambil 10 transaksi terakhir dari tb_trans_final untuk user ini
                                    $sql = "SELECT id_trans, invoice, created_at, grand_total 
                                                            FROM tb_trans_final 
                                                            WHERE userx = '$username' 
                                                            ORDER BY created_at DESC 
                                                            LIMIT 10";
                                    $result = $con->query($sql);
                                    if ($result && $result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check font-size-16">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="transactionCheck<?= htmlspecialchars($row['id_trans']) ?>">
                                                        <label class="form-check-label"
                                                            for="transactionCheck<?= htmlspecialchars($row['id_trans']) ?>"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0);" class="text-body fw-bold">
                                                        <?= htmlspecialchars($row['invoice']) ?>
                                                    </a>
                                                </td>

                                                <td>
                                                    <?= date('d M, Y H:i', strtotime($row['created_at'])) ?>
                                                </td>
                                                <td>
                                                    Rp. <?= number_format($row['grand_total'], 0, ',', '.') ?>
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-primary btn-sm btn-rounded waves-effect waves-light"
                                                        data-bs-toggle="modal" data-bs-target=".transaction-detailModal"
                                                        data-invoice="<?= $row['invoice'] ?>"
                                                        data-name="<?= $row['id_trans'] ?>">
                                                        View Details
                                                    </button>

                                                </td>
                                            </tr>
                                            <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No transactions found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- end table-responsive -->
                    </div>
                </div>
            </div>

            <?php
            // Ambil data booking untuk tanggal hari ini
            $sql = "SELECT * FROM bookings WHERE date = '$datting' ORDER BY time_start ASC";
            $result = $con->query($sql);
            ?>

            <div class="col-xl-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-2">Booking List <?= $dateNOW ?></h4>

                        <div data-simplebar style="max-height: 200px;">
                            <ul class="verti-timeline list-unstyled">

                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <li class="event-list my-1">
                                            <div class="event-timeline-dot">
                                                <i class="bx bx-right-arrow-circle font-size-18"></i>
                                            </div>
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-1">
                                                    <h5 class="font-size-14 mb-0">
                                                        <?= htmlspecialchars($row['time_start']) ?> -
                                                        <?= htmlspecialchars($row['time_end']) ?>
                                                        <i
                                                            class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i>
                                                    </h5>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div>
                                                        <?= htmlspecialchars($row['name']) ?>
                                                        (<?= htmlspecialchars($row['no_ps']) ?>)

                                                        <?php
                                                        // Info usaha
                                                        $nama_usaha = $merchand;
                                                        $alamat_usaha = $address;

                                                        ?>

                                                        <?php if ($row['status'] == "1"): ?>
                                                            <span class="badge bg-success font-size-10">Verified</span>

                                                        <?php elseif ($row['status'] == null || $row['status'] == "pending"): ?>
                                                            <span class="badge bg-warning font-size-10">Unverified</span>

                                                            <?php
                                                            // Format nomor WA
                                                            $nohp = preg_replace('/[^0-9]/', '', $row['no_ps']);
                                                            if (substr($nohp, 0, 1) == "0") {
                                                                $nohp = "62" . substr($nohp, 1);
                                                            }

                                                            // Pesan WA otomatis
                                                            $pesan = "Halo *{$row['name']}*,\n\nKami dari *{$nama_usaha}* ingin menginformasikan bahwa status booking Anda saat ini *belum terverifikasi*.\n\n📅 Tanggal: {$row['date']}\n⏰ Waktu: {$row['time_start']} - {$row['time_end']}\n📍 Alamat: {$alamat_usaha}\n\nMohon segera lakukan *verifikasi pembayaran* untuk menghindari pembatalan otomatis.\n\n";

                                                            $pesan .= "\nTerima kasih.\n- Admin {$nama_usaha}";

                                                            $pesan_enc = urlencode($pesan);
                                                            // Format nomor WhatsApp dengan kode negara
                                                            if (!empty($nohp)) {
                                                                // Hapus karakter non-digit
                                                                $nohp = preg_replace('/[^0-9]/', '', $nohp);

                                                                // Cek dan format nomor
                                                                if (substr($nohp, 0, 2) == '62') {
                                                                    // Sudah ada 62
                                                                    $nohp_wa = $nohp;
                                                                } elseif (substr($nohp, 0, 1) == '0') {
                                                                    // Dimulai dengan 0, ganti dengan 62
                                                                    $nohp_wa = '62' . substr($nohp, 1);
                                                                } else {
                                                                    // Tidak ada 0 atau 62, tambahkan 62
                                                                    $nohp_wa = '62' . $nohp;
                                                                }

                                                                $wa_link = "https://api.whatsapp.com/send?phone={$nohp_wa}&text={$pesan_enc}";
                                                            } else {
                                                                $wa_link = "#";
                                                            }

                                                            // Atau buat sebagai function untuk reusable
                                                            function formatWhatsApp($nomor)
                                                            {
                                                                if (empty($nomor))
                                                                    return '';

                                                                // Hapus semua karakter non-digit
                                                                $nomor = preg_replace('/[^0-9]/', '', $nomor);

                                                                // Format ke 62
                                                                if (substr($nomor, 0, 2) == '62') {
                                                                    return $nomor;
                                                                } elseif (substr($nomor, 0, 1) == '0') {
                                                                    return '62' . substr($nomor, 1);
                                                                } else {
                                                                    return '62' . $nomor;
                                                                }
                                                            }

                                                            // Cara pakai function
                                                            $nohp_wa = formatWhatsApp($nohp);
                                                            $wa_link = "https://api.whatsapp.com/send?phone={$nohp_wa}&text={$pesan_enc}";

                                                            ?>
                                                            <a href="<?= $wa_link ?>" target="_blank" title="Kirim WA">
                                                                <i
                                                                    class="bx bxl-whatsapp-square font-size-18 text-primary align-middle ms-1">

                                                                </i>
                                                            </a>

                                                        <?php elseif ($row['status'] == "0"): ?>
                                                            <span class="badge bg-danger font-size-10">Failed</span>

                                                        <?php else: ?>
                                                            <span
                                                                class="badge bg-secondary font-size-10"><?= htmlspecialchars($row['status']) ?></span>
                                                        <?php endif; ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <li class="event-list my-1">
                                        <div class="d-flex">
                                            <div class="flex-grow-1 text-muted">Tidak ada booking hari ini.</div>
                                        </div>
                                    </li>
                                <?php endif; ?>

                            </ul>
                        </div>
                    </div>

                </div>
                <div class="col-xl-12">

                    <?php
                    // Query: Ambil jumlah unit available per type_ps
                    $r = $con->query("
    SELECT type_ps, COUNT(*) AS total
    FROM playstations
    WHERE status = 'available' AND userx = '$username'
    GROUP BY type_ps
    ORDER BY type_ps
");
                    ?>
                    <div class="card jobs-categories">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Available Unit Rentals</h4>
                            <?php if ($r && $r->num_rows > 0): ?>
                                <?php while ($row = $r->fetch_assoc()): ?>
                                    <a href="#!" class="px-3 py-2 rounded bg-light bg-opacity-50 d-block mb-2">
                                        <?= htmlspecialchars($row['type_ps']) ?>
                                        <span class="badge text-bg-info float-end bg-opacity-100"><?= $row['total'] ?></span>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-muted">
                                    <?php
                                    if (!$r) {
                                        echo "Error: " . $con->error;
                                    } else {
                                        echo "No available units.";
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php $con->close(); ?>
            </div>
            <!-- end row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    <h4 class="card-title mb-3">Undang teman Anda ke portal <?= $merchand ?></h4>
                                    <p class="text-muted">
                                        Temukan informasi lengkap seputar ketersediaan ruangan, proses booking, dan
                                        fitur
                                        penting lainnya. Portal ini dirancang untuk memberikan kemudahan, kecepatan, dan
                                        transparansi bagi seluruh pengguna merchant.
                                    </p>

                                    <div class="mt-0">
                                        <?php
                                        $encryptedId = encrypt($license);
                                        $portalUrl = "./portal.php?q=" . $encryptedId;
                                        ?>

                                        <a href="javascript:void(0);" class="btn btn-primary btn-sm"
                                            onclick="copyToClipboard('<?php echo $portalUrl; ?>')">
                                            <i class='bx bx-copy align-middle'></i> Salin Tautan
                                        </a>

                                        <a href="<?php echo $portalUrl; ?>" class="btn btn-primary btn-sm"
                                            target="_blank">
                                            <i class='bx bx-navigation align-middle'></i> Lihat Portal
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <img src="assets/images/jobs.png" alt="" height="130">
                                </div>
                            </div>

                            <script>
                                function copyToClipboard(url) {
                                    const fullUrl = window.location.origin + '/' + url.replace('./', '');
                                    navigator.clipboard.writeText(fullUrl).then(function () {
                                        // Bisa tambahkan notifikasi berhasil copy
                                        alert('Tautan berhasil disalin!');
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div><!--end card-->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <!-- Transaction Modal -->
    <div class="modal fade transaction-detailModal" tabindex="-1" role="dialog"
        aria-labelledby="transaction-detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transaction-detailModalLabel">Order Details <span class="text-primary"
                            id="modal-invoice">#</span> </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Transaction ID : <span class="text-primary" id="modal-name">#</span></p>

                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap" id="modal-detail-table">
                            <thead>
                                <tr>

                                    <th scope="col">Product Name</th>
                                    <th scope="col">Qty</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded by JS -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><b>Sub Total:</b></td>
                                    <td id="modal-subtotal" class="fw-bold">-</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><b>Disc :</b></td>
                                    <td id="modal-spending" class="fw-bold">-</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><b>Total:</b></td>
                                    <td id="modal-total" class="fw-bold">-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const modal = document.querySelector('.transaction-detailModal');
            const invoiceField = document.getElementById('modal-invoice');
            const nameField = document.getElementById('modal-name');
            const detailTableBody = document.querySelector('#modal-detail-table tbody');
            const subtotalField = document.getElementById('modal-subtotal');
            const spendingField = document.getElementById('modal-spending');
            const totalField = document.getElementById('modal-total');

            function formatRupiah(val) {
                return 'Rp. ' + Number(val).toLocaleString('id-ID');
            }

            document.querySelectorAll('[data-bs-target=".transaction-detailModal"]').forEach(button => {
                button.addEventListener('click', () => {
                    const invoice = button.getAttribute('data-invoice');
                    const name = button.getAttribute('data-name');
                    invoiceField.textContent = `#${invoice}`;
                    nameField.textContent = name;

                    // Reset table
                    detailTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>';
                    subtotalField.textContent = '-';
                    spendingField.textContent = '-';
                    totalField.textContent = '-';

                    // Fetch detail data via AJAX
                    fetch('controller/get_invoice_detail.php?invoice=' + encodeURIComponent(invoice))
                        .then(res => res.json())
                        .then(data => {
                            // Inisialisasi variabel subtotal dan spending
                            let subtotal = 0;
                            let spending = 0;
                            let rows = '';
                            let promoDiscount = 0;
                            if (data.promo) {
                                promoDiscount = parseInt(data.promo); // pastikan promo-nya berupa angka (contoh: "10000")
                            }

                            // Rental (tb_trans)
                            if (Array.isArray(data.trans)) {
                                data.trans.forEach(item => {
                                    const sub = parseInt(item.harga) * parseInt(item.qty); // Menghitung subtotal per item
                                    subtotal += sub; // Menambahkan subtotal ke total keseluruhan
                                    rows += `<tr>
            <td><i class="bx bx-joystick"></i> ${item.type_ps ? `${item.type_ps} (#${item.id_ps})` : '-'}</td>
            <td>${item.durasi} Min (${item.extra == 0 ? 'Reg' : 'Ext'})</td>
            <td>${formatRupiah(item.harga)}</td>
            <td>${formatRupiah(sub)}</td>
        </tr>`;
                                });
                            }

                            // FnB (tb_trans_fnb)
                            if (Array.isArray(data.fnb)) {
                                data.fnb.forEach(item => {
                                    const sub = parseInt(item.total); // Total per item FnB
                                    subtotal += sub; // Menambahkan total FnB ke subtotal
                                    rows += `<tr>
            <td><i class="bx bx-restaurant"></i> ${item.nama || '-'}</td>
            <td>${item.qty} Pcs</td>
            <td>${formatRupiah(sub / item.qty)}</td> <!-- Harga per item -->
            <td>${formatRupiah(sub)}</td> <!-- Total per item -->
        </tr>`;
                                });
                            }

                            // Spending (tb_trans_out)
                            if (Array.isArray(data.spending)) {
                                data.spending.forEach(item => {
                                    const sub = parseInt(item.grand_total); // Total per item FnB
                                    subtotal += sub; // Menambahkan total FnB ke subtotal
                                    rows += `<tr>
            <td><i class="bx bx-money"></i> ${item.note || '-'}</td>
            <td>1</td>
            <td>${formatRupiah(sub)}</td> <!-- Total per item -->
            <td>${formatRupiah(sub)}</td> <!-- Total per item -->
        </tr>`;
                                });
                            }

                            // Menambahkan baris untuk total keseluruhan
                            if (subtotal || spending) {
                                rows += `<tr>
        <td colspan="3" class="text-end"><strong>Total</strong></td>
        <td><strong>${formatRupiah(subtotal + spending)}</strong></td>
    </tr>`;
                            }

                            // Jika tidak ada data
                            if (!rows) {
                                rows = '<tr><td colspan="4" class="text-center text-muted">No data found.</td></tr>';
                            }

                            const total = subtotal - promoDiscount;


                            // Update DOM
                            detailTableBody.innerHTML = rows;
                            subtotalField.textContent = formatRupiah(subtotal);
                            spendingField.textContent = promoDiscount > 0 ? '-' + formatRupiah(promoDiscount) : '-';
                            totalField.textContent = formatRupiah(total > 0 ? total : 0);
                        })
                        .catch(() => {
                            detailTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load data.</td></tr>';
                        });
                });
            });
        });
    </script>

    <!-- end modal -->

    <!-- subscribeModal -->
    <!-- <div class="modal fade" id="subscribeModal" tabindex="-1" aria-labelledby="subscribeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-bottom-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <div class="avatar-md mx-auto mb-4">
                                        <div class="avatar-title bg-light rounded-circle text-primary h1">
                                            <i class="mdi mdi-email-open"></i>
                                        </div>
                                    </div>

                                    <div class="row justify-content-center">
                                        <div class="col-xl-10">
                                            <h4 class="text-primary">Subscribe !</h4>
                                            <p class="text-muted font-size-14 mb-4">Subscribe our newletter and get notification to stay update.</p>

                                            <div class="input-group bg-light rounded">
                                                <input type="email" class="form-control bg-transparent border-0" placeholder="Enter Email address" aria-label="Recipient's username" aria-describedby="button-addon2">
                                                
                                                <button class="btn btn-primary" type="button" id="button-addon2">
                                                    <i class="bx bxs-paper-plane"></i>
                                                </button>
                                                
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
    <!-- end modal -->


</div>
<!-- end main content-->

</div>
<!-- END layout-wrapper -->


<!-- /Right-bar -->

<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.querySelector('.transaction-detailModal');
        const invoiceField = document.getElementById('modal-invoice');
        const nameField = document.getElementById('modal-name');

        document.querySelectorAll('[data-bs-target=".transaction-detailModal"]').forEach(button => {
            button.addEventListener('click', () => {
                const invoice = button.getAttribute('data-invoice');
                const name = button.getAttribute('data-name');

                invoiceField.textContent = `#${invoice}`;
                nameField.textContent = name;
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var nilaiPersen = <?= ROUND($growth, 2) ?>; // Nilai dari PHP (misalnya 75)

        var radialbarColors = getChartColorsArray("radialBar-chart2");

        if (radialbarColors) {
            var options = {
                chart: {
                    height: 200,
                    type: "radialBar",
                    offsetY: -10
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        dataLabels: {
                            name: {
                                fontSize: "13px",
                                offsetY: 60
                            },
                            value: {
                                offsetY: 22,
                                fontSize: "16px",
                                formatter: function (val) {
                                    return val + "%";
                                }
                            }
                        }
                    }
                },
                colors: radialbarColors,
                fill: {
                    type: "gradient",
                    gradient: {
                        shade: "dark",
                        shadeIntensity: .15,
                        inverseColors: false,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 50, 65, 91]
                    }
                },
                stroke: {
                    dashArray: 4
                },
                series: [nilaiPersen], // <-- ini nilainya
                labels: ["Growth"]
            };

            var chart = new ApexCharts(document.querySelector("#radialBar-chart2"), options);
            chart.render();
        }
    });
</script>