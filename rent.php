<?php
session_start();
include "../db.php";

// 1. Initialize variables to prevent "Undefined variable" errors
$message = ""; 

if (!isset($_SESSION['full_name'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenant_id = $_POST['tenant_id'];
    $amount = $_POST['amount'];
    $method = $_POST['payment_method'];
    $month = $_POST['rent_month'] . " " . date("Y");
    $date = date("Y-m-d");

    // Fetch Tenant Phone
    $user_query = $conn->prepare("SELECT phone FROM users WHERE id = ?");
    $user_query->bind_param("i", $tenant_id);
    $user_query->execute();
    $user_data = $user_query->get_result()->fetch_assoc();
    $raw_phone = $user_data['phone'];

    // Format Phone to 254XXXXXXXXX
    $phone = preg_replace('/^0/', '254', $raw_phone);
    if (strlen($phone) < 12) { $phone = "254" . $phone; }

    if ($method == 'mpesa') {
        // --- SAFARICOM BRIDGE ---
        $consumerKey = 'YOUR_CONSUMER_KEY'; 
        $consumerSecret = 'YOUR_CONSUMER_SECRET';
        $BusinessShortCode = '174379'; 
        $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
        $Timestamp = date('YmdHis');
        $Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);

        // Access Token
        $headers = ['Content-Type:application/json; charset=utf8'];
        $curl = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_USERPWD, $consumerKey.':'.$consumerSecret);
        $result = json_decode(curl_exec($curl));
        $access_token = $result->access_token;

        // STK Push Request
        $stk_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $curl_post_data = [
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $Password,
            'Timestamp' => $Timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $BusinessShortCode,
            'PhoneNumber' => $phone,
            'CallBackURL' => 'https://yourdomain.com/callback.php', 
            'AccountReference' => 'RentPayment',
            'TransactionDesc' => 'Rent'
        ];

        $curl = curl_init($stk_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization:Bearer '.$access_token, 'Content-Type:application/json']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($curl));

        if(isset($response->ResponseCode) && $response->ResponseCode == "0"){
            $checkout_id = $response->CheckoutRequestID;
            $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, rent_month, payment_type, payment_date, checkout_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idssss", $tenant_id, $amount, $month, $method, $date, $checkout_id);
            $stmt->execute();
            $message = "<div class='alert alert-success'>STK Push sent! Ask tenant to enter PIN on phone $phone.</div>";
        } else {
            $message = "<div class='alert alert-danger'>M-Pesa Error: " . ($response->errorMessage ?? "Connection failed") . "</div>";
        }

    } else {
        // Cash Logic
        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, rent_month, payment_type, payment_date) VALUES (?, ?, ?, 'cash', ?)");
        $stmt->bind_param("idss", $tenant_id, $amount, $month, $date);
        if ($stmt->execute()) {
            header("Location: receipt.php?id=" . $conn->insert_id . "&success=1");
            exit();
        }
    }
}

$tenants = $conn->query("SELECT id, full_name, phone FROM users ORDER BY full_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 card p-4 shadow-sm">
            <h3 class="mb-4">Record Rent Payment</h3>
            
            <?php echo $message; ?>

            <form method="POST">
                <div class="mb-3">
                    <label>Select Tenant</label>
                    <select name="tenant_id" class="form-select" required>
                        <option value="">-- Choose --</option>
                        <?php while($t = $tenants->fetch_assoc()): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo $t['full_name']; ?> (<?php echo $t['phone']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Amount (Ksh)</label>
                    <input type="number" name="amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Month</label>
                    <select name="rent_month" class="form-select">
                        <option value="<?php echo date('F'); ?>"><?php echo date('F'); ?></option>
                        <option value="January">January</option>
                        <option value="February">February</option>
                        <option value="March">March</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="d-block">Method</label>
                    <input type="radio" name="payment_method" value="cash" checked> Cash
                    <input type="radio" name="payment_method" value="mpesa" class="ms-3"> M-Pesa
                </div>
                <button type="submit" class="btn btn-primary w-100">Process Payment</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>