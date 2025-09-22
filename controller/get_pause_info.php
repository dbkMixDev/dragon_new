 <?php
 include '../include/config.php';
   header('Content-Type: application/json');

if (isset($_POST['no_ps'], $_POST['type_ps'], $_POST['username'])) {
    $no_ps = $_POST['no_ps'];
    $type_ps = $_POST['type_ps'];
    $username = $_POST['username'];

    $res = mysqli_query($con, "SELECT pause_time, end_time FROM playstations WHERE no_ps='$no_ps' AND type_ps='$type_ps' AND userx='$username' LIMIT 1");

    $row = mysqli_fetch_assoc($res);

    echo json_encode([
        'success' => true,
        'pause_time' => $row['pause_time'],
        'end_time' => $row['end_time']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Missing parameters'
    ]);
}
exit;
?>