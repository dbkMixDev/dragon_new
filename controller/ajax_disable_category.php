<?php
session_start();
include '../include/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$username = $_SESSION['username'];
$category_name = $_POST['name'] ?? '';

if (empty($category_name)) {
    echo json_encode(['success' => false, 'error' => 'Category name is required']);
    exit;
}

try {
    // Debug: log input
    error_log("DISABLE_CATEGORY: username=$username, category_name=$category_name");

    // Cek apakah category name dipakai di tabel playstations
    $ps_check_sql = "SELECT 1 FROM playstations WHERE userx = ? AND type_ps = ?";
    $ps_check_stmt = $con->prepare($ps_check_sql);
    $ps_check_stmt->bind_param("ss", $username, $category_name);
    $ps_check_stmt->execute();
    $ps_check_stmt->store_result();

    if ($ps_check_stmt->num_rows > 0) {
        // Sudah dipakai, tidak boleh dinonaktifkan
        echo json_encode(['success' => false, 'error' => 'Kategori sedang dipakai di rental.']);
        $ps_check_stmt->close();
        exit;
    }
    $ps_check_stmt->close();

    // Cek override userx+name (case-insensitive)
    $check_sql = "SELECT id_category FROM tb_category WHERE LOWER(name) = LOWER(?) AND userx = ?";
    $check_stmt = $con->prepare($check_sql);
    $category_name_lc = strtolower($category_name);
    $check_stmt->bind_param("ss", $category_name_lc, $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing override to disable
        $update_sql = "UPDATE tb_category SET status = 'disable' WHERE LOWER(name) = LOWER(?) AND userx = ?";
        $update_stmt = $con->prepare($update_sql);
        $update_stmt->bind_param("ss", $category_name_lc, $username);

        if ($update_stmt->execute()) {
            error_log("DISABLE_CATEGORY: updated $category_name for $username");
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil dinonaktifkan.']);
        } else {
            error_log("DISABLE_CATEGORY: update failed - " . $update_stmt->error);
            echo json_encode(['success' => false, 'error' => 'Gagal update status kategori.']);
        }
        $update_stmt->close();
    } else {
        // Insert new override with disable status
        $insert_sql = "INSERT INTO tb_category (name, status, userx) VALUES (?, 'disable', ?)";
        $insert_stmt = $con->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $category_name, $username);

        if ($insert_stmt->execute()) {
            error_log("DISABLE_CATEGORY: inserted $category_name for $username");
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil dinonaktifkan.']);
        } else {
            error_log("DISABLE_CATEGORY: insert failed - " . $insert_stmt->error);
            echo json_encode(['success' => false, 'error' => 'Gagal insert override kategori.']);
        }
        $insert_stmt->close();
    }

    $check_stmt->close();

} catch (Exception $e) {
    error_log("DISABLE_CATEGORY: exception - " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$con->close();
?>