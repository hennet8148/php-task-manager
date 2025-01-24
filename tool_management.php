<?php
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
$tool_id = "";
$tool_name = "";
$tool_type = "";
$shank_diameter = "";
$cut_length = "";
$overall_length = "";
$notes = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tool_id = $_POST['tool_id'];
    $tool_name = $conn->real_escape_string($_POST['tool_name']);
    $tool_type = $conn->real_escape_string($_POST['tool_type']);
    $shank_diameter = $_POST['shank_diameter'];
    $cut_length = $_POST['cut_length'];
    $overall_length = $_POST['overall_length'];
    $notes = $conn->real_escape_string($_POST['notes']);

    if (!empty($tool_id)) {
        // Update the row
        $update_query = "UPDATE TOOL
                         SET TOOL_NAME = '$tool_name', TOOL_TYPE = '$tool_type',
                             SHANK_DIAMETER = '$shank_diameter', CUT_LENGTH = '$cut_length',
                             OVERALL_LENGTH = '$overall_length', NOTES = '$notes'
                         WHERE ID = $tool_id";
        $conn->query($update_query);
    } else {
        // Insert a new row
        $insert_query = "INSERT INTO TOOL (TOOL_NAME, TOOL_TYPE, SHANK_DIAMETER, CUT_LENGTH, OVERALL_LENGTH, NOTES)
                         VALUES ('$tool_name', '$tool_type', '$shank_diameter', '$cut_length', '$overall_length', '$notes')";
        $conn->query($insert_query);
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM TOOL WHERE ID = $delete_id");
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $conn->query("SELECT * FROM TOOL WHERE ID = $edit_id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tool_id = $row['ID'];
        $tool_name = $row['TOOL_NAME'];
        $tool_type = $row['TOOL_TYPE'];
        $shank_diameter = $row['SHANK_DIAMETER'];
        $cut_length = $row['CUT_LENGTH'];
        $overall_length = $row['OVERALL_LENGTH'];
        $notes = $row['NOTES'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Management</title>
    <link rel="stylesheet" href="styles.php">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <h2><?php echo empty($tool_id) ? "Add a New Tool" : "Edit Tool"; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="tool_id" value="<?php echo $tool_id; ?>">
            <label for="tool_name">Tool Name:</label>
            <input type="text" id="tool_name" name="tool_name" value="<?php echo $tool_name; ?>" required>

            <label for="tool_type">Tool Type:</label>
            <input type="text" id="tool_type" name="tool_type" value="<?php echo $tool_type; ?>" required>

            <label for="shank_diameter">Shank Diameter (mm):</label>
            <input type="number" step="0.01" id="shank_diameter" name="shank_diameter" value="<?php echo $shank_diameter; ?>">

            <label for="cut_length">Cut Length (mm):</label>
            <input type="number" step="0.01" id="cut_length" name="cut_length" value="<?php echo $cut_length; ?>">

            <label for="overall_length">Overall Length (mm):</label>
            <input type="number" step="0.01" id="overall_length" name="overall_length" value="<?php echo $overall_length; ?>">

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes"><?php echo $notes; ?></textarea>

            <button type="submit"><?php echo empty($tool_id) ? "Submit" : "Update"; ?></button>
        </form>

        <h2>Existing Tools</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Shank Diameter</th>
                    <th>Cut Length</th>
                    <th>Overall Length</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM TOOL ORDER BY TOOL_NAME");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['ID']}</td>
                                <td>{$row['TOOL_NAME']}</td>
                                <td>{$row['TOOL_TYPE']}</td>
                                <td>{$row['SHANK_DIAMETER']}</td>
                                <td>{$row['CUT_LENGTH']}</td>
                                <td>{$row['OVERALL_LENGTH']}</td>
                                <td>{$row['NOrTES']}</td>
                                <td>
                                    <a href='?edit_id={$row['ID']}'>Edit</a> |
                                    <a href='?delete_id={$row['ID']}' onclick=\"return confirm('Are you sure?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No tools found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
