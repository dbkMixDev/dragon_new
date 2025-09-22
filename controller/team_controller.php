<?php
// controller/team_controller.php
session_start();
require_once '../include/config.php';

// Set header JSON
header('Content-Type: application/json');

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$current_user = $_SESSION['username'];

// Ambil action dari GET atau POST
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_team':
            getTeamData();
            break;
        case 'get_user':
            getSingleUser();
            break;
        case 'add_user':
            addUser();
            break;
        case 'update_user':
            updateUser();
            break;
        case 'delete_user':
            deleteUser();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

/**
 * Dapatkan data seluruh tim (user dalam satu merchant, kecuali diri sendiri)
 */
function getTeamData() {
    global $con;

    $username = $_SESSION['username'];

    // Ambil merchant user saat ini
    $stmt = $con->prepare("SELECT merchand FROM userx WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'User data not found']);
        return;
    }

    $stmt->bind_result($merchant);
    $stmt->fetch();
    $stmt->close();

    // Ambil semua user dengan merchant yang sama kecuali diri sendiri
    $stmt = $con->prepare("
        SELECT id, username, email, level, merchand, cabang, status, logox, address, last_log, created_at
        FROM userx
        WHERE merchand = ? AND username != ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("ss", $merchant, $username);
    $stmt->execute();

    // Bind hasil
    $stmt->bind_result($id, $username_r, $email, $level, $merchand, $cabang, $status, $logox, $address, $last_log, $created_at);

    $users = [];
    while ($stmt->fetch()) {
        $users[] = [
            'id' => $id,
            'username' => $username_r,
            'email' => $email ?? '',
            'level' => $level ?: 'operator',
            'fullname' => $username_r,
            'status' => ($status == 1) ? 'active' : 'inactive',
            'avatar' => $logox ?? '',
            'address' => $address ?? '',
            'last_log' => $last_log,
            'created_at' => $created_at
        ];
    }
    $stmt->close();

    echo json_encode(['success' => true, 'users' => $users]);
}

/**
 * Ambil data satu user berdasar ID dan merchant sama dengan current user
 */
function getSingleUser() {
    global $con;

    $user_id = $_GET['id'] ?? '';
    if (!is_numeric($user_id)) {
        echo json_encode(['success' => false, 'message' => 'User ID tidak valid']);
        return;
    }
    $user_id = (int)$user_id;

    $username = $_SESSION['username'];

    // Ambil merchant user saat ini
    $stmt = $con->prepare("SELECT merchand FROM userx WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'User data not found']);
        return;
    }
    $stmt->bind_result($merchant);
    $stmt->fetch();
    $stmt->close();

    // Ambil data user sesuai id dan merchant
    $stmt = $con->prepare("
        SELECT id, username, email, level, status, address, logox
        FROM userx
        WHERE id = ? AND merchand = ?
    ");
    $stmt->bind_param("is", $user_id, $merchant);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        return;
    }

    $stmt->bind_result($id, $username_r, $email, $level, $status, $address, $logox);
    $stmt->fetch();
    $stmt->close();

    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $id,
            'username' => $username_r,
            'email' => $email,
            'level' => $level,
            'status' => ($status == 1) ? 'active' : 'inactive',
            'address' => $address,
            'avatar' => $logox ?: '',
            'fullname' => $username_r,
        ]
    ]);
}

/**
 * Tambah user baru dalam merchant yang sama dengan current user
 */
function addUser() {
    global $con;

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $level = $_POST['level'] ?? '';

    // Validasi input
    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'Username minimal 3 karakter']);
        return;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        return;
    }
    if (!in_array($level, ['operator', 'admin', 'manager'])) {
        echo json_encode(['success' => false, 'message' => 'Level tidak valid']);
        return;
    }

    $current_username = $_SESSION['username'];

    // Ambil data current user untuk turunan
    $stmt = $con->prepare("SELECT merchand, cabang, license, email, license_exp, address, logox,timezone FROM userx WHERE username = ?");
    $stmt->bind_param("s", $current_username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Current user data not found']);
        return;
    }
    $stmt->bind_result($merchand, $cabang, $license, $email, $license_exp, $address, $logox,$timezone);
    $stmt->fetch();
    $stmt->close();

    // Cek username sudah dipakai atau belum
    $stmt = $con->prepare("SELECT id FROM userx WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Username sudah digunakan oleh pengguna lain, coba lainnya']);
        return;
    }
    $stmt->close();

    // Batasi maksimal 3 operator per merchant
    $stmt = $con->prepare("SELECT COUNT(*) FROM userx WHERE level = 'operator' AND merchand = ?");
    $stmt->bind_param("s", $merchand);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    if ($total >= 3) {
        echo json_encode(['success' => false, 'message' => 'Anda sudah memiliki batas maksimal 3 operator']);
        return;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user baru
    $stmt = $con->prepare("
        INSERT INTO userx (username, email, pass, level, address, merchand, cabang, license, license_exp, status, created_at, logox,timezone)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), ?,?)
    ");
    $stmt->bind_param("sssssssssss", 
        $username,
        $email,
        $hashed_password,
        $level,
        $address,
        $merchand,
        $cabang,
        $license,
        $license_exp,
        $logox,
        $timezone
    );

    if ($stmt->execute()) {
        $stmt->close();
        error_log("New operator added: $username for merchant: $merchand");
        echo json_encode(['success' => true, 'message' => 'User berhasil ditambahkan']);
    } else {
        $error = $stmt->error;
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan user: ' . $error]);
    }
}

/**
 * Update data user
 */
function updateUser() {
    global $con;

    $user_id = $_POST['user_id'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $level = $_POST['level'] ?? '';
    $status = $_POST['status'] ?? 'active';

    if (!is_numeric($user_id)) {
        echo json_encode(['success' => false, 'message' => 'User ID tidak valid']);
        return;
    }
    $user_id = (int)$user_id;

    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'Username minimal 3 karakter']);
        return;
    }

    if (!empty($password) && strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        return;
    }

    if (!in_array($level, ['operator', 'admin', 'manager'])) {
        echo json_encode(['success' => false, 'message' => 'Level tidak valid']);
        return;
    }

    $current_username = $_SESSION['username'];

    // Ambil merchant current user
    $stmt = $con->prepare("SELECT merchand FROM userx WHERE username = ?");
    $stmt->bind_param("s", $current_username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        return;
    }
    $stmt->bind_result($merchant);
    $stmt->fetch();
    $stmt->close();

    // Pastikan user yang akan diupdate ada dan merchant sama
    $stmt = $con->prepare("SELECT username FROM userx WHERE id = ? AND merchand = ?");
    $stmt->bind_param("is", $user_id, $merchant);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        return;
    }
    $stmt->close();

    // Cek username sudah dipakai user lain (exclude self)
    $stmt = $con->prepare("SELECT id FROM userx WHERE username = ? AND id != ?");
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
        return;
    }
    $stmt->close();

    $status_int = ($status === 'active') ? 1 : 0;

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $con->prepare("
            UPDATE userx
            SET username = ?, pass = ?, level = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("sssii", $username, $hashed_password, $level, $status_int, $user_id);
    } else {
        $stmt = $con->prepare("
            UPDATE userx
            SET username = ?, level = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ssii", $username, $level, $status_int, $user_id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'User berhasil diperbarui']);
    } else {
        $error = $stmt->error;
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui user: ' . $error]);
    }
}

/**
 * Hapus user (kecuali diri sendiri)
 */
function deleteUser() {
    global $con;

    $user_id = $_POST['user_id'] ?? '';

    if (!is_numeric($user_id)) {
        echo json_encode(['success' => false, 'message' => 'User ID tidak valid']);
        return;
    }
    $user_id = (int)$user_id;

    $current_username = $_SESSION['username'];

    // Ambil merchant current user
    $stmt = $con->prepare("SELECT merchand FROM userx WHERE username = ?");
    $stmt->bind_param("s", $current_username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        return;
    }
    $stmt->bind_result($merchant);
    $stmt->fetch();
    $stmt->close();

    // Cek user ada, merchant sama, dan bukan user sendiri
    $stmt = $con->prepare("
        SELECT username FROM userx
        WHERE id = ? AND merchand = ? AND username != ?
    ");
    $stmt->bind_param("iss", $user_id, $merchant, $current_username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan atau tidak dapat dihapus']);
        return;
    }
    $stmt->close();

    // Delete user
    $stmt = $con->prepare("DELETE FROM userx WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
    } else {
        $error = $stmt->error;
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus user: ' . $error]);
    }
}

/**
 * Optional: Log aktivitas user
 */
function logActivity($action, $details) {
    global $con;

    try {
        $stmt = $con->prepare("UPDATE userx SET last_log = NOW() WHERE username = ?");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // ignore
    }

    error_log("[Team Management] User: " . $_SESSION['username'] . " | Action: $action | Details: $details");
}
?>
