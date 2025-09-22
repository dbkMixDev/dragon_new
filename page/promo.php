<div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                              <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Promo List</h4>

                    <button type="button" class="btn btn-sm btn-primary btn-add-units" data-bs-toggle="modal" data-bs-target="#modalAddPromo">
                        + Add
                    </button>

                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        
<!-- Log Perubahan Global -->
 <div class="row">
                             
 <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="card">
                                            <div class="card-body">

   
</h4>
<div class="table-responsive">
    <?php
    // session_start();
    $username = $_SESSION['username'];
$query = "SELECT * FROM tb_promo WHERE userx= '$username' ORDER BY id DESC";
$result = mysqli_query($con, $query);
?>
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>Promo Name</th>
      <th>Type</th>
      <th>Discount</th>
      <th>Status</th>
      <th>User</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['nama_promo'] ?></td>
        <td><?= $row['type_rental'] ?></td>
        <td>
  <?php 
    if ($row['disc_type'] === 'perc') {
        echo $row['qty_potongan'] . '%';
    }elseif ($row['disc_type'] === 'hours') {
        echo $row['qty_potongan'] . ' Hours';
    } else {
        echo 'Rp ' . number_format($row['qty_potongan'], 0, ',', '.');
    }
  ?>
</td>

        <td><?= $row['status'] == 1 ? 'Aktif' : 'Nonaktif' ?></td>
        <td><?= $row['userx'] ?></td>
        <td>
          <button class="btn btn-warning btn-sm btn-edit" data-id="<?= $row['id'] ?>">Edit</button>

          <button onclick="hapusPromo(<?= $row['id'] ?>)" class="btn btn-danger btn-sm">Hapus</button>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
                                        </div>
                                        
<!-- Modal Edit -->
<div class="modal fade" id="modalEditPromo" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="modalEditContent">
      <!-- Konten akan di-load via AJAX -->
    </div>
  </div>
</div>


<!-- Modal Tambah Promo -->
<div class="modal fade" id="modalAddPromo" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formAddPromo">
        <div class="modal-header">
          <h5 class="modal-title">New Promo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Promo Name</label>
            <input type="text" name="nama_promo" class="form-control" required>
          </div>
         <?php

// Query kategori yang aktif dan sesuai user
$query = "SELECT * 
FROM tb_category 
WHERE NOT (userx = '$username' AND status = 'disable') 
GROUP BY name 
ORDER BY name ASC;
";
$result = mysqli_query($con, $query);
?>

<div class="mb-2">
  <label for="type_rental">Type Rental</label>
  <select name="type_rental" class="form-control" required>
    <option value="">-- Select Type --</option>
      <option value="Rental">~All Type Rent~</option>
      <option value="Product">~All Type Product~</option>
    <?php while($row = mysqli_fetch_assoc($result)): ?>
      <option value="<?= htmlspecialchars($row['name']) ?>">
        <?= htmlspecialchars($row['name']) ?>
      </option>
    <?php endwhile; ?>
  </select>
</div>

          <div class="mb-2">
  <label>Discount</label>
  <div class="input-group">
    <input type="number" name="qty_potongan" class="form-control" required>
    <select name="disc_type" class="form-select" style="max-width: 100px;" required>
      <option value="nominal">Rp</option>
      <option value="perc">%</option>
      <option value="hours">Hours</option>
    </select>
  </div>
</div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
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
<script>
$(document).on('click', '.btn-edit', function() {
    var id = $(this).data('id');
    $.ajax({
        url: 'controller/ajax_edit_promo.php',
        type: 'POST',
        data: { id: id },
        success: function(response) {
            $('#modalEditContent').html(response);
            $('#modalEditPromo').modal('show');
        }
    });
});

// Submit form edit
$(document).on('submit', '#formEditPromo', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'controller/ajax_edit_promox.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Promo telah diperbarui',
                timer: 1500,
                showConfirmButton: false
            });
            $('#modalEditPromo').modal('hide');
            setTimeout(() => location.reload(), 1000);
        }
    });
});

// // Hapus
// function hapusPromo(id) {
//     Swal.fire({
//         title: 'Yakin menghapus promo?',
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonText: 'Ya, hapus',
//         cancelButtonText: 'Batal'
//     }).then((result) => {
//         if (result.isConfirmed) {
//             $.post('controller/hapus_promo.php', { id: id }, function(res) {
//                 Swal.fire({
//                     icon: 'success',
//                     title: 'Berhasil',
//                     text: 'Promo dihapus',
//                     timer: 1000,
//                     showConfirmButton: false
//                 });
//                 setTimeout(() => location.reload(), 1000);
//             });
//         }
//     });
// }
// Tambah Promo via AJAX
$(document).on('submit', '#formAddPromo', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'controller/ajax_add_promo.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            $('#modalAddPromo').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Promo berhasil ditambahkan.',
                timer: 1200,
                showConfirmButton: false
            });
            setTimeout(() => location.reload(), 1000);
        }
    });
});

// Delete Promo via SweetAlert
function hapusPromo(id) {
    Swal.fire({
        title: 'Yakin ingin menghapus promo ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('controller/ajax_del_promo.php', { id: id }, function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Promo dihapus.',
                    timer: 1000,
                    showConfirmButton: false
                });
                setTimeout(() => location.reload(), 1000);
            });
        }
    });
}

</script>



        <script src="assets/js/app.js"></script>
<style>
    .editable-input input {
    max-width: 100px !important;  /* Batasi lebar maksimal */
    width: 100% !important;       /* Lebar mengikuti container tapi maksimal 150px */
    box-sizing: border-box;       /* Pastikan padding dan border masuk ke lebar */
}

</style>