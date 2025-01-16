<?php
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "CHUCK";
$password = "Jack.BOX.1234";
$dbname = "NECK";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$operation_id = "";
$task_id = "";
$operation_type = "";
$speed = "";
$feed = "";
$stock_to_leave = 0;
$notes = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $operation_id = $_POST['operation_id'];
    $task_id = $_POST['task_id'];
    $operation_type = $conn->real_escape_string($_POST['operation_type']);
    $speed = $conn->real_escape_string($_POST['speed']);
    $feed = $conn->real_escape_string($_POST['feed']);
    $stock_to_leave = isset($_POST['stock_to_leave']) ? 1 : 0;
    $notes = $conn->real_escape_string($_POST['notes']);

    if (!empty($operation_id)) {
        // Update the row
        $update_query = "UPDATE OPERATION_DETAILS
                         SET TASK_ID = '$task_id', OPERATION_TYPE = '$operation_type', SPEED = '$speed', FEED = '$feed',
                             STOCK_TO_LEAVE = '$stock_to_leave', NOTES = '$notes'
                         WHERE ID = $operation_id";
        if ($conn->query($update_query) === TRUE) {
            echo "<div class='success'>Operation details updated successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO OPERATION_DETAILS (TASK_ID, OPERATION_TYPE, SPEED, FEED, STOCK_TO_LEAVE, NOTES)
                         VALUES ('$task_id', '$operation_type', '$speed', '$feed', '$stock_to_leave', '$notes')";
        if ($conn->query($insert_query) === TRUE) {
            echo "<div class='success'>New operation details added successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM OPERATION_DETAILS WHERE ID = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<div class='success'>Operation details deleted successfully!</div>";
    } else {
        echo "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_query = "SELECT * FROM OPERATION_DETAILS WHERE ID = $edit_id";
    $edit_result = $conn->query($edit_query);
    if ($edit_result->num_rows > 0) {
        $row = $edit_result->fetch_assoc();
        $operation_id = $row['ID'];
        $task_id = $row['TASK_ID'];
        $operation_type = $row['OPERATION_TYPE'];
        $speed = $row['SPEED'];
        $feed = $row['FEED'];
        $stock_to_leave = $row['STOCK_TO_LEAVE'];
        $notes = $row['NOTES'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operation Details Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <h2><?php echo empty($operation_id) ? "Add New Operation Detail" : "Edit Operation Detail"; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="operation_id" value="<?php echo htmlspecialchars($operation_id); ?>">
            <label for="task_id">Task ID:</label>
            <input type="text" id="task_id" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>" required>

            <label for="operation_type">Operation Type:</label>
            <input type="text" id="operation_type" name="operation_type" value="<?php echo htmlspecialchars($operation_type); ?>" required>

            <label for="speed">Speed:</label>
            <input type="text" id="speed" name="speed" value="<?php echo htmlspecialchars($speed); ?>">

            <label for="feed">Feed:</label>
            <input type="text" id="feed" name="feed" value="<?php echo htmlspecialchars($feed); ?>">

            <label for="stock_to_leave">Stock to Leave:</label>
            <input type="checkbox" id="stock_to_leave" name="stock_to_leave" <?php echo $stock_to_leave ? 'checked' : ''; ?>>

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes"><?php echo htmlspecialchars($notes); ?></textarea>

            <button type="submit"><?php echo empty($operation_id) ? "Submit" : "Update"; ?></button>
        </form>

        <h2>Existing Operation Details</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task ID</th>
                    <th>Operation Type</th>
                    <th>Speed</th>
                    <th>Feed</th>
                    <th>Stock to Leave</th>
                    <th>Notes</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM OPERATION_DETAILS");

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row["ID"]) . "</td>
                                <td>" . htmlspecialchars($row["TASK_ID"]) . "</td>
                                <td>" . htmlspecialchars($row["OPERATION_TYPE"]) . "</td>
                                <td>" . htmlspecialchars($row["SPEED"]) . "</td>
                                <td>" . htmlspecialchars($row["FEED"]) . "</td>
                                <td>" . ($row["STOCK_TO_LEAVE"] ? "Yes" : "No") . "</td>
                                <td>" . htmlspecialchars($row["NOTES"]) . "</td>
                                <td>" . htmlspecialchars($row["CREATED_AT"]) . "</td>
                                <td>" . htmlspecialchars($row["UPDATED_AT"]) . "</td>
                                <td>
                                    <a href='?edit_id=" . htmlspecialchars($row["ID"]) . "'>Edit</a> |
                                    <a href='?delete_id=" . htmlspecialchars($row["ID"]) . "' onclick=\"return confirm('Are you sure you want to delete this operation detail?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No operation details found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
