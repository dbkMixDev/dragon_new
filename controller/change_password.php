<?php
session_start();
require_once '../include/config.php';
require_once '../include/crypto.php'; // Jika menggunakan custom encryption

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expired. Please login again.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'change_password') {
    try {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $username = $_SESSION['username'];

        $errors = [];

        if (empty($current_password)) {
            $errors[] = 'Current password is required';
        }

        if (empty($new_password)) {
            $errors[] = 'New password is required';
        }

        if (strlen($new_password) < 8) {
            $errors[] = 'New password must be at least 8 characters';
        }

        if (!isPasswordStrong($new_password)) {
            $errors[] = 'Password must contain at least: 1 uppercase letter, 1 lowercase letter, 1 number, and be at least 8 characters long';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors)
            ]);
            exit;
        }

        $stmt = $con->prepare("SELECT pass FROM userx WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
            exit;
        }

        $stmt->bind_result($stored_password);
        $stmt->fetch();

        $is_current_password_valid = false;

        if (password_verify($current_password, $stored_password)) {
            $is_current_password_valid = true;
        } else if (md5($current_password) === $stored_password) {
            $is_current_password_valid = true;
        } else if (function_exists('encrypt') && encrypt($current_password) === $stored_password) {
            $is_current_password_valid = true;
        } else if ($current_password === $stored_password) {
            $is_current_password_valid = true;
        }

        if (!$is_current_password_valid) {
            echo json_encode([
                'success' => false,
                'message' => 'Current password is incorrect',
                'field_error' => [
                    'field' => 'currentPassword',
                    'message' => 'Current password is incorrect'
                ]
            ]);
            exit;
        }

        if ($current_password === $new_password) {
            echo json_encode([
                'success' => false,
                'message' => 'New password must be different from current password',
                'field_error' => [
                    'field' => 'newPassword',
                    'message' => 'New password must be different from current password'
                ]
            ]);
            exit;
        }

        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update_stmt = $con->prepare("UPDATE userx SET pass = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $hashed_new_password, $username);

        if ($update_stmt->execute()) {
            logPasswordChange($username, $_SERVER['REMOTE_ADDR']);

            echo json_encode([
                'success' => true,
                'message' => 'Password successfully changed'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update password. Please try again.'
            ]);
        }

    } catch (Exception $e) {
        error_log('Change Password Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while changing password. Please try again.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
}

function isPasswordStrong($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    return true;
}

function logPasswordChange($username, $ip_address) {
    global $con;

    try {
        $create_log_table = "
            CREATE TABLE IF NOT EXISTS password_change_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                change_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                user_agent TEXT
            )
        ";
        $con->query($create_log_table);

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $log_stmt = $con->prepare("INSERT INTO password_change_logs (username, ip_address, user_agent) VALUES (?, ?, ?)");
        $log_stmt->bind_param("sss", $username, $ip_address, $user_agent);
        $log_stmt->execute();

    } catch (Exception $e) {
        error_log('Password Change Logging Error: ' . $e->getMessage());
    }
}
?>
