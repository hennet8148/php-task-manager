<?php
// Include the database connection
include 'db_connection.php';

// Handle Create and Update operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemID = isset($_POST['itemID']) ? intval($_POST['itemID']) : null;
    $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $categoryID = isset($_POST['categoryID']) ? intval($_POST['categoryID']) : 0;
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $estimatedCost = isset($_POST['estimatedCost']) ? floatval($_POST['estimatedCost']) : 0.00;
    $actualCost = isset($_POST['actualCost']) ? floatval($_POST['actualCost']) : 0.00;
    $supplier = isset($_POST['supplier']) ? $conn->real_escape_string($_POST['supplier']) : '';
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'To Buy';

    if ($itemID) {
        // Update existing item
        $sql = "UPDATE Remodel_Items SET Name = '$name', CategoryID = $categoryID, Description = '$description', Quantity = $quantity, EstimatedCost = $estimatedCost, ActualCost = $actualCost, Supplier = '$supplier', Status = '$status' WHERE ItemID = $itemID";
    } else {
        // Create new item
        $sql = "INSERT INTO Remodel_Items (Name, CategoryID, Description, Quantity, EstimatedCost, ActualCost, Supplier, Status) VALUES ('$name', $categoryID, '$description', $quantity, $estimatedCost, $actualCost, '$supplier', '$status')";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: index1.php");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle Delete operation
if (isset($_GET['delete'])) {
    $itemID = intval($_GET['delete']);
    $sql = "DELETE FROM Remodel_Items WHERE ItemID = $itemID";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

$conn->close();
?>
