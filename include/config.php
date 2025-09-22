<?php
// ====================
// Database Configuration
// ====================
$servername_master = "localhost";
$database = "gtkalinf_drgnx";
$user = "root";
$password = "";

// Create mysqli connection
$con = new mysqli($servername_master, $user, $password, $database);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// ====================
// Global Constants
// ====================
define('ENCRYPTION_KEY', 'xz-UhysmLp34'); // Jangan ubah ini sembarangan!
// config.php
define('SMTP_PASSWORD', 'nullnullnull123123#');


?>
