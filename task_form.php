<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        main {
            margin: 20px auto;
            max-width: 95%;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        form {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            max-width: 100%;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        form label {
            font-weight: bold;
        }

        form input[type="number"],
        form textarea,
        form button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            background-color: #0056b3;
            color: white;
            cursor: pointer;
            border: none;
            font-size: 16px;
            padding: 10px 20px;
        }

        form button:hover {
            background-color: #004494;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }

        table th, table td {
            text-align: left;
            padding: 12px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            color: #333;
            text-transform: uppercase;
            font-size: 14px;
        }

        table td textarea {
            width: 100%;
            height: 70px;
            font-size: 14px;
        }

        table td input[type="checkbox"] {
            transform: scale(1.5);
            margin: 5px;
        }

        button[type="submit"] {
            margin: 20px auto;
            display: block;
            padding: 15px 25px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
        }

        button[type="submit"]:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            text-align: center;
            margin: 10px 0;
        }

        .success {
            color: green;
            text-align: center;
            margin: 10px 0;
        }
    </style>
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
