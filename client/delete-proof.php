<?php
session_start();
include('../includes/dbconn.php');
include('../includes/check-login.php');
check_login();

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $userId = $_SESSION['id'];

    // Security Check: Ensure the payment belongs to the logged-in user and is not verified
    $query = "SELECT proof_file, status FROM payments WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ii', $id, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($res->num_rows > 0) {
        $row = $res->fetch_object();
        
        if($row->status == 'verified') {
            $_SESSION['msg'] = "Error: Verified payments cannot be deleted.";
        } else {
            // Delete the file if it exists
            if($row->proof_file) {
                $file_path = "../uploads/payments/" . $row->proof_file;
                if(file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Delete the payment record
            $delQuery = "DELETE FROM payments WHERE id = ?";
            $delStmt = $mysqli->prepare($delQuery);
            $delStmt->bind_param('i', $id);
            
            if($delStmt->execute()) {
                $_SESSION['msg'] = "Payment proof and record deleted successfully.";
            } else {
                $_SESSION['msg'] = "Error: Could not delete record from database.";
            }
        }
    } else {
        $_SESSION['msg'] = "Error: Record not found or unauthorized access.";
    }
} else {
    $_SESSION['msg'] = "Error: Invalid request.";
}

header("Location: make-payment.php");
exit();
?>
