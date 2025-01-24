<?php
// Database configuration
require_once 'config.php'; // Include the configuration file

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Handle form submission for Create and Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $categoryID = isset($_POST['categoryID']) ? intval($_POST['categoryID']) : null;
    $categoryName = isset($_POST['categoryName']) ? $conn->real_escape_string($_POST['categoryName']) : "";

    if ($categoryID) {
        // Update existing category
        $sql = "UPDATE Remodel_Categories SET CategoryName = '$categoryName' WHERE CategoryID = $categoryID";
    } else {
        // Create new category
        $sql = "INSERT INTO Remodel_Categories (CategoryName) VALUES ('$categoryName')";
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
    $categoryID = intval($_GET['delete']);
    $sql = "DELETE FROM Remodel_Categories WHERE CategoryID = $categoryID";

    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch categories
$sql = "SELECT * FROM Remodel_Categories ORDER BY CategoryID ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remodel Categories CRUD</title>
    <style>
        table { border-collapse: collapse; width: 50%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Manage Remodel Categories</h1>

    <form method="POST" action="">
        <input type="hidden" name="categoryID" id="categoryID">
        <label for="categoryName">Category Name:</label>
        <input type="text" name="categoryName" id="categoryName" required>
        <button type="submit">Save</button>
        <button type="reset" onclick="clearForm()">Clear</button>
    </form>

    <h2>Category List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['CategoryID']; ?></td>
                        <td><?php echo htmlspecialchars($row['CategoryName']); ?></td>
                        <td>
                            <button onclick="editCategory(<?php echo $row['CategoryID']; ?>, '<?php echo htmlspecialchars($row['CategoryName']); ?>')">Edit</button>
                            <a href="?delete=<?php echo $row['CategoryID']; ?>" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No categories found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function editCategory(id, name) {
            document.getElementById('categoryID').value = id;
            document.getElementById('categoryName').value = name;
        }

        function clearForm() {
            document.getElementById('categoryID').value = "";
            document.getElementById('categoryName').value = "";
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
