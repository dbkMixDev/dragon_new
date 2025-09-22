<div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Product List</h4>

                                  

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <?php
                            // Ambil semua kategori dari tb_category
                             $users =  $_SESSION['username'];
                            // Ambil kategori global (userx='ALL') yang aktif, tapi sembunyikan jika ada override userx='$users' dan status='disable'
                             $categories = ['FnB', 'Others'];
if($categories){
            foreach ($categories as $cat) {
                                    ?>
                                   <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                        <div class="card">
                                            <div class="card-body">

                                               <h4 class="card-title d-flex justify-content-between align-items-center">
    <?php echo htmlspecialchars($cat); ?>
   <button type="button" class="btn btn-sm btn-primary btn-add-pricelist" data-category="<?php echo htmlspecialchars($cat); ?>">
    + Add
</button>

</h4>

                                                
                                                <div class="table-responsive">
                                                    <?php
                                                    // Ambil data pricelist untuk kategori ini
                                                    $sql = "SELECT * FROM tb_fnb WHERE type_fnb = '" . $con->real_escape_string($cat) . "' AND userx='$username' ORDER BY type_fnb ASC";
                                                    $result = $con->query($sql);
                                                    ?>

                                                  <table class="table table-striped table-nowrap mb-0">
  <thead>
    <tr>
      <th>Item</th>
      <th>Price</th>
    
    </tr>
  </thead>
  <tbody>
  <?php

  if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          echo '<tr id="pricelist-row-' . $row['id'] . '">
              <td>
                <button class="btn btn-delete-pricelist p-1 px-2" data-id="' . $row['id'] . '" title="Delete product" style="font-size: 0.75rem; line-height: 1;">
                  <i class="bx bx-minus-circle text-danger"></i>
                </button>
                ' . htmlspecialchars($row['nama']) . ' 
              </td>
              <td>
                <a href="javascript:void(0);" 
                  id="inline-username-' . $row['id'] . '" 
                  data-type="number" 
                  data-pk="' . $row['id'] . '" 
                  data-value="' . $row['harga'] . '"
                  data-title="Enter price"
                  class="editable-price">Rp. ' . number_format($row['harga'], 0, ',', '.') . '</a>
              </td>
             
          </tr>';
      }
  } else {
      echo '<tr><td colspan="3">No data found.</td></tr>';
  }
  ?>
  </tbody>
</table>

                                                </div>

                                            </div>
                                        </div>
                                    </div> <!-- end col -->
                                    <?php
                                }
                            } else {
                                echo '<div class="col-12"><div class="alert alert-warning">No categories found.</div></div>';
                            }
                            ?>
                            
                         
                        </div> <!-- end row -->
                        <div class="modal fade" id="modalAddPricelist" tabindex="-1" aria-labelledby="modalAddPricelistLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAddPricelist">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddPricelistLabel">Add Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="type_ps" id="modalCategory" value="">
          <div class="mb-3">
            <label for="duration" class="form-label">Item Name</label>
            <input type="text" class="form-control" id="duration" name="duration" min="1" required>
          </div>
          <div class="mb-3">
            <label for="price" class="form-label">Price (Rp)</label>
            <input type="number" class="form-control" id="price" name="price" min="0" required>
          </div>
          <div id="addPricelistMessage"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Pricelist</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

        

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
    
      var modal = new bootstrap.Modal(document.getElementById('modalAddPricelist'));
    //   var modal_cat = new bootstrap.Modal(document.getElementById('modalAddCategory'));
    // Initialize x-editable untuk semua price links
    $("a[id^='inline-username-']").editable({
        type: 'text',
        title: 'Enter price',
        mode: 'inline',
        validate: function(value) {
            // Validasi input harus angka
            if (!value || isNaN(value) || parseInt(value) < 0) {
                return 'Harga harus berupa angka positif!';
            }
        },
        display: function(value, sourceData) {
            // Format display dengan rupiah
            if (value) {
                $(this).html('Rp. ' + parseInt(value).toLocaleString('id-ID'));
            }
        },
        success: function(response, newValue) {
            var $this = $(this);
            var pricelistId = $this.data('pk');
            
            // Kirim AJAX untuk save ke database
            $.ajax({
                url: 'controller/ajax_product.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: pricelistId,
                    harga: newValue,
                    userx: '<?php echo $_SESSION['username'] ?? ''; ?>' // Kirim userx dari session
                },
                beforeSend: function() {
                    // Loading indicator
                    $this.addClass('text-muted').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                },
                success: function(result) {
                    if (result.success) {
                        // Update tampilan dengan format rupiah
                        $this.removeClass('text-muted').html('Rp. ' + parseInt(newValue).toLocaleString('id-ID'));
                        
                        // Show success notification
                        showNotification('success', 'Harga berhasil diupdate!');
                        
                        // Optional: Log activity
                        console.log('Product updated:', {
                            id: pricelistId,
                            old_price: result.old_price,
                            new_price: newValue,
                            session_username: result.session_username,
                            userx: result.userx,
                            updated_at: result.updated_at
                        });
                         setTimeout(() => {
        location.reload();
    }, 1000);
                    } else {
                        // Revert jika gagal
                        $this.removeClass('text-muted').html('Rp. ' + parseInt(result.old_price || 0).toLocaleString('id-ID'));
                        showNotification('error', result.error || 'Gagal menyimpan harga!');
                    }
                },
                error: function(xhr, status, error) {
                    // Revert tampilan dan show error
                    $this.removeClass('text-muted').html('Rp. 0');
                    showNotification('error', 'Koneksi error: ' + error);
                    console.error('Ajax Error:', {xhr: xhr, status: status, error: error});
                }
            });
        },
        error: function(errors) {
            showNotification('error', 'Input tidak valid: ' + errors);
        }
    });
    
    // Function untuk show notification
    function showNotification(type, message) {
        // Gunakan library notification yang ada (SweetAlert, Toastr, dll)
        // Atau buat simple alert
        if (typeof Swal !== 'undefined') {
            // Jika pakai SweetAlert
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: type === 'success' ? 'Berhasil!' : 'Error!',
                text: message,
                timer: 1000,
                showConfirmButton: false
            });
        } else {
            // Fallback ke alert biasa
            alert(message);
        }
    }
    
    // Tambahan: Real-time validation saat typing
    $(document).on('shown', '.editable-container input', function() {
        $(this).on('keyup', function() {
            var value = $(this).val();
            var $errorBlock = $(this).siblings('.editable-error-block');
            
            if (value && (isNaN(value) || parseInt(value) < 0)) {
                if ($errorBlock.length === 0) {
                    $(this).after('<div class="editable-error-block help-block text-danger">Harga harus berupa angka positif!</div>');
                }
            } else {
                $errorBlock.remove();
            }
        });
    });
   
$(document).ready(function() {
    // Buka modal & set kategori
    $('.btn-add-pricelist').on('click', function() {
        var category = $(this).data('category');
        $('#modalCategory').val(category);
        $('#modalAddPricelistLabel').text('Add Product for ' + category);
        $('#formAddPricelist')[0].reset();
        $('#addPricelistMessage').html('');
        // var modal = new bootstrap.Modal(document.getElementById('modalAddPricelist'));
        modal.show();
    });

    // Submit form AJAX
    $('#formAddPricelist').on('submit', function(e) {
        e.preventDefault();
        $('#addPricelistMessage').html('');

        var formData = $(this).serialize();

        $.ajax({
            url: 'controller/ajax_add_fnb_list.php', // Ganti sesuai file PHP kamu
            method: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#formAddPricelist button[type=submit]').prop('disabled', true).text('Saving...');
            },
            success: function(res) {
                if (res.success) {
                    $('#addPricelistMessage').html('<div class="alert alert-success">Produk berhasil ditambahkan!</div>');
                    setTimeout(function() {
                        location.reload();  // Reload page biar data update
                    }, 500);
                } else {
                    $('#addPricelistMessage').html('<div class="alert alert-danger">' + (res.error || 'Gagal menambahkan Produk') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#addPricelistMessage').html('<div class="alert alert-danger">Error: ' + error + '</div>');
            },
            complete: function() {
                $('#formAddPricelist button[type=submit]').prop('disabled', false).text('Save Product');
            }
        });
    });
});

$(document).ready(function() {
    // Event handler hapus pricelist dengan SweetAlert2
    $(document).on('click', '.btn-delete-pricelist', function() {
        var id = $(this).data('id');
        var $btn = $(this);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda yakin ingin menghapus produk ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.prop('disabled', true);

                $.ajax({
                    url: 'controller/ajax_delete_product.php',  // ganti dengan path file PHP hapus pricelist
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#pricelist-row-' + id).fadeOut(300, function() {
                                $(this).remove();
                            });
                            Swal.fire(
                                'Terhapus!',
                                'Produk berhasil dihapus.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Gagal!',
                                response.error || 'Gagal menghapus produk.',
                                'error'
                            );
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Terjadi kesalahan saat menghapus produk.',
                            'error'
                        );
                        $btn.prop('disabled', false);
                    }
                });
            }
        });
    });
});
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