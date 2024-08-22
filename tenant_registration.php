<?php
session_start();
include 'db_connect.php';

// Redirect to login page if not logged in as admin
if (!isset($_SESSION['id_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Initialize message variable
$message = '';

// Handle tenant registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_tenant'])) {
    $id_number = $_POST['id_number'];
    $first_name = $_POST['first_name'];
    $surname = $_POST['surname'];
    $lastname = $_POST['lastname'];
    $phone_no = $_POST['phone_no'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

    // Prepare and execute SQL to insert a new tenant
    $sql = "INSERT INTO users (id_number, first_name, surname, lastname, phone_no, email, password, role, registration_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'tenant', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $id_number, $first_name, $surname, $lastname, $phone_no, $email, $password);

    if ($stmt->execute()) {
        $message = "Tenant registered successfully!";
        header("Refresh: 3; url=tenant_registration.php"); // Redirect after 3 seconds
    } else {
        $message = "Error registering tenant: " . $conn->error;
    }

    $stmt->close();
}

// Fetch tenants
$tenants = $conn->query("SELECT * FROM users WHERE role = 'tenant'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar .navbar-nav {
            flex-direction: column;
        }

        .sidebar .nav-link {
            color: #ffffff;
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
            border-radius: 5px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                box-shadow: none;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'nav_bar.php'; ?>

    <div class="main-content">
        <h2>Tenant Management</h2>
        <?php if ($message): ?>
            <div class="alert alert-info" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Button to open the modal -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerTenantModal">
            Add Tenant
        </button>

        <!-- Modal for tenant registration -->
        <div class="modal fade" id="registerTenantModal" tabindex="-1" aria-labelledby="registerTenantModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerTenantModalLabel">Register New Tenant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="tenant_registration.php">
                            <div class="mb-3">
                                <label for="id_number" class="form-label">ID Number</label>
                                <input type="text" class="form-control" id="id_number" name="id_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="surname" class="form-label">Surname</label>
                                <input type="text" class="form-control" id="surname" name="surname" required>
                            </div>
                            <div class="mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone_no" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone_no" name="phone_no" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email (Optional)</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" name="register_tenant" class="btn btn-primary">Register
                                Tenant</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- List of tenants -->
        <h3 class="mt-4">Tenant List</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>First Name</th>
                    <th>Surname</th>
                    <th>Last Name</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($tenant = $tenants->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($tenant['id_number']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($tenant['first_name']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($tenant['surname']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($tenant['lastname']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($tenant['phone_no']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($tenant['email']); ?>
                        </td>
                        <td>
                            <a href="delete_tenant.php?id_number=<?php echo urlencode($tenant['id_number']); ?>"
                                class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>