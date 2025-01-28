<?php
require 'config.php'; // Include database credentials

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

// Handle form submission for adding a new task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $taskName = $conn->real_escape_string($_POST['task_name']);
    $timeEstimate = intval($_POST['time_estimate']);
    $categoryId = intval($_POST['category_id']);
    $categoryExists = $conn->query("SELECT 1 FROM Remodel_Categories WHERE CategoryID = $categoryId");
    if ($categoryExists->num_rows === 0) {
        die("Error: Invalid CategoryID. Please select a valid category.");
    }
    $dependency = isset($_POST['dependency']) ? intval($_POST['dependency']) : NULL;
    $taskOrder = intval($_POST['task_order']);

    $sql = "INSERT INTO Remodel_Task_Tracking (TaskName, TimeEstimateMinutes, CategoryID, Dependency, Completed, TaskOrder)
            VALUES ('$taskName', $timeEstimate, $categoryId, ". ($dependency ? "$dependency" : "NULL") .", FALSE, $taskOrder)";

    if ($conn->query($sql) === TRUE) {
        echo "Task added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle form submission for editing a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $taskId = intval($_POST['task_id']);
    $taskName = $conn->real_escape_string($_POST['task_name']);
    $timeEstimate = intval($_POST['time_estimate']);
    $categoryId = intval($_POST['category_id']);
    $categoryExists = $conn->query("SELECT 1 FROM Remodel_Categories WHERE CategoryID = $categoryId");
    if ($categoryExists->num_rows === 0) {
        die("Error: Invalid CategoryID. Please select a valid category.");
    }
    $dependency = isset($_POST['dependency']) ? intval($_POST['dependency']) : NULL;
    $taskOrder = intval($_POST['task_order']);

    $sql = "UPDATE Remodel_Task_Tracking
            SET TaskName = '$taskName', TimeEstimateMinutes = $timeEstimate, CategoryID = $categoryId, Dependency = ". ($dependency ? "$dependency" : "NULL") .", TaskOrder = $taskOrder
            WHERE TaskID = $taskId";

    if ($conn->query($sql) === TRUE) {
        echo "Task updated successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle deleting a task
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $taskId = intval($_GET['task_id']);

    $sql = "DELETE FROM Remodel_Task_Tracking WHERE TaskID = $taskId";

    if ($conn->query($sql) === TRUE) {
        echo "Task deleted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch categories for the dropdown
$categoryResult = $conn->query("SELECT CategoryID, CategoryName FROM Remodel_Categories");
$categories = $categoryResult->fetch_all(MYSQLI_ASSOC);

// Fetch existing tasks for dependencies dropdown
$tasksResult = $conn->query("SELECT TaskID, TaskName FROM Remodel_Task_Tracking");
$tasks = $tasksResult->fetch_all(MYSQLI_ASSOC);

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
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <label for="task_name">Task Name:</label>
        <input type="text" id="task_name" name="task_name" required><br>

        <label for="time_estimate">Time Estimate (minutes):</label>
        <input type="number" id="time_estimate" name="time_estimate" required><br>

        <label for="category_id">Category:</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['CategoryID'] ?>">
                    <?= htmlspecialchars($category['CategoryName']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="dependency">Dependency (Optional):</label>
        <select id="dependency" name="dependency">
            <option value="">-- No Dependency --</option>
            <?php foreach ($tasks as $task): ?>
                <option value="<?= $task['TaskID'] ?>">
                    <?= htmlspecialchars($task['TaskName']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="task_order">Task Order:</label>
        <input type="number" id="task_order" name="task_order" required><br>

        <button type="submit">Add Task</button>
    </form>

    <h2>Existing Tasks</h2>
    <table border="1">
        <tr>
            <th>Task Name</th>
            <th>Time Estimate (minutes)</th>
            <th>Category</th>
            <th>Dependency</th>
            <th>Task Order</th>
            <th>Completed</th>
            <th>Actions</th>
        </tr>
        <?php
        $conn = new mysqli(
            $db_config['servername'],
            $db_config['username'],
            $db_config['password'],
            $db_config['dbname']
        );
        $tasksResult = $conn->query("SELECT t.TaskID, t.TaskName, t.TimeEstimateMinutes, c.CategoryName, d.TaskName AS DependencyName, t.Completed, t.TaskOrder
                                     FROM Remodel_Task_Tracking t
                                     LEFT JOIN Remodel_Categories c ON t.CategoryID = c.CategoryID
                                     LEFT JOIN Remodel_Task_Tracking d ON t.Dependency = d.TaskID
                                     ORDER BY t.TaskOrder ASC");
        while ($row = $tasksResult->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['TaskName']) ?></td>
                <td><?= $row['TimeEstimateMinutes'] ?></td>
                <td><?= htmlspecialchars($row['CategoryName']) ?></td>
                <td><?= htmlspecialchars($row['DependencyName'] ?? 'None') ?></td>
                <td><?= $row['TaskOrder'] ?></td>
                <td><?= $row['Completed'] ? 'Yes' : 'No' ?></td>
                <td>
                    <a href="?action=edit_form&task_id=<?= $row['TaskID'] ?>">Edit</a> |
                    <a href="?action=delete&task_id=<?= $row['TaskID'] ?>" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php $conn->close(); ?>
    </table>

    <?php if (isset($_GET['action']) && $_GET['action'] === 'edit_form' && isset($_GET['task_id'])):
        $taskId = intval($_GET['task_id']);
        $taskResult = $conn->query("SELECT * FROM Remodel_Task_Tracking WHERE TaskID = $taskId");
        $task = $taskResult->fetch_assoc();
    ?>
        <h2>Edit Task</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="task_id" value="<?= $task['TaskID'] ?>">

            <label for="task_name">Task Name:</label>
            <input type="text" id="task_name" name="task_name" value="<?= htmlspecialchars($task['TaskName']) ?>" required><br>

            <label for="time_estimate">Time Estimate (minutes):</label>
            <input type="number" id="time_estimate" name="time_estimate" value="<?= $task['TimeEstimateMinutes'] ?>" required><br>

            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['CategoryID'] ?>" <?= $category['CategoryID'] == $task['CategoryID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['CategoryName']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label for="dependency">Dependency (Optional):</label>
            <select id="dependency" name="dependency">
                <option value="">-- No Dependency --</option>
                <?php foreach ($tasks as $t): ?>
                    <option value="<?= $t['TaskID'] ?>" <?= $t['TaskID'] == $task['Dependency'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['TaskName']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label for="task_order">Task Order:</label>
            <input type="number" id="task_order" name="task_order" value="<?= $task['TaskOrder'] ?>" required><br>

            <button type="submit">Update Task</button>
        </form>
    <?php endif; ?>
</body>
</html>
