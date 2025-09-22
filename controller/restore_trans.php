<?php
header('Content-Type: application/json');
session_start();
include '../include/config.php';

// VALIDASI ADMIN - PALING PENTING!
if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    echo json_encode([
        "success" => false,
        "message" => "Access denied. Admin level required."
    ]);
    exit;
}

$invoice = $_POST['invoice'] ?? '';
$userx = $_POST['userx'] ?? '';

 $r = $con->query("SELECT * 
FROM userx 
WHERE username ='$userx'");

                                        foreach ($r as $rr) {
                                            $merchand = $rr['merchand'];
                                             $address = $rr['address'];
                                              $logox = $rr['logox'];
                                              $timezone = $rr['timezone'];
                                        }
                                        
date_default_timezone_set($timezone);
  $dateTime = date('d M Y H:i:s');
if (empty($invoice) || empty($userx)) {
    echo json_encode([
        "success" => false,
        "message" => "Invoice dan user required"
    ]);
    exit;
}

// Start transaction
mysqli_autocommit($con, false);

try {
    $deleted_counts = [
        'rental' => 0,
        'fnb' => 0,
        'spending' => 0,
        'final' => 0
    ];
    
    // 1. Delete/soft delete rental transactions
    $queryRental = "UPDATE tb_trans SET is_deleted = NULL
                    WHERE inv = ? AND userx = ? AND (is_deleted = 1)";
    $stmtRental = mysqli_prepare($con, $queryRental);
    mysqli_stmt_bind_param($stmtRental, "ss", $invoice, $userx);
    
    if (mysqli_stmt_execute($stmtRental)) {
        $deleted_counts['rental'] = mysqli_stmt_affected_rows($stmtRental);
    } else {
        throw new Exception("Error deleting rental transactions: " . mysqli_error($con));
    }
    mysqli_stmt_close($stmtRental);
   


    // 2. Delete/soft delete FnB transactions
    $queryFnb = "UPDATE tb_trans_fnb SET is_deleted = NULL 
                 WHERE inv = ? AND userx = ? AND ( is_deleted = 1)";
    $stmtFnb = mysqli_prepare($con, $queryFnb);
    mysqli_stmt_bind_param($stmtFnb, "ss", $invoice, $userx);
    
    if (mysqli_stmt_execute($stmtFnb)) {
        $deleted_counts['fnb'] = mysqli_stmt_affected_rows($stmtFnb);
    } else {
        throw new Exception("Error deleting FnB transactions: " . mysqli_error($con));
    }
    mysqli_stmt_close($stmtFnb);
    
    // 3. Delete/soft delete spending transactions
    $querySpending = "UPDATE tb_trans_out SET is_deleted = NULL , userdel = NULL , datedel = NULL
                      WHERE invoice = ? AND userx = ? AND (is_deleted = 1)";
    $stmtSpending = mysqli_prepare($con, $querySpending);
    mysqli_stmt_bind_param($stmtSpending, "ss", $invoice, $userx);
    
    if (mysqli_stmt_execute($stmtSpending)) {
        $deleted_counts['spending'] = mysqli_stmt_affected_rows($stmtSpending);
    } else {
        throw new Exception("Error deleting spending transactions: " . mysqli_error($con));
    }
    mysqli_stmt_close($stmtSpending);
    
    // 4. Delete/soft delete final transaction
    $queryFinal = "UPDATE tb_trans_final SET is_deleted = NULL , userdel = NULL , datedel = NULL
                   WHERE invoice = ? AND userx = ? AND ( is_deleted = 1) ";
    $stmtFinal = mysqli_prepare($con, $queryFinal);
    mysqli_stmt_bind_param($stmtFinal, "ss", $invoice, $userx);
    
    if (mysqli_stmt_execute($stmtFinal)) {
        $deleted_counts['final'] = mysqli_stmt_affected_rows($stmtFinal);
    } else {
        throw new Exception("Error deleting final transaction: " . mysqli_error($con));
    }
    mysqli_stmt_close($stmtFinal);
    
    // Commit transaction
    mysqli_commit($con);
    
    // Check if anything was actually deleted
    $total_deleted = array_sum($deleted_counts);
    if ($total_deleted == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Tidak ada transaksi yang ditemukan untuk invoice: " . $invoice
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "Invoice berhasil dihapus",
            "deleted_counts" => $deleted_counts,
            "total_deleted" => $total_deleted,
            "invoice" => $invoice
        ]);
    }
    
} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($con);
    
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}

// Restore autocommit
mysqli_autocommit($con, true);
?>