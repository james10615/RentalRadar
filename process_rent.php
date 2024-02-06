<?php
include_once('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $propertyId = $_POST["propertyId"];
    $idNumber = $_POST["idNumber"];
    $firstName = $_POST["firstName"];
    $surname = $_POST["surname"];
    $phoneNumber = $_POST["phoneNumber"];

    // Assuming you have a propertyId stored in a variable $propertyId
    // Insert data into tenants table
    $insertTenantSql = "INSERT INTO tenants (IdNumber, FirstName, Surname, PhoneNumber, propertyId) VALUES (:idNumber, :firstName, :surname, :phoneNumber, :propertyId)";
    $insertTenantStmt = $pdo->prepare($insertTenantSql);
    $insertTenantStmt->bindParam(':idNumber', $idNumber);
    $insertTenantStmt->bindParam(':firstName', $firstName);
    $insertTenantStmt->bindParam(':surname', $surname);
    $insertTenantStmt->bindParam(':phoneNumber', $phoneNumber);
    $insertTenantStmt->bindParam(':propertyId', $propertyId);

    // Update the property status to 'Occupied'
    $updatePropertySql = "UPDATE properties SET status = 'Occupied' WHERE id = :propertyId";
    $updatePropertyStmt = $pdo->prepare($updatePropertySql);
    $updatePropertyStmt->bindParam(':propertyId', $propertyId);

    // Begin a transaction to ensure both queries succeed or fail together
    $pdo->beginTransaction();

    try {
        $insertTenantStmt->execute();
        $updatePropertyStmt->execute();

        // If both queries are successful, commit the transaction
        $pdo->commit();

        // Output a success message
        echo 'Success: Tenant registered and property status updated.';
    } catch (PDOException $e) {
        // If an error occurs, rollback the transaction
        $pdo->rollBack();

        // Output an error message
        echo 'Error: ' . $e->getMessage();
    }
}
