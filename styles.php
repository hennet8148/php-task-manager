<?php
// Database connection for dynamic settings
require_once 'config.php'; // Include the configuration file

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
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
    padding: 10px;
    text-align: center;
    font-size: 13px;
}

nav a {
    color: white;
    text-decoration: none;
    margin: 0 12px;
    font-weight: bold;
}

nav a:hover {
    text-decoration: underline;
}

main {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 14px;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

table th {
    background-color: <?= $settings['primary_color'] ?>;
    color: white;
    font-weight: bold;
}

table tr:nth-child(odd) {
    background-color: <?= $settings['background_color'] ?>;
}

table tr:nth-child(even) {
    background-color: #f2f2f2;
}

/* Hover effect for table rows */
table tr:hover {
    background-color: <?= $settings['hover_color'] ?? '#ddd' ?>;
}

/* Form Styling */
form {
    max-width: 800px;
    margin: 0 auto 20px auto;
    background-color: <?= $settings['form_background'] ?? '#ffffff' ?>;
    border: 1px solid <?= $settings['form_border'] ?? '#ccc' ?>;
    border-radius: 5px;
    padding: 20px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

form label {
    display: block;
    font-weight: bold;
    margin-top: 15px;
    margin-bottom: 5px;
}

form input[type="text"],
form select,
form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid <?= $settings['input_border'] ?? '#ccc' ?>;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

form input[type="checkbox"] {
    width: auto;
    margin-right: 8px;
}

form button {
    display: block;
    width: 100%;
    background-color: #0069d9; /* Set to the desired blue color */
    color: white;
    border: none;
    padding: 12px;
    margin-top: 15px;
    font-size: 14px;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
}

form button:hover {
    background-color: #0056b3; /* Slightly darker shade for hover effect */
}

/* Miscellaneous */
.success {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
}

.error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
}
