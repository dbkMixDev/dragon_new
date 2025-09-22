<?php
//  session_start();
 require_once './include/config.php'; // koneksi database
require_once './include/crypto.php'; // jika password ingin di-enkripsi, tapi gunakan password_hash lebih baik
$username = $_SESSION['username'];
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
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    max-width: 100%;
    height: auto;
}

.modal-dialog-lg {
    max-width: 800px;
}
.modal-link {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 500;
}
.modal-link:hover {
    color: #0a58ca;
    text-decoration: underline;
}
.edit-btn {
    cursor: pointer;
    color: #6c757d;
    transition: color 0.3s;
}
.edit-btn:hover {
    color: #495057;
}
.loading-spinner {
    display: none;
}
.logo-container {
    position: relative;
    display: inline-block;
}
.logo-edit-overlay {
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(50%, -50%);
    background: #2973B2;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    box-shadow: 0 2px 2px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s;
}

.logo-edit-overlay:hover {
    background:rgb(47, 82, 117);
    transform: translate(50%, -50%) scale(1.1);
}
.image-preview {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    border: 2px dashed #ddd;
    padding: 10px;
}
.upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}
.upload-area:hover {
    border-color: #007bff;
    background-color: #f8f9ff;
}
.upload-area.dragover {
    border-color: #007bff;
    background-color: #e7f3ff;
}
/* Tambahkan ke bagian <style> yang sudah ada */
.invalid-feedback {
    display: none;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    padding: 0.5rem;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 0.375rem;
}

.invalid-feedback:not(:empty) {
    display: block;
}

.upload-area.error {
    border-color: #dc3545 !important;
    background-color: #fff5f5;
}

.form-control.is-invalid,
.was-validated .form-control:invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* Style untuk quick actions */
.quick-actions {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f0f0f0;
}

.quick-actions .btn {
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

/* Password strength indicator */
.password-strength {
    height: 4px;
    border-radius: 2px;
    margin-top: 5px;
    background-color: #e9ecef;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    transition: all 0.3s ease;
    width: 0%;
}

.strength-weak { background-color: #dc3545; }
.strength-medium { background-color: #ffc107; }
.strength-strong { background-color: #28a745; }

.password-requirements {
    font-size: 0.8rem;
    margin-top: 0.5rem;
}

.requirement {
    color: #6c757d;
    margin-bottom: 0.2rem;
}

.requirement.met {
    color: #28a745;
}

.requirement.met i {
    color: #28a745;
}

.requirement i {
    color: #dc3545;
    margin-right: 0.3rem;
}

/* === TAMBAHAN UNTUK CROP FUNCTIONALITY === */
.logo-upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: #f8f9fa;
}
.logo-upload-area:hover {
    border-color: #007bff;
    background: #e3f2fd;
}
.logo-upload-area.dragover {
    border-color: #007bff;
    background: #e3f2fd;
}
.logo-preview {
    max-width: 100px;
    max-height: 100px;
    border-radius: 8px;
    margin: 10px 0;
}
.error-message {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 5px;
}

/* Cropper Modal Styles - Simple */
.crop-container {
    max-height: 400px;
    overflow: hidden;
}
.crop-preview {
    width: 120px;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #007bff;
    margin: 10px auto;
}
.modal-lg {
    max-width: 800px;
}

/* Cropper customization - Simple */
.cropper-point {
    width: 8px;
    height: 8px;
    background-color: #007bff;
    border-radius: 50%;
}

.cropper-line {
    background-color: #007bff;
}
</style>

<!-- ========= TAMBAHAN CROPPER.JS CSS ========= -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">General Setting</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

<!-- Modal Add User - Adjusted for Database -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserLabel">
                    <i class="bx bx-user-plus text-success"></i> Add Team Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addUsername" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addUsername" name="username" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- <div class="mb-3">
                        <label for="addEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="addEmail" name="email">
                        <div class="invalid-feedback"></div>
                    </div> -->
                    
                    <div class="mb-3">
                        <label for="addPassword" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="addPassword" name="password" required>
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addLevel" class="form-label">Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="addLevel" name="level" required>
                            <option value="">Pilih Level</option>
                            <option value="operator">Operator</option>
                          
                            
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- <div class="mb-3">
                        <label for="addFullname" class="form-label">Address/Notes</label>
                        <textarea class="form-control" id="addFullname" name="fullname" rows="2" placeholder="Alamat atau catatan tambahan"></textarea>
                        <div class="invalid-feedback"></div>
                    </div> -->
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="bx bx-info-circle"></i> 
                            User baru akan memiliki akses sesuai level yang dipilih
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="addUserBtn">
                        <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <i class="bx bx-plus"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User - Adjusted for Database -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserLabel">
                    <i class="bx bx-edit text-primary"></i> Edit Team
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="editUserId" name="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editUsername" name="username" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email">
                        <div class="invalid-feedback"></div>
                    </div> -->
                    
                    <div class="mb-3">
                        <label for="editPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="editPassword" name="password">
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editLevel" class="form-label">Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="editLevel" name="level" required>
                            <option value="">Pilih Level</option>
                            <option value="operator">Operator</option>
                            <!-- <option value="admin">Admin</option>
                            <option value="manager">Manager</option> -->
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- <div class="mb-3">
                        <label for="editFullname" class="form-label">Address/Notes</label>
                        <textarea class="form-control" id="editFullname" name="fullname" rows="2" placeholder="Alamat atau catatan tambahan"></textarea>
                        <div class="invalid-feedback"></div>
                    </div> -->
                    
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="editUserBtn">
                        <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <i class="bx bx-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="deleteUserLabel">
                    <i class="bx bx-trash text-danger"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="mb-3">Apakah Anda yakin ingin menghapus user <strong id="deleteUserName"></strong>?</p>
                <div class="alert alert-warning">
                    <small><i class="bx bx-warning"></i> Tindakan ini tidak dapat dibatalkan!</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">
                    <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <i class="bx bx-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Change Password -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordLabel">
                    <i class="bx bx-key text-warning"></i> Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePasswordForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                <i class="bx bx-hide"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                <i class="bx bx-hide"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <div class="invalid-feedback"></div>
                        <div class="password-requirements">
                            <div class="requirement" id="req-length">
                                <i class="bx bx-x"></i> Minimal 8 karakter
                            </div>
                            <div class="requirement" id="req-uppercase">
                                <i class="bx bx-x"></i> Minimal 1 huruf besar
                            </div>
                            <div class="requirement" id="req-lowercase">
                                <i class="bx bx-x"></i> Minimal 1 huruf kecil
                            </div>
                            <div class="requirement" id="req-number">
                                <i class="bx bx-x"></i> Minimal 1 angka
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="bx bx-hide"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="bx bx-info-circle"></i> 
                            <strong>Tips Keamanan:</strong> Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol untuk keamanan maksimal.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="changePasswordBtn" disabled>
                        <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <i class="bx bx-key"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-4">
                                <div class="logo-container">
                                    <img src="<?=$logox?>" alt="Logo" class="avatar-sm" id="currentLogo">
                                    <div class="logo-edit-overlay" data-bs-toggle="modal" data-bs-target="#editLogoModal" title="Edit Logo">
                                        <i class="bx bx-camera text-white"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex align-items-center">
                                    <h5 class="text-truncate font-size-15 mb-0" id="merchantName"><?=$merchand?></h5>
                                    <i class="bx bx-edit-alt edit-btn ms-2" data-bs-toggle="modal" data-bs-target="#editMerchantModal" title="Edit Merchant Name"></i>
                                </div>
                                <p class="text-muted"><?=$license?></p>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <h5 class="font-size-15 mt-4 mb-0">Address :</h5>
                            <i class="bx bx-edit-alt edit-btn ms-2 mt-4" data-bs-toggle="modal" data-bs-target="#editAddressModal" title="Edit Address"></i>
                        </div>
                        <p class="text-muted" id="merchantAddress"><?=$address?></p>
                       <div class="row align-items-center mb-3">
    <!-- Tombol Change Password -->
    <div class="col-md-6 text-start">
        <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
            <i class="bx bx-key"></i> Change Password
        </button>
    </div>

    <!-- Select Timezone -->
    <div class="col-md-6 text-end">
          <label class="font-size-10 mt-4 mb-0">Time zone :</label>
        <select name="timezone" id="timezone" class="form-select" required style="max-width: 300px; display: inline-block;">
            <?php
            $timezones = DateTimeZone::listIdentifiers();
            foreach ($timezones as $tz) {
                $selected = (isset($timezone) && $timezone === $tz) ? 'selected' : '';
                echo "<option value=\"$tz\" $selected>$tz</option>";
            }
            ?>
        </select>
    </div>
</div>

                        <!-- Quick Actions Section -->
                        <!--  -->
                        
                        <h5 class="font-size-15 mt-4">Package :</h5>
                        <div class="row text-muted mt-4">
                           <div class="col-md-6">
    <p><i class="bx bx-check-square text-success me-1"></i> Maksimal <?=$unit?> unit Android/Google TV </p>

    <?php if ($portal == 0): ?>
        <p 
            class="text-muted"
            data-bs-toggle="tooltip" 
            title="Unlock fitur ini dengan buat video review terbaik di TikTok dan tag akun drag0n Play Billing.">
            <i class="bx bx-no-entry text-danger me-1"></i> 
            Portal Web & Booking Online
        </p>
    <?php else: ?>
        <p><i class="bx bx-check-square text-success me-1"></i> Portal Web & Booking Online</p>
    <?php endif; ?>

    <p><i class="bx bx-check-square text-success me-1"></i> 3 Akun karyawan</p>
      <p><i class="bx bx-check-square text-success me-1"></i> Smart TV / Billiard Unlimited</p>
</div>

<div class="col-md-6">
    <p><i class="bx bx-check-square text-success me-1"></i> Forum Komunitas</p>

    <?php if ($multi_cabang == 0): ?>
        <p 
            class="text-muted"
            data-bs-toggle="tooltip" 
            title="On progress bossqu.">
            <i class="bx bx-no-entry text-danger me-1"></i> 
            Multi-cabang (1 dashboard owner)
        </p>
    <?php else: ?>
        <p><i class="bx bx-check-square text-success me-1"></i> Multi-cabang (1 dashboard owner)</p>
    <?php endif; ?>

    <?php if ($qr == 0): ?>
        <p 
            class="text-muted"
            data-bs-toggle="tooltip" 
            title="QR Code bisa di scan oleh pengguna, dan nanti akan otomatis menampilkan bill pelanggan, Tapi masih on Progress juga hehe.">
            <i class="bx bx-no-entry text-danger me-1"></i> 
           QR Code Unit Interaktif
        </p>
    <?php else: ?>
        <p><i class="bx bx-check-square text-success me-1"></i> QR Code Unit Interaktif</p>
    <?php endif; ?>
</div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- end col -->

            <!-- Updated Team Section with Add Button - Database Adjusted -->
               <?php
// Check autoprint status
$autoDelEnabled = false;
$stmt = $con->prepare("SELECT status FROM tb_feature WHERE userx = ? AND feature = 'autodel'");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Bind hasil kolom 'status' ke variabel (meskipun kita tidak benar-benar pakai nilainya di sini)
    $stmt->bind_result($status);
    $stmt->fetch();

    // Bisa juga cek nilai status, misalnya:
    if ($status == 1) {
        $autoDelEnabled = true;
    }
}

$stmt->close();
?>

<div class="col-lg-5">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="card-title mb-0">Team</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bx bx-plus"></i> Add User
                </button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap" id="teamTable">
                    <tbody>
                        <!-- Data akan dimuat via AJAX -->
                         <div class="d-flex justify-content-between align-items-center mb-4">
           <p class="text-muted mb-0 font-size-13">
  Perbolehkan Team Hapus Transaksi Kasir 
  <?= $autoDelEnabled ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-ban text-danger"></i>' ?>
</p>

            <div class="form-check form-switch mb-0">
                <input class="form-check-input autodel-switch" 
                    type="checkbox" 
                    id="switch-autodel" 
                    <?= $autoDelEnabled ? 'checked' : '' ?>>
                <label class="form-check-label" for="switch-autodel"></label>
            </div>
        </div>
                        <tr id="loadingRow">
                            <td colspan="4" class="text-center">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                Loading team data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
<script>
document.getElementById('switch-autodel').addEventListener('change', function() {
    const isEnabled = this.checked;
    console.log('Switch toggled:', isEnabled);
    this.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'toggle_autoprint');
    formData.append('status', isEnabled ? '1' : '0');
      formData.append('feature', 'autodel');
    
    fetch('controller/feature_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text(); // Ganti ke text dulu untuk debug
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                location.reload();
            } else {
                console.log('Error message:', data.message);
                this.checked = !isEnabled;
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.log('JSON parse error:', e);
            console.log('Response was:', text);
            this.checked = !isEnabled;
            alert('Server error - check console');
        }
    })
    .catch(error => {
        console.log('Fetch error:', error);
        this.checked = !isEnabled;
        alert('Network error: ' + error.message);
    })
    .finally(() => {
        this.disabled = false;
    });
});
</script>

    </div>
    <?php
// Check autoprint status
$autoPrintEnabled = false;
$stmt = $con->prepare("SELECT status FROM tb_feature WHERE userx = ? AND feature = 'autoprint'");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Bind hasil kolom 'status' ke variabel (meskipun kita tidak benar-benar pakai nilainya di sini)
    $stmt->bind_result($status);
    $stmt->fetch();

    // Bisa juga cek nilai status, misalnya:
    if ($status == 1) {
        $autoPrintEnabled = true;
    }
}

$stmt->close();
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title mb-0">Auto Print</h4>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input autoprint-switch" 
                    type="checkbox" 
                    id="switch-autoprint" 
                    <?= $autoPrintEnabled ? 'checked' : '' ?>>
                <label class="form-check-label" for="switch-autoprint"></label>
            </div>
        </div>

        <!-- Content 1: Print Struk -->
        <div class="mb-3">
            <div class="d-flex align-items-center">
                <i class="bx bx-printer text-primary me-3 font-size-20"></i>
                <div>
                    <h6 class="mb-1">Print Struk Otomatis</h6>
                    <p class="text-muted mb-0 font-size-13">Cetak struk secara otomatis setelah pembayaran</p>
                </div>
            </div>
        </div>

        <!-- Content 2: Print Laporan -->
      


        <!-- Status -->
        <div class="alert <?= $autoPrintEnabled ? 'alert-success' : 'alert-secondary' ?> mb-0">
            <small>
                <i class="bx <?= $autoPrintEnabled ? 'bx-check-circle' : 'bx-x-circle' ?> me-1"></i>
                Status: <?= $autoPrintEnabled ? 'Aktif' : 'Nonaktif' ?>
            </small>
        </div>
    </div>
</div>

<script>
document.getElementById('switch-autoprint').addEventListener('change', function() {
    const isEnabled = this.checked;
    console.log('Switch toggled:', isEnabled);
    this.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'toggle_autoprint');
    formData.append('status', isEnabled ? '1' : '0');
     formData.append('feature', 'autoprint');
    fetch('controller/feature_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text(); // Ganti ke text dulu untuk debug
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                location.reload();
            } else {
                console.log('Error message:', data.message);
                this.checked = !isEnabled;
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.log('JSON parse error:', e);
            console.log('Response was:', text);
            this.checked = !isEnabled;
            alert('Server error - check console');
        }
    })
    .catch(error => {
        console.log('Fetch error:', error);
        this.checked = !isEnabled;
        alert('Network error: ' + error.message);
    })
    .finally(() => {
        this.disabled = false;
    });
});
</script>

</div>


<script>
// Team Management JavaScript - Adjusted for Database Structure
let currentDeleteUserId = null;

// Load team data
function loadTeamData() {
    console.log('Loading team data...');
    
    fetch('controller/team_controller.php?action=get_team')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Team data received:', data);
            
            if (data.success) {
                renderTeamTable(data.users);
            } else {
                showToast(data.message || 'Gagal memuat data team', 'danger');
                document.getElementById('teamTable').querySelector('tbody').innerHTML = 
                    '<tr><td colspan="4" class="text-center text-muted">Gagal memuat data team</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading team:', error);
            showToast('Terjadi kesalahan saat memuat data team: ' + error.message, 'danger');
            document.getElementById('teamTable').querySelector('tbody').innerHTML = 
                '<tr><td colspan="4" class="text-center text-muted">Error loading data</td></tr>';
        });
}

// Render team table - adjusted for database structure
function renderTeamTable(users) {
    const tbody = document.getElementById('teamTable').querySelector('tbody');
    
    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Belum ada data team</td></tr>';
        return;
    }
    
  tbody.innerHTML = users.map(user => {
    // Use logox for avatar or create initial avatar
    const avatar = user.avatar && user.avatar.trim() ? 
        `<img src="${user.avatar}" 
     class="rounded-circle team-avatar" 
     alt="${user.username}" 
     style="width: 40px; height: 40px;" 
     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

         <div class="avatar-xs avatar-initial rounded-circle bg-primary text-white" style="display:none;">${user.username.charAt(0).toUpperCase()}</div>` :
        `<div class="avatar-xs avatar-initial rounded-circle bg-primary text-white">${user.username.charAt(0).toUpperCase()}</div>`;
    
    const levelClass = `level-${user.level}`;
    const statusClass = user.status === 'active' ? 'status-active' : 'status-inactive';
    
    // Show last_log if available
    const subtitle = user.last_log ? 
        `<small class="text-muted">Last: ${user.last_log}</small>` : 
        `<small class="text-muted">No activity</small>`;
    
    return `
        <tr data-user-id="${user.id}">
            <td style="width: 50px;">${avatar}</td>
            <td>
                <h5 class="font-size-14 m-0">
                    <a href="javascript:void(0);" class="text-dark">${user.username}</a>
                </h5>
                ${subtitle}
            </td>
            <td>
                <span class="${levelClass} level-badge">${user.level}</span>
                <span class="${statusClass} status-badge ms-1">${user.status}</span>
            </td>
            <td class="text-end">
                <span class="text-primary me-2" onclick="editUser(${user.id})" style="cursor: pointer;" title="Edit User">
                    <i class="bx bx-edit-alt"></i>
                </span>
                <span class="text-danger" onclick="deleteUser(${user.id}, '${user.username}')" style="cursor: pointer;" title="Delete User">
                    <i class="bx bx-trash"></i>
                </span>
            </td>
        </tr>
    `;
}).join('');
}

// Add user
function initializeAddUserForm() {
    const addUserForm = document.getElementById('addUserForm');
    if (!addUserForm) return;
    
    addUserForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(addUserForm);
        formData.append('action', 'add_user');
        
        const addUserBtn = document.getElementById('addUserBtn');
        const spinner = addUserBtn.querySelector('.loading-spinner');
        
        // Validation
        if (!validateAddUserForm()) return;
        
        // Show loading
        spinner.style.display = 'inline-block';
        addUserBtn.disabled = true;
        
        fetch('controller/team_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                modal.hide();
                
                // Reset form
                addUserForm.reset();
                clearFormValidation('addUserForm');
                
                // Reload team data
                loadTeamData();
                
                showToast('User berhasil ditambahkan!', 'success');
            } else {
                showToast(data.message || 'Gagal menambahkan user', 'danger');
            }
        })
        .catch(error => {
            console.error('Error adding user:', error);
            showToast('Terjadi kesalahan saat menambahkan user: ' + error.message, 'danger');
        })
        .finally(() => {
            spinner.style.display = 'none';
            addUserBtn.disabled = false;
        });
    });
}

// Edit user - adjusted for database structure
function editUser(userId) {
    console.log('Editing user:', userId);
    
    // Get user data
    fetch(`controller/team_controller.php?action=get_user&id=${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.user) {
                const user = data.user;
                
                // Populate form
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editUsername').value = user.username;
                // document.getElementById('editEmail').value = user.email || '';
                document.getElementById('editLevel').value = user.level;
                // document.getElementById('editFullname').value = user.address || ''; // Using address field
                document.getElementById('editStatus').value = user.status || 'active';
                
                // Clear password field
                document.getElementById('editPassword').value = '';
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            } else {
                showToast('User tidak ditemukan', 'danger');
            }
        })
        .catch(error => {
            console.error('Error getting user:', error);
            showToast('Terjadi kesalahan saat mengambil data user: ' + error.message, 'danger');
        });
}

// Initialize edit user form
function initializeEditUserForm() {
    const editUserForm = document.getElementById('editUserForm');
    if (!editUserForm) return;
    
    editUserForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(editUserForm);
        formData.append('action', 'update_user');
        
        const editUserBtn = document.getElementById('editUserBtn');
        const spinner = editUserBtn.querySelector('.loading-spinner');
        
        // Validation
        if (!validateEditUserForm()) return;
        
        // Show loading
        spinner.style.display = 'inline-block';
        editUserBtn.disabled = true;
        
        fetch('controller/team_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                modal.hide();
                
                // Reload team data
                loadTeamData();
                
                showToast('User berhasil diperbarui!', 'success');
            } else {
                showToast(data.message || 'Gagal memperbarui user', 'danger');
            }
        })
        .catch(error => {
            console.error('Error updating user:', error);
            showToast('Terjadi kesalahan saat memperbarui user: ' + error.message, 'danger');
        })
        .finally(() => {
            spinner.style.display = 'none';
            editUserBtn.disabled = false;
        });
    });
}

// Delete user
function deleteUser(userId, username) {
    currentDeleteUserId = userId;
    document.getElementById('deleteUserName').textContent = username;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    modal.show();
}

// Initialize delete confirmation
function initializeDeleteUser() {
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (!confirmDeleteBtn) return;
    
    confirmDeleteBtn.addEventListener('click', function() {
        if (!currentDeleteUserId) return;
        
        const spinner = confirmDeleteBtn.querySelector('.loading-spinner');
        
        // Show loading
        spinner.style.display = 'inline-block';
        confirmDeleteBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('action', 'delete_user');
        formData.append('user_id', currentDeleteUserId);
        
        fetch('controller/team_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteUserModal'));
                modal.hide();
                
                // Reload team data
                loadTeamData();
                
                showToast('User berhasil dihapus!', 'success');
            } else {
                showToast(data.message || 'Gagal menghapus user', 'danger');
            }
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            showToast('Terjadi kesalahan saat menghapus user: ' + error.message, 'danger');
        })
        .finally(() => {
            spinner.style.display = 'none';
            confirmDeleteBtn.disabled = false;
            currentDeleteUserId = null;
        });
    });
}

// Password Strength Checker
function checkPasswordStrength(password) {
    let strength = 0;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    // Update requirement indicators
    updateRequirement('req-length', requirements.length);
    updateRequirement('req-uppercase', requirements.uppercase);
    updateRequirement('req-lowercase', requirements.lowercase);
    updateRequirement('req-number', requirements.number);
    
    // Calculate strength
    Object.values(requirements).forEach(met => {
        if (met) strength++;
    });
    
    // Update strength bar
    const strengthBar = document.getElementById('passwordStrengthBar');
    if (strengthBar) {
        let strengthClass = '';
        let strengthPercent = 0;
        
        if (strength < 3) {
            strengthClass = 'strength-weak';
            strengthPercent = 25;
        } else if (strength < 5) {
            strengthClass = 'strength-medium';
            strengthPercent = 60;
        } else {
            strengthClass = 'strength-strong';
            strengthPercent = 100;
        }
        
        strengthBar.className = `password-strength-bar ${strengthClass}`;
        strengthBar.style.width = `${strengthPercent}%`;
    }
    
    return strength >= 4; // Return true if strong enough
}

function updateRequirement(reqId, met) {
    const element = document.getElementById(reqId);
    if (element) {
        element.classList.toggle('met', met);
        const icon = element.querySelector('i');
        if (icon) {
            icon.className = met ? 'bx bx-check' : 'bx bx-x';
        }
    }
}

// Initialize Change Password Form
function initializeChangePasswordForm() {
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (!changePasswordForm) return;
    
    const currentPassword = document.getElementById('currentPassword');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    
    // Password visibility toggles
    initializePasswordToggle('toggleCurrentPassword', 'currentPassword');
    initializePasswordToggle('toggleNewPassword', 'newPassword');
    initializePasswordToggle('toggleConfirmPassword', 'confirmPassword');
    
    // New password strength checking
    if (newPassword) {
        newPassword.addEventListener('input', function() {
            const isStrong = checkPasswordStrength(this.value);
            validateChangePasswordForm();
        });
    }
    
    // Confirm password validation
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            validateChangePasswordForm();
        });
    }
    
    // Current password validation
    if (currentPassword) {
        currentPassword.addEventListener('input', function() {
            validateChangePasswordForm();
        });
    }
    
    // Form submission
    changePasswordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateChangePasswordForm()) return;
        
        const formData = new FormData();
        formData.append('action', 'change_password');
        formData.append('current_password', currentPassword.value);
        formData.append('new_password', newPassword.value);
        
        const spinner = changePasswordBtn.querySelector('.loading-spinner');
        
        // Show loading
        spinner.style.display = 'inline-block';
        changePasswordBtn.disabled = true;
        
        fetch('controller/change_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                modal.hide();
                
                // Reset form
                changePasswordForm.reset();
                clearPasswordValidation();
                
                showToast('Password berhasil diubah!', 'success');
            } else {
                showToast(data.message || 'Gagal mengubah password', 'danger');
                
                // Show field-specific errors
                if (data.field_error) {
                    showPasswordFieldError(data.field_error.field, data.field_error.message);
                }
            }
        })
        .catch(error => {
            console.error('Error changing password:', error);
            showToast('Terjadi kesalahan saat mengubah password: ' + error.message, 'danger');
        })
        .finally(() => {
            spinner.style.display = 'none';
            changePasswordBtn.disabled = false;
        });
    });
}

function initializePasswordToggle(toggleId, inputId) {
    const toggle = document.getElementById(toggleId);
    const input = document.getElementById(inputId);
    
    if (toggle && input) {
        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = toggle.querySelector('i');
            if (icon) {
                icon.className = type === 'password' ? 'bx bx-hide' : 'bx bx-show';
            }
        });
    }
}

function validateChangePasswordForm() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    
    let isValid = true;
    
    // Clear previous errors
    clearPasswordValidation();
    
    // Current password validation
    if (currentPassword.length < 1) {
        showPasswordFieldError('currentPassword', 'Password saat ini harus diisi');
        isValid = false;
    }
    
    // New password validation
    if (newPassword.length < 8) {
        showPasswordFieldError('newPassword', 'Password baru minimal 8 karakter');
        isValid = false;
    } else if (!checkPasswordStrength(newPassword)) {
        showPasswordFieldError('newPassword', 'Password terlalu lemah');
        isValid = false;
    }
    
    // Confirm password validation
    if (confirmPassword !== newPassword) {
        showPasswordFieldError('confirmPassword', 'Konfirmasi password tidak cocok');
        isValid = false;
    }
    
    // Same password check
    if (currentPassword === newPassword && currentPassword.length > 0) {
        showPasswordFieldError('newPassword', 'Password baru harus berbeda dari password saat ini');
        isValid = false;
    }
    
    // Enable/disable submit button
    if (changePasswordBtn) {
        changePasswordBtn.disabled = !isValid;
    }
    
    return isValid;
}

function showPasswordFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const feedback = field.parentElement.nextElementSibling;
    
    field.classList.add('is-invalid');
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
    }
}

function clearPasswordValidation() {
    const fields = ['currentPassword', 'newPassword', 'confirmPassword'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const feedback = field.parentElement.nextElementSibling;
        
        field.classList.remove('is-invalid');
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
    });
    
    // Reset strength bar
    const strengthBar = document.getElementById('passwordStrengthBar');
    if (strengthBar) {
        strengthBar.style.width = '0%';
        strengthBar.className = 'password-strength-bar';
    }
    
    // Reset requirements
    ['req-length', 'req-uppercase', 'req-lowercase', 'req-number'].forEach(reqId => {
        updateRequirement(reqId, false);
    });
}

// Quick Actions Functions
function downloadBackup() {
    showToast('Preparing backup download...', 'info');
    
    // Create download link
    const link = document.createElement('a');
    link.href = 'controller/backup_controller.php?action=download';
    link.download = `backup_${new Date().toISOString().split('T')[0]}.sql`;
    link.click();
    
    setTimeout(() => {
        showToast('Backup download started!', 'success');
    }, 1000);
}

function exportData() {
    showToast('Preparing data export...', 'info');
    
    // Create export link
    const link = document.createElement('a');
    link.href = 'controller/export_controller.php?action=export_all';
    link.download = `data_export_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    
    setTimeout(() => {
        showToast('Data export started!', 'success');
    }, 1000);
}

// Form validation functions
function validateAddUserForm() {
    const username = document.getElementById('addUsername').value.trim();
    const password = document.getElementById('addPassword').value;
    const level = document.getElementById('addLevel').value;
    
    let isValid = true;
    
    // Clear previous validation
    clearFormValidation('addUserForm');
    
    // Username validation
    if (username.length < 3) {
        showFieldError('addUsername', 'Username minimal 3 karakter');
        isValid = false;
    }
    
    // Password validation
    if (password.length < 6) {
        showFieldError('addPassword', 'Password minimal 6 karakter');
        isValid = false;
    }
    
    // Level validation
    if (!level) {
        showFieldError('addLevel', 'Level harus dipilih');
        isValid = false;
    }
    
    return isValid;
}

function validateEditUserForm() {
    const username = document.getElementById('editUsername').value.trim();
    const password = document.getElementById('editPassword').value;
    const level = document.getElementById('editLevel').value;
    
    let isValid = true;
    
    // Clear previous validation
    clearFormValidation('editUserForm');
    
    // Username validation
    if (username.length < 3) {
        showFieldError('editUsername', 'Username minimal 3 karakter');
        isValid = false;
    }
    
    // Password validation (only if provided)
    if (password && password.length < 6) {
        showFieldError('editPassword', 'Password minimal 6 karakter');
        isValid = false;
    }
    
    // Level validation
    if (!level) {
        showFieldError('editLevel', 'Level harus dipilih');
        isValid = false;
    }
    
    return isValid;
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const feedback = field.nextElementSibling;
    
    field.classList.add('is-invalid');
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
    }
}

function clearFormValidation(formId) {
    const form = document.getElementById(formId);
    const fields = form.querySelectorAll('.form-control, .form-select');
    
    fields.forEach(field => {
        field.classList.remove('is-invalid');
        const feedback = field.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
    });
}

// Initialize team management
function initializeTeamManagement() {
    console.log('Initializing team management...');
    
    loadTeamData();
    initializeAddUserForm();
    initializeEditUserForm();
    initializeDeleteUser();
    initializeChangePasswordForm();
    
    console.log('Team management initialized');
}

// Add to main initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize team management after other components
    setTimeout(initializeTeamManagement, 100);
});
</script>
            <!-- end col -->
        </div>
        <!-- end row -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Overview</h4>
                        
                        <div class="table-responsive">
                            <h5 class="mb-3">🔌 Jenis TV & Kebutuhan Kontrolnya</h5>
                            <table class="table table-bordered align-middle text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Model TV</th>
                                        <th>Butuh PC/Laptop</th>
                                        <th>Mode Kontrol</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Android TV / Google TV</td>
                                        <td>✅ Ya</td>
                                        <td>ADB</td>
                                        <td>ADB butuh PC/laptop & jaringan WiFi. Bisa matikan & hidupkan TV langsung dari PC/Laptop tanpa putus arus  & tanpa kabel.</td>
                                    </tr>
                                    <tr>
                                        <td>Smart TV / TV Non-Smart / TV Tabung</td>
                                        <td>❌ Tidak</td>
                                        <td>IR, Relay</td>
                                        <td>IR meniru remote asli. Relay putus daya listrik.</td>
                                    </tr>
                                    <tr>
                                        <td>Billiard</td>
                                        <td>❌ Tidak</td>
                                        <td>Relay</td>
                                        <td>Gunakan Relay untuk matikan lampu billiard.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4">
                            <h5 class="mb-3">💡 Penjelasan Mode Kontrol</h5>
                            <table class="table table-bordered align-middle text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mode</th>
                                        <th>Kebutuhan</th>
                                        <th>Fungsi</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>ADB</strong></td>
                                        <td>PC/Laptop + Android TV dalam 1 jaringan</td>
                                        <td>
                                            Kontrol via WiFi: kontrol TV,volume, dll.
                                            <br>Wajib menggunakan PC/Laptop (Tanpa Modul)<br>
                                            <a href="#" class="modal-link" data-bs-toggle="modal" data-bs-target="#modalADB">
                                                <i class="bx bx-image"></i> Lihat Gambar
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-sm flex-shrink-0">
                                                    <a href="../apps/apps_dragonplay.zip" download>
                                                        <span class="avatar-title rounded-circle bg-primary bg-soft text-primary font-size-24">
                                                            <i class="bx bxs-download"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                                <div>
                                                    <h5 class="font-size-14 mb-1">
                                                        <a href="../apps/apps_dragonplay.zip" download class="text-dark">ADB Controller V1.0.zip</a>
                                                    </h5>
                                                    <small class="text-muted">Size: 27.5 MB</small>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>IR</strong></td>
                                        <td>SMart IR DragonPlay menghadap ke TV</td>
                                        <td>
                                            Simulasi remote asli: on/off, volume, input.
                                            <br>Bisa berjalan hanya dengan HP/Tablet<br>
                                            <a href="#" class="modal-link" data-bs-toggle="modal" data-bs-target="#modalIR">
                                                <i class="bx bx-image"></i> Lihat Gambar
                                            </a>
                                        </td>
                                        <td>Belum Rilis.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Relay</strong></td>
                                        <td>Pemasangan fisik di jalur listrik</td>
                                        <td>
                                            Putuskan daya listrik secara langsung (hard kill).
                                            <br>Bisa berjalan hanya dengan HP/Tablet<br>
                                            <a href="#" class="modal-link" data-bs-toggle="modal" data-bs-target="#modalRelay">
                                                <i class="bx bx-image"></i> Lihat Gambar
                                            </a>
                                        </td>
                                        <td>
                                  
                                                    
                                            <a href="https://vt.tokopedia.com/t/ZSkcPRp3T/" target="_blank"
                                               class="badge bg-success bg-soft text-success font-size-11 d-inline-flex align-items-center gap-1">
                                                <i class="bx bx-cart"></i> X TiktokShop
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="text-muted">
                                <strong>Note:</strong> Semua model wajib menggunakan wifi untuk berinteraksi dengan server.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->

    </div> <!-- container-fluid -->
</div>

<!-- Modal Edit Logo - UPDATED WITH CROP INTEGRATION -->
<div class="modal fade" id="editLogoModal" tabindex="-1" aria-labelledby="editLogoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLogoLabel">
                    <i class="bx bx-camera text-primary"></i> Edit Logo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLogoForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Logo Saat Ini</label>
                                <div class="text-center">
                                    <img src="<?=$logox?>" alt="Current Logo" class="image-preview" id="currentLogoPreview">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="logoFile" class="form-label">Upload Logo Baru</label>
                                <div class="upload-area" id="uploadArea">
                                    <input type="file" class="d-none" id="logoFile" name="logo" accept="image/*">
                                    <div id="uploadContent">
                                        <i class="bx bx-cloud-upload font-size-48 text-muted"></i>
                                        <p class="text-muted mt-2 mb-1">Drag & drop file di sini atau klik untuk pilih</p>
                                        <p class="text-muted small">Format: JPG, PNG, GIF (Max: 2MB)</p>
                                        <p class="text-primary small"><strong>Logo akan di-crop otomatis menjadi persegi</strong></p>
                                    </div>
                                    <div id="previewContent" style="display: none;">
                                        <img src="" alt="Preview" class="image-preview" id="newLogoPreview">
                                        <p class="text-muted mt-2 mb-0" id="fileName"></p>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1" id="editCropBtn">
                                                <i class="bx bx-crop"></i> Edit Crop
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="removePreview">
                                                <i class="bx bx-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="invalid-feedback" id="logoError"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="bx bx-info-circle"></i> 
                            <strong>Tips:</strong> Logo akan di-crop menjadi persegi secara otomatis untuk hasil terbaik. 
                            Anda dapat menyesuaikan area crop sebelum menyimpan.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="saveLogoBtn" disabled>
                        <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Simpan Logo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========= MODAL CROP - SAMA SEPERTI MERCHANT_SETUP ========= -->
<div class="modal fade" id="cropModalGeneral" tabindex="-1" aria-labelledby="cropModalGeneralLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropModalGeneralLabel">
                    <i class="bx bx-crop"></i> Crop Logo Menjadi Persegi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="crop-container">
                            <img id="cropImageGeneral" style="max-width: 100%;">
                        </div>
                        <div class="mt-2 text-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="resetCenterGeneral">
                                <i class="bx bx-target-lock"></i> Reset Center
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h6>Preview:</h6>
                            <div class="crop-preview" id="cropPreviewGeneral"></div>
                            <small class="text-muted">
                                Logo akan tampil dalam bentuk persegi<br>
                                <strong>Drag untuk geser, resize untuk ubah ukuran</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="cropSaveGeneral">
                    <i class="bx bx-check"></i> Gunakan Crop Ini
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Merchant Name -->
<div class="modal fade" id="editMerchantModal" tabindex="-1" aria-labelledby="editMerchantLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMerchantLabel">
                    <i class="bx bx-edit text-primary"></i> Edit Merchant Name
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMerchantForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="merchantNameInput" class="form-label">Merchant Name</label>
                        <input type="text" class="form-control" id="merchantNameInput" value="<?=$merchand?>" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="alert alert-info">
                        <small><i class="bx bx-info-circle"></i> Nama merchant akan ditampilkan di laporan dan dashboard.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="saveMerchantBtn">
                        <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Address -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAddressLabel">
                    <i class="bx bx-map text-success"></i> Edit Address
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAddressForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addressInput" class="form-label">Address</label>
                        <textarea class="form-control" id="addressInput" rows="4" required><?=$address?></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="alert alert-info">
                        <small><i class="bx bx-info-circle"></i> Alamat lengkap merchant termasuk kota dan kode pos.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="saveAddressBtn">
                        <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ADB -->
<div class="modal fade" id="modalADB" tabindex="-1" aria-labelledby="modalADBLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalADBLabel">
                    <i class="bx bx-wifi text-primary"></i> Gambar Modul ADB
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center">
                <img src="./assets/images/modul/adb.jpg" class="img-fluid" alt="Gambar ADB" style="max-height: 500px;">
                <div class="mt-3">
                    <p class="text-muted">
                        <strong>ADB Control:</strong> Kontrol Android TV melalui jaringan WiFi menggunakan PC/Laptop
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal IR -->
<div class="modal fade" id="modalIR" tabindex="-1" aria-labelledby="modalIRLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalIRLabel">
                    <i class="bx bx-broadcast text-info"></i> Gambar Modul IR
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center">
                <img src="./assets/images/modul/ir.jpg" class="img-fluid" alt="Gambar IR" style="max-height: 500px;">
                <div class="mt-3">
                    <p class="text-muted">
                        <strong>IR Control:</strong> Kontrol TV menggunakan infrared seperti remote control biasa
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Relay -->
<div class="modal fade" id="modalRelay" tabindex="-1" aria-labelledby="modalRelayLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRelayLabel">
                    <i class="bx bx-power-off text-warning"></i> Gambar Modul Relay
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="./assets/images/modul/relay.jpg" class="img-fluid" alt="Gambar Relay" style="max-height: 500px;">
                <div class="mt-3">
                    <p class="text-muted">
                        <strong>Relay Control:</strong> Kontrol TV dengan memutus/menyambung daya listrik secara langsung
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========= TAMBAHAN CROPPER.JS SCRIPT ========= -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
// ========= VARIABEL CROP - SAMA SEPERTI MERCHANT_SETUP ========= 
let cropperGeneral = null;
let currentFileGeneral = null;
let cropModalGeneral = null;

// ========= FUNCTIONS CROP - COPY DARI MERCHANT_SETUP ========= 
function validateLogoGeneral(file) {
    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!validTypes.includes(file.type)) {
        return 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.';
    }
    
    if (file.size > maxSize) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        return `Ukuran file terlalu besar (${sizeMB}MB). Maksimal 2MB.`;
    }
    
    return null;
}

function showErrorGeneral(elementId, message) {
    const errorEl = document.getElementById(elementId);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }
}

function clearErrorGeneral(elementId) {
    const errorEl = document.getElementById(elementId);
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.style.display = 'none';
    }
}

function initCropperGeneral(imageSrc) {
    const cropImageGeneral = document.getElementById('cropImageGeneral');
    cropImageGeneral.src = imageSrc;
    
    if (cropperGeneral) {
        cropperGeneral.destroy();
    }
    
    cropperGeneral = new Cropper(cropImageGeneral, {
        aspectRatio: 1, // Square aspect ratio
        viewMode: 2,
        dragMode: 'move',
        autoCropArea: 0.7,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
        minCropBoxWidth: 100,
        minCropBoxHeight: 100,
        preview: '#cropPreviewGeneral',
        ready: function() {
            centerCropBoxGeneral();
        },
        built: function() {
            setTimeout(centerCropBoxGeneral, 100);
        }
    });
}

function centerCropBoxGeneral() {
    if (!cropperGeneral) return;
    
    const containerData = cropperGeneral.getContainerData();
    const imageData = cropperGeneral.getImageData();
    const canvasData = cropperGeneral.getCanvasData();
    
    const minSize = Math.min(canvasData.width, canvasData.height);
    const cropSize = minSize * 0.8;
    
    const centerX = canvasData.left + (canvasData.width / 2);
    const centerY = canvasData.top + (canvasData.height / 2);
    
    cropperGeneral.setCropBoxData({
        left: centerX - (cropSize / 2),
        top: centerY - (cropSize / 2),
        width: cropSize,
        height: cropSize
    });
}

function showCropModalGeneral(file) {
    currentFileGeneral = file;
    const reader = new FileReader();
    
    reader.onload = function(e) {
        cropModalGeneral.show();
        setTimeout(() => {
            initCropperGeneral(e.target.result);
        }, 300);
    };
    
    reader.readAsDataURL(file);
}

function canvasToBlobGeneral(canvas, callback, type = 'image/jpeg', quality = 0.9) {
    if (canvas.toBlob) {
        canvas.toBlob(callback, type, quality);
    } else {
        setTimeout(function() {
            const dataURL = canvas.toDataURL(type, quality);
            const binaryString = atob(dataURL.split(',')[1]);
            const bytes = new Uint8Array(binaryString.length);
            for (let i = 0; i < binaryString.length; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            const blob = new Blob([bytes], { type: type });
            callback(blob);
        }, 0);
    }
}

function saveCroppedImageGeneral() {
    if (!cropperGeneral) {
        showToast('Cropper tidak tersedia!', 'danger');
        return;
    }
    
    console.log('Starting crop save process...');
    
    const canvas = cropperGeneral.getCroppedCanvas({
        width: 300,
        height: 300,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
    });
    
    if (!canvas) {
        showToast('Gagal membuat canvas crop!', 'danger');
        return;
    }
    
    console.log('Canvas created successfully, size:', canvas.width, 'x', canvas.height);
    
    canvasToBlobGeneral(canvas, function(blob) {
        if (!blob) {
            showToast('Gagal convert canvas ke blob!', 'danger');
            return;
        }
        
        console.log('Blob created, size:', blob.size, 'bytes');
        
        const croppedImageUrl = URL.createObjectURL(blob);
        
        // Update preview
        document.getElementById('newLogoPreview').src = croppedImageUrl;
        document.getElementById('fileName').textContent = currentFileGeneral.name + ' (Cropped)';
        document.getElementById('uploadContent').style.display = 'none';
        document.getElementById('previewContent').style.display = 'block';
        
        // Create new File object from blob
        const croppedFileName = 'cropped_' + currentFileGeneral.name.replace(/\.[^/.]+$/, '') + '.jpg';
        const croppedFile = new File([blob], croppedFileName, {
            type: 'image/jpeg',
            lastModified: Date.now()
        });
        
        // Replace file input
        const logoFileInput = document.getElementById('logoFile');
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(croppedFile);
        logoFileInput.files = dataTransfer.files;
        
        console.log('File input replaced with cropped file:', croppedFileName);
        console.log('New file size:', croppedFile.size, 'bytes');
        
        // Enable save button
        document.getElementById('saveLogoBtn').disabled = false;
        
        // Close modal
        cropModalGeneral.hide();
        
        clearErrorGeneral('logoError');
        
        showToast('Logo berhasil di-crop dan siap disimpan!', 'success');
        
    }, 'image/jpeg', 0.9);
}

function resetLogoUploadGeneral() {
    document.getElementById('logoFile').value = '';
    document.getElementById('uploadContent').style.display = 'block';
    document.getElementById('previewContent').style.display = 'none';
    document.getElementById('saveLogoBtn').disabled = true;
    clearErrorGeneral('logoError');
    currentFileGeneral = null;
}

function processFileGeneral(file) {
    const error = validateLogoGeneral(file);
    if (error) {
        showErrorGeneral('logoError', error);
        return;
    }
    
    clearErrorGeneral('logoError');
    showCropModalGeneral(file);
}

// Toast notification function
function showToast(message, type = 'success') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Create toast container if not exists
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Add toast
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// File validation function - FIXED VERSION
function validateFile(file) {
    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const maxSize = 2 * 1024 * 1024; // 2MB
    
    // Debug: log informasi file
    console.log('File info:', {
        name: file?.name,
        type: file?.type,
        size: file?.size,
        sizeInMB: file?.size ? (file.size / (1024 * 1024)).toFixed(2) : 'undefined'
    });
    
    if (!file) {
        return 'File tidak ditemukan.';
    }
    
    if (typeof file.size !== 'number') {
        return 'Informasi ukuran file tidak valid.';
    }
    
    if (!validTypes.includes(file.type)) {
        return 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.';
    }
    
    if (file.size > maxSize) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        console.log(`File terlalu besar: ${file.size} bytes (max: ${maxSize} bytes)`);
        return `Ukuran file terlalu besar (${sizeMB}MB). Maksimal 2MB.`;
    }
    
    return null; // No error
}

// Function untuk menampilkan error di UI
function showFileError(message) {
    const errorElement = document.getElementById('logoError');
    const fileInput = document.getElementById('logoFile');
    const uploadArea = document.getElementById('uploadArea');
    
    if (errorElement && fileInput) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        fileInput.classList.add('is-invalid');
        if (uploadArea) {
            uploadArea.style.borderColor = '#dc3545';
        }
        
        // Disable save button
        const saveBtn = document.getElementById('saveLogoBtn');
        if (saveBtn) {
            saveBtn.disabled = true;
        }
    }
}

// Function untuk clear error
function clearFileError() {
    const errorElement = document.getElementById('logoError');
    const fileInput = document.getElementById('logoFile');
    const uploadArea = document.getElementById('uploadArea');
    
    if (errorElement && fileInput) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
        fileInput.classList.remove('is-invalid');
        if (uploadArea) {
            uploadArea.style.borderColor = '#ddd';
        }
    }
}

// Function untuk reset form
function resetUploadForm() {
    const logoFile = document.getElementById('logoFile');
    const uploadContent = document.getElementById('uploadContent');
    const previewContent = document.getElementById('previewContent');
    const saveBtn = document.getElementById('saveLogoBtn');
    
    if (logoFile) logoFile.value = '';
    if (uploadContent) uploadContent.style.display = 'block';
    if (previewContent) previewContent.style.display = 'none';
    if (saveBtn) saveBtn.disabled = true;
    clearFileError();
}

// Preview image function - CONSOLIDATED
function previewImage(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const uploadContent = document.getElementById('uploadContent');
            const previewContent = document.getElementById('previewContent');
            const newLogoPreview = document.getElementById('newLogoPreview');
            const fileName = document.getElementById('fileName');
            const saveBtn = document.getElementById('saveLogoBtn');
            
            if (newLogoPreview) newLogoPreview.src = e.target.result;
            if (fileName) fileName.textContent = file.name;
            if (uploadContent) uploadContent.style.display = 'none';
            if (previewContent) previewContent.style.display = 'block';
            if (saveBtn) saveBtn.disabled = false;
            
            // Clear any previous errors
            clearFileError();
            
        } catch (error) {
            console.error('Error in previewImage:', error);
            showFileError('Gagal menampilkan preview image');
        }
    };
    
    reader.onerror = function() {
        showFileError('Gagal membaca file. File mungkin rusak.');
    };
    
    reader.readAsDataURL(file);
}

// Edit Logo AJAX - UPDATED WITH CROP INTEGRATION
function initializeLogoFormSubmit() {
    const editLogoForm = document.getElementById('editLogoForm');
    if (!editLogoForm) return;
    
    editLogoForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const logoFile = document.getElementById('logoFile').files[0];
        const saveBtn = document.getElementById('saveLogoBtn');
        const spinner = saveBtn.querySelector('.loading-spinner');
        
        if (!logoFile) {
            showToast('Pilih file logo terlebih dahulu', 'warning');
            return;
        }
        
        // Final validation before submit
        const error = validateFile(logoFile);
        if (error) {
            showFileError(error);
            return;
        }
        
        // Show loading
        if (spinner) spinner.style.display = 'inline-block';
        saveBtn.disabled = true;
        
        // Create FormData
        const formData = new FormData();
        formData.append('action', 'update_logo');
        formData.append('logo', logoFile);
        
        // AJAX Request
        fetch('controller/update_merchant.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    // Update logo in UI
                    const newLogoUrl = data.logo_url + '?t=' + new Date().getTime(); // Prevent cache
                    const currentLogo = document.getElementById('currentLogo');
                    const currentLogoPreview = document.getElementById('currentLogoPreview');
                    
                    if (currentLogo) currentLogo.src = newLogoUrl;
                    if (currentLogoPreview) currentLogoPreview.src = newLogoUrl;
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editLogoModal'));
                    if (modal) modal.hide();
                    
                    // Reset form
                    resetUploadForm();
                    
                    // Show success toast
                    showToast('Logo berhasil diperbarui!', 'success');
                } else {
                    showToast(data.message || 'Gagal memperbarui logo', 'danger');
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response was:', text);
                showToast('Error: Server tidak mengembalikan JSON yang valid. Check console untuk detail.', 'danger');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showToast('Terjadi kesalahan: ' + error.message, 'danger');
        })
        .finally(() => {
            // Hide loading
            if (spinner) spinner.style.display = 'none';
            saveBtn.disabled = false;
        });
    });
}

// Edit Merchant Name AJAX
function initializeMerchantFormSubmit() {
    const editMerchantForm = document.getElementById('editMerchantForm');
    if (!editMerchantForm) return;
    
    editMerchantForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const merchantName = document.getElementById('merchantNameInput').value.trim();
        const saveBtn = document.getElementById('saveMerchantBtn');
        const spinner = saveBtn.querySelector('.loading-spinner');
        const input = document.getElementById('merchantNameInput');
        
        // Validation
        if (merchantName.length < 3) {
            input.classList.add('is-invalid');
            input.nextElementSibling.textContent = 'Nama merchant minimal 3 karakter';
            return;
        }
        
        // Clear validation
        input.classList.remove('is-invalid');
        
        // Show loading
        if (spinner) spinner.style.display = 'inline-block';
        saveBtn.disabled = true;
        
        // Create form data for better compatibility
        const formData = new FormData();
        formData.append('action', 'update_merchant_name');
        formData.append('merchant_name', merchantName);
        
        // AJAX Request with better error handling
        fetch('controller/update_merchant.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    // Update UI
                    const merchantNameElement = document.getElementById('merchantName');
                    if (merchantNameElement) merchantNameElement.textContent = merchantName;
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editMerchantModal'));
                    if (modal) modal.hide();
                    
                    // Show success toast
                    showToast('Nama merchant berhasil diperbarui!', 'success');
                } else {
                    showToast(data.message || 'Gagal memperbarui nama merchant', 'danger');
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response was:', text);
                showToast('Error: Server tidak mengembalikan JSON yang valid. Check console untuk detail.', 'danger');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showToast('Terjadi kesalahan: ' + error.message, 'danger');
        })
        .finally(() => {
            // Hide loading
            if (spinner) spinner.style.display = 'none';
            saveBtn.disabled = false;
        });
    });
}

// Edit Address AJAX
function initializeAddressFormSubmit() {
    const editAddressForm = document.getElementById('editAddressForm');
    if (!editAddressForm) return;
    
    editAddressForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const address = document.getElementById('addressInput').value.trim();
        const saveBtn = document.getElementById('saveAddressBtn');
        const spinner = saveBtn.querySelector('.loading-spinner');
        const input = document.getElementById('addressInput');
        
        // Validation
        if (address.length < 10) {
            input.classList.add('is-invalid');
            input.nextElementSibling.textContent = 'Alamat minimal 10 karakter';
            return;
        }
        
        // Clear validation
        input.classList.remove('is-invalid');
        
        // Show loading
        if (spinner) spinner.style.display = 'inline-block';
        saveBtn.disabled = true;
        
        // Create form data for better compatibility
        const formData = new FormData();
        formData.append('action', 'update_address');
        formData.append('address', address);
        
        // AJAX Request with better error handling
        fetch('controller/update_merchant.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    // Update UI
                    const merchantAddress = document.getElementById('merchantAddress');
                    if (merchantAddress) merchantAddress.textContent = address;
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editAddressModal'));
                    if (modal) modal.hide();
                    
                    // Show success toast
                    showToast('Alamat berhasil diperbarui!', 'success');
                } else {
                    showToast(data.message || 'Gagal memperbarui alamat', 'danger');
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response was:', text);
                showToast('Error: Server tidak mengembalikan JSON yang valid. Check console untuk detail.', 'danger');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showToast('Terjadi kesalahan: ' + error.message, 'danger');
        })
        .finally(() => {
            // Hide loading
            if (spinner) spinner.style.display = 'none';
            saveBtn.disabled = false;
        });
    });
}

// CONSOLIDATED FILE UPLOAD FUNCTIONALITY - UPDATED WITH CROP
function initializeFileUpload() {
    const uploadArea = document.getElementById('uploadArea');
    const logoFile = document.getElementById('logoFile');
    const removePreview = document.getElementById('removePreview');
    const editCropBtn = document.getElementById('editCropBtn');
    
    if (!uploadArea || !logoFile) {
        console.warn('Upload elements not found');
        return;
    }
    
    console.log('Initializing file upload with crop...');
    
    // ======= FILE INPUT CHANGE EVENT =======
    logoFile.addEventListener('change', function(e) {
        console.log('File input changed');
        const file = e.target.files[0];
        
        if (file) {
            console.log('File selected:', file.name, 'Size:', file.size);
            
            // VALIDATE FILE AND SHOW CROP MODAL
            const error = validateLogoGeneral(file);
            if (error) {
                console.log('Validation error:', error);
                showErrorGeneral('logoError', error);
                e.target.value = '';
                return;
            }
            
            // File is valid, show crop modal immediately
            console.log('File is valid, showing crop modal');
            processFileGeneral(file);
        }
    });
    
    // ======= UPLOAD AREA CLICK =======
    uploadArea.addEventListener('click', function(e) {
        if (!e.target.closest('#removePreview') && !e.target.closest('#editCropBtn')) {
            console.log('Upload area clicked, opening file dialog');
            clearErrorGeneral('logoError');
            logoFile.click();
        }
    });
    
    // ======= DRAG AND DROP EVENTS =======
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.style.borderColor = '#007bff';
        uploadArea.style.backgroundColor = '#f8f9ff';
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.style.borderColor = '#ddd';
        uploadArea.style.backgroundColor = '';
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.style.borderColor = '#ddd';
        uploadArea.style.backgroundColor = '';
        
        const files = e.dataTransfer.files;
        console.log('Files dropped:', files.length);
        
        if (files.length > 0) {
            const file = files[0];
            console.log('Processing dropped file:', file.name);
            
            // VALIDATE FILE AND SHOW CROP MODAL
            const error = validateLogoGeneral(file);
            if (error) {
                console.log('Validation error for dropped file:', error);
                showErrorGeneral('logoError', error);
                return;
            }
            
            // File is valid, update input and show crop modal
            const dt = new DataTransfer();
            dt.items.add(file);
            logoFile.files = dt.files;
            processFileGeneral(file);
        }
    });
    
    // ======= REMOVE PREVIEW =======
    if (removePreview) {
        removePreview.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Removing preview');
            resetLogoUploadGeneral();
        });
    }
    
    // ======= EDIT CROP BUTTON =======
    if (editCropBtn) {
        editCropBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (currentFileGeneral) {
                showCropModalGeneral(currentFileGeneral);
            }
        });
    }
    
    console.log('File upload with crop initialized successfully');
}

// Initialize crop functionality
function initializeCropFunctionality() {
    console.log('Initializing crop functionality...');
    
    // Initialize crop modal
    cropModalGeneral = new bootstrap.Modal(document.getElementById('cropModalGeneral'));
    
    // Save crop button
    const cropSaveGeneral = document.getElementById('cropSaveGeneral');
    if (cropSaveGeneral) {
        cropSaveGeneral.addEventListener('click', function() {
            saveCroppedImageGeneral();
        });
    }
    
    // Reset center crop button
    const resetCenterGeneral = document.getElementById('resetCenterGeneral');
    if (resetCenterGeneral) {
        resetCenterGeneral.addEventListener('click', function() {
            centerCropBoxGeneral();
            showToast('Crop area di-reset ke center!', 'info');
        });
    }
    
    // Clean up on modal hide
    document.getElementById('cropModalGeneral').addEventListener('hidden.bs.modal', function() {
        if (cropperGeneral) {
            cropperGeneral.destroy();
            cropperGeneral = null;
        }
    });
    
    console.log('Crop functionality initialized successfully');
}

// Bootstrap modal fallback
function initializeModalFallback() {
    // Check if Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap tidak dimuat! Pastikan Bootstrap JS sudah di-include');
        
        // Fallback manual modal function
        window.openModalManual = function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                
                // Add backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'modal-backdrop-' + modalId;
                document.body.appendChild(backdrop);
                
                // Close modal function
                modal.querySelector('.btn-close, [data-bs-dismiss="modal"]').onclick = function() {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    modal.setAttribute('aria-hidden', 'true');
                    const backDrop = document.getElementById('modal-backdrop-' + modalId);
                    if (backDrop) backDrop.remove();
                };
            }
        };
        
        // Add onclick to all modal triggers
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(trigger) {
            trigger.onclick = function(e) {
                e.preventDefault();
                const targetModal = this.getAttribute('data-bs-target').substring(1);
                openModalManual(targetModal);
            };
        });
    } else {
        console.log('Bootstrap loaded successfully');
    }
}

// Test image loading
function testImageLoading() {
    const images = ['./assets/images/modul/adb.jpg', './assets/images/modul/ir.jpg', './assets/images/modul/relay.jpg'];
    images.forEach(function(imgSrc) {
        const img = new Image();
        img.onload = function() {
            console.log('Image loaded: ' + imgSrc);
        };
        img.onerror = function() {
            console.error('Image NOT found: ' + imgSrc);
        };
        img.src = imgSrc;
    });
}
// Timezone Auto-Update Function
function initializeTimezoneUpdate() {
    const timezoneSelect = document.getElementById('timezone');
    if (!timezoneSelect) {
        console.warn('Timezone select element not found');
        return;
    }
    
    console.log('Initializing timezone auto-update...');
    
    timezoneSelect.addEventListener('change', function() {
        const selectedTimezone = this.value;
        const originalValue = this.getAttribute('data-original') || this.value;
        
        // Don't update if same value
        if (selectedTimezone === originalValue) {
            return;
        }
        
        console.log('Timezone changed to:', selectedTimezone);
        
        // Show loading state
        this.disabled = true;
        const originalHTML = this.style.background;
        this.style.background = '#f8f9fa url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 12 12\' width=\'12\' height=\'12\' fill=\'none\' stroke=\'%23007bff\'%3e%3ccircle cx=\'6\' cy=\'6\' r=\'4.5\'/%3e%3cpath d=\'m9.5 7.5-3-3-1.5 1.5\'/%3e%3c/svg%3e") no-repeat right 0.75rem center/16px 12px';
        
        // Create FormData
        const formData = new FormData();
        formData.append('action', 'update_timezone');
        formData.append('timezone', selectedTimezone);
        
        // AJAX Request
        fetch('controller/update_merchant.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                
              if (data.success) {
    // Update data-original attribute for future comparisons
    timezoneSelect.setAttribute('data-original', selectedTimezone);
    
    // Show success
    showToast('Timezone berhasil diperbarui!', 'success');
    
    // Optional: Update any time displays on the page
    updateTimeDisplays(selectedTimezone);
    
    // ⏳ Delay 1 detik sebelum reload
    setTimeout(() => {
        location.reload();
    }, 1000);

} else {
    // Revert to previous value on error
    timezoneSelect.value = originalValue;
    showToast(data.message || 'Gagal memperbarui timezone', 'danger');
}

            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response was:', text);
                
                // Revert to previous value
                timezoneSelect.value = originalValue;
                showToast('Error: Server tidak mengembalikan JSON yang valid', 'danger');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            
            // Revert to previous value
            timezoneSelect.value = originalValue;
            showToast('Terjadi kesalahan: ' + error.message, 'danger');
        })
        .finally(() => {
            // Restore normal state
            this.disabled = false;
            this.style.background = originalHTML;
        });
    });
    
    // Set initial data-original attribute
    timezoneSelect.setAttribute('data-original', timezoneSelect.value);
    
    console.log('Timezone auto-update initialized successfully');
}

// Optional: Function to update time displays across the page
function updateTimeDisplays(timezone) {
    try {
        // Update any elements that show current time
        const timeElements = document.querySelectorAll('[data-time-display]');
        timeElements.forEach(element => {
            const now = new Date();
            const options = {
                timeZone: timezone,
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            };
            element.textContent = now.toLocaleTimeString('en-US', options);
        });
        
        console.log('Time displays updated for timezone:', timezone);
    } catch (error) {
        console.error('Error updating time displays:', error);
    }
}

// Utility function to validate timezone
function isValidTimezone(timezone) {
    try {
        Intl.DateTimeFormat(undefined, { timeZone: timezone });
        return true;
    } catch (error) {
        return false;
    }
}

// Add to main initialization
document.addEventListener('DOMContentLoaded', function() {
    // Add timezone initialization to existing code
    setTimeout(function() {
        initializeTimezoneUpdate();
    }, 200);
});
// ===== MAIN INITIALIZATION - SINGLE DOMContentLoaded EVENT =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing all components...');
    
    // Initialize all components
    initializeFileUpload();
    initializeCropFunctionality(); // NEW: Initialize crop functionality
    initializeLogoFormSubmit();
    initializeMerchantFormSubmit();
    initializeAddressFormSubmit();
    initializeModalFallback();
    testImageLoading();
    
    console.log('All components initialized successfully');
    console.log('File upload with crop validation script loaded.');
});
</script>
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

  <!-- JAVASCRIPT -->
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenu/metisMenu.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/node-waves/waves.min.js"></script>

        <!-- apexcharts -->
        <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

        <script src="assets/js/pages/project-overview.init.js"></script>

        <script src="assets/js/app.js"></script>