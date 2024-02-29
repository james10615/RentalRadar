<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rentalradar";

try {

    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $propertyName = isset($_POST["propertyName"]) ? $_POST["propertyName"] : null;
        $unit = isset($_POST["unit"]) ? $_POST["unit"] : null;
        $rent = isset($_POST["rent"]) ? $_POST["rent"] : null;

        if (isset($_POST["remove_property"])) {
        } elseif (empty($propertyName) || empty($unit) || empty($rent)) {
            echo "Please fill in all the fields.";
        } else {
            $sql = "INSERT INTO properties (property_name, unit, rent) VALUES (:propertyName, :unit, :rent)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':propertyName', $propertyName);
            $stmt->bindParam(':unit', $unit);
            $stmt->bindParam(':rent', $rent);

            if ($stmt->execute()) {
                echo '<p class="alert alert-success">New property added successful!</p>';
            } else {
                echo '<div class="alert alert-danger" role="alert">Error adding property: ' . $stmt->errorInfo()[2] . '</div>';
            }
        }
    }
    $stmt = $pdo->query("SELECT * FROM properties");
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>







<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Properties | RentalRadar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 56px;
            margin-bottom: 150px;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .property-card {
            width: 200px;
            height: 250px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px;
            display: inline-block;
            position: relative;
            text-align: center;
            transition: transform 0.3s ease-in-out;
        }

        .property-card .card-body {
            padding: 20px;
        }

        .add-property-card {
            background-color: #f8f9fa;
            border: 2px dashed #adb5bd;
            cursor: pointer;
        }

        .add-property-card:hover {
            background-color: #e9ecef;
        }

        .add-property-card .plus-icon {
            font-size: 48px;
            color: #adb5bd;
            margin-top: 60px;
        }

        .plus-icon {
            height: 15%;
            margin-top: 10px;
        }

        .property-card:hover {
            transform: scale(1.1);
        }

        .modal-content.zoom-out {
            animation: zoomOut 1s ease-out;
        }

        @keyframes zoomOut {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
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
                <li class="nav-item">
                    <a class="nav-link" href="#">Home</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="#">Properties</a>
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
    <div id="statusMessageContainer"></div>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="property-card add-property-card text-center" data-toggle="modal"
                    data-target="#addPropertyModal">
                    <div class="plus-icon">+</div>
                    <div class="card-body">
                        <h5 class="card-title">Add New Property</h5>
                        <p class="card-text">Click to add a new property</p>
                    </div>
                </div>
            </div>
            <?php foreach ($properties as $property): ?>
                <div class="col-md-3">
                    <div class="property-card">
                        <div class="card-body">
                            <h5>
                                <?php echo $property['property_name']; ?>
                            </h5>
                            <p>Unit:
                                <?php echo $property['unit']; ?>
                            </p>
                            <p>Rent: Ksh
                                <?php echo $property['rent']; ?>/month
                            </p>
                            <?php
                            $status = $property['status'];
                            $buttonClass = ($status == 'Occupied') ? 'btn btn-sm btn-dark disabled' : 'btn btn-sm btn-success';
                            $buttonName = ($status == 'Occupied') ? 'Occupied' : 'Rent';
                            $buttonTextStyle = ($status == 'Occupied') ? 'text-decoration: line-through;' : '';
                            ?>
                            <button class="<?php echo $buttonClass; ?>" <?php echo ($status == 'Occupied') ? 'disabled' : 'onclick="openTenantRegistrationModal(' . $property['id'] . ')"'; ?>
                                data-property-id="<?php echo $property['id']; ?>">
                                <span style=" <?php echo $buttonTextStyle; ?>">
                                    <?php echo $buttonName; ?>
                                </span>
                            </button>
                            <button class="btn btn-sm btn-dark">Details</button>

                            <hr>
                            <form action="" method="post" class="remove-property-form">
                                <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger remove-property-btn"
                                    name="remove_property">Remove</button>
                            </form>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove_property"]) && isset($_POST["property_id"])) {
                if (isset($_POST["property_id"])) {
                    $propertyIdToRemove = $_POST["property_id"];

                    $deleteSql = "DELETE FROM properties WHERE id = :id";
                    $deleteStmt = $pdo->prepare($deleteSql);
                    $deleteStmt->bindParam(':id', $propertyIdToRemove);

                    if ($deleteStmt->execute()) {
                        echo '<p class="alert alert-success">Property removed successfully!</p>';
                    } else {
                        echo '<div class="alert alert-danger" role="alert">Error removing property: ' . $deleteStmt->errorInfo()[2] . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger" role="alert">Invalid request. Property ID not provided.</div>';
                }
            }
            ?>
        </div>
    </div>



    </div>
    </div>
    <div class="modal" id="addPropertyModal" tabindex="-1" role="dialog" aria-labelledby="addPropertyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="addPropertyModalLabel">Add New Property</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="propertyName">Property Name</label>
                            <input type="text" class="form-control" id="propertyName" name="propertyName"
                                placeholder="Enter property name" required>
                        </div>
                        <div class="form-group">
                            <label for="location">Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit"
                                placeholder="Enter Unit name/number" required>
                        </div>
                        <div class="form-group">
                            <label for="rent">Rent</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Ksh</span>
                                </div>
                                <input type="number" class="form-control" id="rent" name="rent" placeholder="Enter rent"
                                    required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-dark">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="tenantRegistrationModal" tabindex="-1" role="dialog"
        aria-labelledby="tenantRegistrationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tenantRegistrationModalLabel">Register Tenant</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="tenantRegistrationForm">
                        <div><input type="hidden" id="propertyIdInput" name="propertyId">
                        </div>
                        <div class="form-group">
                            <label for="idNumber">ID Number:</label>
                            <input type="text" class="form-control rounded" id="idNumber" name="idNumber"
                                placeholder="Enter ID Number" required>
                        </div>
                        <div class="form-group">
                            <label for="firstName">First Name:</label>
                            <input type="text" class="form-control rounded" id="firstName" name="firstName"
                                placeholder="Enter First Name" required>
                        </div>
                        <div class="form-group">
                            <label for="surname">Surname:</label>
                            <input type="text" class="form-control rounded" id="surname" name="surname"
                                placeholder="Enter Surname" required>
                        </div>
                        <div class="form-group">
                            <label for="phoneNumber">Phone Number:</label>
                            <input type="tel" class="form-control rounded" id="phoneNumber" name="phoneNumber"
                                placeholder="Enter Phone Number" required>
                        </div>
                        <button type="submit" class="btn btn-success rounded-pill" id="rentButton">Rent</button>
                    </form>


                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-1.11.0.min.js"
        integrity="sha384-/Gm+ur33q/W+9ANGYwB2Q4V0ZWApToOzRuA8md/1p9xMMxpqnlguMvk8QuEFWA1B"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>

    <script>

        jQuery(document).on('click', '.remove-property-btn', function (e) {
            e.preventDefault();

            var propertyId = $(this).closest('.remove-property-form').find('input[name="property_id"]').val();
            console.log("Property ID to remove: " + propertyId);

            var cardToRemove = $(this).closest('.property-card');

            var confirmDelete = confirm("Are you sure you want to delete this property? This action cannot be undone.");

            if (confirmDelete) {
                $.ajax({
                    type: 'POST',
                    url: 'property.php',
                    data: {
                        remove_property: true,
                        property_id: propertyId
                    },
                    success: function (response) {
                        console.log("Ajax success");

                        if (response.toLowerCase().includes('success')) {
                            console.log("Property removed successfully");
                            cardToRemove.remove();
                            $('#statusMessageContainer').html('<p class="alert alert-success">Property removed successfully!</p>');
                        } else {
                            console.log("Error removing property: " + response);
                            alert('Error removing property: ' + response);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log("Ajax error", xhr, status, error.responseText);
                    }
                });
            } else {
                console.log("Deletion canceled by user.");
            }
        });
    </script>

    <script>
        function openTenantRegistrationModal(propertyId) {
            $('#tenantRegistrationModal').find('#propertyIdInput').val(propertyId);

            $('#tenantRegistrationModal').modal('show');
        }

        $(document).ready(function () {
            $('#tenantRegistrationForm').submit(function (event) {
                event.preventDefault();



                $('#tenantRegistrationModal').modal('hide');
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#tenantRegistrationForm').submit(function (e) {
                e.preventDefault();
                var formData = {
                    propertyId: $('#propertyIdInput').val(),
                    idNumber: $('#idNumber').val(),
                    firstName: $('#firstName').val(),
                    surname: $('#surname').val(),
                    phoneNumber: $('#phoneNumber').val(),
                };

                $.ajax({
                    type: 'POST',
                    url: 'process_rent.php',
                    data: {
                        propertyId: $('#propertyIdInput').val(),
                        idNumber: $('#idNumber').val(),
                        firstName: $('#firstName').val(),
                        surname: $('#surname').val(),
                        phoneNumber: $('#phoneNumber').val(),
                    },
                    success: function (response) {
                        console.log(response);

                        if (response.toLowerCase().includes('success')) {
                            $('#tenantRegistrationModal').modal('hide');

                            location.reload();
                        } else {
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            });
        });
    </script>
    <script>
        jQuery(document).on('click', '.occupied-btn', function () {
            var propertyId = $(this).data('property-id');
            $('#propertyIdInput').val(propertyId);
            $('#rentModal').modal('show');
        });
    </script>


    <footer class="footer mt-auto py-3 text-center fixed-bottom">
        <div class="container">
            <span class="text-muted">Copyright &copy; 2024 RentalRadar. All rights reserved.</span>
        </div>
    </footer>

</body>

</html>