<?php
// Database connection details
require_once 'config.php'; // Include the configuration file

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Handle filtering
$whereClause = "1"; // Default: No filter
if (!empty($_GET['categoryID'])) {
    $categoryID = intval($_GET['categoryID']);
    $whereClause .= " AND i.CategoryID = $categoryID"; // Use table alias "i."
}
if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $whereClause .= " AND i.Status = '$status'";
}

// Fetch filtered items
$sql = "SELECT i.ItemID, i.Name, i.Description, i.Quantity, i.EstimatedCost, i.ActualCost, i.Supplier, i.Status, c.CategoryName
        FROM Remodel_Items i
        JOIN Remodel_Categories c ON i.CategoryID = c.CategoryID
        WHERE $whereClause
        ORDER BY i.ItemID ASC";
$items = $conn->query($sql);

// Fetch categories for filter dropdown
$categories = $conn->query("SELECT c.CategoryID, c.CategoryName FROM Remodel_Categories c ORDER BY c.CategoryName ASC");

// Calculate totals
$totalsSql = "SELECT SUM(i.EstimatedCost) AS TotalEstimatedCost, SUM(i.ActualCost) AS TotalActualCost
              FROM Remodel_Items i
              WHERE $whereClause";
$totals = $conn->query($totalsSql)->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remodel Data Analysis</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Remodel Data Analysis</h1>

    <!-- Filters -->
    <form method="GET" action="">
        <label for="categoryID">Category:</label>
        <select name="categoryID" id="categoryID">
            <option value="">-- All Categories --</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?php echo $row['CategoryID']; ?>" <?php echo (isset($_GET['categoryID']) && $_GET['categoryID'] == $row['CategoryID']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($row['CategoryName']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="">-- All Statuses --</option>
            <option value="To Buy" <?php echo (isset($_GET['status']) && $_GET['status'] == 'To Buy') ? 'selected' : ''; ?>>To Buy</option>
            <option value="Purchased" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Purchased') ? 'selected' : ''; ?>>Purchased</option>
            <option value="Installed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Installed') ? 'selected' : ''; ?>>Installed</option>
        </select>

        <button type="submit">Filter</button>
        <a href="data_analysis.php">Reset Filters</a>
    </form>

    <!-- Summary -->
    <div class="summary">
        <strong>Total Estimated Cost:</strong> $<?php echo number_format($totals['TotalEstimatedCost'], 2); ?><br>
        <strong>Total Actual Cost:</strong> $<?php echo number_format($totals['TotalActualCost'], 2); ?>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Estimated Cost</th>
                <th>Actual Cost</th>
                <th>Supplier</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items->num_rows > 0): ?>
                <?php while ($row = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['ItemID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['CategoryName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Description']); ?></td>
                        <td><?php echo $row['Quantity']; ?></td>
                        <td>$<?php echo number_format($row['EstimatedCost'], 2); ?></td>
                        <td>$<?php echo number_format($row['ActualCost'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['Supplier']); ?></td>
                        <td><?php echo htmlspecialchars($row['Status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No items found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
