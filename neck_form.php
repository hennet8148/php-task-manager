<?php
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connections
require_once 'config.php'; // Include the configuration file

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Initialize variables
$neck_id = "";
$neck_name = "";
$notes = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $neck_id = $_POST['neck_id'];
    $neck_name = $conn->real_escape_string($_POST['neck_name']);
    $notes = $conn->real_escape_string($_POST['notes']);

    if (!empty($neck_id)) {
        // Update the row
        $update_query = "UPDATE NECK SET NECK_NAME = '$neck_name', NOTES = '$notes' WHERE ID = $neck_id";
        if ($conn->query($update_query) === TRUE) {
            echo "<div class='success'>Neck updated successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO NECK (NECK_NAME, NOTES) VALUES ('$neck_name', '$notes')";
        if ($conn->query($insert_query) === TRUE) {
            echo "<div class='success'>New neck added successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM NECK WHERE ID = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<div class='success'>Neck deleted successfully!</div>";
    } else {
        echo "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_query = "SELECT * FROM NECK WHERE ID = $edit_id";
    $edit_result = $conn->query($edit_query);
    if ($edit_result->num_rows > 0) {
        $row = $edit_result->fetch_assoc();
        $neck_id = $row['ID'];
        $neck_name = $row['NECK_NAME'];
        $notes = $row['NOTES'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NECK Table Form</title>
    <link rel="stylesheet" href="styles.php">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <h2><?php echo empty($neck_id) ? "Add a New Neck" : "Edit Neck"; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="neck_id" value="<?php echo htmlspecialchars($neck_id); ?>">
            <label for="neck_name">Neck Name:</label>
            <input type="text" id="neck_name" name="neck_name" value="<?php echo htmlspecialchars($neck_name); ?>" required>

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes"><?php echo htmlspecialchars($notes); ?></textarea>

            <button type="submit"><?php echo empty($neck_id) ? "Submit" : "Update"; ?></button>
        </form>

        <div class="links">
            <a href="?">Add New Neck</a>
        </div>

        <h2>Existing Necks</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Neck Name</th>
                    <th>Notes</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM NECK");

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row["ID"]) . "</td>
                                <td>" . htmlspecialchars($row["NECK_NAME"]) . "</td>
                                <td>" . htmlspecialchars($row["NOTES"]) . "</td>
                                <td>" . htmlspecialchars($row["CREATED_AT"]) . "</td>
                                <td>" . htmlspecialchars($row["UPDATED_AT"]) . "</td>
                                <td>
                                    <a href='?edit_id=" . htmlspecialchars($row["ID"]) . "'>Edit</a> |
                                    <a href='?delete_id=" . htmlspecialchars($row["ID"]) . "' onclick=\"return confirm('Are you sure you want to delete this neck?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No necks found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
