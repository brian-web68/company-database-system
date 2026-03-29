<?php
session_start();
include "../db.php"; 

// 1. Security Check: Redirect if not logged in
if (!isset($_SESSION['full_name'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<div class='container mt-5 alert alert-danger'>Error: A valid Receipt ID is required.</div>");
}

$payment_id = intval($_GET['id']);

// 3. Fetch payment details using Prepared Statement
$stmt = $conn->prepare("
    SELECT p.*, u.full_name 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    die("<div class='container mt-5 alert alert-danger'>Error: Receipt not found in the system.</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt_#<?php echo $payment['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .receipt-card {
            max-width: 480px;
            margin: 40px auto;
            background: #fff;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .receipt-header {
            background: #0d6efd;
            color: white;
            padding: 25px;
            text-align: center;
        }
        .receipt-body { padding: 30px; }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }
        .info-label { color: #6c757d; }
        .info-value { font-weight: 600; color: #212529; }
        .amount-box {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }
        .amount-box h2 { color: #198754; font-weight: 800; margin: 0; }
        
        /* PRINT OPTIMIZATION */
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            .receipt-card { margin: 0 auto; box-shadow: none; border: 1px solid #ddd; }
            .receipt-header { background: #000 !important; color: #fff !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success mt-3 no-print text-center mx-auto" style="max-width: 480px;">
        <i class="bi bi-check-circle-fill me-2"></i> Payment Recorded Successfully!
    </div>
    <?php endif; ?>

    <div class="receipt-card">
        <div class="receipt-header">
            <h4 class="mb-0 fw-bold">ESTATE PRO</h4>
            <small>Official Transaction Receipt</small>
        </div>

        <div class="receipt-body">
            <div class="info-row">
                <span class="info-label">Receipt Number</span>
                <span class="info-value">#<?php echo $payment['id']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date Processed</span>
                <span class="info-value"><?php echo date("d-M-Y", strtotime($payment['payment_date'])); ?></span>
            </div>
            
            <hr>

            <div class="info-row">
                <span class="info-label">Member Name</span>
                <span class="info-value"><?php echo htmlspecialchars($payment['full_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Category</span>
                <span class="info-value text-uppercase"><?php echo htmlspecialchars($payment['payment_type']); ?></span>
            </div>
            
            <?php if(!empty($payment['rent_month'])): ?>
            <div class="info-row">
                <span class="info-label">For Period</span>
                <span class="info-value"><?php echo htmlspecialchars($payment['rent_month']); ?></span>
            </div>
            <?php endif; ?>

            <div class="amount-box">
                <small class="text-muted text-uppercase fw-bold">Total Amount Paid</small>
                <h2>KES <?php echo number_format($payment['amount'], 2); ?></h2>
            </div>

            <div class="mt-4 text-center border-top pt-3">
                <p class="text-muted small mb-1 italic">Authorized by: <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                <p class="fw-bold text-primary mb-0">*** Thank You ***</p>
            </div>

            <div class="mt-4 d-grid gap-2 no-print">
                <button onclick="window.print()" class="btn btn-dark btn-lg">
                    <i class="bi bi-printer me-2"></i> Print Receipt
                </button>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="rent.php" class="btn btn-outline-primary w-100">Add New</a>
                    </div>
                    <div class="col-6">
                        <a href="../dashboard.php" class="btn btn-outline-secondary w-100">Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>