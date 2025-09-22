<div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Category</h4>

                                  

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        
<!-- Log Perubahan Global -->
 <div class="row">
                             
 <div class="col-12 col-sm-4 col-md-4 col-lg-4">
                                <div class="card">
                                            <div class="card-body">

      <h4 class="card-title d-flex justify-content-between align-items-center">
    Category List
   <!-- <button type="button" class="btn btn-sm btn-primary btn-add-category" data-category="<?php echo htmlspecialchars($cat['id_category']); ?>">
    + Add
</button> -->

</h4>
<div class="table-responsive">
    <?php
    // Pastikan session sudah dimulai dan variabel $username sudah ada
    if (!isset($username)) {
        session_start();
        $username = $_SESSION['username'] ?? '';
    }

    // Ambil semua kategori userx='ALL' (global)
    $sql_all = "SELECT id_category, name, status, userx FROM tb_category WHERE userx='ALL' ORDER BY name ASC";
    $result_all = $con->query($sql_all);

    // Ambil semua kategori milik user (override)
    $sql_user = "SELECT name, status FROM tb_category WHERE userx='$username'";
    $result_user = $con->query($sql_user);

    // Buat array override: [name => status]
    $user_override = [];
    if ($result_user && $result_user->num_rows > 0) {
        while ($row = $result_user->fetch_assoc()) {
            $user_override[strtolower($row['name'])] = strtolower($row['status']);
        }
    }

    if ($result_all && $result_all->num_rows > 0) {
        echo '<table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
        while ($cat = $result_all->fetch_assoc()) {
            $cat_name = strtolower($cat['name']);
            $isDisabled = (isset($user_override[$cat_name]) && $user_override[$cat_name] === 'disable');
            echo '<tr id="category-row-' . $cat['id_category'] . '">
                    <td>' . htmlspecialchars($cat['name']) . '</td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input category-switch" 
                                type="checkbox" 
                                data-category="' . htmlspecialchars($cat['name']) . '" 
                                id="switch-category-' . $cat['id_category'] . '" 
                                ' . ($isDisabled ? '' : 'checked') . '>
                            <label class="form-check-label" for="switch-category-' . $cat['id_category'] . '"></label>
                        </div>
                    </td>
                </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="text-muted">No category yet.</div>';
    }
    ?>
</div>

</div>
</div>

                    </div> <!-- container-fluid -->
        

                </div>


        <!-- JAVASCRIPT -->
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

       <!-- FRONTEND: Tambahkan script ini di bagian bawah halaman pricelist -->
<script>

    

$(document).ready(function() {
    // Pastikan event handler hanya di-bind sekali dan benar
    $(document).off('change', '.category-switch'); // Unbind dulu untuk mencegah double binding

    function handleCategorySwitchChange() {
        var $switch = $(this);
        var $row = $switch.closest('tr');
        var category = $switch.data('category');
        var checked = $switch.is(':checked');

        $switch.prop('disabled', true);

        var url = checked ? 'controller/ajax_enable_category.php' : 'controller/ajax_disable_category.php';
        var revertState = !checked;

        // Debug: cek trigger event
        console.log('Switch changed:', category, checked, url);

        $.ajax({
            url: url,
            type: 'POST',
            data: { name: category },
            dataType: 'json',
            success: function(res) {
                // Debug: tampilkan hasil response
                console.log('AJAX response:', res);
                if (res.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Status kategori berhasil diubah.',
                            timer: 1000,
                            showConfirmButton: false
                        });
                         setTimeout(function() {
                        location.reload();  // Reload page biar data update
                    }, 500);
                    }
                } else {
                    $switch.prop('checked', revertState);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: res.error || 'Gagal mengubah status kategori.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.error || 'Gagal mengubah status kategori.');
                    }
                }
            },
            error: function(xhr, status, error) {
                $switch.prop('checked', revertState);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan koneksi.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    alert('Terjadi kesalahan koneksi.');
                }
            },
            complete: function() {
                $switch.prop('disabled', false);
            }
        });
    }

    // Gunakan event delegation agar event tetap aktif untuk elemen dinamis
    $(document).on('change', '.category-switch', handleCategorySwitchChange);
});


</script>


        <script src="assets/js/app.js"></script>
<style>
    .editable-input input {
    max-width: 100px !important;  /* Batasi lebar maksimal */
    width: 100% !important;       /* Lebar mengikuti container tapi maksimal 150px */
    box-sizing: border-box;       /* Pastikan padding dan border masuk ke lebar */
}

</style>