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

// Output as CSS
header("Content-Type: text/css");
?>

/* Global Styles */
body {
    font-family: <?= $settings['body_font'] ?? 'Arial, sans-serif' ?>;
    margin: 0;
    padding: 0;
    background-color: <?= $settings['background_color'] ?? '#f8f9fa' ?>;
    color: <?= $settings['text_color'] ?? '#343a40' ?>;
    font-size: 14px;
}

header, footer {
    background-color: <?= $settings['primary_color'] ?? '#0056b3' ?>;
    color: white;
    text-align: center;
    padding: 8px 0;
    font-size: 14px;
}

nav {
    background-color: <?= $settings['secondary_color'] ?? '#0069d9' ?>;
    padding: 8px;
    text-align: center;
    font-size: 13px;
}

nav a {
    color: white;
    text-decoration: none;
    margin: 0 8px;
    font-weight: bold;
}

nav a:hover {
    text-decoration: underline;
}

main {
    padding: 15px;
}

/* Add additional styles as needed */
