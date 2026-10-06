<?php
session_start();

// Like a guardian at the temple gates, we verify credentials
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'DBConn.php';

// Initialize message variable, like an empty scroll awaiting wisdom
$message = '';

// Handle form submission for adding a new user, each step precise as a warrior's kata
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addUser'])) {
    $userEmail = $_POST['userEmail'];  // Changed from userIC
    $userName = $_POST['userName'];
    $userPassword = password_hash($_POST['userPassword'], PASSWORD_DEFAULT);
    $userRole = $_POST['userRole'];

    // Insert the new user into the database with the grace of a practiced technique
    $query = "INSERT INTO user (UserEmail, UserName, UserPassword, UserRole) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $userEmail, $userName, $userPassword, $userRole);

    if ($stmt->execute()) {
        $message = "User added successfully!";
    } else {
        $message = "Error adding user: " . $stmt->error;
    }

    $stmt->close();
}

// Handle edit user functionality, like adjusting a student's stance
if (isset($_GET['edit'])) {
    $userEmail = $_GET['edit'];  // Changed from userIC

    // Fetch user details with the precision of a master selecting the right scroll
    $query = "SELECT * FROM user WHERE UserEmail = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateUser'])) {
        $userName = $_POST['userName'];
        $userPassword = $_POST['userPassword'] ? password_hash($_POST['userPassword'], PASSWORD_DEFAULT) : $user['UserPassword'];
        $userRole = $_POST['userRole'];

        $query = "UPDATE user SET UserName = ?, UserPassword = ?, UserRole = ? WHERE UserEmail = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $userName, $userPassword, $userRole, $userEmail);

        if ($stmt->execute()) {
            $message = "User updated successfully!";
            header("Location: manageUser.php");
            exit();
        } else {
            $message = "Error updating user: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Handle delete user functionality, like a master carefully removing an old scroll
if (isset($_GET['delete'])) {
    $userEmail = $_GET['delete'];  // Changed from userIC

    $query = "DELETE FROM user WHERE UserEmail = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userEmail);

    if ($stmt->execute()) {
        $message = "User deleted successfully!";
    } else {
        $message = "Error deleting user: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all users, like gathering all scrolls for inspection
$query = "SELECT * FROM user";
$result = $conn->query($query);
$users = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - JejakPeribahasa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">JejakPeribahasa Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="AdminDashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manageUser.php">Manage User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manageClerk.php">Manage Clerk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manageCommunityChat.php">Manage Chat</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Success Message Container -->
    <div id="messageContainer" style="margin-top: 56px;">
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="container mt-5 pt-4">
        <h2>Manage Users</h2>

        <!-- Add User Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Add New User</h5>
            </div>
            <div class="card-body">
                <form id="addUserForm" method="POST">
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="text" class="form-control" id="userEmail" name="userEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="userName" class="form-label">User Name</label>
                        <input type="text" class="form-control" id="userName" name="userName" required>
                    </div>
                    <div class="mb-3">
                        <label for="userPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="userPassword" name="userPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="userRole" class="form-label">Role</label>
                        <select class="form-select" id="userRole" name="userRole" required>
                    
                            <option value="user">User</option>
                        </select>
                    </div>
                    <button type="submit" name="addUser" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">User List</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>User Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['UserEmail']); ?></td>
                                <td><?php echo htmlspecialchars($user['UserName']); ?></td>
                                <td><?php echo htmlspecialchars($user['UserRole']); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $user['UserEmail']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editUserModal<?php echo $user['UserEmail']; ?>" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="userEmail" class="form-label">Email</label>
                                                    <input type="text" class="form-control" id="userEmail" name="userEmail" value="<?php echo htmlspecialchars($user['UserEmail']); ?>" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="userName" class="form-label">User Name</label>
                                                    <input type="text" class="form-control" id="userName" name="userName" value="<?php echo htmlspecialchars($user['UserName']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="userPassword" class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="userPassword" name="userPassword">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="userRole" class="form-label">Role</label>
                                                    <select class="form-select" id="userRole" name="userRole" required>
                                                        <option value="admin" <?php echo ($user['UserRole'] === 'admin' ? 'selected' : ''); ?>>Admin</option>
                                                        <option value="clerk" <?php echo ($user['UserRole'] === 'clerk' ? 'selected' : ''); ?>>Clerk</option>
                                                        <option value="user" <?php echo ($user['UserRole'] === 'user' ? 'selected' : ''); ?>>User</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="updateUser" class="btn btn-primary">Update User</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> JejakPeribahasa. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>