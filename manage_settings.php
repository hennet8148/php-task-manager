<?php
// Database connection
$servername = "localhost";
$username = "CHUCK";
$password = "Jack.BOX.1234";
$dbname = "NECK";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to update settings
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['settings'] as $name => $value) {
        $name = $conn->real_escape_string($name);
        $value = $conn->real_escape_string($value);
        $conn->query("UPDATE site_settings SET setting_value = '$value' WHERE setting_name = '$name'");
    }
    echo "<div class='success'>Settings updated successfully!</div>";
}

// Fetch current settings
$settings = [];
$result = $conn->query("SELECT * FROM site_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Site Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main>
        <h2>Manage Site Settings</h2>
        <form method="post" action="">
            <?php foreach ($settings as $name => $value): ?>
                <label for="<?= $name ?>"><?= ucfirst(str_replace('_', ' ', $name)) ?>:</label>
                <input type="text" id="<?= $name ?>" name="settings[<?= $name ?>]" value="<?= htmlspecialchars($value) ?>">
            <?php endforeach; ?>
            <button type="submit">Save Settings</button>
        </form>
    </main>
</body>
</html>
