<?php
include '../include/config.php';
$id = $_POST['id'];
mysqli_query($con, "DELETE FROM tb_promo WHERE id='$id'");
echo 'ok';
