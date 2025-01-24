<?php
// Load database configuration
require_once 'config.php';

// Create connection
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM Remodel_Categories ORDER BY CategoryName ASC");

// Initialize variables for form fields
$itemID = $name = $categoryID = $description = $quantity = $estimatedCost = $actualCost = $supplier = $status = "";

// Handle edit request
if (isset($_GET['edit'])) {
    $editID = intval($_GET['edit']);
    $editQuery = $conn->query("SELECT * FROM Remodel_Items WHERE ItemID = $editID");

    if ($editQuery->num_rows > 0) {
        $editRow = $editQuery->fetch_assoc();
        $itemID = $editRow['ItemID'];
        $name = htmlspecialchars($editRow['Name']);
        $categoryID = $editRow['CategoryID'];
        $description = htmlspecialchars($editRow['Description']);
        $quantity = $editRow['Quantity'];
        $estimatedCost = $editRow['EstimatedCost'];
        $actualCost = $editRow['ActualCost'];
        $supplier = htmlspecialchars($editRow['Supplier']);
        $status = htmlspecialchars($editRow['Status']);
    }
}

// Handle form submission for Create and Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $itemID = isset($_POST['itemID']) ? intval($_POST['itemID']) : null;
    $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : "";
    $categoryID = isset($_POST['categoryID']) ? intval($_POST['categoryID']) : 0;
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : "";
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $estimatedCost = isset($_POST['estimatedCost']) ? floatval($_POST['estimatedCost']) : 0.00;
    $actualCost = isset($_POST['actualCost']) ? floatval($_POST['actualCost']) : 0.00;
    $supplier = isset($_POST['supplier']) ? $conn->real_escape_string($_POST['supplier']) : "";
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : "To Buy";

    if ($itemID) {
        // Update existing item
        $sql = "UPDATE Remodel_Items SET Name = '$name', CategoryID = $categoryID, Description = '$description', Quantity = $quantity, EstimatedCost = $estimatedCost, ActualCost = $actualCost, Supplier = '$supplier', Status = '$status' WHERE ItemID = $itemID";
    } else {
        // Create new item
        $sql = "INSERT INTO Remodel_Items (Name, CategoryID, Description, Quantity, EstimatedCost, ActualCost, Supplier, Status) VALUES ('$name', $categoryID, '$description', $quantity, $estimatedCost, $actualCost, '$supplier', '$status')";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $itemID = intval($_GET['delete']);
    $sql = "DELETE FROM Remodel_Items WHERE ItemID = $itemID";

    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch items
$sql = "SELECT i.ItemID, i.Name, i.Description, i.Quantity, i.EstimatedCost, i.ActualCost, i.Supplier, i.Status, c.CategoryName FROM Remodel_Items i JOIN Remodel_Categories c ON i.CategoryID = c.CategoryID ORDER BY i.ItemID ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remodel Items CRUD</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Manage Remodel Items</h1>

    <form method="POST" action="">
        <input type="hidden" name="itemID" id="itemID" value="<?php echo $itemID; ?>">
        <label for="name">Item Name:</label>
        <input type="text" name="name" id="name" value="<?php echo $name; ?>" required><br>

        <label for="categoryID">Category:</label>
        <select name="categoryID" id="categoryID" required>
            <option value="">-- Select Category --</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?php echo $row['CategoryID']; ?>" <?php echo ($row['CategoryID'] == $categoryID) ? 'selected' : ''; ?>><?php echo $row['CategoryName']; ?></option>
            <?php endwhile; ?>
        </select><br>

        <label for="description">Description:</label>
        <textarea name="description" id="description"><?php echo $description; ?></textarea><br>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" value="<?php echo $quantity; ?>" min="1"><br>

        <label for="estimatedCost">Estimated Cost:</label>
        <input type="number" name="estimatedCost" id="estimatedCost" step="0.01" value="<?php echo $estimatedCost; ?>"><br>

        <label for="actualCost">Actual Cost:</label>
        <input type="number" name="actualCost" id="actualCost" step="0.01" value="<?php echo $actualCost; ?>"><br>

        <label for="supplier">Supplier:</label>
        <input type="text" name="supplier" id="supplier" value="<?php echo $supplier; ?>"><br>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="To Buy" <?php echo ($status == "To Buy") ? 'selected' : ''; ?>>To Buy</option>
            <option value="Purchased" <?php echo ($status == "Purchased") ? 'selected' : ''; ?>>Purchased</option>
            <option value="Installed" <?php echo ($status == "Installed") ? 'selected' : ''; ?>>Installed</option>
        </select><br>

        <button type="submit">Save</button>
        <button type="reset" onclick="clearForm()">Clear</button>
    </form>

    <h2>Items List</h2>
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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['ItemID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['CategoryName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Description']); ?></td>
                        <td><?php echo $row['Quantity']; ?></td>
                        <td><?php echo $row['EstimatedCost']; ?></td>
                        <td><?php echo $row['ActualCost']; ?></td>
                        <td><?php echo htmlspecialchars($row['Supplier']); ?></td>
                        <td><?php echo htmlspecialchars($row['Status']); ?></td>
                        <td>
                            <a href="?edit=<?php echo $row['ItemID']; ?>">Edit</a>
                            <a href="?delete=<?php echo $row['ItemID']; ?>" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10">No items found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function clearForm() {
            document.getElementById('itemID').value = "";
            document.getElementById('name').value = "";
            document.getElementById('categoryID').value = "";
            document.getElementById('description').value = "";
            document.getElementById('quantity').value = 1;
            document.getElementById('estimatedCost').value = 0.00;
            document.getElementById('actualCost').value = 0.00;
            document.getElementById('supplier').value = "";
            document.getElementById('status').value = "To Buy";
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
