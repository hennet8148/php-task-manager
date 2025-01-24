<?php
// Database connection
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php'; // Include the configuration file

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Initialize variables
$setup_id = "";
$task_id = "";
$model_stock = "";
$model_cut = "";
$parent_id = NULL; // Use NULL by default
$notes = "";

// Fetch tasks for dropdown, limited to type 'SETUP'
$tasks_result = $conn->query("SELECT ID, TASK_NAME FROM TASK_TEMPLATE WHERE TASK_TYPE = 'SETUP' ORDER BY TASK_NAME");
$tasks = [];
while ($row = $tasks_result->fetch_assoc()) {
    $tasks[$row['ID']] = $row['TASK_NAME'];
}

// Fetch components for dropdowns
$components_result = $conn->query("SELECT ID, NAME FROM COMPONENTS ORDER BY NAME");
$components = [];
while ($row = $components_result->fetch_assoc()) {
    $components[$row['ID']] = $row['NAME'];
}

// Fetch tasks for Parent Step dropdown, limited to type 'Sub-Step'
$substeps_result = $conn->query("
    SELECT ID, TASK_NAME
    FROM TASK_TEMPLATE
    WHERE TASK_TYPE = 'Sub-Step'
    ORDER BY TASK_NAME
");

if ($substeps_result === FALSE) {
    die("Error fetching Parent Steps: " . $conn->error);
}

$substeps = [];
while ($row = $substeps_result->fetch_assoc()) {
    $substeps[$row['ID']] = $row['TASK_NAME'];
}



// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $setup_id = $_POST['setup_id'];
    $task_id = $_POST['task_id'];
    $model_stock = $_POST['model_stock'];
    $model_cut = $_POST['model_cut'];
    $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? $_POST['parent_id'] : NULL; // Convert empty to NULL
    if ($parent_id !== NULL) {
        // Check if the submitted parent_id is valid
        $parent_check_query = "SELECT ID FROM TASK_TEMPLATE WHERE ID = $parent_id AND TASK_TYPE = 'Sub-Step'";
        $parent_check_result = $conn->query($parent_check_query);

        // If no matching row is found, stop the process with an error
        if ($parent_check_result->num_rows === 0) {
            die("<div class='error'>Invalid Parent Step selected. Please select a valid Parent Step.</div>");
        }
    }


    $notes = $conn->real_escape_string($_POST['notes']);

    if (!empty($setup_id)) {
        // Update the row
        $update_query = "UPDATE SETUP_DETAILS
                         SET TASK_ID = '$task_id', MODEL_STOCK = '$model_stock', MODEL_CUT = '$model_cut', PARENT_ID = " . ($parent_id !== NULL ? "'$parent_id'" : "NULL") . ", NOTES = '$notes'
                         WHERE ID = $setup_id";
        if ($conn->query($update_query) === TRUE) {
            echo "<div class='success'>Setup details updated successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO SETUP_DETAILS (TASK_ID, MODEL_STOCK, MODEL_CUT, PARENT_ID, NOTES)
                         VALUES ('$task_id', '$model_stock', '$model_cut', " . ($parent_id !== NULL ? "'$parent_id'" : "NULL") . ", '$notes')";
        if ($conn->query($insert_query) === TRUE) {
            echo "<div class='success'>New setup details added successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM SETUP_DETAILS WHERE ID = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<div class='success'>Setup details deleted successfully!</div>";
    } else {
        echo "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM SETUP_DETAILS WHERE ID = $edit_id";
    $edit_result = $conn->query($edit_query);
    if ($edit_result->num_rows > 0) {
        $row = $edit_result->fetch_assoc();
        $setup_id = $row['ID'];
        $task_id = $row['TASK_ID'];
        $model_stock = $row['MODEL_STOCK'];
        $model_cut = $row['MODEL_CUT'];
        $parent_id = $row['PARENT_ID'];
        $notes = $row['NOTES'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Details Management</title>
    <link rel="stylesheet" href="styles.php">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <h2><?php echo empty($setup_id) ? "Add a New Setup Detail" : "Edit Setup Detail"; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="setup_id" value="<?php echo $setup_id; ?>">

            <label for="task_id">Task:</label>
            <select id="task_id" name="task_id" required>
                <option value="">-- Select Task --</option>
                <?php foreach ($tasks as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo $task_id == $id ? 'selected' : ''; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="model_stock">Model Stock:</label>
            <select id="model_stock" name="model_stock" required>
                <option value="">-- Select Model Stock --</option>
                <?php foreach ($components as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo $model_stock == $id ? 'selected' : ''; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="model_cut">Model Cut:</label>
            <select id="model_cut" name="model_cut" required>
                <option value="">-- Select Model Cut --</option>
                <?php foreach ($components as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo $model_cut == $id ? 'selected' : ''; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="parent_id">Parent Step:</label>
  <select id="parent_id" name="parent_id">
      <option value="">-- Select Parent Step --</option>
      <?php foreach ($substeps as $id => $name): ?>
          <option value="<?php echo $id; ?>" <?php echo $parent_id == $id ? 'selected' : ''; ?>>
              <?php echo $name; ?>
          </option>
      <?php endforeach; ?>
  </select>



            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes"><?php echo $notes; ?></textarea>

            <button type="submit"><?php echo empty($setup_id) ? "Submit" : "Update"; ?></button>
        </form>

        <h2>Existing Setup Details</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task</th>
                    <th>Model Stock</th>
                    <th>Model Cut</th>
                    <th>Parent Step</th>
                    <th>Notes</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT sd.*, t.TASK_NAME, cs1.NAME AS STOCK_NAME, cs2.NAME AS CUT_NAME, tp.TASK_NAME AS PARENT_TASK
                          FROM SETUP_DETAILS sd
                          LEFT JOIN TASK_TEMPLATE t ON sd.TASK_ID = t.ID
                          LEFT JOIN COMPONENTS cs1 ON sd.MODEL_STOCK = cs1.ID
                          LEFT JOIN COMPONENTS cs2 ON sd.MODEL_CUT = cs2.ID
                          LEFT JOIN TASK_TEMPLATE tp ON sd.PARENT_ID = tp.ID");


                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["ID"] . "</td>
                                <td>" . $row["TASK_NAME"] . "</td>
                                <td>" . $row["STOCK_NAME"] . "</td>
                                <td>" . $row["CUT_NAME"] . "</td>
                                <td>" . ($row["PARENT_TASK"] ? "Parent Task ID: " . $row["PARENT_TASK"] : "None") . "</td>
                                <td>" . $row["NOTES"] . "</td>
                                <td>" . $row["CREATED_AT"] . "</td>
                                <td>" . $row["UPDATED_AT"] . "</td>
                                <td>
                                    <a href='?edit_id=" . $row["ID"] . "'>Edit</a> |
                                    <a href='?delete_id=" . $row["ID"] . "' onclick=\"return confirm('Are you sure you want to delete this setup detail?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No setup details found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
