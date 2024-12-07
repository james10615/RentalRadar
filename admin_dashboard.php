<?php
session_start();

// Redirect to login page if not logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rentalradar";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$totalProperties = 0;
$totalRent = 0;
$totalTaxes = 0;

// Query to get total properties (handle missing table)
$totalPropertiesQuery = "SELECT COUNT(*) AS total_properties FROM properties";
$totalPropertiesResult = $conn->query($totalPropertiesQuery);

if ($totalPropertiesResult) {
    $totalProperties = $totalPropertiesResult->fetch_assoc()['total_properties'];
} else {
    // Log error or handle it appropriately
    $totalProperties = 0;
}

// Query to get total rent collected
$totalRentQuery = "SELECT SUM(rent_amount) AS total_rent FROM payments";
$totalRentResult = $conn->query($totalRentQuery);

if ($totalRentResult) {
    $totalRent = $totalRentResult->fetch_assoc()['total_rent'];
} else {
    // Log error or handle it appropriately
    $totalRent = 0;
}

// Calculate total taxes (assuming a fixed tax rate of 10%)
$taxRate = 0.10;
$totalTaxes = $totalRent * $taxRate;

// Query to get properties status (handle missing table)
$propertiesQuery = "SELECT * FROM properties";
$propertiesResult = $conn->query($propertiesQuery);

// Query to get recent payments
$paymentsQuery = "SELECT * FROM payments ORDER BY payment_date DESC LIMIT 5";
$paymentsResult = $conn->query($paymentsQuery);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalRadar - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding-top: 20px;
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: #adb5bd;
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .card {
            margin-bottom: 20px;
        }

        .icon {
            font-size: 2rem;
        }
    </style>
</head>

<body>
    <?php include 'nav_bar.php'; ?>
    <div class="content">
        <div class="container-fluid">
            <h2 class="mb-4">Admin Dashboard</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-building icon"></i> Total Properties</h5>
                            <p class="card-text">
                                <?php echo $totalProperties; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-dollar-sign icon"></i> Total Rent Collected</h5>
                            <p class="card-text">$
                                <?php echo number_format($totalRent, 2); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-calculator icon"></i> Total Taxes</h5>
                            <p class="card-text">$
                                <?php echo number_format($totalTaxes, 2); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Properties Status</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($propertiesResult && $propertiesResult->num_rows > 0): ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($property = $propertiesResult->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($property['id']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($property['unit_name']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($property['is_occupied']); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No properties found or properties table does not exist.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Recent Payments</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($paymentsResult && $paymentsResult->num_rows > 0): ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>User ID</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($payment = $paymentsResult->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($payment['payment_id']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($payment['user_id']); ?>
                                                </td>
                                                <td>$
                                                    <?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($payment['payment_date']); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No recent payments found or payments table does not exist.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>