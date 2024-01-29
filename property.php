<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rentalradar";

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if form fields are set before accessing them
        $propertyName = isset($_POST["propertyName"]) ? $_POST["propertyName"] : null;
        $unit = isset($_POST["unit"]) ? $_POST["unit"] : null;
        $rent = isset($_POST["rent"]) ? $_POST["rent"] : null;

        // Validate form data (you may add more validation as needed)
        if (empty($propertyName) || empty($unit) || empty($rent)) {
            echo "Please fill in all the fields.";
        } else {
            // SQL query to insert data into the properties table using prepared statements
            $sql = "INSERT INTO properties (property_name, unit, rent) VALUES (:propertyName, :unit, :rent)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':propertyName', $propertyName);
            $stmt->bindParam(':unit', $unit);
            $stmt->bindParam(':rent', $rent);

            // Execute the query
            if ($stmt->execute()) {
                echo '<p class="alert alert-success">New property added successful!</p>';
            } else {
                echo "Error: " . $sql . "<br>" . $stmt->errorInfo()[2];
            }
        }
    }
    // Fetch data from the database
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
            /* Ensure space for footer */
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

        /* Animation keyframes */
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

    <div class="container">
        <div class="row">
            <!-- Add New Property Card -->
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
            <!-- Loop through properties and display cards -->
            <?php foreach ($properties as $property): ?>
                <div class="col-md-3">
                    <div class="property-card">
                        <div class="card-body">
                            <!-- Property Details -->
                            <h5>
                                <?php echo $property['property_name']; ?>
                            </h5>
                            <p>Unit:
                                <?php echo $property['unit']; ?>
                            </p>
                            <p>Rent: $
                                <?php echo $property['rent']; ?>/month
                            </p>
                            <!-- Additional Features -->
                            <button class="btn btn-sm btn-dark">View Details</button>
                            <hr>
                            <button class="btn btn-sm btn-danger">Remove</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>


        </div>
    </div>



    </div>
    </div>
    <!-- Modal for Add Property Form -->
    <div class="modal" id="addPropertyModal" tabindex="-1" role="dialog" aria-labelledby="addPropertyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content zoom-out">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPropertyModalLabel">Add New Property</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Your form fields go here -->
                    <form action="" method="post">
                        <!-- Example: -->
                        <div class="form-group">
                            <label for="propertyName">Property Name</label>
                            <input type="text" class="form-control" id="propertyName" name="propertyName"
                                placeholder="Enter property name">
                        </div>
                        <div class="form-group">
                            <label for="location">Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit"
                                placeholder="Enter Unit name/number">
                        </div>
                        <div class="form-group">
                            <label for="rent">Rent</label>
                            <input type="number" class="form-control" id="rent" name="rent"
                                placeholder="Enter rent in Ksh">
                        </div>
                        <!-- Add more fields as needed -->

                        <button type="submit" class="btn btn-dark">Submit</button>
                    </form>
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