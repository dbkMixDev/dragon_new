<?php
session_start();
include '../include/config.php';

$nama   = $_POST['nama_promo'];
$type   = $_POST['type_rental'];
$qty    = $_POST['qty_potongan'];
$disc   = $_POST['disc_type']; // ambil dari select input
$userx  = $_SESSION['username'];

// Simpan ke database termasuk disc_type
mysqli_query($con, "INSERT INTO tb_promo 
  (nama_promo, type_rental, qty_potongan, disc_type, status, userx) 
  VALUES 
  ('$nama', '$type', '$qty', '$disc', 1, '$userx')");

echo 'ok';
