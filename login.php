<?php
session_start();

// Database connection
require_once 'config.php'; // Include the configuration file

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Fetch user from the database
    $query = "SELECT * FROM USERS WHERE USERNAME = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $row['PASSWORD_HASH'])) {
            // Set session variables
            $_SESSION['user_id'] = $row['ID'];
            $_SESSION['username'] = $row['USERNAME'];
            $_SESSION['role'] = $row['ROLE'];

            // Redirect to the desired page
            header("Location: task_template_form.php");
            exit;
        } else {
            echo "<div class='error'>Invalid password.</div>";
        }
    } else {
        echo "<div class='error'>User not found.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.php">
</head>
<body>
    <main>
        <h2>Login</h2>
        <form method="post" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>
