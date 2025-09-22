<?php
include 'config.php';

$username = 'admin';
$pass = password_hash('123456', PASSWORD_DEFAULT);
$merchand = 'Dragon Play';
$level = 'admin';
$cabang = 'OWNER';
$status = 1;
$license = 'LIC-Xdrkm0001';

$sql = "INSERT INTO userx (usernames, pass, merchands, level, cabang, status, license)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $con->prepare($sql);
$stmt->bind_param("sssssis", $username, $pass, $merchand, $level, $cabang, $status, $license);
$stmt->execute();

?>