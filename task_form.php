<?php
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
$task_id = "";
$neck_id = "";
$parent_id = "";
$task_name = "";
$task_type = "";
$sort_order = "";
$notes = "";
$status = "Pending";
$complete = 0;
$task_template_id = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_id = $_POST['task_id'];
    $neck_id = $_POST['neck_id'];
    $parent_id = $_POST['parent_id'];
    $task_name = $_POST['task_name'];
    $task_type = $_POST['task_type'];
    $sort_order = $_POST['sort_order'];
    $notes = $_POST['notes'];
    $status = $_POST['status'];
    $complete = isset($_POST['complete']) ? 1 : 0;
    $task_template_id = $_POST['task_template_id'];

    if (!empty($task_id)) {
        // Update the row
        $update_query = "UPDATE TASK 
                         SET NECK_ID = '$neck_id', PARENT_ID = " . ($parent_id === '' ? 'NULL' : "'$parent_id'") . ",
                             TASK_NAME = '$task_name', TASK_TYPE = '$task_type', SORT_ORDER = '$sort_order', 
                             NOTES = '$notes', STATUS = '$status', COMPLETE = '$complete', 
                             TASK_TEMPLATE_ID = " . ($task_template_id === '' ? 'NULL' : "'$task_template_id'") . " 
                         WHERE ID = $task_id";
        if ($conn->query($update_query) === TRUE) {
            echo "<div class='success'>Task updated successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO TASK (NECK_ID, PARENT_ID, TASK_NAME, TASK_TYPE, SORT_ORDER, NOTES, STATUS, COMPLETE, TASK_TEMPLATE_ID) 
                         VALUES ('$neck_id', " . ($parent_id === '' ? 'NULL' : "'$parent_id'") . ", '$task_name', '$task_type', 
                                 '$sort_order', '$notes', '$status', '$complete', " . ($task_template_id === '' ? 'NULL' : "'$task_template_id'") . ")";
        if ($conn->query($insert_query) === TRUE) {
            echo "<div class='success'>New task added successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM TASK WHERE ID = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<div class='success'>Task deleted successfully!</div>";
    } else {
        echo "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM TASK WHERE ID = $edit_id";
    $edit_result = $conn->query($edit_query);
    if ($edit_result->num_rows > 0) {
        $row = $edit_result->fetch_assoc();
        $task_id = $row['ID'];
        $neck_id = $row['NECK_ID'];
        $parent_id = $row['PARENT_ID'];
        $task_name = $row['TASK_NAME'];
        $task_type = $row['TASK_TYPE'];
        $sort_order = $row['SORT_ORDER'];
        $notes = $row['NOTES'];
        $status = $row['STATUS'];
        $complete = $row['COMPLETE'];
        $task_template_id = $row['TASK_TEMPLATE_ID'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f9;
        }

        h2 {
            color: #333;
        }

        form {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            max-width: 500px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        input[type="checkbox"] {
            margin-bottom: 15px;
        }

        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #4cae4c;
        }

        .success {
            color: green;
            margin: 10px 0;
        }

        .error {
            color: red;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f4f4f4;
        }

        .links {
            margin-top: 10px;
        }

        .links a {
            text-decoration: none;
            color: #007bff;
            margin-right: 15px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2><?php echo empty($task_id) ? "Add New Task" : "Edit Task"; ?></h2>
    <form method="post" action="">
        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
        <label for="neck_id">Neck ID:</label>
        <input type="text" id="neck_id" name="neck_id" value="<?php echo $neck_id; ?>" required>

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

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="In Progress" <?php echo $status == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
            <option value="Complete" <?php echo $status == 'Complete' ? 'selected' : ''; ?>>Complete</option>
        </select>

        <label for="complete">Complete:</label>
        <input type="checkbox" id="complete" name="complete" <?php echo $complete ? 'checked' : ''; ?>>

        <label for="task_template_id">Task Template ID:</label>
        <input type="text" id="task_template_id" name="task_template_id" value="<?php echo $task_template_id; ?>">

        <input type="submit" value="<?php echo empty($task_id) ? "Submit" : "Update"; ?>">
    </form>

    <div class="links">
        <a href="?">Add New Task</a>
        <a href="?view=all">View All Tasks</a>
        <a href="neck_form.php">Manage Necks</a>
        <a href="task_template_form.php">Manage Task Templates</a>
        <a href="setup_details_form.php">Manage Setup Details</a>
        <a href="operation_details_form.php">Manage Operation Details</a>
    </div>

    <h2>Existing Tasks</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Neck ID</th>
            <th>Parent ID</th>
            <th>Task Name</th>
            <th>Task Type</th>
            <th>Sort Order</th>
            <th>Status</th>
            <th>Complete</th>
            <th>Notes</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM TASK ORDER BY NECK_ID, SORT_ORDER");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["ID"] . "</td>
                        <td>" . $row["NECK_ID"] . "</td>
                        <td>" . ($row["PARENT_ID"] ?: "None") . "</td>
                        <td>" . $row["TASK_NAME"] . "</td>
                        <td>" . $row["TASK_TYPE"] . "</td>
                        <td>" . $row["SORT_ORDER"] . "</td>
                        <td>" . $row["STATUS"] . "</td>
                        <td>" . ($row["COMPLETE"] ? "Yes" : "No") . "</td>
                        <td>" . $row["NOTES"] . "</td>
                        <td>
                            <a href='?edit_id=" . $row["ID"] . "'>Edit</a> |
                            <a href='?delete_id=" . $row["ID"] . "' onclick=\"return confirm('Are you sure you want to delete this task?');\">Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='10'>No tasks found</td></tr>";
        }
        ?>
    </table>
</body>
</html>

