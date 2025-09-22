<?php
//  session_start();
require_once './include/config.php'; // koneksi database
require_once './include/crypto.php'; // jika password ingin di-enkripsi, tapi gunakan password_hash lebih baik
$username = $_SESSION['username'];

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
$r = $con->query("SELECT *
FROM userx
JOIN tb_package ON userx.username = tb_package.username
WHERE userx.username = '$username'");
foreach ($r as $rr) {
    $merchand = $rr['merchand'];
    $level = $rr['level'];
    $license = $rr['license'];
    $exp = $rr['license_exp'];
    $cabang = $rr['cabang'];
    $address = $rr['address'];
    $logox = $rr['logox'];
    $unit = $rr['unit'];
    $portal = $rr['portal'];
    $timezone = $rr['timezone'];

    $multi_cabang = $rr['multi_cabang'];
    $qr = $rr['qr'];
}


?>

<style>
    .modal-body img {
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        max-width: 100%;
        height: auto;
    }

    .modal-dialog-lg {
        max-width: 800px;
    }

    .booking-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .booking-row:hover {
        background-color: #f8f9fa;
    }

    .payment-status-badge {
        margin-right: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .action-btn {
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .booking-actions {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #dee2e6;
    }

    .ps-selector {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }
</style>

<div class="page-content" id="app">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Portal & Booking</h4>
                </div>


            </div>
        </div>
        <!-- end page title -->
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Detail Pembayaran & Rental</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <!-- Info User -->
                        <div class="row mb-1">
                            <div class="col-sm-4"><strong>Nama:</strong></div>
                            <div class="col-sm-8" id="modalName">-</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-sm-4"><strong>Telepon:</strong></div>
                            <div class="col-sm-8" id="modalPhone">-</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-sm-4"><strong>Waktu:</strong></div>
                            <div class="col-sm-8" id="modalTime">-</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-sm-4"><strong>Harga Total:</strong></div>
                            <div class="col-sm-8" id="modalTotalPrice">0</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-sm-4"><strong>Sudah Dibayar:</strong></div>
                            <div class="col-sm-8" id="modalPaidAmount">0</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-sm-4"><strong>Sisa Bayar:</strong></div>
                            <div class="col-sm-8 text-danger fw-bold" id="modalRemainingAmount">0</div>
                        </div>

                        <hr>

                        <!-- 2 Kolom -->
                        <div class="row">
                            <!-- Kiri: Pembayaran -->
                            <div class="col-md-6 border-end">
                                <h6>Pembayaran</h6>
                                <div class="mb-3">
                                    <label class="form-label">Jumlah Bayar</label>
                                    <input type="number" class="form-control" id="paymentAmount"
                                        placeholder="Masukkan jumlah bayar">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Metode Pembayaran</label>
                                    <select class="form-select" id="paymentMethod">
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer Bank</option>
                                        <option value="qris">QRIS</option>
                                        <option value="ewallet">E-Wallet</option>
                                    </select>
                                </div>

                                <!-- Status otomatis -->
                                <div class="mb-3">
                                    <label class="form-label">Status Pembayaran</label>
                                    <div id="paymentStatus" class="fw-bold text-primary">-</div>
                                </div>

                                <!-- Tombol Konfirmasi -->
                                <div class="text-start">
                                    <button type="button" class="btn btn-primary" id="confirmPayment">Konfirmasi
                                        Pembayaran</button>
                                </div>
                            </div>

                            <!-- Kanan: Rental -->
                            <div class="col-md-6">
                                <h6>Start Rental</h6>
                                <div class="mb-3">
                                    <label class="form-label">Pilih Type PS</label>
                                    <select class="form-select" id="psType">
                                        <option value="">-- Pilih Type --</option>
                                        <?php
                                        $sqlType = "SELECT DISTINCT type_ps FROM playstations WHERE status='available' ORDER BY type_ps";
                                        $resType = $con->query($sqlType);
                                        while ($row = $resType->fetch_assoc()) {
                                            echo "<option value='{$row['type_ps']}'>" . strtoupper($row['type_ps']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pilih No PS</label>
                                    <select class="form-select" id="psNumber">
                                        <option value="">-- Pilih Nomor --</option>
                                    </select>
                                </div>
                                <!-- Tombol Start Rental -->
                                <div class="text-start">
                                    <button type="button" class="btn btn-success" id="startRental">Start Rental</button>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toNumber(str) {
                if (!str) return 0;
                // Buang koma dan semua non-angka
                str = str.replace(',00', '');
                let cleaned = str.replace(/[^0-9]/g, '');
                return parseInt(cleaned, 10) || 0;
            }

            document.getElementById("paymentAmount").addEventListener("input", function () {
                let total = toNumber(document.getElementById("modalTotalPrice").innerText);
                let sudahBayar = toNumber(document.getElementById("modalPaidAmount").innerText);
                let inputBayar = parseInt(this.value) || 0;

                // Hitung sisa yang harus dibayar
                let sisaHarusBayar = total - sudahBayar;

                // Validasi: jangan biarkan input melebihi sisa yang harus dibayar
                if (inputBayar > sisaHarusBayar) {
                    this.value = sisaHarusBayar;
                    inputBayar = sisaHarusBayar;

                    // Tampilkan pesan peringatan (opsional)
                    alert(`Pembayaran tidak boleh melebihi sisa tagihan: ${sisaHarusBayar.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}`);
                }

                let sisa = total - (sudahBayar + inputBayar);
                if (sisa < 0) sisa = 0;

                // Tampilkan dengan format Rupiah
                document.getElementById("modalRemainingAmount").innerText =
                    sisa.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });

                // Status pembayaran
                let paymentStatus = document.getElementById("paymentStatus");
                if (inputBayar + sudahBayar >= total) {
                    paymentStatus.innerText = "Full Payment";
                    paymentStatus.className = "fw-bold text-success";
                } else {
                    paymentStatus.innerText = "Down Payment";
                    paymentStatus.className = "fw-bold text-warning";
                }
            });

            // Alternatif: gunakan event 'keyup' dan 'paste' untuk validasi real-time
            document.getElementById("paymentAmount").addEventListener("keyup", validatePayment);
            document.getElementById("paymentAmount").addEventListener("paste", function () {
                setTimeout(validatePayment, 10); // Delay sedikit untuk memastikan paste selesai
            });

            function validatePayment() {
                let paymentInput = document.getElementById("paymentAmount");
                let total = toNumber(document.getElementById("modalTotalPrice").innerText);
                let sudahBayar = toNumber(document.getElementById("modalPaidAmount").innerText);
                let inputBayar = parseInt(paymentInput.value) || 0;

                let sisaHarusBayar = total - sudahBayar;

                if (inputBayar > sisaHarusBayar) {
                    paymentInput.value = sisaHarusBayar;
                    // Trigger input event untuk update tampilan
                    paymentInput.dispatchEvent(new Event('input'));
                }
            }

            // Tambahkan validasi pada form submit (sebagai backup)
            document.querySelector("form").addEventListener("submit", function (e) {
                let total = toNumber(document.getElementById("modalTotalPrice").innerText);
                let sudahBayar = toNumber(document.getElementById("modalPaidAmount").innerText);
                let inputBayar = parseInt(document.getElementById("paymentAmount").value) || 0;

                if (inputBayar > (total - sudahBayar)) {
                    e.preventDefault();
                    alert("Jumlah pembayaran tidak boleh melebihi sisa tagihan!");
                    return false;
                }
            });

            // ========== Ambil nomor PS berdasarkan type ==========
            document.getElementById("psType").addEventListener("change", function () {
                let type = this.value;
                let psNumber = document.getElementById("psNumber");
                psNumber.innerHTML = "<option value=''>Loading...</option>";

                if (type) {
                    fetch("get_ps_number.php?type_ps=" + type)
                        .then(res => res.json())
                        .then(data => {
                            psNumber.innerHTML = "<option value=''>-- Pilih Nomor --</option>";
                            data.forEach(item => {
                                psNumber.innerHTML += `<option value="${item.id}">No. ${item.no_ps}</option>`;
                            });
                        });
                } else {
                    psNumber.innerHTML = "<option value=''>-- Pilih Nomor --</option>";
                }
            });
        </script>



        <?php
        // Ambil data booking untuk tanggal hari ini dengan kolom yang sesuai
        $sql = "SELECT b.id, b.name, b.date, b.time_start, b.time_end, b.no_ps, b.status,b.payment_status, b.tip, b.userx, b.duration, b.type_ps, b.acc_admin,
                       COALESCE(p.price, 0) as total_price
                FROM bookings b 
                LEFT JOIN tb_pricelist p ON b.duration = p.duration AND b.type_ps = p.type_ps AND p.userx = '$username'
                WHERE b.date = '$datting' 
                ORDER BY b.time_start ASC";
        $result = $con->query($sql);

        // Check for SQL errors
        if (!$result) {
            echo "Error: " . $con->error;
            $result = null;
        }
        ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Booking List <?= $dateNOW ?></h4>

                        <div class="table-responsive" style="max-height: 700px; overflow-y:auto;">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Nama</th>
                                        <th>Telepon</th>

                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr class="booking-row" data-booking-id="<?= $row['id'] ?>"
                                                data-status="<?= htmlspecialchars($row['status']) ?>"
                                                data-tip="<?= htmlspecialchars($row['tip']) ?>"
                                                data-no-ps="<?= htmlspecialchars($row['no_ps']) ?>"
                                                data-total-price="<?= htmlspecialchars($row['total_price']) ?>">
                                                <td>
                                                    <?= htmlspecialchars($row['time_start']) ?> -
                                                    <?= htmlspecialchars($row['time_end']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['name']) ?></td>
                                                <td><?= htmlspecialchars($row['no_ps']) ?></td>
                                                <!-- <td><?= htmlspecialchars($row['type_ps']) ?></td> -->
                                                <td>
                                                    <?php
                                                    $nama_usaha = $merchand;
                                                    $alamat_usaha = $address;

                                                    if ($row['status'] == "1"): ?>
                                                        <span class="badge bg-success">Verified</span>

                                                    <?php elseif ($row['status'] == null || $row['status'] == "pending"): ?>
                                                        <span class="badge bg-warning">Unverified</span>
                                                        <?php
                                                        $nohp = preg_replace('/[^0-9]/', '', $row['no_ps']);
                                                        if (substr($nohp, 0, 1) == "0") {
                                                            $nohp = "62" . substr($nohp, 1);
                                                        }

                                                        $pesan = "Halo *{$row['name']}*,\n\nKami dari *{$nama_usaha}* ingin menginformasikan bahwa status booking Anda saat ini *belum terverifikasi*.\n\n📅 Tanggal: {$row['date']}\n⏰ Waktu: {$row['time_start']} - {$row['time_end']}\n📍 Alamat: {$alamat_usaha}\n\nMohon segera lakukan *verifikasi pembayaran* untuk menghindari pembatalan otomatis.\n\n";
                                                        $pesan .= "\nTerima kasih.\n- Admin {$nama_usaha}";

                                                        $pesan_enc = urlencode($pesan);
                                                        $wa_link = "https://api.whatsapp.com/send?phone={$nohp}&text={$pesan_enc}";
                                                        ?>
                                                    <?php elseif ($row['status'] == "0"): ?>
                                                        <span class="badge bg-danger">Failed</span>

                                                    <?php else: ?>
                                                        <span
                                                            class="badge bg-secondary"><?= htmlspecialchars($row['status']) ?></span>
                                                    <?php endif; ?>

                                                    <br><br>

                                                    <!-- Payment Status -->
                                                    <?php if ($row['payment_status'] == "full"): ?>
                                                        <span class="badge bg-success">Full Payment</span>
                                                    <?php elseif ($row['payment_status'] == "dp"): ?>
                                                        <span class="badge bg-warning">DP</span>
                                                    <?php else: ?>
                                                        <span
                                                            class="badge bg-secondary"><?= htmlspecialchars($row['payment_status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td onclick="event.stopPropagation();">
                                                    <?php if ($row['status'] == null || $row['status'] == "pending"): ?>
                                                        <a href="<?= $wa_link ?>" target="_blank" title="Kirim WA">
                                                            <i class="bx bxl-whatsapp-square font-size-20 text-success"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <?php if (!$result): ?>
                                                    Error loading data. Please check database connection.
                                                <?php else: ?>
                                                    Tidak ada booking hari ini.
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

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
                                <?php
                                $sql = "SELECT phone, id FROM booking_phone WHERE status = 'success' AND email='$username' LIMIT 1";
                                $result = $con->query($sql);
                                $hasData = ($result && $result->num_rows > 0);
                                ?>

                                <div class="mt-0">
                                    <?php
                                    $encryptedId = encrypt($license);
                                    $portalUrl = "./portal.php?q=" . $encryptedId;
                                    ?>

                                    <a href="javascript:void(0);" class="btn btn-primary btn-sm"
                                        onclick="<?php echo $hasData ? "copyToClipboard('$portalUrl')" : "showNotFoundAlert()"; ?>">
                                        <i class='bx bx-copy align-middle'></i> Salin Tautan
                                    </a>

                                    <a href="<?php echo $hasData ? $portalUrl : 'javascript:void(0);'; ?>"
                                        class="btn btn-primary btn-sm" <?php echo $hasData ? 'target="_blank"' : 'onclick="showNotFoundAlert()"'; ?>>
                                        <i class='bx bx-navigation align-middle'></i> Lihat Portal
                                    </a>
                                </div>

                                <script>
                                    function showNotFoundAlert() {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Data Tidak Ditemukan',
                                            text: 'Phone order tidak ditemukan dalam sistem',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                </script>
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

            <div class="col-lg-4">
                <!-- Tambahkan CDN SweetAlert2 di head -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>
                <link rel="stylesheet"
                    href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css">

                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title d-flex justify-content-between align-items-center">Whatsapp Booking</h4>

                        <div class="table-responsive">

                            <?php if ($hasData): ?>
                                <?php $row = $result->fetch_assoc(); ?>
                                <div id="phone-display-<?php echo $row['id']; ?>" class="phone-section">
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                        <div>
                                            <div class="phone-value fs-6">
                                                <i class="fab fa-whatsapp text-success me-2"></i>
                                                <span
                                                    id="phone-text-<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['phone']); ?></span>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm"
                                            onclick="editPhone(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </div>
                                </div>

                                <div id="phone-edit-<?php echo $row['id']; ?>" class="phone-section" style="display: none;">
                                    <form onsubmit="savePhone(event, <?php echo $row['id']; ?>)">
                                        <div class="input-group">
                                            <span class="input-group-text"><i
                                                    class="fab fa-whatsapp text-success"></i></span>
                                            <input type="tel" id="phone-input-<?php echo $row['id']; ?>"
                                                class="form-control" value="<?php echo htmlspecialchars($row['phone']); ?>"
                                                placeholder="Enter phone number" required>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save"></i>
                                            </button>
                                            <button type="button" class="btn btn-secondary"
                                                onclick="cancelEdit(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div id="phone-add-section">
                                    <div id="add-button-container" class="text-center p-4">

                                        <button class="btn btn-success" onclick="showAddForm()">
                                            <i class="fas fa-plus-circle"></i> Add Phone Number
                                        </button>
                                    </div>

                                    <div id="add-form-container" style="display: none;">
                                        <form onsubmit="addPhone(event)">
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="fab fa-whatsapp text-success"></i></span>
                                                <input type="tel" id="new-phone-input" class="form-control"
                                                    placeholder="Enter whatsapp number" required>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                                <button type="button" class="btn btn-secondary" onclick="hideAddForm()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <script>
                    function editPhone(id) {
                        document.getElementById('phone-display-' + id).style.display = 'none';
                        document.getElementById('phone-edit-' + id).style.display = 'block';
                    }

                    function cancelEdit(id) {
                        document.getElementById('phone-display-' + id).style.display = 'block';
                        document.getElementById('phone-edit-' + id).style.display = 'none';
                    }

                    function savePhone(event, id) {
                        event.preventDefault();
                        const phoneValue = document.getElementById('phone-input-' + id).value;

                        fetch('./controller/ajax_phone_handler.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'update',
                                id: id,
                                phone: phoneValue
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    document.getElementById('phone-text-' + id).textContent = phoneValue;
                                    cancelEdit(id);
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Phone number updated successfully',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    alert('Error updating phone');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error updating phone');
                            });
                    }

                    function showAddForm() {
                        document.getElementById('add-button-container').style.display = 'none';
                        document.getElementById('add-form-container').style.display = 'block';
                    }

                    function hideAddForm() {
                        document.getElementById('add-button-container').style.display = 'block';
                        document.getElementById('add-form-container').style.display = 'none';
                        document.getElementById('new-phone-input').value = '';
                    }

                    function addPhone(event) {
                        event.preventDefault();
                        const phoneValue = document.getElementById('new-phone-input').value;

                        fetch('./controller/ajax_phone_handler.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'add',
                                phone: phoneValue
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Phone number added successfully',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    alert('Error adding phone');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error adding phone');
                            });
                    }
                </script>

                <style>
                    .phone-section {
                        margin: 7px 0;
                    }

                    .phone-value {
                        font-weight: 500;
                    }

                    .btn-sm {
                        padding: 5px 5px;
                    }
                </style>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="font-size-15 mt-4 mb-0">Fasilitas :</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#addFacilityModal">
                                <i class="bx bx-plus"></i> Add
                            </button>
                        </div>

                        <div class="mt-3">
                            <?php
                            $facility = $con->query("SELECT id, name FROM tb_facility WHERE userx = '$username' ORDER BY name ASC");

                            if ($facility->num_rows > 0) {
                                while ($f = $facility->fetch_assoc()) {
                                    echo '<div class="d-flex justify-content-between align-items-center mb-1">
                            <p class="mb-0"><i class="bx bx-check-square text-success me-1"></i> ' . htmlspecialchars($f['name']) . '</p>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteFacility(' . $f['id'] . ')">
                                <i class="bx bx-trash"></i>
                            </button>
                          </div>';
                                }
                            } else {
                                echo '<p class="mb-0 text-danger"><i class="mdi mdi-alert-circle-outline align-middle me-1"></i> Tidak ada fasilitas terdaftar.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Modal Add Facility -->
                <div class="modal fade" id="addFacilityModal" tabindex="-1" aria-labelledby="addFacilityModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addFacilityModalLabel">Tambah Fasilitas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="" method="POST">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="facilityName" class="form-label">Nama Fasilitas</label>
                                        <input type="text" class="form-control" id="facilityName" name="facility_name"
                                            maxlength="25" required>
                                        <div class="form-text">Maksimal 25 karakter</div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="add_facility" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php
                if (isset($_POST['add_facility'])) {
                    $facility_name = trim($_POST['facility_name']);

                    if (!empty($facility_name) && strlen($facility_name) <= 25) {
                        $stmt = $con->prepare("INSERT INTO tb_facility (userx, name) VALUES (?, ?)");
                        $stmt->bind_param("ss", $username, $facility_name);

                        if ($stmt->execute()) {
                            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Fasilitas berhasil ditambahkan',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = window.location.href;
                });
            </script>";
                        } else {
                            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal menambahkan fasilitas'
                });
            </script>";
                        }
                        $stmt->close();
                    }
                }

                if (isset($_POST['delete_facility'])) {
                    $facility_id = $_POST['facility_id'];

                    $stmt = $con->prepare("DELETE FROM tb_facility WHERE id = ? AND userx = ?");
                    $stmt->bind_param("is", $facility_id, $username);

                    if ($stmt->execute()) {
                        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Fasilitas berhasil dihapus',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = window.location.href;
            });
        </script>";
                    } else {
                        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menghapus fasilitas'
            });
        </script>";
                    }
                    $stmt->close();
                }
                ?>

                <script>
                    function deleteFacility(id) {
                        Swal.fire({
                            title: 'Hapus Fasilitas?',
                            text: 'Data akan dihapus permanen',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Hapus',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.innerHTML = `<input type="hidden" name="delete_facility" value="1">
                             <input type="hidden" name="facility_id" value="${id}">`;
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });
                    }
                </script>


                <div class="card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="font-size-15 mt-4 mb-0">Ketentuan :</h5>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#addKetentuanModal">
                                    <i class="bx bx-plus"></i> Add
                                </button>
                            </div>

                            <div class="mt-3">
                                <?php
                                $ketentuan = $con->query("SELECT id, name FROM tb_ketentuan WHERE userx = '$username' ORDER BY name ASC");

                                if ($ketentuan->num_rows > 0) {
                                    while ($k = $ketentuan->fetch_assoc()) {
                                        echo '<div class="d-flex justify-content-between align-items-center mb-1">
                                <p class="mb-0"><i class="bx bx-check-square text-success me-1"></i> ' . htmlspecialchars($k['name']) . '</p>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteKetentuan(' . $k['id'] . ')">
                                    <i class="bx bx-trash"></i>
                                </button>
                                </div>';
                                    }
                                } else {
                                    echo '<p class="mb-0 text-danger"><i class="mdi mdi-alert-circle-outline align-middle me-1"></i> Tidak ada ketentuan terdaftar.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Add Ketentuan -->
                    <div class="modal fade" id="addKetentuanModal" tabindex="-1"
                        aria-labelledby="addKetentuanModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addKetentuanModalLabel">Tambah Ketentuan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="" method="POST">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="ketentuanName" class="form-label">Nama Ketentuan</label>
                                            <input type="text" class="form-control" id="ketentuanName"
                                                name="ketentuan_name" maxlength="25" required>
                                            <div class="form-text">Maksimal 25 karakter</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="add_ketentuan"
                                            class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php
                    if (isset($_POST['add_ketentuan'])) {
                        $ketentuan_name = trim($_POST['ketentuan_name']);

                        if (!empty($ketentuan_name) && strlen($ketentuan_name) <= 25) {
                            $stmt = $con->prepare("INSERT INTO tb_ketentuan (userx, name) VALUES (?, ?)");
                            $stmt->bind_param("ss", $username, $ketentuan_name);

                            if ($stmt->execute()) {
                                echo "<script>
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Ketentuan berhasil ditambahkan',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = window.location.href;
                                });
                                </script>";
                            } else {
                                echo "<script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Gagal menambahkan ketentuan'
                                });
                                </script>";
                            }
                            $stmt->close();
                        }
                    }

                    if (isset($_POST['delete_ketentuan'])) {
                        $ketentuan_id = $_POST['ketentuan_id'];

                        $stmt = $con->prepare("DELETE FROM tb_ketentuan WHERE id = ? AND userx = ?");
                        $stmt->bind_param("is", $ketentuan_id, $username);

                        if ($stmt->execute()) {
                            echo "<script>
                            Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Ketentuan berhasil dihapus',
                            timer: 1500,
                            showConfirmButton: false
                            }).then(() => {
                            window.location.href = window.location.href;
                            });
                            </script>";
                        } else {
                            echo "<script>
                            Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Gagal menghapus ketentuan'
                            });
                            </script>";
                        }
                        $stmt->close();
                    }
                    ?>

                    <script>
                        function deleteKetentuan(id) {
                            Swal.fire({
                                title: 'Hapus Ketentuan?',
                                text: 'Data akan dihapus permanen',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Hapus',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.innerHTML = `<input type="hidden" name="delete_ketentuan" value="1">
                        <input type="hidden" name="ketentuan_id" value="${id}">`;
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            });
                        }
                    </script>

                    <style>
                        .payment-method {
                            cursor: pointer;
                            transition: all 0.3s ease;
                            border: 2px solid transparent;
                        }

                        .payment-method:hover {
                            border-color: #007bff;
                            transform: translateY(-2px);
                        }

                        .payment-method.selected {
                            border-color: #007bff;
                            background-color: #e7f3ff;
                        }

                        .booking-row {
                            cursor: pointer;
                        }

                        .booking-row:hover {
                            background-color: #f8f9fa;
                        }
                    </style>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const bookingRows = document.querySelectorAll('.booking-row');
                            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                            let selectedPaymentMethod = null;
                            let currentBookingId = null;

                            // Event listener untuk klik row
                            bookingRows.forEach(row => {
                                row.addEventListener('click', function () {
                                    const bookingId = this.dataset.bookingId;
                                    const status = this.dataset.status;
                                    const tip = this.dataset.tip || '0';
                                    const noPs = this.dataset.noPs;
                                    const totalPrice = this.dataset.totalPrice;

                                    currentBookingId = bookingId;

                                    // Isi data ke modal
                                    document.getElementById('modalName').textContent = this.cells[1].textContent;
                                    document.getElementById('modalPhone').textContent = this.cells[2].textContent;
                                    document.getElementById('modalTime').textContent = this.cells[0].textContent;

                                    // Ambil harga dari database berdasarkan type PS
                                    fetchPriceAndCalculate(noPs, totalPrice, tip);

                                    // Reset form pembayaran
                                    document.getElementById('paymentAmount').value = '';
                                    document.getElementById('paymentMethod').value = '';
                                    selectedPaymentMethod = null;

                                    paymentModal.show();
                                });
                            });

                            // Event listener untuk select metode pembayaran
                            const paymentSelect = document.getElementById('paymentMethod');
                            paymentSelect.addEventListener('change', function () {
                                selectedPaymentMethod = this.value;
                            });

                            // Event listener untuk konfirmasi pembayaran
                            document.getElementById('confirmPayment').addEventListener('click', function () {
                                const paymentAmount = document.getElementById('paymentAmount').value;

                                if (!selectedPaymentMethod) {
                                    alert('Pilih metode pembayaran terlebih dahulu');
                                    return;
                                }
                                if (!paymentAmount || parseFloat(paymentAmount) <= 0) {
                                    alert('Masukkan jumlah bayar yang valid');
                                    return;
                                }

                                // Proses konfirmasi pembayaran
                                processPayment(currentBookingId, selectedPaymentMethod, paymentAmount);
                            });

                            function fetchPriceAndCalculate(noPs, totalPrice, tip) {
                                fetch('get_price.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: `type=ps&no_ps=${noPs}`
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        const basePrice = parseFloat(data.price || 0);
                                        const tipAmount = parseFloat(tip || 0);
                                        const total = basePrice + tipAmount;
                                        const paid = parseFloat(totalPrice || 0);
                                        const remaining = total - paid;

                                        document.getElementById('modalTotalPrice').textContent = formatRupiah(total);
                                        document.getElementById('modalPaidAmount').textContent = formatRupiah(paid);
                                        document.getElementById('modalRemainingAmount').textContent = formatRupiah(remaining);
                                    })
                                    .catch(error => {
                                        console.error('Error fetching price:', error);
                                        const total = parseFloat(totalPrice || 0);
                                        document.getElementById('modalTotalPrice').textContent = formatRupiah(total);
                                        document.getElementById('modalPaidAmount').textContent = 'Rp 0';
                                        document.getElementById('modalRemainingAmount').textContent = formatRupiah(total);
                                    });
                            }

                            function processPayment(bookingId, method, amount) {
                                fetch('controller/payment_booking.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: `booking_id=${bookingId}&payment_method=${method}&amount=${amount}`
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert('Pembayaran berhasil dikonfirmasi');
                                            paymentModal.hide();
                                            location.reload(); // Refresh halaman
                                        } else {
                                            alert('Gagal konfirmasi pembayaran: ' + data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error processing payment:', error);
                                        alert('Terjadi kesalahan saat memproses pembayaran');
                                    });
                            }

                            function formatRupiah(number) {
                                return new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(number);
                            }
                        });
                    </script>

                </div>



                <div class="card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="font-size-15 mt-4 mb-0">Galeri :</h5>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#addGalleryModal">
                                    <i class="bx bx-plus"></i> Add
                                </button>
                            </div>

                            <div class="mt-3">
                                <?php
                                $gallery = $con->query("SELECT name, date, pic FROM tb_galery WHERE userx = '$username' ORDER BY date DESC");

                                if ($gallery->num_rows > 0) {
                                    echo '<div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                            <div class="carousel-inner">';

                                    $active = 'active';
                                    while ($g = $gallery->fetch_assoc()) {
                                        echo '<div class="carousel-item ' . $active . '">
                                <div class="position-relative">
                                    <img src="' . htmlspecialchars($g['pic']) . '" class="d-block w-100 gallery-image" alt="' . htmlspecialchars($g['name']) . '">
                                    <div class="carousel-caption d-md-block bg-dark bg-opacity-50 rounded">
                                        <h6 class="mb-1">' . htmlspecialchars($g['name']) . '</h6>
                                        <small>' . date('d/m/Y', strtotime($g['date'])) . '</small>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteGallery(\'' . htmlspecialchars($g['pic']) . '\', \'' . htmlspecialchars($g['name']) . '\')">
                                                <i class="bx bx-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                              </div>';
                                        $active = '';
                                    }

                                    echo '</div>
                          <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                          </button>
                          <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                          </button>
                        </div>';
                                } else {
                                    echo '<div class="text-center py-4">
                            <i class="bx bx-image-alt text-muted" style="font-size: 3rem;"></i>
                            <p class="mb-0 text-muted mt-2">Belum ada foto di galeri.</p>
                          </div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Add Gallery -->
                    <div class="modal fade" id="addGalleryModal" tabindex="-1" aria-labelledby="addGalleryModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addGalleryModalLabel">Tambah Foto Galeri</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="galleryName" class="form-label">Nama Foto</label>
                                            <input type="text" class="form-control" id="galleryName" name="gallery_name"
                                                maxlength="50" required>
                                            <div class="form-text">Maksimal 50 karakter</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="galleryImage" class="form-label">Upload Foto</label>
                                            <input type="file" class="form-control" id="galleryImage"
                                                name="gallery_image" accept="image/*" required>
                                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB</div>
                                        </div>
                                        <div class="mb-3">
                                            <div id="imagePreview" class="text-center" style="display: none;">
                                                <img id="previewImg" src="#" alt="Preview" class="img-thumbnail"
                                                    style="max-width: 200px; max-height: 200px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="add_gallery" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php
                    if (isset($_POST['add_gallery'])) {
                        $gallery_name = trim($_POST['gallery_name']);

                        if (!empty($gallery_name) && strlen($gallery_name) <= 50 && isset($_FILES['gallery_image'])) {
                            $upload_dir = 'assets/images/galery/';

                            // Create directory if not exists
                            if (!file_exists($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }

                            $file = $_FILES['gallery_image'];
                            $file_name = $file['name'];
                            $file_tmp = $file['tmp_name'];
                            $file_size = $file['size'];
                            $file_error = $file['error'];

                            // Check for upload errors
                            if ($file_error === 0) {
                                // Check file size (2MB max)
                                if ($file_size <= 2 * 1024 * 1024) {
                                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

                                    if (in_array($file_ext, $allowed_ext)) {
                                        // Generate unique filename
                                        $new_filename = $username . '_' . time() . '_' . uniqid() . '.' . $file_ext;
                                        $file_path = $upload_dir . $new_filename;

                                        if (move_uploaded_file($file_tmp, $file_path)) {
                                            $stmt = $con->prepare("INSERT INTO tb_galery (userx, name, date, pic) VALUES (?, ?, NOW(), ?)");
                                            $stmt->bind_param("sss", $username, $gallery_name, $file_path);

                                            if ($stmt->execute()) {
                                                echo "<script>
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Foto berhasil ditambahkan ke galeri',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = window.location.href;
                                });
                                </script>";
                                            } else {
                                                // Delete uploaded file if database insert fails
                                                unlink($file_path);
                                                echo "<script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Gagal menambahkan foto ke database'
                                });
                                </script>";
                                            }
                                            $stmt->close();
                                        } else {
                                            echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Gagal mengupload file'
                            });
                            </script>";
                                        }
                                    } else {
                                        echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Format Tidak Valid!',
                            text: 'Hanya file JPG, PNG, dan GIF yang diperbolehkan'
                        });
                        </script>";
                                    }
                                } else {
                                    echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'File Terlalu Besar!',
                        text: 'Ukuran file maksimal 2MB'
                    });
                    </script>";
                                }
                            } else {
                                echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error Upload!',
                    text: 'Terjadi error saat mengupload file'
                });
                </script>";
                            }
                        }
                    }

                    if (isset($_POST['delete_gallery'])) {
                        $gallery_pic = $_POST['gallery_pic'];

                        $stmt = $con->prepare("DELETE FROM tb_galery WHERE pic = ? AND userx = ?");
                        $stmt->bind_param("ss", $gallery_pic, $username);

                        if ($stmt->execute()) {
                            // Delete file from server
                            if (file_exists($gallery_pic)) {
                                unlink($gallery_pic);
                            }

                            echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Foto berhasil dihapus dari galeri',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = window.location.href;
            });
            </script>";
                        } else {
                            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menghapus foto dari galeri'
            });
            </script>";
                        }
                        $stmt->close();
                    }
                    ?>

                    <script>
                        // Image preview function
                        document.getElementById('galleryImage').addEventListener('change', function (e) {
                            const file = e.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function (e) {
                                    document.getElementById('previewImg').src = e.target.result;
                                    document.getElementById('imagePreview').style.display = 'block';
                                }
                                reader.readAsDataURL(file);
                            } else {
                                document.getElementById('imagePreview').style.display = 'none';
                            }
                        });

                        // Delete gallery function
                        function deleteGallery(picPath, name) {
                            Swal.fire({
                                title: 'Hapus Foto?',
                                text: `Foto "${name}" akan dihapus permanen`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Hapus',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.innerHTML = `<input type="hidden" name="delete_gallery" value="1">
                        <input type="hidden" name="gallery_pic" value="${picPath}">`;
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            });
                        }

                        // Reset modal when closed
                        document.getElementById('addGalleryModal').addEventListener('hidden.bs.modal', function () {
                            document.getElementById('galleryName').value = '';
                            document.getElementById('galleryImage').value = '';
                            document.getElementById('imagePreview').style.display = 'none';
                        });
                    </script>

                    <style>
                        .gallery-image {
                            height: 300px;
                            object-fit: cover;
                            border-radius: 0.5rem;
                        }

                        .carousel-item {
                            transition: transform 0.6s ease-in-out;
                        }

                        .carousel-caption {
                            bottom: 1rem;
                            padding: 1rem;
                        }

                        .carousel-control-prev,
                        .carousel-control-next {
                            width: 15%;
                        }

                        .carousel-control-prev-icon,
                        .carousel-control-next-icon {
                            width: 2rem;
                            height: 2rem;
                        }

                        @media (max-width: 768px) {
                            .gallery-image {
                                height: 200px;
                            }

                            .carousel-caption h6 {
                                font-size: 0.9rem;
                            }

                            .carousel-caption small {
                                font-size: 0.7rem;
                            }
                        }
                    </style>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>
<script src="assets/js/app.js"></script>