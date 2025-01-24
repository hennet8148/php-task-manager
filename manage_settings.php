<?php
// Database connection
require_once 'config.php'; // Include the configuration file

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Handle settings update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $theme = $conn->real_escape_string($_POST['theme']);
    $primary_color = $conn->real_escape_string($_POST['primary_color']);
    $background_color = $conn->real_escape_string($_POST['background_color']);
    $text_color = $conn->real_escape_string($_POST['text_color']);

    // Update settings in the database
    $update_queries = [
        "UPDATE site_settings SET setting_value = '$theme' WHERE setting_name = 'theme'",
        "UPDATE site_settings SET setting_value = '$primary_color' WHERE setting_name = 'primary_color'",
        "UPDATE site_settings SET setting_value = '$background_color' WHERE setting_name = 'background_color'",
        "UPDATE site_settings SET setting_value = '$text_color' WHERE setting_name = 'text_color'"
    ];

    $errors = [];
    foreach ($update_queries as $query) {
        if (!$conn->query($query)) {
            $errors[] = "Error updating: " . $conn->error;
        }
    }

    if (empty($errors)) {
        echo "<div class='success'>Settings updated successfully!</div>";
    } else {
        echo "<div class='error'>" . implode('<br>', $errors) . "</div>";
    }
}

// Fetch current settings
$settings = [];
$result = $conn->query("SELECT * FROM site_settings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
} else {
    die("Error fetching settings: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Settings</title>
    <link rel="stylesheet" href="styles.php">
    <script>
        // Function to update the hex input box based on dropdown selection
        function updateColorInput(selectElement, inputId) {
            document.getElementById(inputId).value = selectElement.value;
        }
    </script>
</head>
<body>
    <main>
        <h2>Manage Site Appearance</h2>
        <form method="post" action="">
            <label for="theme">Theme:</label>
            <select id="theme" name="theme">
                <option value="light" <?php echo $settings['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                <option value="dark" <?php echo $settings['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                <option value="custom" <?php echo $settings['theme'] === 'custom' ? 'selected' : ''; ?>>Custom</option>
            </select>

            <label for="primary_color">Primary Color:</label>
            <select onchange="updateColorInput(this, 'primary_color')" style="width: 100%;">
                <option value="#0056b3" style="background-color: #0056b3; color: white;">Blue</option>
                <option value="#ff0000" style="background-color: #ff0000; color: white;">Red</option>
                <option value="#00ff00" style="background-color: #00ff00; color: black;">Green</option>
                <option value="#0000ff" style="background-color: #0000ff; color: white;">Dark Blue</option>
                <option value="#ffff00" style="background-color: #ffff00; color: black;">Yellow</option>
            </select>
            <input type="text" id="primary_color" name="primary_color" value="<?php echo $settings['primary_color']; ?>" readonly>

            <label for="background_color">Background Color:</label>
            <select onchange="updateColorInput(this, 'background_color')" style="width: 100%;">
                <option value="#f8f9fa" style="background-color: #f8f9fa; color: black;">Light Gray</option>
                <option value="#121212" style="background-color: #121212; color: white;">Dark Gray</option>
                <option value="#ffffff" style="background-color: #ffffff; color: black;">White</option>
                <option value="#000000" style="background-color: #000000; color: white;">Black</option>
            </select>
            <input type="text" id="background_color" name="background_color" value="<?php echo $settings['background_color']; ?>" readonly>

            <label for="text_color">Text Color:</label>
            <select onchange="updateColorInput(this, 'text_color')" style="width: 100%;">
                <option value="#343a40" style="background-color: #343a40; color: white;">Dark Gray</option>
                <option value="#e0e0e0" style="background-color: #e0e0e0; color: black;">Light Gray</option>
                <option value="#ffffff" style="background-color: #ffffff; color: black;">White</option>
                <option value="#000000" style="background-color: #000000; color: white;">Black</option>
            </select>
            <input type="text" id="text_color" name="text_color" value="<?php echo $settings['text_color']; ?>" readonly>

            <button type="submit">Save Settings</button>
        </form>
    </main>
</body>
</html>
