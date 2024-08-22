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

$success = false;

// Handle form submission to update the property
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_property'])) {
    $property_id = $_POST['property_id'];
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

    // Prepare and execute the update query
    $sql = "UPDATE properties SET property_name = ?, unit_name = ?, property_type = ?, address = ?, size = ?, rent = ?, deposit = ?, tenant_id = ?, move_in_date = ?, is_occupied = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssissii", $property_name, $unit_name, $property_type, $address, $size, $rent, $deposit, $tenant_id, $move_in_date, $is_occupied, $property_id);

    if ($stmt->execute()) {
        $success = true; // Update successful
    } else {
        echo "<div class='alert alert-danger'>Error updating property: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Retrieve the property details if id is provided
if (isset($_GET['id'])) {
    $property_id = $_GET['id'];
    $sql = "SELECT * FROM properties WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $property = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    header("Location: properties.php");
    exit();
}

// Retrieve tenants for the dropdown
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
    <title>Edit Property</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .message {
            display: none;
        }

        .edit-property-form {
            max-width: 600px;
            /* Maximum width for larger screens */
            margin: 0 auto;
            /* Center the form horizontally */
        }
    </style>
    <script>
        function showMessage() {
            var message = document.getElementById('message');
            if (message) {
                message.style.display = 'block';
                setTimeout(function () {
                    message.style.display = 'visible';
                    window.location.href = 'properties.php'; // Redirect after 3 seconds
                }, 3000);
            }
        }
    </script>
</head>

<body onload="showMessage()">
    <?php include 'nav_bar.php'; ?>
    <div class="container mt-4">
        <?php if ($success): ?>
            <div id="message" class="alert alert-success">Property updated successfully!</div>
        <?php endif; ?>

        <h2 class="text-center">Edit Property</h2>
        <form action="edit_property.php" method="POST" class="edit-property-form">
            <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property['id']); ?>">
            <div class="mb-3">
                <label for="property_name" class="form-label">Property Name</label>
                <input type="text" class="form-control" id="property_name" name="property_name"
                    value="<?php echo htmlspecialchars($property['property_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="unit_name" class="form-label">Unit Name</label>
                <input type="text" class="form-control" id="unit_name" name="unit_name"
                    value="<?php echo htmlspecialchars($property['unit_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="property_type" class="form-label">Property Type</label>
                <input type="text" class="form-control" id="property_type" name="property_type"
                    value="<?php echo htmlspecialchars($property['property_type']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address"
                    value="<?php echo htmlspecialchars($property['address']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="size" class="form-label">Size</label>
                <input type="text" class="form-control" id="size" name="size"
                    value="<?php echo htmlspecialchars($property['size']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="rent" class="form-label">Rent</label>
                <input type="number" class="form-control" id="rent" name="rent" step="0.01"
                    value="<?php echo htmlspecialchars($property['rent']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="deposit" class="form-label">Deposit</label>
                <input type="number" class="form-control" id="deposit" name="deposit" step="0.01"
                    value="<?php echo htmlspecialchars($property['deposit']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tenant_id" class="form-label">Tenant ID</label>
                <select id="tenant_id" name="tenant_id" class="form-select">
                    <option value="">Select Tenant</option>
                    <?php foreach ($tenants as $id_number => $full_name): ?>
                        <option value="<?php echo htmlspecialchars($id_number); ?>" <?php echo $property['tenant_id'] == $id_number ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($full_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="move_in_date" class="form-label">Move-In Date</label>
                <input type="date" class="form-control" id="move_in_date" name="move_in_date"
                    value="<?php echo htmlspecialchars($property['move_in_date']); ?>">
            </div>
            <div class="mb-3">
                <label for="is_occupied" class="form-label">Occupied</label>
                <select id="is_occupied" name="is_occupied" class="form-select">
                    <option value="occupied" <?php echo $property['is_occupied'] == 'occupied' ? 'selected' : ''; ?>>
                        Occupied</option>
                    <option value="un-occupied" <?php echo $property['is_occupied'] == 'un-occupied' ? 'selected' : ''; ?>>Un-Occupied</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100" name="update_property">Update Property</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>