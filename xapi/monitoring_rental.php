<?php
// FIXED: Set headers lebih awal dan lebih lengkap
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, User-Agent, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // Cache preflight untuk 24 jam

// FIXED: Disable any output buffering yang bisa ganggu response
if (ob_get_level())
    ob_end_clean();

require_once '../include/config.php'; // koneksi ke DB

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// FIXED: Function untuk safely get input data dengan multiple methods
function getInputData()
{
    $ping_data = null;

    // Method 1: Try standard JSON POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        // FIXED: Be more lenient with Content-Type checking
        if (
            stripos($content_type, 'json') !== false ||
            stripos($content_type, 'text/plain') !== false ||
            empty($content_type)
        ) {

            $input = file_get_contents('php://input');
            if (!empty($input)) {
                // Try to decode JSON
                $decoded = json_decode($input, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['ping_status'])) {
                    $ping_data = $decoded['ping_status'];
                }
            }
        }

        // Method 2: Try form-encoded POST
        if (!$ping_data && !empty($_POST['ping_status'])) {
            $ping_data = json_decode($_POST['ping_status'], true);
        }
    }

    // Method 3: Try GET parameter as fallback
    if (!$ping_data && !empty($_GET['ping_status'])) {
        $ping_data = json_decode(urldecode($_GET['ping_status']), true);
    }

    // Method 4: Check for ping_fallback mode (GET with encoded data)
    if (!$ping_data && isset($_GET['ping_fallback']) && !empty($_GET['p'])) {
        $ping_data = json_decode(base64_decode($_GET['p']), true);
    }

    return $ping_data;
}

// FIXED: Escape function untuk prevent SQL injection
function escape($con, $str)
{
    return $con->real_escape_string($str);
}

// Ambil ID dari query string dengan sanitasi
$id = isset($_GET['id']) ? escape($con, $_GET['id']) : null;

// FIXED: Validate ID format (misal harus alphanumeric)
if (!$id || !preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid license format',
        'client' => null,
        'devices' => []
    ], JSON_PRETTY_PRINT);
    exit;
}

// Get ping data dengan multiple methods
$ping_data = getInputData();

// FIXED: Use prepared statement untuk security
$stmt = $con->prepare("SELECT * FROM userx WHERE license = ? AND level = 'admin' LIMIT 1");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

$merchand = $username = $license = $timezone = $license_exp = null;
if ($row = $result->fetch_assoc()) {
    $merchand = $row['merchand'];
    $username = $row['username'];
    $license = $row['license'];
    $timezone = $row['timezone'] ?? 'Asia/Jakarta'; // Default timezone
    $license_exp = $row['license_exp'];
}
$stmt->close();

// Set timezone dengan fallback
if (!empty($timezone)) {
    date_default_timezone_set($timezone);
} else {
    date_default_timezone_set('Asia/Jakarta');
}

// Jika user tidak ditemukan
if (!$username) {
    http_response_code(404);
    echo json_encode([
        'error' => 'License not found',
        'client' => null,
        'exp' => null,
        'total_unit' => 0,
        'active_unit' => 0,
        'stop_unit' => 0,
        'devices' => [],
        'last_update' => date('Y-m-d H:i:s'),
    ], JSON_PRETTY_PRINT);
    exit;
}

// Update status_device HANYA untuk device yang ping online
$ping_updated_count = 0;
if ($ping_data && is_array($ping_data)) {
    $current_time = date('Y-m-d H:i:s');

    // FIXED: Use prepared statement untuk update
    $update_stmt = $con->prepare("UPDATE playstations 
                                  SET status_device = ? 
                                  WHERE no_ps = ? AND userx = ?");

    foreach ($ping_data as $unit_no => $ping_status) {
        if ($ping_status === 'online' && is_numeric($unit_no)) {
            $update_stmt->bind_param("sis", $current_time, $unit_no, $username);
            $update_stmt->execute();
            if ($update_stmt->affected_rows > 0) {
                $ping_updated_count++;
            }
        }
    }
    $update_stmt->close();
}

// FIXED: Use prepared statement untuk query devices
$stmt = $con->prepare("SELECT * FROM playstations 
                       WHERE userx = ? 
                       AND type_modul IN ('ANDROID TV', 'GOOGLE TV') 
                       ORDER BY no_ps ASC");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$devices = [];
$total_unit = 0;
$active_unit = 0;
$stop_unit = 0;
$now = time();

while ($row = $result->fetch_assoc()) {
    $start = strtotime($row['start_time']);
    $stop = strtotime($row['end_time']);
    $pause_time = $row['pause_time'];
    $status = $row['status'];
    $on_layar = ($status == 'available') ? 0 : 1;
    $status_device = $row['status_device'];

    // Hitung remaining time
    if ($status === 'paused' && !empty($pause_time)) {
        $pause_ref = strtotime($pause_time);
        $sisa = ($stop > $pause_ref) ? ($stop - $pause_ref) : 0;
    } else {
        $sisa = ($stop > $now) ? ($stop - $now) : 0;
    }

    // Tentukan ping status berdasarkan data yang dikirim client
    $ping_status = 'unknown';
    if ($ping_data && isset($ping_data[$row['no_ps']])) {
        $ping_status = $ping_data[$row['no_ps']];
    }

    // Hitung unit aktif
    $aktif = ($status !== 'available' && $sisa > 0) ? 1 : 0;
    if ($aktif) {
        $active_unit++;
    } else {
        $stop_unit++;
    }

    $devices[] = [
        'unit_no' => (int) $row['no_ps'],
        'ip' => $row['id_usb'] ?? 'N/A',
        'start' => $row['start_time'],
        'stop' => $row['end_time'],
        'status' => $status,
        'on_layar' => $on_layar,
        'remaining' => (int) $sisa,
        'type_modul' => $row['type_modul'] ?? 'unknown',
        'status_device' => $status_device,
        'ping_status' => $ping_status
    ];

    $total_unit++;
}
$stmt->close();

// FIXED: Log untuk debugging (optional - comment out in production)
if (isset($_GET['debug'])) {
    error_log("DragonPlay API - User: $username, Method: {$_SERVER['REQUEST_METHOD']}, Ping Data: " .
        ($ping_data ? 'Yes' : 'No') . ", Updated: $ping_updated_count");
}

// Response JSON dengan error handling
$response = [
    'client' => $merchand,
    'exp' => $license_exp,
    'total_unit' => $total_unit,
    'active_unit' => $active_unit,
    'stop_unit' => $stop_unit,
    'devices' => $devices,
    'last_update' => date('Y-m-d H:i:s'),
    'ping_updated' => $ping_updated_count,
    'api_version' => '2.1' // Version tracking
];

// FIXED: Ensure clean JSON output
$json_output = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if ($json_output === false) {
    http_response_code(500);
    echo json_encode(['error' => 'JSON encoding failed'], JSON_PRETTY_PRINT);
} else {
    echo $json_output;
}

$con->close();

// FIXED: Ensure no additional output
exit();
?>