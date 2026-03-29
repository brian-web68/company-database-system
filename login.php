<?php
session_start();
include "db.php"; 

// Force errors to show for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    // 1. Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // 2. Verify Password (Hashed or Plain Text)
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            
            // 3. SUCCESS - Create Sessions
            // We set 'full_name' specifically because dashboard.php requires it
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user'] = $user; // Keeps the whole array for backup
            
            // 4. Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password does not match our records.";
        }
    } else {
        $error = "Phone number not found in database.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Estate Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; }
        .login-card { border-radius: 15px; margin-top: 100px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card p-4 shadow-sm border-0 login-card">
                <h3 class="text-center fw-bold mb-3">Estate Login</h3>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger small text-center"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 0706493316" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="pass" class="form-control" required>
                            <span class="input-group-text" style="cursor:pointer;" onclick="toggle()">
                                <i class="bi bi-eye" id="eye"></i>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Login to System</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggle() {
    var x = document.getElementById("pass");
    var y = document.getElementById("eye");
    if (x.type === "password") {
        x.type = "text";
        y.classList.replace("bi-eye", "bi-eye-slash");
    } else {
        x.type = "password";
        y.classList.replace("bi-eye-slash", "bi-eye");
    }
}
</script>
</body>
</html>