<?php
/**
 * M-Pesa Daraja Callback Webhook
 * Daraja will send a POST request with JSON payload to this endpoint
 * after a successful or failed STK push transaction.
 */

include('../includes/dbconn.php');

// Read JSON payload from Daraja
$payload = file_get_contents('php://input');
if (!$payload) {
    http_response_code(400);
    echo "No payload";
    exit;
}

$data = json_decode($payload, true);
file_put_contents('../logs/mpesa_' . date('Y-m-d_H-i-s') . '.log', $payload); // Audit log

if (isset($data['Body']['stkCallback'])) {
    $callback = $data['Body']['stkCallback'];
    $merchantRequestID = $callback['MerchantRequestID'];
    $checkoutRequestID = $callback['CheckoutRequestID'];
    $resultCode = $callback['ResultCode'];
    $resultDesc = $callback['ResultDesc'];

    if ($resultCode == 0) {
        // Success
        $items = $callback['CallbackMetadata']['Item'];
        $amount = 0;
        $mpesaReceiptNumber = '';
        $phone = '';
        
        foreach ($items as $item) {
            if ($item['Name'] == 'Amount') $amount = $item['Value'];
            if ($item['Name'] == 'MpesaReceiptNumber') $mpesaReceiptNumber = $item['Value'];
            if ($item['Name'] == 'PhoneNumber') $phone = $item['Value'];
        }

        // Verify we have a pending payment waiting for this checkout ID
        $stmt = $mysqli->prepare("SELECT id, booking_id, client_id, tenant_id FROM payments WHERE transaction_id = ? AND status='pending' LIMIT 1");
        $stmt->bind_param('s', $checkoutRequestID);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_object();

        if ($payment) {
            // Update the pending payment with the actual M-Pesa Receipt ID and set to verified
            $upd = $mysqli->prepare("UPDATE payments SET transaction_id = ?, status = 'verified', updated_at = NOW() WHERE id = ?");
            $upd->bind_param('si', $mpesaReceiptNumber, $payment->id);
            $upd->execute();
            
            // Note: We could notify the landlord or client via notification-helper here
            // include_once('../includes/notification-helper.php');
            // sendNotification($payment->client_id, 'Payment Verified', "Your payment ($mpesaReceiptNumber) of KSh $amount was verified.", $payment->tenant_id);
        }
    } else {
        // Failed / Cancelled by user
        // Mark pending payment as rejected
        $cancelStmt = $mysqli->prepare("UPDATE payments SET status = 'rejected' WHERE transaction_id = ?");
        $cancelStmt->bind_param('s', $checkoutRequestID);
        $cancelStmt->execute();
    }
}

// Acknowledge receipt
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
?>
