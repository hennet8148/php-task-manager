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
        <h2><?php echo empty($template_id) ? "Add a New Task Template" : "Edit Task Template"; ?></h2>
        <form method="post" action="">
            <!-- ID Field (Read-Only for Updates) -->
            <label for="template_id">Template ID:</label>
            <input type="text" id="template_id" name="template_id" value="<?php echo $template_id; ?>" <?php echo empty($template_id) ? '' : 'readonly'; ?>>

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
                $result = $conn->query("SELECT * FROM TASK_TEMPLATE ORDER BY SORT_ORDER");

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["ID"] . "</td>
                                <td>" . $row["PARENT_ID"] . "</td>
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
