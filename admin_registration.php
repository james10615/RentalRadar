<?php
session_start();

// Database connection
$servername = "localhost"; // Update if needed
$username = "root";        // Update with your DB username
$password = "";            // Update with your DB password
$dbname = "rentalradar";   // The name of your database

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

// Check if an admin already exists
$checkAdmin = "SELECT COUNT(*) AS admin_count FROM users WHERE role = 'admin'";
$result = $conn->query($checkAdmin);
$row = $result->fetch_assoc();
$adminExists = $row['admin_count'] > 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_number = trim($_POST['id_number']);
    $first_name = trim($_POST['first_name']);
    $surname = trim($_POST['surname']);
    $lastname = trim($_POST['lastname']);
    $phone_no = trim($_POST['phone_no']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if id_number or email already exists
        $sql = "SELECT id_number FROM users WHERE id_number = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $id_number, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "ID number or email is already taken.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert admin into database
            $sql = "INSERT INTO users (id_number, first_name, surname, lastname, phone_no, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $role = 'admin'; // Set role as admin
            $stmt->bind_param("ssssssss", $id_number, $first_name, $surname, $lastname, $phone_no, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $success = "Admin registered successfully.";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// If an admin exists, ensure only logged-in admins can access this page
if ($adminExists && (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true)) {
    header("Location: admin_login.php"); // Redirect to login page if not logged in
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalRadar - Admin Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .register-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2 class="text-center mb-4">Register New Admin</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div id="successMessage" class="alert alert-success">
                <?php echo $success; ?>
            </div>
            <script>
                // Show the success message for 1 second and then redirect
                setTimeout(function () {
                    window.location.href = 'admin_login.php';
                }, 3000); // Redirect after 1000 milliseconds (1 second)
            </script>
        <?php endif; ?>
        <?php if (!$success): ?>
            <form action="" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="id_number" class="form-label">Country ID Number</label>
                        <input type="text" name="id_number" class="form-control" id="id_number" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone_no" class="form-label">Phone Number</label>
                        <input type="text" name="phone_no" class="form-control" id="phone_no" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" id="first_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="surname" class="form-label">Surname</label>
                        <input type="text" name="surname" class="form-control" id="surname" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="lastname" class="form-label">Last Name</label>
                        <input type="text" name="lastname" class="form-control" id="lastname" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email (Optional)</label>
                        <input type="email" name="email" class="form-control" id="email">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>