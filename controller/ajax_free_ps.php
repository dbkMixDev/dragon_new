<?php
session_start();
include '../include/config.php';
$username = $_SESSION['username'] ?? '';
// Endpoint AJAX untuk membebaskan PS
$userid = $_SESSION['user_id'];
$r = $con->query("SELECT timezone FROM userx WHERE username = '$userid'");
foreach ($r as $rr) {
     
         $timezone = $rr['timezone'];
        

}

date_default_timezone_set($timezone);
$now = date('Y-m-d H:i:s');
if (isset($_POST['action']) && $_POST['action'] === 'free_ps') {
    $no_ps = $_POST['no_ps'];
    $type_ps = $_POST['type_ps'];
    $mode_stop = 'AUTO';
    
    
    // Update status PlayStation menjadi available
    $q = "UPDATE playstations SET status='available', start_time=NULL, end_time=NULL, duration=NULL WHERE no_ps='$no_ps' AND type_ps='$type_ps' and userx='" . mysqli_real_escape_string($con, $username) . "'";
    $result1 = mysqli_query($con, $q);
    
    // Update transaksi dengan manual_stop
    $harga_update = '';
    if (isset($_POST['harga']) && is_numeric($_POST['harga'])) {
        $harga = (int)$_POST['harga'];
        $harga_update = ", harga='$harga'";
    }
    
    $q2 = "UPDATE tb_trans SET manual_stop='$now', mode_stop='$mode_stop' $harga_update WHERE mode_stop IS NULL AND id_ps = '$no_ps' and userx='" . mysqli_real_escape_string($con, $username) . "'";
    $result2 = mysqli_query($con, $q2);
    
    // Return response
    if ($result1 && $result2) {
        echo json_encode(['success' => true, 'message' => 'PS berhasil dibebaskan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal membebaskan PS']);
    }
    
    exit;
}
?>