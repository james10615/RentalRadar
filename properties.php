<?php
session_start();

// Redirect to login page if not logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

include 'db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle property addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_property'])) {
    $property_name = $_POST['property_name'];
    $unit_name = $_POST['unit_name'];
    $property_type = $_POST['property_type'];
    $address = $_POST['address'];
    $size = $_POST['size'];
    $rent = $_POST['rent'];
    $deposit = $_POST['deposit'];
    $tenant_id = $_POST['tenant_id'];
    $move_in_date = $_POST['move_in_date'];
    $is_occupied = $_POST['is_occupied'];

    // Insert property into the database
    $sql = "INSERT INTO properties (property_name, unit_name, property_type, address, size, rent, deposit, tenant_id, move_in_date, is_occupied) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssissi", $property_name, $unit_name, $property_type, $address, $size, $rent, $deposit, $tenant_id, $move_in_date, $is_occupied);

    if ($stmt->execute()) {
        $message = "Property added successfully!";
        header("Refresh: 3; url=properties.php");
    } else {
        echo "<div class='alert alert-danger'>Error adding property: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Handle property deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_property_id'])) {
    $delete_id = $_POST['delete_property_id'];

    $sql = "DELETE FROM properties WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $message = "Property deleted successfully!";
        header("Refresh: 3; url=properties.php");
    } else {
        echo "<div class='alert alert-danger'>Error deleting property: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Query to get properties
$sql = "SELECT * FROM properties";
$result = $conn->query($sql);

// Query to get tenants
$tenantSql = "SELECT id_number, CONCAT(first_name, ' ', surname) AS full_name FROM users WHERE role = 'tenant'";
$tenantResult = $conn->query($tenantSql);

$tenants = [];
if ($tenantResult) {
    while ($tenant = $tenantResult->fetch_assoc()) {
        $tenants[$tenant['id_number']] = $tenant['full_name'];
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalRadar - Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h4 {
            color: #ffffff;
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
        <h2 class="mb-4">Properties</h2>
        <div class="mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">Add New
                Property</button>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Property Name</th>
                        <th>Unit Name</th>
                        <th>Property Type</th>
                        <th>Address</th>
                        <th>Size</th>
                        <th>Rent</th>
                        <th>Deposit</th>
                        <th>Occupied</th>
                        <th>Tenant Name</th>
                        <th>Move-In Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($property = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($property['id']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($property['property_name']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($property['unit_name']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($property['property_type']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($property['address']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($property['size']); ?>
                            </td>
                            <td>Kshs
                                <?php echo number_format($property['rent'], 2); ?>
                            </td>
                            <td>Kshs
                                <?php echo number_format($property['deposit'], 2); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($property['is_occupied']); ?>
                            </td>
                            <td>
                                <?php
                                if ($property['tenant_id'] && isset($tenants[$property['tenant_id']])) {
                                    echo htmlspecialchars($tenants[$property['tenant_id']]);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <?php echo $property['move_in_date'] ? htmlspecialchars($property['move_in_date']) : 'N/A'; ?>
                            </td>
                            <td>
                                <!-- Edit Form -->
                                <form action="edit_property.php" method="get" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($property['id']); ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">Edit</button>
                                </form>

                                <!-- Delete Form -->
                                <form action="properties.php" method="post" style="display:inline;">
                                    <input type="hidden" name="delete_property_id"
                                        value="<?php echo htmlspecialchars($property['id']); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this property?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No properties found.</p>
        <?php endif; ?>
    </div>

    <!-- Add Property Modal -->
    <div class="modal fade" id="addPropertyModal" tabindex="-1" aria-labelledby="addPropertyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPropertyModalLabel">Add New Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="properties.php" method="POST">
                        <div class="mb-3">
                            <label for="property_name" class="form-label">Property Name</label>
                            <input type="text" class="form-control" id="property_name" name="property_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="unit_name" class="form-label">Unit Name</label>
                            <input type="text" class="form-control" id="unit_name" name="unit_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="property_type" class="form-label">Property Type</label>
                            <input type="text" class="form-control" id="property_type" name="property_type" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="mb-3">
                            <label for="size" class="form-label">Size</label>
                            <input type="text" class="form-control" id="size" name="size" required>
                        </div>
                        <div class="mb-3">
                            <label for="rent" class="form-label">Rent</label>
                            <input type="number" class="form-control" id="rent" name="rent" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="deposit" class="form-label">Deposit</label>
                            <input type="number" class="form-control" id="deposit" name="deposit" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="tenant_id" class="form-label">Tenant ID</label>
                            <select id="tenant_id" name="tenant_id" class="form-select">
                                <option value="">Select Tenant</option>
                                <?php foreach ($tenants as $id_number => $full_name): ?>
                                    <option value="<?php echo htmlspecialchars($id_number); ?>">
                                        <?php echo htmlspecialchars($full_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="move_in_date" class="form-label">Move-In Date</label>
                            <input type="date" class="form-control" id="move_in_date" name="move_in_date">
                        </div>
                        <div class="mb-3">
                            <label for="is_occupied" class="form-label">Occupied</label>
                            <select id="is_occupied" name="is_occupied" class="form-select">
                                <option value="occupied">Occupied</option>
                                <option value="un-occupied">Un-Occupied</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" name="add_property">Add Property</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>