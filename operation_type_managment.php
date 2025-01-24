<?php
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
$operation_type_id = "";
$name = "";
$description = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $operation_type_id = $_POST['operation_type_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);

    if (!empty($operation_type_id)) {
        // Update the row
        $update_query = "UPDATE OPERATION_TYPE SET NAME = '$name', DESCRIPTION = '$description' WHERE ID = $operation_type_id";
        $conn->query($update_query);
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO OPERATION_TYPE (NAME, DESCRIPTION) VALUES ('$name', '$description')";
        $conn->query($insert_query);
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM OPERATION_TYPE WHERE ID = $delete_id");
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $conn->query("SELECT * FROM OPERATION_TYPE WHERE ID = $edit_id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $operation_type_id = $row['ID'];
        $name = $row['NAME'];
        $description = $row['DESCRIPTION'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operation Type Management</title>
    <link rel="stylesheet" href="styles.php">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <h2><?php echo empty($operation_type_id) ? "Add a New Operation Type" : "Edit Operation Type"; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="operation_type_id" value="<?php echo $operation_type_id; ?>">
            <label for="name">Operation Type:</label>
            <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description"><?php echo $description; ?></textarea>

            <button type="submit"><?php echo empty($operation_type_id) ? "Submit" : "Update"; ?></button>
        </form>

        <h2>Existing Operation Types</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM OPERATION_TYPE ORDER BY NAME");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['ID']}</td>
                                <td>{$row['NAME']}</td>
                                <td>{$row['DESCRIPTION']}</td>
                                <td>
                                    <a href='?edit_id={$row['ID']}'>Edit</a> |
                                    <a href='?delete_id={$row['ID']}' onclick=\"return confirm('Are you sure?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No operation types found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
