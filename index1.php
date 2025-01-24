<?php
// Database connection details
require_once 'config.php'; // Include the configuration file

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Handle form submission (Create/Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    var_dump($_POST); // Debugging to verify form data
    echo "</pre>";

    $itemID = isset($_POST['itemID']) ? intval($_POST['itemID']) : null;
    $name = $conn->real_escape_string($_POST['name']);
    $categoryID = intval($_POST['categoryID']);
    $description = $conn->real_escape_string($_POST['description']);
    $quantity = intval($_POST['quantity']);
    $estimatedCost = floatval($_POST['estimatedCost']);
    $actualCost = floatval($_POST['actualCost']);
    $supplier = $conn->real_escape_string($_POST['supplier']);
    $status = $conn->real_escape_string($_POST['status']);

    if ($itemID) {
        // Update existing item
        $sql = "UPDATE Remodel_Items SET Name='$name', CategoryID=$categoryID, Description='$description', Quantity=$quantity, EstimatedCost=$estimatedCost, ActualCost=$actualCost, Supplier='$supplier', Status='$status' WHERE ItemID=$itemID";


    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
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

// Handle delete
if (isset($_GET['delete'])) {
    $itemID = intval($_GET['delete']);
    $sql = "DELETE FROM Remodel_Items WHERE ItemID=$itemID";

    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch categories
$categories = $conn->query("SELECT * FROM Remodel_Categories ORDER BY CategoryName ASC");

// Fetch items
$items = $conn->query("SELECT i.ItemID, i.Name, i.Description, i.Quantity, i.EstimatedCost, i.ActualCost, i.Supplier, i.Status, c.CategoryName FROM Remodel_Items i JOIN Remodel_Categories c ON i.CategoryID = c.CategoryID ORDER BY i.ItemID ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remodel Items</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Manage Remodel Items</h1>

    <?php
$editItem = null;
if (isset($_GET['edit'])) {
    $editID = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM Remodel_Items WHERE ItemID = $editID");
    $editItem = $result->fetch_assoc();
}
?>
<form method="POST" action="">
        <input type="hidden" name="itemID" id="itemID" value="<?php echo $editItem['ItemID'] ?? ''; ?>">

        <label for="name">Item Name:</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($editItem['Name'] ?? ''); ?>" required><br>

        <label for="categoryID">Category:</label>
        <select name="categoryID" id="categoryID" required>
    <option value="">-- Select Category --</option>
    <?php while ($row = $categories->fetch_assoc()): ?>
        <option value="<?php echo $row['CategoryID']; ?>"><?php echo $row['CategoryName']; ?></option>
    <?php endwhile; ?>
</select><br>

        <label for="description">Description:</label>
        <textarea name="description" id="description"><?php echo htmlspecialchars($editItem['Description'] ?? ''); ?></textarea><br>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" value="<?php echo $editItem['Quantity'] ?? 1; ?>" min="1"><br>

        <label for="estimatedCost">Estimated Cost:</label>
        <input type="number" name="estimatedCost" id="estimatedCost" step="0.01" value="<?php echo $editItem['EstimatedCost'] ?? 0.00; ?>"><br>

        <label for="actualCost">Actual Cost:</label>
        <input type="number" name="actualCost" id="actualCost" step="0.01" value="<?php echo $editItem['ActualCost'] ?? 0.00; ?>"><br>

        <label for="supplier">Supplier:</label>
        <input type="text" name="supplier" id="supplier" value="<?php echo htmlspecialchars($editItem['Supplier'] ?? ''); ?>"><br>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="To Buy" <?php echo isset($editItem['Status']) && $editItem['Status'] == 'To Buy' ? 'selected' : ''; ?>>To Buy</option>
            <option value="Purchased" <?php echo isset($editItem['Status']) && $editItem['Status'] == 'Purchased' ? 'selected' : ''; ?>>Purchased</option>
            <option value="Installed" <?php echo isset($editItem['Status']) && $editItem['Status'] == 'Installed' ? 'selected' : ''; ?>>Installed</option>
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
            <?php if ($items->num_rows > 0): ?>
                <?php while ($row = $items->fetch_assoc()): ?>
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
        function editItem(id, name, categoryID, description, quantity, estimatedCost, actualCost, supplier, status) {
    console.log("Editing Item:", { id, name, categoryID, description, quantity, estimatedCost, actualCost, supplier, status });
    document.getElementById('itemID').value = id;
    document.getElementById('name').value = name;
    document.getElementById('categoryID').value = categoryID;
    document.getElementById('description').value = description;
    document.getElementById('quantity').value = quantity;
    document.getElementById('estimatedCost').value = estimatedCost;
    document.getElementById('actualCost').value = actualCost;
    document.getElementById('supplier').value = supplier;
    document.getElementById('status').value = status;
}

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
