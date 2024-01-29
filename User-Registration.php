<?php
// Define your database connection parameters
$host = 'localhost';
$dbname = 'rentalradar';
$username = 'root';
$password = '';

// Establish a connection to the database using PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $firstName = $_POST['firstName'];
    $surname = $_POST['surname'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate form data (you can add more validation if needed)

    // Check if passwords match
    if ($password !== $confirmPassword) {
        die("Passwords do not match");
    }

    // Hash the password securely using bcrypt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user data into the database
    $stmt = $pdo->prepare("INSERT INTO users (first_name, surname, last_name, email, password) VALUES (?, ?, ?, ?, ?)");

    try {
        $stmt->execute([$firstName, $surname, $lastName, $email, $hashedPassword]);
        echo "Registration successful!";
    } catch (PDOException $e) {
        die("Registration failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style>
        body {
            background-color: #e6e6e6;
            padding-top: 56px;
            /* Adjusted to account for the fixed navbar */
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .container {
            margin-top: 80px;
            /* Adjusted to provide space below the fixed navbar */
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e6e6e6;">
        <a class="navbar-brand" href="#">RentalRadar</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">About</a>
                </li>
            </ul>
        </div>
        <div class="navbar-nav ml-auto">
            <a class="nav-item nav-link btn btn-dark" href="#" style="color: white;">Contact Us</a>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        User Registration
                    </div>
                    <div class="card-body">
                        <!-- Alert for displaying errors -->
                        <div id="errorAlert" class="alert alert-danger" style="display: none;"></div>

                        <form id="registrationForm" action="User-Registration.php" method="post">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            <div class="form-group">
                                <label for="surname">Surname</label>
                                <input type="text" class="form-control" id="surname" name="surname" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var registrationForm = document.getElementById('registrationForm');
            var errorAlert = document.getElementById('errorAlert');

            registrationForm.addEventListener('submit', function (event) {
                var isValid = true;
                errorAlert.innerHTML = ''; // Clear previous error messages

                if (!validateEmail()) {
                    isValid = false;
                }

                if (!validatePassword()) {
                    isValid = false;
                }

                if (!validateConfirmPassword()) {
                    isValid = false;
                }

                if (!isValid) {
                    event.preventDefault();
                    errorAlert.style.display = 'block';
                } else {
                    errorAlert.style.display = 'none';
                }
            });

            function validateEmail() {
                var emailInput = document.getElementById('email');
                var emailValue = emailInput.value.trim();
                var emailError = document.getElementById('emailError');

                // Basic email validation
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailValue)) {
                    errorAlert.innerHTML += 'Please enter a valid email address.<br>';
                    return false;
                }

                return true;
            }

            function validatePassword() {
                var passwordInput = document.getElementById('password');
                var passwordValue = passwordInput.value.trim();

                // Basic password validation (minimum length)
                if (passwordValue.length < 6) {
                    errorAlert.innerHTML += 'Password must be at least 6 characters long.<br>';
                    return false;
                }

                return true;
            }

            function validateConfirmPassword() {
                var passwordInput = document.getElementById('password');
                var confirmPasswordInput = document.getElementById('confirmPassword');
                var confirmPasswordValue = confirmPasswordInput.value.trim();

                // Check if passwords match
                if (passwordInput.value !== confirmPasswordValue) {
                    errorAlert.innerHTML += 'Passwords do not match.<br>';
                    return false;
                }

                return true;
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>
    <!-- Footer -->
    <footer class="footer mt-auto py-3 text-center">
        <div class="container">
            <span class="text-muted">Copyright &copy; 2024 RentalRadar. All rights reserved.</span>
        </div>
    </footer>

</body>

</html>