<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "todo_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'tasks' table exists
$tableExistsQuery = "SHOW TABLES LIKE 'tasks'";
$tableExistsResult = $conn->query($tableExistsQuery);

if ($tableExistsResult->num_rows === 0) {
    // Create the 'tasks' table if it doesn't exist
    $createTableQuery = "
        CREATE TABLE tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status ENUM('To Do', 'In Progress', 'Done') DEFAULT 'To Do',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    if ($conn->query($createTableQuery) === TRUE) {
        echo "Table 'tasks' created successfully.<br>";
    } else {
        die("Error creating table: " . $conn->error);
    }
}

// Fetch tasks
$query = "SELECT * FROM tasks";
$result = $conn->query($query);

// Add new task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addTask'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $conn->query("INSERT INTO tasks (title, description) VALUES ('$title', '$description')");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update task status
if (isset($_POST['updateStatus'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $conn->query("UPDATE tasks SET status='$status' WHERE id=$id");
    echo 'update test';
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete task
if (isset($_POST['deleteTask'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM tasks WHERE id=$id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Shafiq To-Do</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h1 class="text-center">Shafiq To-Do</h1>
        <div class="row">
            <?php
            $statuses = ['To Do', 'In Progress', 'Done'];
            foreach ($statuses as $status) {
                echo '<div class="col-md-4">';
                echo '<h3>' . $status . '</h3>';
                echo '<div class="card bg-light mb-3">';
                echo '<div class="card-body">';

                if ($result->num_rows > 0) {
                    $result->data_seek(0); 
                    while ($row = $result->fetch_assoc()) {
                        if ($row['status'] === $status) {
                            echo '<div class="card my-2">';
                            echo '  <div class="card-body">';
                            echo '      <h5>' . $row['title'] . '</h5>';
                            echo '      <p>' . $row['description'] . '</p>';
                            echo '      <form method="POST" class="d-inline">';
                            echo '          <input type="hidden" name="id" value="' . $row['id'] . '">';
                            echo '          <input type="hidden" name="updateStatus" value="1">'; // Add this hidden input
                            echo '          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">';
                            foreach ($statuses as $opt) {
                                $selected = $row['status'] === $opt ? 'selected' : '';
                                echo '          <option value="' . $opt . '" ' . $selected . '>' . $opt . '</option>';
                            }
                            echo '          </select>';
                            echo '      </form>';
                            echo '      <form method="POST" class="d-inline">';
                            echo '          <input type="hidden" name="id" value="' . $row['id'] . '">';
                            echo '          <button type="submit" name="deleteTask" class="btn btn-danger btn-sm mt-2">Delete</button>';
                            echo '      </form>';
                            echo '  </div>';
                            echo '</div>';
                        }
                    }
                }
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
        <form method="POST" class="mt-5">
            <div class="mb-3">
                <input type="text" name="title" class="form-control" placeholder="Task Title" required>
            </div>
            <div class="mb-3">
                <textarea name="description" class="form-control" rows="3" placeholder="Task Description"></textarea>
            </div>
            <button type="submit" name="addTask" class="btn btn-primary">Add Task</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>