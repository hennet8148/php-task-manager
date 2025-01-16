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
$template_id = "";
$parent_id = "";
$task_name = "";
$task_type = "";
$sort_order = "";
$notes = "";

// Handle sync tasks button
if (isset($_POST['sync_tasks'])) {
    $neck_id = intval($_POST['neck_id']);

    if ($neck_id > 0) {
        // SQL to synchronize tasks and include PARENT_ID mapping
        $sync_query = "
            INSERT INTO TASK (NECK_ID, TASK_TEMPLATE_ID, PARENT_ID, TASK_NAME, TASK_TYPE, SORT_ORDER, NOTES, STATUS, COMPLETE)
            SELECT
                $neck_id,
                t.ID,
                tk.ID AS PARENT_ID,
                t.TASK_NAME,
                t.TASK_TYPE,
                t.SORT_ORDER,
                t.NOTES,
                'Pending',
                0
            FROM TASK_TEMPLATE t
            LEFT JOIN TASK tk ON t.PARENT_ID = tk.TASK_TEMPLATE_ID AND tk.NECK_ID = $neck_id
            LEFT JOIN TASK existing ON t.ID = existing.TASK_TEMPLATE_ID AND existing.NECK_ID = $neck_id
            WHERE existing.ID IS NULL;
        ";

        if ($conn->query($sync_query) === TRUE) {
            echo "<div class='success'>Tasks synchronized successfully for Neck ID $neck_id!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='error'>Invalid Neck ID!</div>";
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['sync_tasks'])) {
    $template_id = $_POST['template_id'];
    $parent_id = $_POST['parent_id'];
    $task_name = $_POST['task_name'];
    $task_type = $_POST['task_type'];
    $sort_order = $_POST['sort_order'];
    $notes = $_POST['notes'];

    if (!empty($template_id)) {
        // Update the row
        $update_query = "UPDATE TASK_TEMPLATE
                         SET PARENT_ID = " . ($parent_id === '' ? 'NULL' : "'$parent_id'") . ",
                             TASK_NAME = '$task_name',
                             TASK_TYPE = '$task_type',
                             SORT_ORDER = '$sort_order',
                             NOTES = '$notes'
                         WHERE ID = $template_id";
        if ($conn->query($update_query) === TRUE) {
            echo "<div class='success'>Task template updated successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO TASK_TEMPLATE (PARENT_ID, TASK_NAME, TASK_TYPE, SORT_ORDER, NOTES)
                         VALUES (" . ($parent_id === '' ? 'NULL' : "'$parent_id'") . ", '$task_name', '$task_type', '$sort_order', '$notes')";
        if ($conn->query($insert_query) === TRUE) {
            echo "<div class='success'>New task template added successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM TASK_TEMPLATE WHERE ID = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<div class='success'>Task template deleted successfully!</div>";
    } else {
        echo "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM TASK_TEMPLATE WHERE ID = $edit_id";
    $edit_result = $conn->query($edit_query);
    if ($edit_result->num_rows > 0) {
        $row = $edit_result->fetch_assoc();
        $template_id = $row['ID'];
        $parent_id = $row['PARENT_ID'];
        $task_name = $row['TASK_NAME'];
        $task_type = $row['TASK_TYPE'];
        $sort_order = $row['SORT_ORDER'];
        $notes = $row['NOTES'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Template Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <!-- Sync Tasks Form -->
        <h2>Sync Tasks</h2>
        <form method="post" action="">
            <label for="neck_id">Enter Neck ID:</label>
            <input type="number" id="neck_id" name="neck_id" required>
            <button type="submit" name="sync_tasks">Sync Tasks</button>
        </form>

        <!-- Add/Edit Task Template Form -->
        <h2><?php echo empty($template_id) ? "Add a New Task Template" : "Edit Task Template"; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="template_id" value="<?php echo $template_id; ?>">
            <label for="parent_id">Parent ID:</label>
            <input type="text" id="parent_id" name="parent_id" value="<?php echo $parent_id; ?>">

            <label for="task_name">Task Name:</label>
            <input type="text" id="task_name" name="task_name" value="<?php echo $task_name; ?>" required>

            <label for="task_type">Task Type:</label>
            <select id="task_type" name="task_type" required>
                <option value="">--Select Task Type--</option>
                <option value="Step" <?php echo $task_type == 'Step' ? 'selected' : ''; ?>>Step</option>
                <option value="Sub-Step" <?php echo $task_type == 'Sub-Step' ? 'selected' : ''; ?>>Sub-Step</option>
                <option value="Setup" <?php echo $task_type == 'Setup' ? 'selected' : ''; ?>>Setup</option>
                <option value="Operation" <?php echo $task_type == 'Operation' ? 'selected' : ''; ?>>Operation</option>
            </select>

            <label for="sort_order">Sort Order:</label>
            <input type="text" id="sort_order" name="sort_order" value="<?php echo $sort_order; ?>" required>

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes"><?php echo $notes; ?></textarea>

            <button type="submit"><?php echo empty($template_id) ? "Submit" : "Update"; ?></button>
        </form>

        <!-- Display Task Templates -->
        <h2>Existing Task Templates</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Parent ID</th>
                    <th>Task Name</th>
                    <th>Task Type</th>
                    <th>Sort Order</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM TASK_TEMPLATE ORDER BY SORT_ORDER";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["ID"] . "</td>
                                <td>" . ($row["PARENT_ID"] ?? "None") . "</td>
                                <td>" . $row["TASK_NAME"] . "</td>
                                <td>" . $row["TASK_TYPE"] . "</td>
                                <td>" . $row["SORT_ORDER"] . "</td>
                                <td>" . $row["NOTES"] . "</td>
                                <td>
                                    <a href='?edit_id=" . $row["ID"] . "'>Edit</a> |
                                    <a href='?delete_id=" . $row["ID"] . "' onclick=\"return confirm('Are you sure you want to delete this task?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No task templates found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
