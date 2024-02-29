<?php
include_once('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $propertyId = $_POST["propertyId"];
    $idNumber = $_POST["idNumber"];
    $firstName = $_POST["firstName"];
    $surname = $_POST["surname"];
    $phoneNumber = $_POST["phoneNumber"];


    $insertTenantSql = "INSERT INTO tenants (IdNumber, FirstName, Surname, PhoneNumber, propertyId) VALUES (:idNumber, :firstName, :surname, :phoneNumber, :propertyId)";
    $insertTenantStmt = $pdo->prepare($insertTenantSql);
    $insertTenantStmt->bindParam(':idNumber', $idNumber);
    $insertTenantStmt->bindParam(':firstName', $firstName);
    $insertTenantStmt->bindParam(':surname', $surname);
    $insertTenantStmt->bindParam(':phoneNumber', $phoneNumber);
    $insertTenantStmt->bindParam(':propertyId', $propertyId);

    $updatePropertySql = "UPDATE properties SET status = 'Occupied' WHERE id = :propertyId";
    $updatePropertyStmt = $pdo->prepare($updatePropertySql);
    $updatePropertyStmt->bindParam(':propertyId', $propertyId);

    $pdo->beginTransaction();

    try {
        $insertTenantStmt->execute();
        $updatePropertyStmt->execute();

        $pdo->commit();

        echo 'Success: Tenant registered and property status updated.';
    } catch (PDOException $e) {
        $pdo->rollBack();

        echo 'Error: ' . $e->getMessage();
    }
}
