<?php
session_start();
include "db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $house_number = trim($_POST['house_number']);
    // Securely hashing the password
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Default role = member (You can manually change to 'admin' in DB for yourself)
    $role = 'member';

    // 1. Check if phone already exists
    $check = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $check->bind_param("s", $phone);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "This phone number is already registered!";
    } else {
        // 2. Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (full_name, phone, house_number, role, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $phone, $house_number, $role, $password);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed: " . $stmt->error;
        }
        $stmt->close();
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Estate Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .reg-card { max-width: 450px; margin: 50px auto; border-radius: 15px; }
        .input-group-text { cursor: pointer; background: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="card reg-card shadow border-0">
        <div class="card-body p-4">
            <h3 class="text-center mb-3">Create Account</h3>
            <p class="text-center text-muted small">Join the Estate Management System</p>
            <hr>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="0700000000" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">House/Plot Number</label>
                    <input type="text" name="house_number" class="form-control" placeholder="Unit A1" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="regPassword" class="form-control" placeholder="Create password" required>
                        <span class="input-group-text" onclick="toggleRegPassword()">
                            <i class="bi bi-eye" id="regIcon"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Register Now</button>
            </form>

            <div class="text-center mt-3">
                <p class="small">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRegPassword() {
    const passwordField = document.getElementById('regPassword');
    const toggleIcon = document.getElementById('regIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}
</script>

</body>
</html>