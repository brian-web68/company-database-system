<?php
session_start();
include "../db.php";

// Redirect if not logged in
if (!isset($_SESSION['full_name'])) {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";
$user_id = $_SESSION['user_id']; // Using the specific ID key we set in login.php

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = trim($_POST['description']);

    if (!empty($description)) {
        $stmt = $conn->prepare("INSERT INTO problems (user_id, description, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("is", $user_id, $description);

        if ($stmt->execute()) {
            $success = "Your complaint has been submitted successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Please provide a description of the problem.";
    }
}

// Fetch user complaints using a prepared statement for security
$stmt = $conn->prepare("SELECT id, description, status, created_at FROM problems WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$complaints = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints & Feedback - Estate Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .status-pending { color: #fd7e14; font-weight: bold; }
        .status-resolved { color: #198754; font-weight: bold; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-chat-left-dots text-primary me-2"></i> Complaints & Feedback</h2>
                <a href="../dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">Submit a New Complaint</h5>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success border-0 shadow-sm"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if($error): ?>
                        <div class="alert alert-danger border-0 shadow-sm"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Describe the issue in detail:</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="e.g. Water leakage in Block B, or noisy neighbors..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-send me-2"></i> Submit Complaint
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Your Complaint History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">ID</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Date Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($complaints->num_rows > 0): ?>
                                    <?php while($row = $complaints->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-4 text-muted">#<?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td>
                                            <?php 
                                                $status = strtolower($row['status']);
                                                $badge_class = ($status == 'pending') ? 'bg-warning text-dark' : 'bg-success';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted">
                                            <?php echo date("d M Y, H:i", strtotime($row['created_at'])); ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">You haven't submitted any complaints yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>