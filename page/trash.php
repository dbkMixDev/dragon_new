<div class="page-content">
    <div class="container-fluid">
 <?php
   $psnumberx = 0;
//    echo $_SESSION['level'];
                // Ambil transaksi tb_trans untuk PS ini yang inv IS NULL dan userx sama
                $qTransList = "SELECT * FROM tb_trans WHERE id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "' AND inv IS NULL AND userx = '" . mysqli_real_escape_string($con, $username) . "' ORDER BY start DESC";
                $rTransList = mysqli_query($con, $qTransList);

                // Ambil id_trans utama (pertama) untuk sesi ini
                $mainTrans = mysqli_fetch_assoc(mysqli_query($con, "SELECT id_trans FROM tb_trans WHERE id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "' AND inv IS NULL AND userx = '" . mysqli_real_escape_string($con, $username) . "' ORDER BY start ASC LIMIT 1"));
                $main_id_trans = $mainTrans ? $mainTrans['id_trans'] : null;

                // Ambil produk FNB
                $fnbList = [];
                $qFnb = "SELECT * FROM tb_fnb where userx='$username' ORDER BY nama ASC";
                $rFnb = mysqli_query($con, $qFnb);
                while ($f = mysqli_fetch_assoc($rFnb)) {
                    $fnbList[] = $f;
                }

                // Gabungan data rental dan FNB
                $allRows = [];
                $totalRental = 0;
                $totalFnb = 0;

                // Rental rows
                mysqli_data_seek($rTransList, 0);
                while ($tr = mysqli_fetch_assoc($rTransList)) {
                    $harga = $tr['harga'] ? (int)$tr['harga'] : 0;
                    $totalRental += $harga;
                    $mode_stop = $tr['mode_stop'];
                    // Nama item: Rental {durasi} Min PS (#no_ps)
                    $durasi = $tr['durasi'] ? $tr['durasi'] : '-';
                    $namaRental = "Rental " . $durasi . " Min (" . $tytyp . ")";
                    $allRows[] = [
                        'id' => $tr['id'], // Tambahkan ID unik untuk rental
                        'nama' => $namaRental,
                        'qty' => 1,
                        'harga' => $harga,
                        'total' => $harga,
                         'tipe' => 'rental',
                         'modestop' => $mode_stop, // Tambahkan mode_stop
                        // tipe dihilangkan
                    ];
                }

                // FNB rows dari tb_trans_fnb yang inv IS NULL dan id_trans ada di tb_trans
                if ($main_id_trans) {
                    $qFnbTrans = "SELECT tf.*, f.nama, f.harga FROM tb_trans_fnb tf LEFT JOIN tb_fnb f ON tf.id_fnb = f.id WHERE tf.id_trans = '" . mysqli_real_escape_string($con, $main_id_trans) . "'";
                    $rFnbTrans = mysqli_query($con, $qFnbTrans);
                    while ($ft = mysqli_fetch_assoc($rFnbTrans)) {
                        $total = $ft['qty'] * $ft['harga'];
                        $totalFnb += $total;
                        $allRows[] = [
                            'id' => $ft['id'],
                            'nama' => $ft['nama'],
                            'qty' => $ft['qty'],
                            'harga' => $ft['harga'],
                            'total' => $total,
                            'tipe' => 'fnb'
                        ];
                    }
                }

                // FNB rows dari tb_trans_fnb yang inv IS NULL tetapi id_trans tidak ada di tb_trans (orphans)
                $qFnbOrphan = "
                    SELECT tf.*, f.nama, f.harga 
                    FROM tb_trans_fnb tf
                    LEFT JOIN tb_fnb f ON tf.id_fnb = f.id
                    LEFT JOIN tb_trans t ON tf.id_trans = t.id_trans
                    WHERE tf.inv IS NULL 
                      AND t.id_trans IS NULL
                      AND tf.id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "'
                ";
                $rFnbOrphan = mysqli_query($con, $qFnbOrphan);
                while ($ft = mysqli_fetch_assoc($rFnbOrphan)) {
                    $total = $ft['qty'] * $ft['harga'];
                    $totalFnb += $total;
                    $allRows[] = [
                        'id' => $ft['id'],
                        'nama' => $ft['nama'],
                        'qty' => $ft['qty'],
                        'harga' => $ft['harga'],
                        'total' => $total,
                        'tipe' => 'fnb'
                    ];
                }
                ?>
              <div class="modal fade" id="modalSpending<?= $psnumberx ?>" tabindex="-1" aria-labelledby="modalSpendingLabel" aria-hidden="true">
  <div class="modal-dialog">
  <div class="modal-content">
    <form id="formSpending<?= $psnumberx ?>">
      <div class="modal-header">
        <h5 class="modal-title" id="modalSpendingLabel">Input Spending</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <input type="hidden" name="category" value="Playstation">
 <!-- New Tanggal dan Waktu input with default value for current date and time -->
        <div class="mb-2">
          <label for="tanggal_waktu" class="form-label">Date & Time</label>
          <?php
          // Get current date and time in the format YYYY-MM-DDTHH:MM
          $currentDateTime = date('Y-m-d\TH:i:s');
          ?>
          <input type="datetime-local" name="datetimes" class="form-control" value="<?= $currentDateTime ?>" required>
        </div>
        <div class="mb-2">
          <label for="grand_total" class="form-label">Price (Rp)</label>
          <input type="number" name="grand_total" class="form-control" required>
        </div>

        <div class="mb-2">
          <label for="note" class="form-label">Details</label>
          <textarea name="note" class="form-control" rows="2" required></textarea>
        </div>

        <div class="mb-2">
          <label for="metode_pembayaran" class="form-label">Payment Method</label>
          <select name="metode_pembayaran" class="form-select" required>
            <option value="cash">Cash</option>
            <option value="qris">QRIS</option>
            <option value="debit">Bank Transfer</option>
            <option value="ewalet">E-walet</option>
          </select>
        </div>

        <!-- New Tanggal dan Waktu input -->
       

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success btn-sm">Simpan</button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>
</div>

   <script>
document.querySelector('#formSpending<?= $psnumberx ?>').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch('controller/ajax_spending.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(response => {
        if (response.trim() === 'ok') {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Data pengeluaran berhasil disimpan!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Gagal!',
                text: 'Gagal: ' + response,
                icon: 'error',
                confirmButtonText: 'Tutup'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            title: 'Kesalahan!',
            text: 'Terjadi kesalahan saat menyimpan.',
            icon: 'error',
            confirmButtonText: 'Tutup'
        });
    });
});
</script>



    <div class="modal fade" id="modal<?= $psnumberx ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel<?= $psnumberx ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel<?= $psnumberx ?>">Order Details for PS #<?= $psnumberx ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="location.reload();"></button>
            </div>
            
            <div class="modal-body">
 <!-- Form tambah FNB tetap -->
<form id="formAddFnb<?= $psnumberx ?>" class="row g-2 align-items-end mb-2">
    <input type="hidden" name="id_trans" value="<?= htmlspecialchars($main_id_trans) ?>">
    <div class="col-8">
        
        <select class="form-select form-select-sm" name="id_fnb" required>
            <option value="" disabled selected>Select Product...</option>
            <?php foreach ($fnbList as $fnb): ?>
            <option value="<?= $fnb['id'] ?>"><?= htmlspecialchars($fnb['nama']) ?> - Rp <?= number_format($fnb['harga'], 0, ',', '.') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-2">
        <input type="number" min="1" class="form-control form-control-sm" name="qty" value="1" placeholder="Qty" required>
    </div>
    <div class="col-2">
        <button type="button" class="btn btn-success btn-sm w-100 btn-add-fnb" data-ps="<?= $psnumberx ?>">+</button>
    </div>
</form>
<div class="mb-0 fw-bold">Transaction</div>
<!-- Change modal-dialog to modal-md for a narrower modal -->
<div class="table-responsive mb-0" id="trxTable<?= $psnumberx ?>">
    <table id="fnb-table<?= $psnumberx ?>" class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="fnb-body<?= $psnumberx ?>">
            <?php if (count($allRows) > 0): $no=1; foreach ($allRows as $row): ?>
               <tr id="row-fnb-<?= $row['id'] ?>">

                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= $row['qty'] ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                    <td>
        <?php if (isset($row['tipe']) && $row['tipe'] === 'fnb'): ?>
        <button type="button"
                class="btn btn-danger btn-sm w-50 btn-del-fnb"
                data-ps="<?= $psnumberx ?>"
                data-id="<?= $row['id'] ?>"
                id="btnDelFnb<?= $row['id'] ?>">
            -
        </button>
        <?php endif; ?>
        <?php if (isset($row['tipe']) && $row['tipe'] === 'rental'): ?>
        <button type="button"
                class="btn btn-danger btn-sm w-50 btn-del-Rent"
                data-ps="<?= $psnumberx ?>"
                data-id="<?= $row['id'] ?>"
                id="btnDelRental<?= $row['id'] ?>">
            -
        </button>
        <?php endif; ?>
    </td>
                </tr>
            <?php endforeach; ?>
           
           
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No transactions yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
           
            <tr>
                <th colspan="4" class="text-end">Total</th>
                <th colspan="1" id="grand-total<?= $psnumberx ?>">Rp <?= number_format($totalRental + $totalFnb, 0, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>
</div>

                <?php
                    $grandTotal = ($totalRental ?? 0) + ($totalFnb ?? 0);
                ?>
                <hr>
               

                <div class="row mb-1" style="margin-top:-20px">
                    <div class="col-6 text-end">Promo:</div>
                    <div class="col-6">
                           <select class="form-select form-select-sm promo-select" id="promo<?= $psnumberx ?>" name="promo" data-ps="<?= $psnumberx ?>">
                            <option value="0">-- Select Promo --</option>
                           <?php 
$qPromo = "SELECT * FROM tb_promo 
           WHERE (type_rental = '$tytyp' OR type_rental = 'Product') 
           AND userx = '$username' 
           AND status = 1";
            

$rPromo = mysqli_query($con, $qPromo);
while($p = mysqli_fetch_assoc($rPromo)): 
    $type = $p['disc_type']; // nominal, perc, hours
    $qty = $p['qty_potongan'];
  
    // Label tampilannya
    if ($type === 'nominal') {
        $label = 'Rp ' . number_format($qty, 0, ',', '.');
        $value = $qty;
    } elseif ($type === 'perc') {
        $label = $qty . '%';
        $value = ($qty / 100)*$grandTotal; // akan dihitung di JS
    } elseif ($type === 'hours') {
         $durationminutes = intval($qty) * 60;
$q = mysqli_query($con, "SELECT price FROM tb_pricelist WHERE duration = '$durationminutes' AND type_ps = '$tytyp' AND userx='$username' LIMIT 1");
$data = mysqli_fetch_assoc($q);

$qty = $data['price'] ?? 0;
        $label = 'Rp ' . number_format($qty, 0, ',', '.');
        $value = $qty; // juga akan dihitung di JS
    }
?>
    <option 
        value="<?= $value ?>" 
        data-nama="<?= htmlspecialchars($p['nama_promo']) ?>" 
        data-type="<?= $type ?>" 
        data-qty="<?= $qty ?>">
        <?= htmlspecialchars($p['nama_promo']) ?> (<?= $label ?>)
    </option>
<?php endwhile; ?>

                        </select>
                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-6 text-end">Grand Total:</div>
                    <div class="col-6 fw-bold" id="displayTotal<?= $psnumberx ?>">Rp <?= number_format($grandTotal, 0, ',', '.') ?></div>
                </div>
                
                <!-- Tambahkan dropdown metode pembayaran -->
<div class="row mb-1">
    <div class="col-6 text-end">Payment Method:</div>
    <div class="col-6">
        <select class="form-select form-select-sm" id="paymentMethod<?= $psnumberx ?>">
            <option value="tunai">Cash</option>
            <option value="qris">QRIS</option>
            <option value="debit">Bank Transfer</option>
            <option value="ewalet">E-walet</option>
        </select>
    </div>
</div>

<!-- Kolom bayar & kembalian (hanya untuk tunai) -->
<div class="row mb-1" id="tunaiFields<?= $psnumberx ?>">
    <div class="col-6 text-end">Pay:</div>
    <div class="col-6">
        <input type="number" min="0" class="form-control form-control-sm bayar-input" id="bayar<?= $psnumberx ?>" placeholder="Rp" data-ps="<?= $psnumberx ?>">
    </div>
    <div class="col-6 text-end mt-2">Change:</div>
    <div class="col-6 mt-2">
        <span class="form-control form-control-sm bg-light" id="kembali<?= $psnumberx ?>">Rp 0</span>
    </div>
</div>

               

            <div class="modal-footer">
<button 
  type="button" 
  class="btn btn-info btnPayNow" 
  data-ps="<?= $psnumberx ?>" 
  data-id_trans="<?= $main_id_trans ?>" 
  data-userx="<?= $username ?>" 
  disabled>
  Pay Now
</button>



</div>

        </div>
    </div>
    </div>
    </div>


        <!-- start page title -->
        <div class="row">
            <div class="col-12">
  <div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0 font-size-18">Trash List</h4>
 

    <!-- Container tombol di kanan -->
    <div class="btn-icon-group" style="display: flex; gap: 10px;">
        
      <?php
    // Hitung jumlah transaksi rental (tb_trans)
  
    $qInvRental = "
        SELECT COUNT(*) as cnt FROM tb_trans 
        WHERE id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "' 
        AND inv IS NULL AND userx = '" . mysqli_real_escape_string($con, $username) . "'";
    $invCountRental = (int)(mysqli_fetch_assoc(mysqli_query($con, $qInvRental))['cnt'] ?? 0);

    // Hitung jumlah transaksi FNB (qty) (tb_trans_fnb)
    $qInvFnb = "
        SELECT SUM(qty) as cnt FROM tb_trans_fnb 
        WHERE id_ps = '" . mysqli_real_escape_string($con, $psnumberx) . "' 
        AND inv IS NULL AND userx = '" . mysqli_real_escape_string($con, $username) . "'";
    $invCountFnb = (int)(mysqli_fetch_assoc(mysqli_query($con, $qInvFnb))['cnt'] ?? 0);

    // Hitung total transaksi
    $invCount = $invCountRental + $invCountFnb;

    // Tombol order hanya disable jika PS 'available' dan belum ada transaksi
    $disabled = ($invCount > 0) ? 'disabled' : '';
    ?>
   
  <div class="d-flex justify-content-end align-items-end mb-0">
                        <div class="me-2">
                            <!-- <label for="date_start" class="form-label mb-0">Start Date :</label> -->
                            <input type="date" id="date_start" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="me-2">-</div>
                        <div class="me-2">
                            <!-- <label for="date_end" class="form-label mb-0">End Date :</label> -->
                            <input type="date" id="date_end" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                        </div>
                        <!-- <div class="align-self-end">
                            <button id="btnFilter" class="btn btn-sm btn-primary"><i class="bx bx-filter-alt"></i> Filter</button>
                        </div> -->
                    </div>


    </div>
    
  </div>
</div>

<!-- Jangan lupa load boxicons kalau belum -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

        </div>
        <!-- end page title -->

        <div class="col-12">
            <div class="card">
                <div class="card-body">

                  
                    <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Date Trans</th>
                                <th>Details</th>
                                <th>Total (Rp)</th>
                               
                                <th>User Deleted</th>
                                 <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
<?php echo '<script>window.userLevel = "' . ($_SESSION['level'] ?? 'user') . '";</script>'; ?>
        <!-- JAVASCRIPT -->
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenu/metisMenu.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/node-waves/waves.min.js"></script>

        <!-- Required datatable js -->
        <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/jszip/jszip.min.js"></script>
        <script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
        <script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

        <!-- Responsive examples -->
        <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="assets/js/pages/datatables.init.js"></script>

        <script src="assets/js/app.js"></script>
    <script>
   // Initialize DataTable
   // UPDATED: Add FNB dengan promo integration
$('.btn-add-fnb').on('click', function () {
    const ps = $(this).data('ps');
    const form = $(this).closest('form');
    const id_trans = form.find('input[name="id_trans"]').val();
    const id_fnb = parseInt(form.find('select[name="id_fnb"]').val());
    const qty = parseInt(form.find('input[name="qty"]').val());

    if (!id_fnb || qty < 1) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Pilih produk dan isi qty minimal 1!',
        });
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'controller/ajax_add_fnb.php',
        data: { id_trans, id_fnb, qty,ps },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                const tbody = $('#trxTable' + ps).find('tbody');
                let found = false;

                tbody.find('tr').each(function () {
                    const row = $(this);
                    const namaItem = row.find('td').eq(1).text().trim();
                    if (namaItem === res.data.nama) {
                        const currentQty = parseInt(row.find('td').eq(2).text());
                        const newQty = currentQty + qty;
                        const newTotal = newQty * res.data.harga;

                        row.find('td').eq(2).text(newQty);
                        row.find('td').eq(4).text('Rp ' + newTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
                        
                        row.attr('id', `row-fnb-${res.data.real_db_id}`);
                        row.attr('data-id', res.data.real_db_id);
                        row.find('.btn-del-fnb').attr('data-id', res.data.real_db_id);
                        
                        found = true;
                        return false;
                    }
                });

                if (!found) {
                     tbody.find('tr').each(function () {
                        if ($(this).find('td').length === 1 && $(this).find('td').text().includes('No transactions yet')) {
                            $(this).remove();
                        }
                    });
                    const no = tbody.find('tr').length + 1;
                    const newRow = `
                         <tr id="row-fnb-${res.data.real_db_id}" data-id="${res.data.real_db_id}">
                            <td>${no}</td>
                            <td>${res.data.nama}</td>
                            <td>${res.data.qty}</td>
                            <td>Rp ${res.data.harga.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}</td>
                            <td>Rp ${res.data.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-danger btn-sm w-50 btn-del-fnb" 
                                        data-ps="${ps}" 
                                        data-id="${res.data.real_db_id}">
                                    -
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(newRow);
                }

                // Update nomor urut
                tbody.find('tr').each(function (idx) {
                    $(this).find('td').eq(0).text(idx + 1);
                });

                // UPDATED: Update total dengan promo consideration
                updateTotalWithPromo(ps, res.grand_total);

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: res.error || 'Gagal menambahkan produk.',
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan pada server.',
            });
        }
    });
});

// UPDATED: Delete FNB dengan promo integration
$('body').on('click', '.btn-del-fnb', function (e) {
    e.preventDefault();
    const id = $(this).data('id');
    const ps = $(this).data('ps');
    
    if (!id) {
        Swal.fire('Error', 'ID tidak ditemukan.', 'error');
        return;
    }

    Swal.fire({
        title: 'Hapus item?',
        text: 'Yakin ingin menghapus item ini dari transaksi?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'controller/ajax_del_fnb.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (res) {
                    if (res && res.success) {
                        $(`#row-fnb-${id}`).remove();

                        // Update nomor urut
                        const tbody = $('#trxTable' + ps).find('tbody');
                        tbody.find('tr').each(function (idx) {
                            $(this).find('td').eq(0).text(idx + 1);
                        });

                        // UPDATED: Update total dengan promo consideration
                        updateTotalWithPromo(ps, res.grand_total);

                        Swal.fire('Berhasil', 'Item berhasil dihapus.', 'success');
                    } else {
                        Swal.fire('Gagal', res.error || 'Gagal menghapus item.', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire('Error', 'Gagal menghubungi server: ' + error, 'error');
                }
            });
        }
    });
});
function calculateGrandTotalWithPromo(ps) {
    const totalRental = parseInt($('#trxTable' + ps).find('[data-rental-total]').text().replace(/[^0-9]/g, '')) || 0;
    const totalFnb = parseInt($('#trxTable' + ps).find('#grand-total' + ps).attr('data-fnb-total')) || 0; // ✅ ambil dari atribut
    const promoDiscount = parseInt($('#promo' + ps).val()) || 0;

    const grandTotal = (totalRental + totalFnb) - promoDiscount;
    const finalTotal = Math.max(0, grandTotal);

    $('#displayTotal' + ps).text('Rp ' + finalTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    $('#grand-total' + ps).text('Rp ' + totalFnb.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')); // biar konsisten

    $('#bayar' + ps).val('');
    $('#kembali' + ps).text('Rp 0');

    return finalTotal;
}
// Event handler untuk perubahan promo
$(document).on('change', '.promo-select', function() {
    const ps = $(this).data('ps');
    // Ambil total dari tabel (tfoot grand-total)
    let tableTotalText = $('#trxTable' + ps).find('tfoot #grand-total' + ps).text().replace(/[^0-9]/g, '');
    let tableTotal = parseInt(tableTotalText) || 0;

    // Set grand total = total tabel (tanpa promo)
    $('#displayTotal' + ps).text('Rp ' + tableTotal.toLocaleString('id-ID'));

    // Jika promo dipilih, kurangi qty_potongan
    const promoValue = parseInt($(this).val()) || 0;
    if (promoValue > 0) {
        let afterPromo = Math.max(0, tableTotal - promoValue);
        $('#displayTotal' + ps).text('Rp ' + afterPromo.toLocaleString('id-ID'));
        // Optional: notifikasi
        const promoName = $(this).find('option:selected').data('nama') || '';
        Swal.fire({
            icon: 'success',
            title: 'Promo Applied!',
            text: promoName + ' - Diskon Rp ' + promoValue.toLocaleString('id-ID'),
            timer: 1500,
            showConfirmButton: false
        });
    }

    // Reset bayar & kembalian
    $('#bayar' + ps).val('');
    $('#kembali' + ps).text('Rp 0');
});

// Payment method: show/hide bayar & kembalian
$(document).on('change', '[id^="paymentMethod"]', function () {
    const ps = $(this).attr('id').replace('paymentMethod', '');
    const metode = $(this).val();
    const btn = $('.btnPayNow[data-ps="' + ps + '"]');

    if (metode === 'tunai') {
        $('#tunaiFields' + ps).show();
        $('#bayar' + ps).focus();
        btn.prop('disabled', true); // butuh input bayar
    } else {
        $('#tunaiFields' + ps).hide();
        $('#bayar' + ps).val('');
        $('#kembali' + ps).text('Rp 0');
        btn.prop('disabled', false); // langsung bisa klik
    }
});

$(document).on('input', '.bayar-input', function () {
    const ps = $(this).data('ps');
    const bayar = parseInt($(this).val()) || 0;
    const grandTotal = parseInt($('#displayTotal' + ps).text().replace(/[^0-9]/g, '')) || 0;

    const kembali = Math.max(0, bayar - grandTotal);
    $('#kembali' + ps).text('Rp ' + kembali.toLocaleString('id-ID'));

    const btn = $('.btnPayNow[data-ps="' + ps + '"]');

    if (bayar >= grandTotal) {
        btn.prop('disabled', false);
    } else {
        btn.prop('disabled', true);
        if (bayar > 0) {
            $('#kembali' + ps).html('<span class="text-danger">Kurang Rp ' +
                (grandTotal - bayar).toLocaleString('id-ID') + '</span>');
        }
    }
});
$(document).on('change', '[id^="paymentMethod"]', function () {
    const ps = $(this).attr('id').replace('paymentMethod', '');
    const metode = $(this).val();
    const btn = $('.btnPayNow[data-ps="' + ps + '"]');

    if (metode === 'tunai') {
        $('#tunaiFields' + ps).show();
        $('#bayar' + ps).focus();
        btn.prop('disabled', true); // karena butuh input bayar
    } else {
        $('#tunaiFields' + ps).hide();
        $('#bayar' + ps).val('');
        $('#kembali' + ps).text('Rp 0');
        btn.prop('disabled', false); // langsung bisa klik
    }
});

$(document).on('input', '.bayar-input', function () {
    const ps = $(this).data('ps');
    const bayar = parseInt($(this).val()) || 0;
    const grandTotal = parseInt($('#displayTotal' + ps).text().replace(/[^0-9]/g, '')) || 0;

    const kembali = Math.max(0, bayar - grandTotal);
    $('#kembali' + ps).text('Rp ' + kembali.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));

    const btn = $('.btnPayNow[data-ps="' + ps + '"]');

    if (bayar >= grandTotal) {
        btn.prop('disabled', false);
    } else {
        btn.prop('disabled', true);
        if (bayar > 0) {
            $('#kembali' + ps).html('<span class="text-danger">Kurang Rp ' + 
                (grandTotal - bayar).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '</span>');
        }
    }
});



// Inisialisasi saat halaman load
$(document).ready(function() {
    // Sembunyikan semua field tunai di awal
    $('[id^="tunaiFields"]').hide();
    
    // Set default payment method ke tunai dan tampilkan field
    $('[id^="paymentMethod"]').each(function() {
        const ps = $(this).attr('id').replace('paymentMethod', '');
        $(this).val('tunai');
        $('#tunaiFields' + ps).show();
    });
});

function updateTotalWithPromo(ps, newGrandTotal) {
    // Update base total tampilan
    $('#trxTable' + ps).find('#grand-total' + ps)
        .text('Rp ' + newGrandTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'))
        .attr('data-fnb-total', newGrandTotal); // ✅ Simpan nilai total FNB untuk perhitungan promo

    // Ambil nilai promo
    const promoDiscount = parseInt($('#promo' + ps).val()) || 0;
    const finalTotal = Math.max(0, newGrandTotal - promoDiscount);

    // Update tampilan total akhir
    $('#displayTotal' + ps).text('Rp ' + finalTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));

    // Reset bayar dan kembali
    $('#bayar' + ps).val('');
    $('#kembali' + ps).text('Rp 0');
}
$(document).on('click', '.btnPayNow', function () {
    console.log('Pay Now clicked');
    const ps = $(this).data('ps');
    const id_trans = $(this).data('id_trans'); // tanpa butuh form
    const userx = $(this).data('userx');
    const grandtotal = parseInt($('#displayTotal' + ps).text().replace(/[^0-9]/g, '')) || 0;

    const metode = $('#paymentMethod' + ps).val();
    const bayar = parseInt($('#bayar' + ps).val()) || 0;
    const kembali = parseInt($('#kembali' + ps).text().replace(/[^0-9]/g, '')) || 0;
    const promo = parseInt($('#promo' + ps).val()) || 0;
    const invoice = 'INV' + Date.now();

    $.ajax({
        url: 'controller/ajax_save_trans.php',
        method: 'POST',
        dataType: 'json',
       data: { metode, bayar, kembali, promo, invoice, id_trans, userx, ps, grandtotal },

        success: function (res) {
            if (res.success) {
                Swal.fire('Sukses', 'Transaksi berhasil disimpan!', 'success').then(() => {
                    $('#modal' + ps).modal('hide');
                    location.reload();
                });
                         // Jika fitur autopilot aktif, langsung buka struk
        if (res.autopilot) {
            window.open(`controller/print_struk.php?inv=${res.inv}`, 'strukWindow', 'width=500,height=800,top=100,left=300');
        }

            } else {
                Swal.fire('Gagal', res.error || 'Gagal menyimpan transaksi.', 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Terjadi error koneksi.', 'error');
        }
    });
});


let transactionTable;

$(document).ready(function() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#datatable-buttons')) {
        $('#datatable-buttons').DataTable().destroy();
    }

    // Initialize DataTable with configuration
    transactionTable = $('#datatable-buttons').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        responsive: true,
        processing: true,
        serverSide: false,
        destroy: true, // Allow re-initialization
        ajax: {
            url: 'controller/fetch_trash.php',
            type: 'GET',
            data: function(d) {
                // Clear default DataTables parameters we don't need
                delete d.draw;
                delete d.columns;
                delete d.order;
                delete d.start;
                delete d.length;
                delete d.search;
                
                // Add our date parameters
                d.start = $('#date_start').val() || new Date().toISOString().slice(0, 8) + '01';
                d.end = $('#date_end').val() || new Date().toISOString().slice(0, 10);
            },
            dataSrc: function(json) {
                console.log('Server response:', json);
                
                // Handle server response
                if (json.error) {
                    console.error('Server Error:', json.error);
                    // showAlert('Error loading transactions: ' + json.error, 'danger');
                    return [];
                }
                
                // Validate data structure
                if (!json.data || !Array.isArray(json.data)) {
                    console.warn('Invalid data structure received:', json);
                    // showAlert('Invalid data format received from server', 'warning');
                    return [];
                }
                
                // Show success message
                if (json.data.length > 0) {
                    // showAlert(`Successfully loaded ${json.data.length} transaction(s)`, 'success');
                } else {
                    // showAlert('No transactions found for the selected date range', 'info');
                }
                
                return json.data;
            },
            error: function(xhr, status, error) {
                console.error('Ajax Error:', {xhr, status, error});
                // showAlert('Failed to load transactions. Please check your connection and try again.', 'danger');
                return [];
            }
        },
        columns: [
            { 
                data: 'tanggal',
                title: 'Date Trans',
                defaultContent: '-',
                width: '15%',
            },
            { 
                data: 'details',
                title: 'Details',
                defaultContent: '<i class="text-muted">No details available</i>',
                width: '30%',
            },
            { 
                data: 'total',
                title: 'Total (Rp)',
                className: 'text-end',
                defaultContent: 'Rp 0',
                width: '10%',
            },
            
            { 
                data: 'userid',
                title: 'User Deleted',
                defaultContent: '-',
                width: '10%',
             },
             // Di halaman PHP, set user level ke JavaScript


{
    data: null,
    title: 'Action',
    orderable: false,
    width: '10%',
    searchable: false,
    className: 'text-center',
  render: function(data, type, row) {
    if (row.is_parent) {
        let deleteButton = '';
        let printButton = '';
        let downloadButton = '';

        // Tombol delete hanya untuk admin
        if (window.userLevel === 'admin') {
            deleteButton = `
                <button class="btn btn-sm btn-success btn-delete-transaction"
                        data-id="${row.id}"
                        data-source="${row.source}"
                        data-invoice="${row.inv}"
                        title="Restore Delete Entire">
                    <i class="bx bx-revision"></i>
                </button>`;
        }

        // Jika bukan spending, tampilkan tombol cetak & download
       
        return `
            <div class="d-flex align-items-center gap-1">
                ${deleteButton}
                ${printButton}
                ${downloadButton}
            </div>`;
    }
    return '';
}

}


        ],
        order: [[0, 'desc']], // Sort by date descending
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>',
            emptyTable: "No transactions found for the selected date range",
            zeroRecords: "No matching transactions found",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            infoEmpty: "Showing 0 to 0 of 0 transactions",
            infoFiltered: "(filtered from _MAX_ total transactions)"
        },
        drawCallback: function(settings) {
            // Update summary after each draw
            updateTableSummary(this.api().data().toArray());
        }
    });

    // Filter button click event
    $('#btnFilter').on('click', function(e) {
        e.preventDefault();
        
        const startDate = $('#date_start').val();
        const endDate = $('#date_end').val();
        
        // Validate dates
        if (!startDate || !endDate) {
            showAlert('Please select both start and end dates', 'warning');
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            showAlert('Start date cannot be greater than end date', 'warning');
            return;
        }

        // Show loading state
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Loading...');
        
        // Reload data
        transactionTable.ajax.reload(function(json) {
            // Reset button state
            $btn.prop('disabled', false).html('<i class="bx bx-filter-alt"></i> Filter');
        }, false); // false = don't reset paging
    });

    // Auto-filter when date inputs change (with debounce)
    let filterTimeout;
    $('#date_start, #date_end').on('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            const startDate = $('#date_start').val();
            const endDate = $('#date_end').val();
            
            // Only auto-reload if both dates are selected and valid
            if (startDate && endDate && new Date(startDate) <= new Date(endDate)) {
                transactionTable.ajax.reload();
            }
        }, 500); // Wait 500ms after user stops typing
    });
});

/**
 * Show alert message
 * @param {string} message - Alert message
 * @param {string} type - Alert type (success, danger, warning, info)
 */
function showAlert(message, type = 'info') {
    // Remove existing alerts
    $('.custom-alert').remove();
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show custom-alert" role="alert">
            <i class="bx ${getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert alert before the card
    $('.card').before(alertHtml);
    
    // Auto-hide success and info alerts after 5 seconds
    if (type === 'success' || type === 'info') {
        setTimeout(() => {
            $('.custom-alert').fadeOut(() => {
                $('.custom-alert').remove();
            });
        }, 5000);
    }
}

/**
 * Get appropriate icon for alert type
 * @param {string} type - Alert type
 * @returns {string} Icon class
 */
function getAlertIcon(type) {
    const icons = {
        'success': 'bx-check-circle',
        'danger': 'bx-error-circle',
        'warning': 'bx-error',
        'info': 'bx-info-circle'
    };
    return icons[type] || 'bx-info-circle';
}

/**
 * Update table summary information
 * @param {Array} data - Transaction data
 */
function updateTableSummary(data) {
    if (!data || data.length === 0) return;
    
    try {
        // Calculate totals by category
        const summary = data.reduce((acc, row) => {
            const category = row.kategori || 'Unknown';
            // Extract numeric value from formatted currency
            const totalStr = (row.total || '0').toString();
            const numericStr = totalStr.replace(/[^\d,-]/g, '').replace(/\./g, '').replace(',', '.');
            const total = parseFloat(numericStr) || 0;
            
            if (!acc[category]) {
                acc[category] = { count: 0, total: 0 };
            }
            acc[category].count++;
            acc[category].total += total;
            
            return acc;
        }, {});
        
        // Log summary for debugging
        console.log('Transaction Summary:', summary);
        
        // Calculate grand total
        const grandTotal = Object.values(summary).reduce((sum, cat) => sum + cat.total, 0);
        console.log('Grand Total:', formatCurrency(grandTotal));
        
    } catch (error) {
        console.error('Error calculating summary:', error);
    }
}

/**
 * Refresh transactions data
 */
function refreshTransactions() {
    if (transactionTable && transactionTable.ajax) {
        transactionTable.ajax.reload();
    } else {
        console.warn('DataTable not initialized yet');
        location.reload(); // Fallback to page reload
    }
}

/**
 * Export transactions data
 * @param {string} format - Export format (csv, excel, pdf)
 */
function exportTransactions(format) {
    if (!transactionTable) {
        showAlert('Table not initialized yet', 'warning');
        return;
    }
    
    try {
        const button = transactionTable.button(`0:name(${format})`);
        if (button && button.length > 0) {
            button.trigger();
        } else {
            // Fallback export methods
            switch(format.toLowerCase()) {
                case 'csv':
                    transactionTable.button('.buttons-csv').trigger();
                    break;
                case 'excel':
                    transactionTable.button('.buttons-excel').trigger();
                    break;
                case 'pdf':
                    transactionTable.button('.buttons-pdf').trigger();
                    break;
                default:
                    showAlert('Export format not supported', 'warning');
            }
        }
    } catch (error) {
        console.error('Export error:', error);
        showAlert('Export failed', 'danger');
    }
}

/**
 * Utility function to format currency
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

/**
 * Initialize table manually if needed
 */
function initializeTable() {
    if (!transactionTable) {
        console.log('Initializing table manually...');
        $(document).ready(function() {
            // Trigger the initialization
        });
    }
}
// Global error handler for DataTables
$(document).on('error.dt', function(e, settings, techNote, message) {
    console.error('DataTables Error:', {
        error: e,
        settings: settings,
        techNote: techNote,
        message: message
    });
    Swal.fire('Error', 'Table error occurred. Please refresh the page.', 'error');
});

$('#datatable-buttons').on('click', '.btn-delete-transaction', function() {
    const id = $(this).data('id');
    const source = $(this).data('source');
    const invoice = $(this).data('invoice');

    Swal.fire({
        title: 'Yakin ingin mengembalikan seluruh invoice?',
        html: `<strong>Invoice: ${invoice}</strong><br>
               Semua transaksi dalam invoice ini akan dikembalikan:<br>
               <small class="text-muted">- Rental transactions<br>
               - FnB transactions<br>
               - Spending transactions
              </small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, kembalikan seluruh invoice!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        confirmButtonColor: '#4A9782'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memulihkan...',
                text: 'Sedang Memulihkan semua transaksi dalam invoice',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'controller/restore_trans.php',
                type: 'POST',
                data: { 
                    invoice: invoice,
                    userx: '<?=$username?>' // atau ambil dari session/variable
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: `Invoice <strong>${invoice}</strong> berhasil dipulihkan.<br>
                                   <small class="text-muted">
                                   Rental: ${response.deleted_counts.rental} transaksi<br>
                                   FnB: ${response.deleted_counts.fnb} transaksi<br>
                                   Spending: ${response.deleted_counts.spending} transaksi
                                   </small>`
                        });
                        transactionTable.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal', response.message || 'Gagal menghapus invoice.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete error:', error);
                    Swal.fire('Error', 'Terjadi kesalahan saat menghapus invoice.', 'error');
                }
            });
        }
    });
});


// Prevent multiple initializations
window.transactionTableInitialized = true;
    </script>
       
    </div>
</div>