<?php
// Pastikan sudah koneksi $con dan $username sudah didefinisikan di file ini
 // Ambil timezone user jika ada
$ruser = $con->query("SELECT timezone FROM userx WHERE username = '$username' LIMIT 1");
if ($ruser && $ruser->num_rows > 0) {
    $tz = $ruser->fetch_assoc();
    $timezone = $tz['timezone'] ?: $timezone;
}
date_default_timezone_set($timezone);
// Ambil kategori aktif untuk user tertentu
$cat_sql = "
  SELECT name, type FROM tb_category
  WHERE name NOT IN (
    SELECT name FROM tb_category WHERE userx = '$username' AND status = 'disable'
  )
  ORDER BY name ASC
";
$cat_res = $con->query($cat_sql);
$categories = [];
if ($cat_res && $cat_res->num_rows > 0) {
    while ($cat = $cat_res->fetch_assoc()) {
        $categories[] = [
            'name' => $cat['name'],
            'type' => strtolower($cat['type'] ?? '')
        ];
    }
}
?>

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Unit Rentals</h4>

                    <button type="button" class="btn btn-sm btn-primary btn-add-units">
                        + Add
                    </button>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive">
                            <?php
                            // Ambil semua unit rental untuk user tertentu
                            $log_sql = "
                                SELECT * FROM playstations WHERE userx='$username' AND type_ps != '' ORDER BY no_ps ASC
                            ";

                            $log_result = $con->query($log_sql);

                            if ($log_result && $log_result->num_rows > 0) {
                                echo '<table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>No Unit</th>
                                                <th>Type</th>
                                                <th>Category</th>
                                                <th>IP / ID Device</th>
                                                <th>Model</th>
                                                <th>Status Device</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                                while ($log = $log_result->fetch_assoc()) {
                                 echo '<tr>
    <td>' . htmlspecialchars($log['no_ps']) . '</td>
    <td>' . htmlspecialchars($log['type_rental']) . '</td>
    <td>' . htmlspecialchars($log['type_ps']) . '</td>
    <td>' . htmlspecialchars($log['id_usb']) . '</td>
    <td>' . htmlspecialchars($log['type_modul']) . '</td>';

$statusTime = strtotime($log['status_device']);
$iprelay = @(($log['type_modul'] == "SMART TV" || $log['type_modul'] == "BILLIARD") ? '('.$log['ip_relay'].')' : '');
$now = time();
$diffMinutes = round(($now - $statusTime) / 60);

if (!function_exists('formatTimeAgo')) {
    function formatTimeAgo($minutes) {
        if ($minutes < 60) {
            return $minutes . ' minutes ago';
        } else {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' 
                . ($remainingMinutes > 0 ? $remainingMinutes . ' minutes ' : '') . 'ago';
        }
    }
}

if ($diffMinutes > 5) {
    echo '<td>
        <span class="badge bg-danger">Disconnected</span><br>';
if ($statusTime !='') {
    echo '<small class="text-muted">' . formatTimeAgo($diffMinutes) . '</small>';
}else{
     echo '<small class="text-muted">Not Regist</small>';
}
echo '</td>';

} else {
    echo '<td>
            <span class="badge bg-success">Connected '.$iprelay.'</span><br>
            <small class="text-muted">' . formatTimeAgo($diffMinutes) . '</small>
          </td>';
}


echo '<td>
        <button class="btn btn-edit-unit p-0 px-2" 
                data-id="' . $log['no_ps'] . '" 
                data-type="' . htmlspecialchars($log['type_rental']) . '"
                data-category="' . htmlspecialchars($log['type_ps']) . '"
                data-model="' . htmlspecialchars($log['type_modul']) . '"
                data-device="' . htmlspecialchars($log['id_usb']) . '"
                title="Edit unit" style="font-size: 0.75rem; line-height: 1;">
            <i class="mdi mdi-pencil text-warning"></i>
        </button>
        <button class="btn btn-delete-unit p-0 px-2" data-id="' . $log['no_ps'] . '" title="Delete unit" style="font-size: 0.75rem; line-height: 1;">
            <i class="mdi mdi-delete text-danger"></i>
        </button>
    </td>
</tr>';

                                }
                                echo '</tbody></table>';
                            } else {
                                echo '<div class="text-muted">Tidak ada unit rental ditemukan.</div>';
                            }
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div> <!-- container-fluid -->
</div>

<style>
    .editable-input input {
        max-width: 100px !important;
        width: 100% !important;
        box-sizing: border-box;
    }
</style>

<!-- Modal Add Unit -->
<div class="modal fade" id="modalAddUnit" tabindex="-1" aria-labelledby="modalAddUnitLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formAddUnit">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAddUnitLabel">Add New Rental Units</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label for="no_ps" class="form-label">No Unit</label>
                        <input type="number" class="form-control" id="no_ps" name="no_ps" required>
                    </div>
                    <div class="mb-2">
                        <label for="type_rental" class="form-label">Type</label>
                        <select class="form-control" id="type_rental" name="type_rental" required>
                            <option value="" disabled selected>Select Type</option>
                            <option value="Playstation">Playstation</option>
                            <option value="Playbox">Playbox</option>
                            <option value="Billiard">Billiard</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="type_ps" class="form-label">Category</label>
                        <select class="form-control" id="type_ps" name="type_ps" required disabled>
                            <option value="" disabled selected>Select Kategori</option>
                            <!-- options akan diisi via JS -->
                        </select>
                    </div>
                     <div class="mb-2">
                        <label for="type_modul" class="form-label">Model</label>
                        <select class="form-control" id="type_modul" name="type_modul" required>
                            <option value="" disabled selected>Select Model</option>
                            <option value="ANDROID TV">ANDROID TV</option>
                            <option value="GOOGLE TV">GOOGLE TV</option>
                            <option value="SMART TV">SMART TV</option>
                            <option value="BILLIARD">BILLIARD</option>
                            <option value="PLAYBOX">PLAYBOX</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="id_usb" class="form-label">IP / ID Device</label>
                        <input type="text" class="form-control" id="id_usb" name="id_usb" required value="">
                    </div>
                   
                   
                    <div id="addUnitMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Unit -->
<div class="modal fade" id="modalEditUnit" tabindex="-1" aria-labelledby="modalEditUnitLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditUnit">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditUnitLabel">Edit Rental Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_no_ps_old" name="no_ps_old">
                    <div class="mb-2">
                        <label for="edit_no_ps" class="form-label">No Unit</label>
                        <input type="number" class="form-control" id="edit_no_ps" name="no_ps" required>
                    </div>
                    <div class="mb-2">
                        <label for="edit_type_rental" class="form-label">Type</label>
                        <select class="form-control" id="edit_type_rental" name="type_rental" required>
                            <option value="" disabled>Select Type</option>
                            <option value="Playstation">Playstation</option>
                            <option value="Playbox">Playbox</option>
                            <option value="Billiard">Billiard</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="edit_type_ps" class="form-label">Category</label>
                        <select class="form-control" id="edit_type_ps" name="type_ps" required>
                            <option value="" disabled>Select Kategori</option>
                            <!-- options akan diisi via JS -->
                        </select>
                    </div>
                     <div class="mb-2">
                        <label for="edit_type_modul" class="form-label">Model</label>
                        <select class="form-control" id="edit_type_modul" name="type_modul" required>
                            <option value="" disabled>Select Model</option>
                            <option value="ANDROID TV">ANDROID TV</option>
                            <option value="GOOGLE TV">GOOGLE TV</option>
                            <option value="SMART TV">SMART TV</option>
                            <option value="BILLIARD">BILLIARD</option>
                            <option value="PLAYBOX">PLAYBOX</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="edit_id_usb" class="form-label">IP / ID Device</label>
                        <input type="text" class="form-control" id="edit_id_usb" name="id_usb" required>
                    </div>
                   
                   
                    <div id="editUnitMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div class="modal fade" id="modalDeleteUnit" tabindex="-1" aria-labelledby="modalDeleteUnitLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDeleteUnitLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus unit <strong id="deleteUnitNo"></strong>?</p>
                <input type="hidden" id="deleteUnitId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-confirm-delete">Hapus</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- Kirim data kategori ke JS -->
<script>
    var categories = <?php echo json_encode($categories); ?>;
</script>

<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>

<!-- Plugins js -->
<script src="assets/libs/bootstrap-editable/js/index.js"></script>
<script src="assets/libs/moment/min/moment.min.js"></script>

<!-- Init js-->
<script src="assets/js/pages/form-xeditable.init.js"></script>

<script src="assets/js/app.js"></script>

<script>
$(document).ready(function() {
    // Show modal on button click
    $('.btn-add-units').on('click', function() {
        $('#formAddUnit')[0].reset();
        $('#addUnitMessage').html('');
        $('#type_ps').prop('disabled', true).html('<option value="" disabled selected>Pilih Kategori</option>');
        var modal = new bootstrap.Modal(document.getElementById('modalAddUnit'));
        modal.show();
    });

    // Saat Type berubah, filter category yang sesuai
    $('#type_rental').on('change', function() {
        var selectedType = $(this).val().toLowerCase();
        var filtered = categories.filter(function(cat) {
            return cat.type === selectedType;
        });

        var options = '<option value="" disabled selected>Pilih Kategori</option>';
        filtered.forEach(function(cat) {
            options += '<option value="' + cat.name + '">' + cat.name + '</option>';
        });

        $('#type_ps').html(options).prop('disabled', filtered.length === 0);
    });

    // Mapping Type ke Model
    const modelOptions = {
        'Playstation': ['ANDROID TV', 'GOOGLE TV', 'SMART TV'],
        'Playbox': ['PLAYBOX'],
        'Billiard': ['BILLIARD']
    };

    // Di awal: disable category & model
    $('#type_ps').prop('disabled', true);
    $('#type_modul').prop('disabled', true);

    // Saat type dipilih
    $('#type_rental').on('change', function () {
        const selectedType = $(this).val();

        // Reset category dan model
        $('#type_ps').val('').prop('disabled', !selectedType);
        $('#type_modul').empty().prop('disabled', true);

        // Isi model jika ada type yang valid
        if (modelOptions[selectedType]) {
            $('#type_modul').append('<option value="" selected disabled>Select Model</option>');
            modelOptions[selectedType].forEach(model => {
                $('#type_modul').append(`<option value="${model}">${model}</option>`);
            });
            $('#type_modul').prop('disabled', false);
        }
    });

    // AJAX submit form tambah unit
    $('#formAddUnit').on('submit', function(e) {
        e.preventDefault();
        $('#addUnitMessage').html('');
        var formData = $(this).serialize();
        $.ajax({
            url: 'controller/ajax_add_unit.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#formAddUnit button[type=submit]').prop('disabled', true).text('Menyimpan...');
            },
            success: function(res) {
                if (res.success) {
                    $('#addUnitMessage').html('<div class="alert alert-success">Unit berhasil ditambahkan!</div>');
                    setTimeout(function() {
                        $('#modalAddUnit').modal('hide');
                        location.reload(); // Auto refresh tabel
                    }, 700);
                } else {
                    $('#addUnitMessage').html('<div class="alert alert-danger">' + (res.error || 'Gagal menambah unit') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#addUnitMessage').html('<div class="alert alert-danger">Error: ' + error + '</div>');
            },
            complete: function() {
                $('#formAddUnit button[type=submit]').prop('disabled', false).text('Simpan');
            }
        });
    });

    // Disable category select by default
    $('#type_ps').prop('disabled', true);

    // ===== FUNGSI EDIT =====
    // Saat tombol edit diklik
    $(document).on('click', '.btn-edit-unit', function() {
        var unitId = $(this).data('id');
        var type = $(this).data('type');
        var category = $(this).data('category');
        var model = $(this).data('model');
        var device = $(this).data('device');

        // Reset form dan message
        $('#formEditUnit')[0].reset();
        $('#editUnitMessage').html('');

        // Isi data ke form
        $('#edit_no_ps_old').val(unitId);
        $('#edit_no_ps').val(unitId);
        $('#edit_type_rental').val(type);
        $('#edit_id_usb').val(device);

        // Load category berdasarkan type
        var selectedType = type.toLowerCase();
        var filtered = categories.filter(function(cat) {
            return cat.type === selectedType;
        });

        var options = '<option value="" disabled>Pilih Kategori</option>';
        filtered.forEach(function(cat) {
            options += '<option value="' + cat.name + '">' + cat.name + '</option>';
        });
        $('#edit_type_ps').html(options).val(category);

        // Load model berdasarkan type
        $('#edit_type_modul').empty();
        if (modelOptions[type]) {
            $('#edit_type_modul').append('<option value="" disabled>Select Model</option>');
            modelOptions[type].forEach(function(m) {
                $('#edit_type_modul').append('<option value="' + m + '">' + m + '</option>');
            });
        }
        $('#edit_type_modul').val(model);

        // Show modal
        var modal = new bootstrap.Modal(document.getElementById('modalEditUnit'));
        modal.show();
    });

    // Saat Type berubah di form edit
    $('#edit_type_rental').on('change', function() {
        var selectedType = $(this).val();
        
        // Update category options
        var selectedTypeLower = selectedType.toLowerCase();
        var filtered = categories.filter(function(cat) {
            return cat.type === selectedTypeLower;
        });

        var options = '<option value="" disabled selected>Pilih Kategori</option>';
        filtered.forEach(function(cat) {
            options += '<option value="' + cat.name + '">' + cat.name + '</option>';
        });
        $('#edit_type_ps').html(options).prop('disabled', filtered.length === 0);

        // Update model options
        $('#edit_type_modul').empty().prop('disabled', true);
        if (modelOptions[selectedType]) {
            $('#edit_type_modul').append('<option value="" selected disabled>Select Model</option>');
            modelOptions[selectedType].forEach(model => {
                $('#edit_type_modul').append(`<option value="${model}">${model}</option>`);
            });
            $('#edit_type_modul').prop('disabled', false);
        }
    });

    // AJAX submit form edit unit
    $('#formEditUnit').on('submit', function(e) {
        e.preventDefault();
        $('#editUnitMessage').html('');
        var formData = $(this).serialize();
        $.ajax({
            url: 'controller/ajax_edit_unit.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#formEditUnit button[type=submit]').prop('disabled', true).text('Updating...');
            },
            success: function(res) {
                if (res.success) {
                    $('#editUnitMessage').html('<div class="alert alert-success">Unit berhasil diupdate!</div>');
                    setTimeout(function() {
                        $('#modalEditUnit').modal('hide');
                        location.reload();
                    }, 700);
                } else {
                    $('#editUnitMessage').html('<div class="alert alert-danger">' + (res.error || 'Gagal update unit') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#editUnitMessage').html('<div class="alert alert-danger">Error: ' + error + '</div>');
            },
            complete: function() {
                $('#formEditUnit button[type=submit]').prop('disabled', false).text('Update');
            }
        });
    });

    // ===== FUNGSI DELETE =====
    // Saat tombol delete diklik
    $(document).on('click', '.btn-delete-unit', function() {
        var unitId = $(this).data('id');
        $('#deleteUnitId').val(unitId);
        $('#deleteUnitNo').text(unitId);
        
        var modal = new bootstrap.Modal(document.getElementById('modalDeleteUnit'));
        modal.show();
    });

    // Konfirmasi delete
    $('.btn-confirm-delete').on('click', function() {
        var unitId = $('#deleteUnitId').val();
        
        $.ajax({
            url: 'controller/ajax_delete_unit.php',
            type: 'POST',
            data: { no_ps: unitId },
            dataType: 'json',
            beforeSend: function() {
                $('.btn-confirm-delete').prop('disabled', true).text('Menghapus...');
            },
            success: function(res) {
                if (res.success) {
                    $('#modalDeleteUnit').modal('hide');
                    location.reload();
                } else {
                    alert(res.error || 'Gagal menghapus unit');
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            },
            complete: function() {
                $('.btn-confirm-delete').prop('disabled', false).text('Hapus');
            }
        });
    });
});
</script>