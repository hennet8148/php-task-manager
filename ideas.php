<?php
// Load database configuration
require_once 'config.php';

// Create connection
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for Create and Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ideaID = isset($_POST['ideaID']) ? intval($_POST['ideaID']) : null;
    $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : "";
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : "";
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : "Proposed";

    if ($ideaID) {
        // Update existing idea
        $sql = "UPDATE IDEAS SET Title = '$title', Description = '$description', Status = '$status' WHERE IdeaID = $ideaID";
    } else {
        // Create new idea
        $sql = "INSERT INTO IDEAS (Title, Description, Status) VALUES ('$title', '$description', '$status')";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $ideaID = intval($_GET['delete']);
    $sql = "DELETE FROM IDEAS WHERE IdeaID = $ideaID";

    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch ideas
$sql = "SELECT * FROM IDEAS ORDER BY CreatedAt DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Ideas</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Manage Ideas</h1>

    <form method="POST" action="">
        <input type="hidden" name="ideaID" id="ideaID">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required><br>

        <label for="description">Description:</label>
        <textarea name="description" id="description"></textarea><br>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="Proposed">Proposed</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
        </select><br>

        <button type="submit">Save</button>
        <button type="reset" onclick="clearForm()">Clear</button>
    </form>

    <h2>Ideas List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['IdeaID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Title']); ?></td>
                        <td><?php echo htmlspecialchars($row['Description']); ?></td>
                        <td><?php echo htmlspecialchars($row['Status']); ?></td>
                        <td><?php echo $row['CreatedAt']; ?></td>
                        <td>
                            <button onclick="editIdea(<?php echo $row['IdeaID']; ?>, '<?php echo htmlspecialchars($row['Title']); ?>', '<?php echo htmlspecialchars($row['Description']); ?>', '<?php echo htmlspecialchars($row['Status']); ?>')">Edit</button>
                            <a href="?delete=<?php echo $row['IdeaID']; ?>" onclick="return confirm('Are you sure you want to delete this idea?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No ideas found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function editIdea(id, title, description, status) {
            document.getElementById('ideaID').value = id;
            document.getElementById('title').value = title;
            document.getElementById('description').value = description;
            document.getElementById('status').value = status;
        }

        function clearForm() {
            document.getElementById('ideaID').value = "";
            document.getElementById('title').value = "";
            document.getElementById('description').value = "";
            document.getElementById('status').value = "Proposed";
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
