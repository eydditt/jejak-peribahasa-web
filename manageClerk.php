<?php
session_start();

// Check if user is logged in and has correct user type
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Include the database connection
include 'DBConn.php';

// Initialize message variable
$message = '';

// Handle form submission for adding a new clerk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addClerk'])) {
    $clerkID = $_POST['clerkID'];
    $clerkName = $_POST['clerkName'];
    $clerkPassword = password_hash($_POST['clerkPassword'], PASSWORD_DEFAULT); // Hash the password

    // Insert the new clerk into the database
    $query = "INSERT INTO clerk (ClerkID, ClerkName, ClerkPassword) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $clerkID, $clerkName, $clerkPassword);

    if ($stmt->execute()) {
        $message = "Clerk added successfully!";
    } else {
        $message = "Error adding clerk: " . $stmt->error;
    }

    $stmt->close();
}

// Handle edit clerk functionality
if (isset($_GET['edit'])) {
    $clerkID = $_GET['edit'];

    // Fetch the clerk details to prefill the form
    $query = "SELECT * FROM clerk WHERE ClerkID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $clerkID);
    $stmt->execute();
    $result = $stmt->get_result();
    $clerk = $result->fetch_assoc();

    // Handle form submission for updating clerk data
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateClerk'])) {
        $clerkName = $_POST['clerkName'];
        $clerkPassword = $_POST['clerkPassword'] ? password_hash($_POST['clerkPassword'], PASSWORD_DEFAULT) : $clerk['ClerkPassword']; // Update only if password is provided

        // Update clerk details in the database
        $query = "UPDATE clerk SET ClerkName = ?, ClerkPassword = ? WHERE ClerkID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $clerkName, $clerkPassword, $clerkID);

        if ($stmt->execute()) {
            $message = "Clerk updated successfully!";
            header("Location: manageClerk.php"); // Redirect to avoid resubmitting the form on refresh
            exit();
        } else {
            $message = "Error updating clerk: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Handle delete clerk functionality
if (isset($_GET['delete'])) {
    $clerkID = $_GET['delete'];

    // Delete clerk from the database
    $query = "DELETE FROM clerk WHERE ClerkID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $clerkID);

    if ($stmt->execute()) {
        $message = "Clerk deleted successfully!";
    } else {
        $message = "Error deleting clerk: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all clerks from the database
$query = "SELECT * FROM clerk";
$result = $conn->query($query);
$clerks = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clerks - JejakPeribahasa</title>
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
                        <a class="nav-link" href="manageUser.php">Manage User</a>
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
        <h2>Manage Clerks</h2>

        <!-- Add Clerk Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Add New Clerk</h5>
            </div>
            <div class="card-body">
                <form id="addClerkForm" method="POST">
                    <div class="mb-3">
                        <label for="clerkID" class="form-label">Clerk ID</label>
                        <input type="text" class="form-control" id="clerkID" name="clerkID" required>
                    </div>
                    <div class="mb-3">
                        <label for="clerkName" class="form-label">Clerk Name</label>
                        <input type="text" class="form-control" id="clerkName" name="clerkName" required>
                    </div>
                    <div class="mb-3">
                        <label for="clerkPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="clerkPassword" name="clerkPassword" required>
                    </div>
                    <button type="submit" name="addClerk" class="btn btn-primary">Add Clerk</button>
                </form>
            </div>
        </div>

        <!-- Clerks Table -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Clerk List</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Clerk ID</th>
                            <th>Clerk Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clerks as $clerk): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($clerk['ClerkID']); ?></td>
                                <td><?php echo htmlspecialchars($clerk['ClerkName']); ?></td>
                                <td>
                                    <!-- Trigger modal -->
                                    <!-- <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editClerkModal<?php echo $clerk['ClerkID']; ?>">Edit</a> -->
                                    <a href="?delete=<?php echo $clerk['ClerkID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this clerk?');">Delete</a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editClerkModal<?php echo $clerk['ClerkID']; ?>" tabindex="-1" aria-labelledby="editClerkModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editClerkModalLabel">Edit Clerk</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="clerkID" class="form-label">Clerk ID</label>
                                                    <input type="text" class="form-control" id="clerkID" name="clerkID" value="<?php echo htmlspecialchars($clerk['ClerkID']); ?>" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="clerkName" class="form-label">Clerk Name</label>
                                                    <input type="text" class="form-control" id="clerkName" name="clerkName" value="<?php echo htmlspecialchars($clerk['ClerkName']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="clerkPassword" class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="clerkPassword" name="clerkPassword">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="updateClerk" class="btn btn-primary">Update Clerk</button>
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