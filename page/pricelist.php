<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Pricelist</h4>



                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <?php
            // Ambil semua kategori dari tb_category
            $users = $_SESSION['username'];
            // Ambil kategori global (userx='ALL') yang aktif, tapi sembunyikan jika ada override userx='$users' dan status='disable'
            $sql_cat = "
                               SELECT *
FROM tb_category
WHERE userx = 'ALL'
  AND name NOT IN (
    SELECT name
    FROM tb_category
    WHERE userx = '$users' AND status = 'disable'
)
ORDER BY name ASC           ";
            $result_cat = $con->query($sql_cat);

            if ($result_cat && $result_cat->num_rows > 0) {
                while ($cat = $result_cat->fetch_assoc()) {
                    ?>
                    <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">

                                <h4 class="card-title d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                    <button type="button" class="btn btn-sm btn-primary btn-add-pricelist"
                                        data-category="<?php echo htmlspecialchars($cat['name']); ?>">
                                        + Add
                                    </button>

                                </h4>


                                <div class="table-responsive">
                                    <?php
                                    // Ambil data pricelist untuk kategori ini
                                    $sql = "SELECT id, duration, price FROM tb_pricelist WHERE type_ps = '" . $con->real_escape_string($cat['name']) . "' AND userx='$username' ORDER BY price ASC";
                                    $result = $con->query($sql);
                                    ?>

                                    <table class="table table-striped table-nowrap mb-0">
                                        <thead>
                                            <tr>
                                                <th>Duration</th>
                                                <th>Price</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php

                                            if ($result && $result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo '<tr id="pricelist-row-' . $row['id'] . '">
              <td>
                <button class="btn btn-delete-pricelist p-1 px-2" data-id="' . $row['id'] . '" title="Delete pricelist" style="font-size: 0.75rem; line-height: 1;">
                  <i class="bx bx-minus-circle text-danger"></i>
                </button>
                ' . htmlspecialchars($row['duration']) . ' Min
              </td>
              <td>
                <a href="javascript:void(0);" 
                  id="inline-username-' . $row['id'] . '" 
                  data-type="number" 
                  data-pk="' . $row['id'] . '" 
                  data-value="' . $row['price'] . '"
                  data-title="Enter price"
                  class="editable-price">Rp. ' . number_format($row['price'], 0, ',', '.') . '</a>
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
        <div class="modal fade" id="modalAddPricelist" tabindex="-1" aria-labelledby="modalAddPricelistLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form id="formAddPricelist">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAddPricelistLabel">Add Pricelist</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="type_ps" id="modalCategory" value="">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" min="1"
                                    required>
                                <label for="duration" class="form-label">Saran : Kelipatan 30,60,90,120,dst
                                    (Menit)</label>
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

        <div class="modal fade" id="modalAddCategory" tabindex="-1" aria-labelledby="modalAddCategoryLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form id="formAddCategory">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAddCategoryLabel">Add Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="type_ps" id="modalCategory" value="">
                            <div class="mb-3">
                                <label for="name_cat" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="name_cat" name="name_cat" required>
                            </div>

                            <div id="addCategoryMessage"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Category</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Log Perubahan Global -->
        <div class="row">



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
        $(document).ready(function () {

            var modal = new bootstrap.Modal(document.getElementById('modalAddPricelist'));
            var modal_cat = new bootstrap.Modal(document.getElementById('modalAddCategory'));
            // Initialize x-editable untuk semua price links
            $("a[id^='inline-username-']").editable({
                type: 'text',
                title: 'Enter price',
                mode: 'inline',
                validate: function (value) {
                    // Validasi input harus angka
                    if (!value || isNaN(value) || parseInt(value) < 0) {
                        return 'Harga harus berupa angka positif!';
                    }
                },
                display: function (value, sourceData) {
                    // Format display dengan rupiah
                    if (value) {
                        $(this).html('Rp. ' + parseInt(value).toLocaleString('id-ID'));
                    }
                },
                success: function (response, newValue) {
                    var $this = $(this);
                    var pricelistId = $this.data('pk');

                    // Kirim AJAX untuk save ke database
                    $.ajax({
                        url: 'controller/ajax_pricelist.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: pricelistId,
                            price: newValue,
                            userx: '<?php echo $_SESSION['username'] ?? ''; ?>' // Kirim userx dari session
                        },
                        beforeSend: function () {
                            // Loading indicator
                            $this.addClass('text-muted').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                        },
                        success: function (result) {
                            if (result.success) {
                                // Update tampilan dengan format rupiah
                                $this.removeClass('text-muted').html('Rp. ' + parseInt(newValue).toLocaleString('id-ID'));

                                // Show success notification
                                showNotification('success', 'Harga berhasil diupdate!');

                                // Optional: Log activity
                                console.log('Price updated:', {
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
                        error: function (xhr, status, error) {
                            // Revert tampilan dan show error
                            $this.removeClass('text-muted').html('Rp. 0');
                            showNotification('error', 'Koneksi error: ' + error);
                            console.error('Ajax Error:', { xhr: xhr, status: status, error: error });
                        }
                    });
                },
                error: function (errors) {
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
            $(document).on('shown', '.editable-container input', function () {
                $(this).on('keyup', function () {
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

            $(document).ready(function () {
                // Buka modal & set kategori
                $('.btn-add-pricelist').on('click', function () {
                    var category = $(this).data('category');
                    $('#modalCategory').val(category);
                    $('#modalAddPricelistLabel').text('Add Pricelist for ' + category);
                    $('#formAddPricelist')[0].reset();
                    $('#addPricelistMessage').html('');
                    // var modal = new bootstrap.Modal(document.getElementById('modalAddPricelist'));
                    modal.show();
                });

                // Submit form AJAX
                $('#formAddPricelist').on('submit', function (e) {
                    e.preventDefault();
                    $('#addPricelistMessage').html('');

                    var formData = $(this).serialize();

                    $.ajax({
                        url: 'controller/ajax_add_pricelist.php', // Ganti sesuai file PHP kamu
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        beforeSend: function () {
                            $('#formAddPricelist button[type=submit]').prop('disabled', true).text('Saving...');
                        },
                        success: function (res) {
                            if (res.success) {
                                $('#addPricelistMessage').html('<div class="alert alert-success">Pricelist berhasil ditambahkan!</div>');
                                setTimeout(function () {
                                    location.reload();  // Reload page biar data update
                                }, 500);
                            } else {
                                $('#addPricelistMessage').html('<div class="alert alert-danger">' + (res.error || 'Gagal menambahkan pricelist') + '</div>');
                            }
                        },
                        error: function (xhr, status, error) {
                            $('#addPricelistMessage').html('<div class="alert alert-danger">Error: ' + error + '</div>');
                        },
                        complete: function () {
                            $('#formAddPricelist button[type=submit]').prop('disabled', false).text('Save Pricelist');
                        }
                    });
                });
            });


            $(document).ready(function () {
                // Buka modal & set kategori
                $('.btn-add-category').on('click', function () {
                    var category = $(this).data('category');
                    $('#modalCategory').val(category);
                    $('#modalAddCategoryLabel').text('Add Category for ' + category);
                    $('#formAddCategory')[0].reset();
                    $('#addCategoryMessage').html('');
                    // var modal = new bootstrap.Modal(document.getElementById('modalAddCategory'));
                    modal_cat.show();
                });

                // Submit form AJAX
                $('#formAddCategory').on('submit', function (e) {
                    e.preventDefault();
                    $('#addCategoryMessage').html('');

                    var formData = $(this).serialize();

                    $.ajax({
                        url: 'controller/ajax_add_category.php', // Ganti sesuai file PHP kamu
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        beforeSend: function () {
                            $('#formAddCategory button[type=submit]').prop('disabled', true).text('Saving...');
                        },
                        success: function (res) {
                            if (res.success) {
                                $('#addCategoryMessage').html('<div class="alert alert-success">Category berhasil ditambahkan!</div>');
                                setTimeout(function () {
                                    location.reload();  // Reload page biar data update
                                }, 500);
                            } else {
                                $('#addCategoryMessage').html('<div class="alert alert-danger">' + (res.error || 'Gagal menambahkan Category') + '</div>');
                            }
                        },
                        error: function (xhr, status, error) {
                            $('#addCategoryMessage').html('<div class="alert alert-danger">Error: ' + error + '</div>');
                        },
                        complete: function () {
                            $('#formAddCategory button[type=submit]').prop('disabled', false).text('Save Category');
                        }
                    });
                });
            });
            $(document).ready(function () {
                // Event handler hapus pricelist dengan SweetAlert2
                $(document).on('click', '.btn-delete-pricelist', function () {
                    var id = $(this).data('id');
                    var $btn = $(this);

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Anda yakin ingin menghapus pricelist ini?",
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
                                url: 'controller/ajax_delete_pricelist.php',  // ganti dengan path file PHP hapus pricelist
                                method: 'POST',
                                data: { id: id },
                                dataType: 'json',
                                success: function (response) {
                                    if (response.success) {
                                        $('#pricelist-row-' + id).fadeOut(300, function () {
                                            $(this).remove();
                                        });
                                        Swal.fire(
                                            'Terhapus!',
                                            'Pricelist berhasil dihapus.',
                                            'success'
                                        );
                                    } else {
                                        Swal.fire(
                                            'Gagal!',
                                            response.error || 'Gagal menghapus pricelist.',
                                            'error'
                                        );
                                        $btn.prop('disabled', false);
                                    }
                                },
                                error: function () {
                                    Swal.fire(
                                        'Error!',
                                        'Terjadi kesalahan saat menghapus pricelist.',
                                        'error'
                                    );
                                    $btn.prop('disabled', false);
                                }
                            });
                        }
                    });
                });
            });

            $(document).ready(function () {
                // Event handler hapus pricelist dengan SweetAlert2
                $(document).on('click', '.btn-delete-category', function () {
                    var id = $(this).data('id');
                    var $btn = $(this);

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Anda yakin ingin menghapus kategori ini?",
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
                                url: 'controller/ajax_delete_category.php',  // ganti dengan path file PHP hapus category
                                method: 'POST',
                                data: { id: id },
                                dataType: 'json',
                                success: function (response) {
                                    if (response.success) {
                                        $('#category-row-' + id).fadeOut(300, function () {
                                            $(this).remove();
                                        });
                                        Swal.fire(
                                            'Terhapus!',
                                            'kategori berhasil dihapus.',
                                            'success'
                                        );
                                    } else {
                                        Swal.fire(
                                            'Gagal!',
                                            response.error || 'Gagal menghapus kategori.',
                                            'error'
                                        );
                                        $btn.prop('disabled', false);
                                    }
                                },
                                error: function () {
                                    Swal.fire(
                                        'Error!',
                                        'Terjadi kesalahan saat menghapus kategori.',
                                        'error'
                                    );
                                    $btn.prop('disabled', false);
                                }
                            });
                        }
                    });
                });
            });

            $(document).ready(function () {
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
                        success: function (res) {
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
                                    setTimeout(function () {
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
                        error: function (xhr, status, error) {
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
                        complete: function () {
                            $switch.prop('disabled', false);
                        }
                    });
                }

                // Gunakan event delegation agar event tetap aktif untuk elemen dinamis
                $(document).on('change', '.category-switch', handleCategorySwitchChange);
            });

        });
    </script>


    <script src="assets/js/app.js"></script>
    <style>
        .editable-input input {
            max-width: 100px !important;
            /* Batasi lebar maksimal */
            width: 100% !important;
            /* Lebar mengikuti container tapi maksimal 150px */
            box-sizing: border-box;
            /* Pastikan padding dan border masuk ke lebar */
        }
    </style>