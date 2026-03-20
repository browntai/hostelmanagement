<?php
/**
 * localhost simulation tool to fake a callback response to `api/mpesa-callback.php`
 * This is used to test the STK Push automation flow when running locally without a public IP
 */
include('../includes/dbconn.php');

if (!isset($_GET['checkoutRequestID'])) {
    die("Error: No CheckoutRequestID provided");
}

$checkoutRequestID = $_GET['checkoutRequestID'];
$amount = $_GET['amount'] ?? 1000;
$phone = $_GET['phone'] ?? '254700000000';
$receipt = 'MK' . rand(100, 999) . 'A' . rand(10, 99) . 'FTX'; // Fake receipt no

// Create mock Daraja payload
$mockPayload = json_encode([
    'Body' => [
        'stkCallback' => [
            'MerchantRequestID' => '29115-34620561-1',
            'CheckoutRequestID' => $checkoutRequestID,
            'ResultCode' => 0,
            'ResultDesc' => 'The service request is processed successfully.',
            'CallbackMetadata' => [
                'Item' => [
                    ['Name' => 'Amount', 'Value' => $amount],
                    ['Name' => 'MpesaReceiptNumber', 'Value' => $receipt],
                    ['Name' => 'Balance', 'Value' => null],
                    ['Name' => 'TransactionDate', 'Value' => date('YmdHis')],
                    ['Name' => 'PhoneNumber', 'Value' => $phone]
                ]
            ]
        ]
    ]
]);

// Send the fake payload locally using cURL
$ch = curl_init('http://localhost/HostelManagement-PHP/api/mpesa-callback.php');
curl_setopt($ch, CURLOPT_POSTFIELDS, $mockPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);

// Redirect user back to dashboard or payments
session_start();
$_SESSION['msg'] = "M-Pesa payment simulated successfully! Receipt ID: $receipt";
header("Location: make-payment.php");
exit();
?>
