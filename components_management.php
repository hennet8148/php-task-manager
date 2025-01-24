<?php
// Database connection
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php'; // Include the configuration file

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Initialize variables
$component_id = "";
$name = "";
$parent_id = "";
$type = "";

// Fetch parent components for dropdown
$parent_result = $conn->query("SELECT ID, NAME FROM COMPONENTS ORDER BY NAME");
$parents = [];
while ($row = $parent_result->fetch_assoc()) {
    $parents[$row['ID']] = $row['NAME'];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $component_id = $_POST['component_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $type = $conn->real_escape_string($_POST['type']);

    if (!empty($component_id)) {
        // Update the row
        $update_query = "UPDATE COMPONENTS
                         SET NAME = '$name', PARENT_ID = " . ($parent_id ? "'$parent_id'" : "NULL") . ", TYPE = '$type'
                         WHERE ID = $component_id";
        if ($conn->query($update_query) === TRUE) {
            echo "<div class='success'>Component updated successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO COMPONENTS (NAME, PARENT_ID, TYPE)
                         VALUES ('$name', " . ($parent_id ? "'$parent_id'" : "NULL") . ", '$type')";
        if ($conn->query($insert_query) === TRUE) {
            echo "<div class='success'>New component added successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM COMPONENTS WHERE ID = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<div class='success'>Component deleted successfully!</div>";
    } else {
        echo "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM COMPONENTS WHERE ID = $edit_id";
    $edit_result = $conn->query($edit_query);
    if ($edit_result->num_rows > 0) {
        $row = $edit_result->fetch_assoc();
        $component_id = $row['ID'];
        $name = $row['NAME'];
        $parent_id = $row['PARENT_ID'];
        $type = $row['TYPE'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Component Management</title>
    <link rel="stylesheet" href="styles.php">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <h2><?php echo empty($component_id) ? "Add a New Component" : "Edit Component"; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="component_id" value="<?php echo $component_id; ?>">
            <label for="name">Component Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>

            <label for="parent_id">Parent Component:</label>
            <select id="parent_id" name="parent_id">
                <option value="">-- No Parent --</option>
                <?php foreach ($parents as $id => $parent_name): ?>
                    <option value="<?php echo $id; ?>" <?php echo $parent_id == $id ? 'selected' : ''; ?>><?php echo $parent_name; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="type">Component Type:</label>
            <input type="text" id="type" name="type" value="<?php echo $type; ?>">

            <button type="submit"><?php echo empty($component_id) ? "Submit" : "Update"; ?></button>
        </form>

        <h2>Existing Components</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Parent</th>
                    <th>Type</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT c1.*, c2.NAME AS PARENT_NAME
                                        FROM COMPONENTS c1
                                        LEFT JOIN COMPONENTS c2 ON c1.PARENT_ID = c2.ID");

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["ID"] . "</td>
                                <td>" . $row["NAME"] . "</td>
                                <td>" . ($row["PARENT_NAME"] ?? "None") . "</td>
                                <td>" . $row["TYPE"] . "</td>
                                <td>" . $row["CREATED_AT"] . "</td>
                                <td>" . $row["UPDATED_AT"] . "</td>
                                <td>
                                    <a href='?edit_id=" . $row["ID"] . "'>Edit</a> |
                                    <a href='?delete_id=" . $row["ID"] . "' onclick=\"return confirm('Are you sure you want to delete this component?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No components found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
