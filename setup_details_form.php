<?php
// Database connection
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "CHUCK";
$password = "Jack.BOX.1234";
$dbname = "NECK";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$setup_id = "";
$task_id = "";
$model_stock = "";
$model_cut = "";
$notes = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $setup_id = $_POST['setup_id'];
    $task_id = $_POST['task_id'];
    $model_stock = $_POST['model_stock'];
    $model_cut = $_POST['model_cut'];
    $notes = $_POST['notes'];

    if (!empty($setup_id)) {
        // Update the row
        $update_query = "UPDATE SETUP_DETAILS
                         SET TASK_ID = '$task_id', MODEL_STOCK = '$model_stock', MODEL_CUT = '$model_cut', NOTES = '$notes'
                         WHERE ID = $setup_id";
        if ($conn->query($update_query) === TRUE) {
            echo "<div class='success'>Setup details updated successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO SETUP_DETAILS (TASK_ID, MODEL_STOCK, MODEL_CUT, NOTES)
                         VALUES ('$task_id', '$model_stock', '$model_cut', '$notes')";
        if ($conn->query($insert_query) === TRUE) {
            echo "<div class='success'>New setup details added successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM SETUP_DETAILS WHERE ID = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<div class='success'>Setup details deleted successfully!</div>";
    } else {
        echo "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM SETUP_DETAILS WHERE ID = $edit_id";
    $edit_result = $conn->query($edit_query);
    if ($edit_result->num_rows > 0) {
        $row = $edit_result->fetch_assoc();
        $setup_id = $row['ID'];
        $task_id = $row['TASK_ID'];
        $model_stock = $row['MODEL_STOCK'];
        $model_cut = $row['MODEL_CUT'];
        $notes = $row['NOTES'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Details Management</title>
    <link rel="stylesheet" href="styles.php">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <h2><?php echo empty($setup_id) ? "Add a New Setup Detail" : "Edit Setup Detail"; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="setup_id" value="<?php echo $setup_id; ?>">
            <label for="task_id">Task ID:</label>
            <input type="text" id="task_id" name="task_id" value="<?php echo $task_id; ?>" required>

            <label for="model_stock">Model Stock:</label>
            <input type="text" id="model_stock" name="model_stock" value="<?php echo $model_stock; ?>" required>

            <label for="model_cut">Model Cut:</label>
            <input type="text" id="model_cut" name="model_cut" value="<?php echo $model_cut; ?>" required>

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes"><?php echo $notes; ?></textarea>

            <button type="submit"><?php echo empty($setup_id) ? "Submit" : "Update"; ?></button>
        </form>

        <h2>Existing Setup Details</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task ID</th>
                    <th>Model Stock</th>
                    <th>Model Cut</th>
                    <th>Notes</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM SETUP_DETAILS");

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["ID"] . "</td>
                                <td>" . $row["TASK_ID"] . "</td>
                                <td>" . $row["MODEL_STOCK"] . "</td>
                                <td>" . $row["MODEL_CUT"] . "</td>
                                <td>" . $row["NOTES"] . "</td>
                                <td>" . $row["CREATED_AT"] . "</td>
                                <td>" . $row["UPDATED_AT"] . "</td>
                                <td>
                                    <a href='?edit_id=" . $row["ID"] . "'>Edit</a> |
                                    <a href='?delete_id=" . $row["ID"] . "' onclick=\"return confirm('Are you sure you want to delete this setup detail?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No setup details found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
