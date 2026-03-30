<?php
// Configuration
$consumerKey = 'YOUR_CONSUMER_KEY'; 
$consumerSecret = 'YOUR_CONSUMER_SECRET';
$BusinessShortCode = '174379'; // Sandbox Paybill
$Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$PartyA = '2547XXXXXXXX'; // Tenant Phone Number
$AccountReference = 'EstateSystem';
$TransactionDesc = 'Rent Payment';
$Amount = '1'; // Amount to test
$Timestamp = date('YmdHis');
$Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);

// 1. Get Access Token
$headers = ['Content-Type:application/json; charset=utf8'];
$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_HEADER, FALSE);
curl_setopt($curl, CURLOPT_USERPWD, $consumerKey.':'.$consumerSecret);
$result = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$result = json_decode($result);
$access_token = $result->access_token;

// 2. Initiate STK Push
$stk_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/query'; // For production use 'v1/processrequest'
$curl_post_data = [
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $Amount,
    'PartyA' => $PartyA,
    'PartyB' => $BusinessShortCode,
    'PhoneNumber' => $PartyA,
    'CallBackURL' => 'https://yourdomain.com/pages/mpesa_callback.php',
    'AccountReference' => $AccountReference,
    'TransactionDesc' => $TransactionDesc
];

$data_string = json_encode($curl_post_data);
$curl = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$access_token));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
$curl_response = curl_exec($curl);

echo $curl_response;
?>