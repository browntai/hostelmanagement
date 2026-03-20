<?php
/**
 * ShimaHome M-Pesa Daraja API Helper
 * Includes functions for STK Push and Callback handling.
 * Note: Configured with Sandbox values and a simulator mode for localhost.
 */

// Daraja API Configuration (Sandbox)
define('MPESA_ENV', 'sandbox'); // sandbox or live
define('MPESA_CONSUMER_KEY', 'YOUR_CONSUMER_KEY'); // Replace with Daraja App Consumer Key
define('MPESA_CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET'); // Replace with Daraja App Consumer Secret
define('MPESA_SHORTCODE', '174379'); // Sandbox Paybill
define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'); // Sandbox Passkey

// Base URL based on environment
$mpesaBaseUrl = (MPESA_ENV == 'sandbox') 
    ? 'https://sandbox.safaricom.co.ke'
    : 'https://api.safaricom.co.ke';

/**
 * Generate M-Pesa Access Token
 */
function getMpesaAccessToken() {
    global $mpesaBaseUrl;
    $url = $mpesaBaseUrl . '/oauth/v1/generate?grant_type=client_credentials';
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($status == 200) {
        $json = json_decode($result);
        return $json->access_token;
    }
    return null;
}

/**
 * Initiate STK Push (Express)
 */
function initiateStkPush($phone, $amount, $reference, $description) {
    global $mpesaBaseUrl;
    
    // In a real environment, you'd use a public URL. 
    // For localhost testing, we use a fake URL but provide a simulator to trigger callbacks manually.
    $callbackUrl = "https://yourdomain.com/api/mpesa-callback.php"; 
    
    // Format phone number to 2547XXXXXXXX
    $phone = preg_replace('/^0/', '254', $phone);
    $phone = preg_replace('/^\+/', '', $phone);
    
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
    
    // SIMULATION MODE FOR LOCALHOST:
    // If we're on localhost, we don't actually call Daraja because the callback would fail.
    // Instead, we return a mock successful response so the UI can proceed.
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
        // Log the mock attempt
        error_log("MOCK STK PUSH: Phone $phone, Amount $amount, Ref $reference");
        
        return [
            'success' => true,
            'simulated' => true,
            'CheckoutRequestID' => 'ws_CO_' . date('dmYHis') . rand(100, 999),
            'MerchantRequestID' => '29115-34620561-1',
            'ResponseCode' => '0',
            'CustomerMessage' => 'Success. Request accepted for processing (Mock)'
        ];
    }
    
    // Real Daraja API Call
    $token = getMpesaAccessToken();
    if (!$token) return ['success' => false, 'error' => 'Failed to get access token'];
    
    $url = $mpesaBaseUrl . '/mpesa/stkpush/v1/processrequest';
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $token));
    
    $curl_post_data = array(
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => MPESA_SHORTCODE,
        'PhoneNumber' => $phone,
        'CallBackURL' => $callbackUrl,
        'AccountReference' => $reference,
        'TransactionDesc' => $description
    );
    
    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $curl_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($status == 200) {
        $res = json_decode($curl_response, true);
        $res['success'] = true;
        return $res;
    } else {
        return ['success' => false, 'error' => 'STK Push failed', 'response' => $curl_response];
    }
}
?>
