<?php
require 'config.php'; // Include database credentials

// Database connection
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskName = $conn->real_escape_string($_POST['task_name']);
    $timeEstimate = intval($_POST['time_estimate']);
    $categoryId = intval($_POST['category_id']);
    $dependency = isset($_POST['dependency']) ? intval($_POST['dependency']) : NULL;

    $sql = "INSERT INTO Remodel_Task_Tracking (TaskName, TimeEstimateMinutes, CategoryID, Dependency, Completed)
            VALUES ('$taskName', $timeEstimate, $categoryId, ". ($dependency ? "$dependency" : "NULL") .", FALSE)";

    if ($conn->query($sql) === TRUE) {
        echo "Task added successfully!";
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

        <button type="submit">Add Task</button>
    </form>

    <h2>Existing Tasks</h2>
    <table border="1">
        <tr>
            <th>Task Name</th>
            <th>Time Estimate (minutes)</th>
            <th>Category</th>
            <th>Dependency</th>
            <th>Completed</th>
        </tr>
        <?php
        $conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
        $tasksResult = $conn->query("SELECT t.TaskName, t.TimeEstimateMinutes, c.CategoryName, d.TaskName AS DependencyName, t.Completed
                                     FROM Remodel_Task_Tracking t
                                     LEFT JOIN Remodel_Categories c ON t.CategoryID = c.CategoryID
                                     LEFT JOIN Remodel_Task_Tracking d ON t.Dependency = d.TaskID");
        while ($row = $tasksResult->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['TaskName']) ?></td>
                <td><?= $row['TimeEstimateMinutes'] ?></td>
                <td><?= htmlspecialchars($row['CategoryName']) ?></td>
                <td><?= htmlspecialchars($row['DependencyName'] ?? 'None') ?></td>
                <td><?= $row['Completed'] ? 'Yes' : 'No' ?></td>
            </tr>
        <?php endwhile; ?>
        <?php $conn->close(); ?>
    </table>
</body>
</html>
