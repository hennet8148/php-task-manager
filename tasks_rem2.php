<?php

if (!$tasks) {
    die("Error fetching tasks: " . $conn->error);
}

require 'config.php'; // Database credentials

// Database connection
$conn = new mysqli(
    $db_config['servername'],
    $db_config['username'],
    $db_config['password'],
    $db_config['dbname']
);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle CRUD actions
$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskName = $conn->real_escape_string($_POST['task_name']);
    $timeEstimate = intval($_POST['time_estimate']);
    $categoryId = intval($_POST['category_id']);
    $dependency = isset($_POST['dependency']) ? intval($_POST['dependency']) : 'NULL';
    $taskOrder = intval($_POST['task_order']);

    if ($action === 'add') {
        $sql = "INSERT INTO Remodel_Task_Tracking (TaskName, TimeEstimateMinutes, CategoryID, Dependency, TaskOrder)
                VALUES ('$taskName', $timeEstimate, $categoryId, $dependency, $taskOrder)";
    } elseif ($action === 'edit') {
        $taskId = intval($_POST['task_id']);
        $sql = "UPDATE Remodel_Task_Tracking SET TaskName='$taskName', TimeEstimateMinutes=$timeEstimate,
                CategoryID=$categoryId, Dependency=$dependency, TaskOrder=$taskOrder WHERE TaskID=$taskId";
    }
    $conn->query($sql);
    header("Location: ?");
    exit;
}

if ($action === 'delete') {
    $taskId = intval($_GET['task_id']);
    $conn->query("DELETE FROM Remodel_Task_Tracking WHERE TaskID=$taskId");
    header("Location: ?");
    exit;
}

// Fetch data
$tasks = $conn->query("SELECT t.*, c.CategoryName, d.TaskName AS DependencyName FROM Remodel_Task_Tracking t
    LEFT JOIN Remodel_Categories c ON t.CategoryID = c.CategoryID
    LEFT JOIN Remodel_Task_Tracking d ON t.Dependency = d.TaskID ORDER BY t.TaskOrder ASC");
$categories = $conn->query("SELECT * FROM Remodel_Categories");
$conn->close();
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
        <?php if (isset($_GET['edit'])): ?>
            <?php $editTask = $tasks->fetch_assoc(); ?>
            <input type="hidden" name="task_id" value="<?= $editTask['TaskID'] ?>">
        <?php endif; ?>

        <label>Task Name: <input type="text" name="task_name" value="<?= $editTask['TaskName'] ?? '' ?>" required></label><br>
        <label>Time Estimate (min): <input type="number" name="time_estimate" value="<?= $editTask['TimeEstimateMinutes'] ?? '' ?>" required></label><br>
        <label>Category:
            <select name="category_id" required>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?= $category['CategoryID'] ?>" <?= isset($editTask) && $editTask['CategoryID'] == $category['CategoryID'] ? 'selected' : '' ?>>
                        <?= $category['CategoryName'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <label>Dependency:
            <select name="dependency">
                <option value="">None</option>
                <?php foreach ($tasks as $task): ?>
                    <option value="<?= $task['TaskID'] ?>" <?= isset($editTask) && $editTask['Dependency'] == $task['TaskID'] ? 'selected' : '' ?>>
                        <?= $task['TaskName'] ?>
                    </option>
                <?php endforeach; ?>
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
        <?php while ($task = $tasks->fetch_assoc()): ?>
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
