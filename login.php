<?php
if (isset($_GET['registration']) && $_GET['registration'] === 'success') {
    echo '<p class="alert alert-success">Registration successful! You can now log in.</p>';
}

include_once('db_connection.php');

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];

        header('Location: dashboard.php');
        exit();
    } else {
        echo '<p class="alert alert-danger">Login failed. Please check your email and password.</p>';
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style>
        body {
            background-color: #e6e6e6;
            padding-top: 56px;

        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .container {
            margin-top: 80px;

        }

        .card {
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }

        .form-control {
            border-radius: 8px;
        }

        .card:hover {
            transform: scale(1.1);
        }

        .center-text {
            text-align: center;
        }

        .btn-center {
            display: block;
            margin: 0 auto;
        }

        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>

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
                    <a class="nav-link" href="User-Registration.php">Register</a>
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

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="text-center mt-0">
                        <img src="icons/account.png" alt="Icon" height="50"
                            style="position: absolute; left: 45%; transform: translatey(-60%); ">
                    </div>
                    <div class="card-header">
                        User Login
                    </div>
                    <div class="card-body">
                        <form id="loginForm" action="login.php" method="post">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Enter your email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="enter your password" required>
                            </div>
                            <button type="submit" class="btn btn-dark btn-center">Login</button>
                            <div class="forgot-password">
                                <a href="#">Forgot Password?</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="center-text mt-2">
                    <p>Don't have an account? <a href="User-Registration.php">Register now</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>

    <footer class="footer mt-auto py-3 text-center fixed-bottom">
        <div class="container">
            <span class="text-muted">Copyright &copy; 2024 RentalRadar. All rights reserved.</span>
        </div>
    </footer>

</body>

</html>