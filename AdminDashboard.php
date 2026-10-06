<?php
session_start();

// Like a sentinel guarding ancient scrolls
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'DBConn.php';

// Initialize message variable, like a blank scroll awaiting wisdom
$message = '';

// Handle form submission with the grace of a practiced technique
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addUser'])) {
    $userEmail = $_POST['userEmail']; // Changed from userIC
    $userName = $_POST['userName'];
    $userPassword = password_hash($_POST['userPassword'], PASSWORD_DEFAULT);
    $userRole = $_POST['userRole'];

    // Insert the new user with precision
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

// Gather statistics like a strategist surveying the battlefield
$stats = array();
$queries = [
    "SELECT COUNT(*) as total_proverbs FROM peribahasa",
    "SELECT COUNT(*) as total_users FROM user",
    "SELECT COUNT(*) as total_clerks FROM clerk",
    "SELECT COUNT(*) as total_categories FROM category"
];

foreach ($queries as $query) {
    $result = $conn->query($query);
    $stats[] = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - JejakPeribahasa</title>
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
                        <a class="nav-link active" href="AdminDashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manageUser.php">Manage User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manageClerk.php">Manage Clerk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manageCommunityChat.php">Manage Chat</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">Manager: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
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
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3" onclick="window.location.href='listProverbs.php';" style="cursor: pointer;">
                    <div class="card-body">
                        <h5 class="card-title">Total Proverbs</h5>
                        <p class="card-text h2"><?php echo $stats[0]['total_proverbs'] + 2 ; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3" onclick="window.location.href='listUsers.php';" style="cursor: pointer;">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text h2"><?php echo $stats[1]['total_users']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3" onclick="window.location.href='listClerks.php';" style="cursor: pointer;">
                    <div class="card-body">
                        <h5 class="card-title">Total Clerks</h5>
                        <p class="card-text h2"><?php echo $stats[2]['total_clerks']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Categories</h5>
                        <p class="card-text h2"><?php echo $stats[3]['total_categories']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <p>Activity log will be displayed here</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="button" onclick="window.location.href='managePeribahasa.php';">Manage Peribahasa</button>
                            <button class="btn btn-dark" type="button" onclick="window.location.href='manageQuiz.php';">Manage Quiz</button>
                            <button class="btn btn-info" type="button" onclick="window.location.href='manageCommunityChat.php';">Manage Community Chat</button>
                        </div>
                    </div>
                </div>
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
    <script>
        window.onload = function() {
            var loginMessage = sessionStorage.getItem('loginMessage');
            if (loginMessage) {
                var alertDiv = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                    loginMessage +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                document.getElementById('messageContainer').innerHTML = alertDiv;
                sessionStorage.removeItem('loginMessage');
            }
        };
    </script>
</body>
</html>