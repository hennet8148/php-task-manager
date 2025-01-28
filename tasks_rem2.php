<?php
require 'config.php'; // Database credentials

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli(
    $db_config['servername'],
    $db_config['username'],
    $db_config['password'],
    $db_config['dbname']
);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connection successful.<br>";
}

// Handle CRUD actions
$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Processing form submission...<br>";
    $taskName = $conn->real_escape_string($_POST['task_name']);
    $timeEstimate = intval($_POST['time_estimate']);
    $categoryId = intval($_POST['category_id']);
    $dependency = isset($_POST['dependency']) ? intval($_POST['dependency']) : 'NULL';
    $taskOrder = intval($_POST['task_order']);

    if ($action === 'add') {
        echo "Adding a new task...<br>";
        $sql = "INSERT INTO Remodel_Task_Tracking (TaskName, TimeEstimateMinutes, CategoryID, Dependency, TaskOrder)
                VALUES ('$taskName', $timeEstimate, $categoryId, $dependency, $taskOrder)";
    } elseif ($action === 'edit') {
        echo "Editing an existing task...<br>";
        $taskId = intval($_POST['task_id']);
        $sql = "UPDATE Remodel_Task_Tracking SET TaskName='$taskName', TimeEstimateMinutes=$timeEstimate,
                CategoryID=$categoryId, Dependency=$dependency, TaskOrder=$taskOrder WHERE TaskID=$taskId";
    }
    if ($conn->query($sql) === TRUE) {
        echo "Query executed successfully: $sql<br>";
    } else {
        echo "Error executing query: " . $conn->error . "<br>";
    }
    header("Location: ?");
    exit;
}

if ($action === 'delete') {
    echo "Deleting a task...<br>";
    $taskId = intval($_GET['task_id']);
    $sql = "DELETE FROM Remodel_Task_Tracking WHERE TaskID=$taskId";
    if ($conn->query($sql) === TRUE) {
        echo "Task deleted successfully.<br>";
    } else {
        echo "Error deleting task: " . $conn->error . "<br>";
    }
    header("Location: ?");
    exit;
}

// Fetch data
$tasks = $conn->query("SELECT t.*, c.CategoryName, d.TaskName AS DependencyName FROM Remodel_Task_Tracking t
    LEFT JOIN Remodel_Categories c ON t.CategoryID = c.CategoryID
    LEFT JOIN Remodel_Task_Tracking d ON t.Dependency = d.TaskID ORDER BY t.TaskOrder ASC");
if (!$tasks) {
    die("Error fetching tasks: " . $conn->error);
} else {
    echo "Tasks fetched: " . $tasks->num_rows . "<br>";
}

$categories = $conn->query("SELECT * FROM Remodel_Categories");
if (!$categories) {
    die("Error fetching categories: " . $conn->error);
} else {
    echo "Categories fetched: " . $categories->num_rows . "<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remodel Task Tracking</title>
</head>
<body>
    <h1>Remodel Task Tracking</h1>

    <form method="POST" action="?action=<?= isset($_GET['edit']) ? 'edit' : 'add' ?>">
        <?php if (isset($_GET['edit']) && isset($_GET['task_id'])): ?>
            <?php
            $taskId = intval($_GET['task_id']);
            $editTaskResult = $conn->query("SELECT * FROM Remodel_Task_Tracking WHERE TaskID=$taskId");
            if ($editTaskResult->num_rows === 0) {
                die("Error: Task not found.");
            }
            $editTask = $editTaskResult->fetch_assoc();
            echo "Loaded task for editing: " . htmlspecialchars($editTask['TaskName']) . "<br>";
            ?>
            <input type="hidden" name="task_id" value="<?= $editTask['TaskID'] ?>">
        <?php endif; ?>

        <label>Task Name: <input type="text" name="task_name" value="<?= $editTask['TaskName'] ?? '' ?>" required></label><br>
        <label>Time Estimate (min): <input type="number" name="time_estimate" value="<?= $editTask['TimeEstimateMinutes'] ?? '' ?>" required></label><br>
        <label>Category:
            <select name="category_id" required>
                <?php $categories->data_seek(0); while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?= $category['CategoryID'] ?>" <?= isset($editTask) && $editTask['CategoryID'] == $category['CategoryID'] ? 'selected' : '' ?>>
                        <?= $category['CategoryName'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <label>Dependency:
            <select name="dependency">
                <option value="">None</option>
                <?php $tasks->data_seek(0); while ($task = $tasks->fetch_assoc()): ?>
                    <option value="<?= $task['TaskID'] ?>" <?= isset($editTask) && $editTask['Dependency'] == $task['TaskID'] ? 'selected' : '' ?>>
                        <?= $task['TaskName'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <label>Task Order: <input type="number" name="task_order" value="<?= $editTask['TaskOrder'] ?? '' ?>" required></label><br>
        <button type="submit">Submit</button>
    </form>

    <h2>Existing Tasks</h2>
    <table border="1">
        <tr>
            <th>Task Name</th>
            <th>Time Estimate</th>
            <th>Category</th>
            <th>Dependency</th>
            <th>Task Order</th>
            <th>Completed</th>
            <th>Actions</th>
        </tr>
        <?php $tasks->data_seek(0); while ($task = $tasks->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($task['TaskName']) ?></td>
                <td><?= $task['TimeEstimateMinutes'] ?></td>
                <td><?= htmlspecialchars($task['CategoryName']) ?></td>
                <td><?= htmlspecialchars($task['DependencyName'] ?? 'None') ?></td>
                <td><?= $task['TaskOrder'] ?></td>
                <td><?= $task['Completed'] ? 'Yes' : 'No' ?></td>
                <td>
                    <a href="?edit=1&task_id=<?= $task['TaskID'] ?>">Edit</a> |
                    <a href="?action=delete&task_id=<?= $task['TaskID'] ?>" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
