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

// Fetch current settings
$settings = [];
$result = $conn->query("SELECT * FROM site_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['setting_value'];
}

// Output CSS
header("Content-Type: text/css");
echo "
:root {
    --primary-color: {$settings['primary_color']};
    --secondary-color: {$settings['secondary_color']};
    --background-color: {$settings['background_color']};
    --text-color: {$settings['text_color']};
    --header-font: {$settings['header_font']};
    --body-font: {$settings['body_font']};
    --form-width: {$settings['form_width']};
    --table-font-size: {$settings['table_font_size']};
}

/* Your existing CSS styles using variables */
body {
    font-family: var(--body-font);
    background-color: var(--background-color);
    color: var(--text-color);
}

/* Add your other styles here */
";
?>
