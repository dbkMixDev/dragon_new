<?php
include '../include/config.php';
session_start();
$username = $_SESSION['username'];
$id = $_POST['id'];
$q = mysqli_query($con, "SELECT * FROM tb_promo WHERE id='$id' AND userx='$username'");
$data = mysqli_fetch_assoc($q);
?>

<div class="modal-header">
  <h5 class="modal-title">Edit Promo</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
  <form id="formEditPromo">
    <input type="hidden" name="id" value="<?= $data['id'] ?>">
    <div class="mb-2">
      <label>Promo Name</label>
      <input type="text" name="nama_promo" class="form-control" value="<?= $data['nama_promo'] ?>" required>
    </div>
    <div class="mb-2">
  <label>Type Rental</label>
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
  <select name="type_rental" class="form-control" required>
    <option value="">-- Select Type --</option>
    <option value="Rental" <?= $data['type_rental'] === 'Rental' ? 'selected' : '' ?>>~All Type Rent~</option>
    <option value="Product" <?= $data['type_rental'] === 'Product' ? 'selected' : '' ?>>~All Type Product~</option>
    <?php while($row = mysqli_fetch_assoc($result)): ?>
      <option value="<?= htmlspecialchars($row['name']) ?>" 
        <?= $data['type_rental'] === $row['name'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($row['name']) ?>
      </option>
    <?php endwhile; ?>
  </select>
</div>

       <div class="mb-2">
  <label>Discount</label>
  <div class="input-group">
    <input type="number" name="qty_potongan" class="form-control" value="<?= $data['qty_potongan'] ?>" required>
   <select name="disc_type" class="form-select" style="max-width: 100px;" required>
  <option value="nominal" <?= $data['disc_type'] === 'nominal' ? 'selected' : '' ?>>Rp</option>
  <option value="perc" <?= $data['disc_type'] === 'perc' ? 'selected' : '' ?>>%</option>
  <option value="hours" <?= $data['disc_type'] === 'hours' ? 'selected' : '' ?>>Hours</option>
</select>

  </div>
</div>
    <button type="submit" class="btn btn-success">Update</button>
  </form>
</div>
