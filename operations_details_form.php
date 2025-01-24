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
$operation_id = "";
$task_id = "";
$parent_step_id = "";
$operation_type_id = "";
$tool_id = "";
$speed = "";
$feed = "";
$stock_to_leave = false;
$notes = "";

// Fetch tasks for dropdown (Operation tasks)
$tasks_result = $conn->query("SELECT ID, TASK_NAME FROM TASK_TEMPLATE WHERE TASK_TYPE = 'Operation' ORDER BY TASK_NAME");
$tasks = [];
while ($row = $tasks_result->fetch_assoc()) {
    $tasks[$row['ID']] = $row['TASK_NAME'];
}

// Fetch parent steps for dropdown (Setup steps)
$parent_steps_result = $conn->query("SELECT ID, TASK_NAME FROM TASK_TEMPLATE WHERE TASK_TYPE = 'Setup' ORDER BY TASK_NAME");
$parent_steps = [];
while ($row = $parent_steps_result->fetch_assoc()) {
    $parent_steps[$row['ID']] = $row['TASK_NAME'];
}

// Fetch operation types for dropdown
$operation_types_result = $conn->query("SELECT ID, NAME FROM OPERATION_TYPE ORDER BY NAME");
$operation_types = [];
while ($row = $operation_types_result->fetch_assoc()) {
    $operation_types[$row['ID']] = $row['NAME'];
}

// Fetch tools for dropdown
$tools_result = $conn->query("SELECT ID, TOOL_NAME FROM TOOL ORDER BY TOOL_NAME");
$tools = [];
while ($row = $tools_result->fetch_assoc()) {
    $tools[$row['ID']] = $row['TOOL_NAME'];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $operation_id = $_POST['operation_id'];
    $task_id = $_POST['task_id'];
    $parent_step_id = $_POST['parent_step_id'];
    $operation_type_id = $_POST['operation_type_id'];
    $tool_id = $_POST['tool_id'];
    $speed = isset($_POST['speed']) && is_numeric($_POST['speed']) ? intval($_POST['speed']) : NULL;
    $feed = isset($_POST['feed']) && is_numeric($_POST['feed']) ? intval($_POST['feed']) : NULL;
    $duration = isset($_POST['duration']) && is_numeric($_POST['duration']) ? intval($_POST['duration']) : NULL;
    $stock_to_leave = isset($_POST['stock_to_leave']) ? 1 : 0;
    $notes = $conn->real_escape_string($_POST['notes']);
    $gcode_folder = $conn->real_escape_string($_POST['gcode_folder']);
    $gcode_file = $conn->real_escape_string($_POST['gcode_file']);

    if (!empty($operation_id)) {
        // Update the row
        $update_query = "UPDATE OPERATION_DETAILS
                         SET TASK_ID = '$task_id', PARENT_STEP_ID = '$parent_step_id',
                             OPERATION_TYPE_ID = '$operation_type_id', TOOL_ID = '$tool_id',
                             SPEED = " . ($speed !== NULL ? $speed : "NULL") . ",
                             FEED = " . ($feed !== NULL ? $feed : "NULL") . ",
                             DURATION = " . ($duration !== NULL ? $duration : "NULL") . ",
                             STOCK_TO_LEAVE = '$stock_to_leave',
                             GCODE_FOLDER = '$gcode_folder', GCODE_FILE = '$gcode_file',
                             NOTES = '$notes'
                         WHERE ID = $operation_id";

        if ($conn->query($update_query) === TRUE) {
            echo "<div class='success'>Operation details updated successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO OPERATION_DETAILS (TASK_ID, PARENT_STEP_ID, OPERATION_TYPE_ID, TOOL_ID, SPEED, FEED, DURATION, STOCK_TO_LEAVE, GCODE_FOLDER, GCODE_FILE, NOTES)
                         VALUES ('$task_id', '$parent_step_id', '$operation_type_id', '$tool_id',
                                 " . ($speed !== NULL ? $speed : "NULL") . ",
                                 " . ($feed !== NULL ? $feed : "NULL") . ",
                                 " . ($duration !== NULL ? $duration : "NULL") . ",
                                 '$stock_to_leave', '$gcode_folder', '$gcode_file', '$notes')";

        if ($conn->query($insert_query) === TRUE) {
            echo "<div class='success'>New operation details added successfully!</div>";
        } else {
            echo "<div class='error'>Error: " . $conn->error . "</div>";
        }
    }
}


// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM OPERATION_DETAILS WHERE ID = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<div class='success'>Operation details deleted successfully!</div>";
    } else {
        echo "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM OPERATION_DETAILS WHERE ID = $edit_id";
    $edit_result = $conn->query($edit_query);
    if ($edit_result->num_rows > 0) {
        $row = $edit_result->fetch_assoc();
        $operation_id = $row['ID'];
        $task_id = $row['TASK_ID'];
        $parent_step_id = $row['PARENT_STEP_ID'];
        $operation_type_id = $row['OPERATION_TYPE_ID'];
        $tool_id = $row['TOOL_ID'];
        $speed = $row['SPEED'];
        $feed = $row['FEED'];
        $stock_to_leave = $row['STOCK_TO_LEAVE'];
        $notes = $row['NOTES'];
        $gcode_folder = $row['GCODE_FOLDER'];
        $gcode_file = $row['GCODE_FILE'];
        $duration = $row['DURATION']; // Add this line
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operation Details Management</title>
    <link rel="stylesheet" href="styles.php">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <h2><?php echo empty($operation_id) ? "Add a New Operation Detail" : "Edit Operation Detail"; ?></h2>
        <form method="post" action="">
      <input type="hidden" name="operation_id" value="<?php echo $operation_id; ?>">

      <label for="task_id">Task:</label>
      <select id="task_id" name="task_id" required>
          <option value="">-- Select Task --</option>
          <?php foreach ($tasks as $id => $name): ?>
              <option value="<?php echo $id; ?>" <?php echo $task_id == $id ? 'selected' : ''; ?>><?php echo $name; ?></option>
          <?php endforeach; ?>
      </select>

      <label for="parent_step_id">Parent Step:</label>
      <select id="parent_step_id" name="parent_step_id" required>
          <option value="">-- Select Parent Step --</option>
          <?php foreach ($parent_steps as $id => $name): ?>
              <option value="<?php echo $id; ?>" <?php echo $parent_step_id == $id ? 'selected' : ''; ?>><?php echo $name; ?></option>
          <?php endforeach; ?>
      </select>

      <label for="operation_type_id">Operation Type:</label>
      <select id="operation_type_id" name="operation_type_id" required>
          <option value="">-- Select Operation Type --</option>
          <?php foreach ($operation_types as $id => $name): ?>
              <option value="<?php echo $id; ?>" <?php echo $operation_type_id == $id ? 'selected' : ''; ?>><?php echo $name; ?></option>
          <?php endforeach; ?>
      </select>

      <label for="tool_id">Tool:</label>
      <select id="tool_id" name="tool_id" required>
          <option value="">-- Select Tool --</option>
          <?php foreach ($tools as $id => $name): ?>
              <option value="<?php echo $id; ?>" <?php echo $tool_id == $id ? 'selected' : ''; ?>><?php echo $name; ?></option>
          <?php endforeach; ?>
      </select>

      <label for="speed">Speed:</label>
      <input type="text" id="speed" name="speed" value="<?php echo $speed; ?>">

      <label for="feed">Feed:</label>
      <input type="text" id="feed" name="feed" value="<?php echo $feed; ?>">

      <label for="duration">Duration (minutes):</label>
      <input type="text" id="duration" name="duration" value="<?php echo $duration; ?>">

      <label for="stock_to_leave">Stock to Leave:</label>
      <input type="checkbox" id="stock_to_leave" name="stock_to_leave" <?php echo $stock_to_leave ? 'checked' : ''; ?>>

      <label for="gcode_folder">G-Code Folder:</label>
      <input type="text" id="gcode_folder" name="gcode_folder" value="<?php echo $gcode_folder; ?>">

      <label for="gcode_file">G-Code File:</label>
      <input type="text" id="gcode_file" name="gcode_file" value="<?php echo $gcode_file; ?>">

      <label for="notes">Notes:</label>
      <textarea id="notes" name="notes"><?php echo $notes; ?></textarea>

      <button type="submit"><?php echo empty($operation_id) ? "Submit" : "Update"; ?></button>
  </form>


        <h2>Existing Operation Details</h2>
        <table>
          <thead>
    <tr>
        <th>ID</th>
        <th>Task</th>
        <th>Parent Step</th>
        <th>Operation Type</th>
        <th>Tool</th>
        <th>Speed</th>
        <th>Feed</th>
        <th>Duration (minutes)</th>
        <th>Stock to Leave</th>
        <th>G-Code Folder</th>
        <th>G-Code File</th>
        <th>Notes</th>
        <th>Actions</th>
    </tr>
</thead>

<tbody>
<?php
$result = $conn->query("
SELECT od.*, t1.TASK_NAME AS TASK_NAME, t2.TASK_NAME AS PARENT_STEP_NAME,
       ot.NAME AS OPERATION_TYPE_NAME, tl.TOOL_NAME AS TOOL_NAME,
       od.GCODE_FOLDER, od.GCODE_FILE, od.DURATION
FROM OPERATION_DETAILS od
LEFT JOIN TASK_TEMPLATE t1 ON od.TASK_ID = t1.ID
LEFT JOIN TASK_TEMPLATE t2 ON od.PARENT_STEP_ID = t2.ID
LEFT JOIN OPERATION_TYPE ot ON od.OPERATION_TYPE_ID = ot.ID
LEFT JOIN TOOL tl ON od.TOOL_ID = tl.ID
");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row["ID"]) . "</td>
                <td>" . htmlspecialchars($row["TASK_NAME"]) . "</td>
                <td>" . htmlspecialchars($row["PARENT_STEP_NAME"]) . "</td>
                <td>" . htmlspecialchars($row["OPERATION_TYPE_NAME"]) . "</td>
                <td>" . htmlspecialchars($row["TOOL_NAME"]) . "</td>
                <td>" . htmlspecialchars($row["SPEED"]) . "</td>
                <td>" . htmlspecialchars($row["FEED"]) . "</td>
                <td>" . htmlspecialchars($row["DURATION"]) . "</td>
                <td>" . ($row["STOCK_TO_LEAVE"] ? "Yes" : "No") . "</td>
                <td>" . htmlspecialchars($row["GCODE_FOLDER"]) . "</td>
                <td>" . htmlspecialchars($row["GCODE_FILE"]) . "</td>
                <td>" . htmlspecialchars($row["NOTES"]) . "</td>
                <td>
                    <a href='?edit_id=" . $row["ID"] . "'>Edit</a> |
                    <a href='?delete_id=" . $row["ID"] . "' onclick=\"return confirm('Are you sure you want to delete this operation detail?');\">Delete</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='13'>No operation details found</td></tr>";
}
?>
</tbody>



        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
