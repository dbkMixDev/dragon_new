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
    error_log("ENABLE_CATEGORY: username=$username, category_name=$category_name");

    // Hapus override userx+name (case-insensitive)
    $delete_sql = "DELETE FROM tb_category WHERE LOWER(name) = LOWER(?) AND userx = ?";
    $delete_stmt = $con->prepare($delete_sql);
    $category_name_lc = strtolower($category_name);
    $delete_stmt->bind_param("ss", $category_name_lc, $username);

    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            error_log("ENABLE_CATEGORY: deleted override $category_name for $username");
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil diaktifkan (override dihapus).']);
        } else {
            error_log("ENABLE_CATEGORY: no override found for $category_name $username");
            echo json_encode(['success' => true, 'message' => 'Kategori sudah aktif.']);
        }
    } else {
        error_log("ENABLE_CATEGORY: delete failed - " . $delete_stmt->error);
        echo json_encode(['success' => false, 'error' => 'Gagal menghapus override kategori.']);
    }

    $delete_stmt->close();

} catch (Exception $e) {
    error_log("ENABLE_CATEGORY: exception - " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$con->close();
?>