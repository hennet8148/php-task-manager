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
$neck_id = "";
$tasks = [];

// Handle filter by Neck ID
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter_neck'])) {
    $neck_id = intval($_POST['neck_id']);

    // Fetch tasks for the specified Neck ID
    $query = "SELECT * FROM TASK WHERE NECK_ID = $neck_id ORDER BY SORT_ORDER";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    } else {
        $tasks = []; // No tasks found
    }
}

// Handle updating tasks
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_tasks'])) {
    if (!empty($_POST['tasks']) && is_array($_POST['tasks'])) {
        foreach ($_POST['tasks'] as $task_id => $task_data) {
            $task_id = intval($task_id);
            $notes = $conn->real_escape_string($task_data['notes']);
            $complete = isset($task_data['complete']) ? 1 : 0;

            // Update each task
            $update_query = "UPDATE TASK SET NOTES = '$notes', COMPLETE = $complete WHERE ID = $task_id";
            $conn->query($update_query);
        }
        echo "<div class='success'>Tasks updated successfully!</div>";
    } else {
        echo "<div class='error'>No tasks to update.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <!-- Filter by Neck ID -->
        <h2>Filter Tasks by Neck ID</h2>
        <form method="post" action="">
            <label for="neck_id">Enter Neck ID:</label>
            <input type="number" id="neck_id" name="neck_id" value="<?php echo $neck_id; ?>" required>
            <button type="submit" name="filter_neck">Filter Tasks</button>
        </form>

        <!-- Display tasks for the selected NECK_ID -->
        <?php if (!empty($tasks)) : ?>
            <h2>Tasks for Neck ID <?php echo $neck_id; ?></h2>
            <form method="post" action="">
                <input type="hidden" name="update_tasks" value="1">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Parent ID</th>
                            <th>Task Name</th>
                            <th>Task Type</th>
                            <th>Sort Order</th>
                            <th>Notes</th>
                            <th>Complete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task) : ?>
                            <tr>
                                <td><?php echo $task['ID']; ?></td>
                                <td><?php echo $task['PARENT_ID'] ?: 'None'; ?></td>
                                <td><?php echo $task['TASK_NAME']; ?></td>
                                <td><?php echo $task['TASK_TYPE']; ?></td>
                                <td><?php echo $task['SORT_ORDER']; ?></td>
                                <td>
                                    <textarea name="tasks[<?php echo $task['ID']; ?>][notes]"><?php echo $task['NOTES']; ?></textarea>
                                </td>
                                <td>
                                    <input type="checkbox" name="tasks[<?php echo $task['ID']; ?>][complete]" <?php echo $task['COMPLETE'] ? 'checked' : ''; ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Update Tasks</button>
            </form>
        <?php elseif (!empty($neck_id)) : ?>
            <p>No tasks found for Neck ID <?php echo $neck_id; ?>.</p>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
