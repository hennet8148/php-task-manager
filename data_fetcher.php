<?php
// Include the database connection
include 'db_connection.php';

// Set up sorting
$orderBy = isset($_GET['sort']) ? $_GET['sort'] : 'ItemID';
$allowedSorts = ['ItemID', 'Name', 'CategoryName', 'Description', 'Quantity', 'EstimatedCost', 'ActualCost', 'Supplier', 'Status'];
$orderBy = in_array($orderBy, $allowedSorts) ? $orderBy : 'ItemID';

// Fetch categories for the dropdown
$categories = $conn->query("SELECT * FROM Remodel_Categories ORDER BY CategoryName ASC");
if (!$categories) {
    die("Error fetching categories: " . $conn->error);
}

// Fetch items with category names
$sql = "SELECT i.ItemID, i.Name, i.Description, i.Quantity, i.EstimatedCost, i.ActualCost, i.Supplier, i.Status, c.CategoryName
        FROM Remodel_Items i
        JOIN Remodel_Categories c ON i.CategoryID = c.CategoryID
        ORDER BY $orderBy ASC";

$items = $conn->query($sql);
if (!$items) {
    die("Error fetching items: " . $conn->error);
}
?>
