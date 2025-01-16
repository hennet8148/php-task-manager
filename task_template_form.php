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
$template_id = "";
$parent_id = "";
$task_name = "";
$task_type = "";
$sort_order = "";
$notes = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
