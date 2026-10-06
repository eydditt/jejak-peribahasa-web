<?php
session_start();

// Security check
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'DBConn.php';

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteMessage') {
    $messageID = $_POST['messageID'];
    
    // Delete likes first due to foreign key constraint
    $conn->query("DELETE FROM message_likes WHERE MessageID = '$messageID'");
    // Then delete the message
    $conn->query("DELETE FROM community_chat WHERE MessageID = '$messageID'");
    
    // Set success message
    $_SESSION['success_message'] = "Message deleted successfully!";
    header("Location: manageCommunityChat.php");
    exit();
}

// Fetch all messages with user information
$query = "SELECT c.*, u.UserName, 
          (SELECT COUNT(*) FROM message_likes WHERE MessageID = c.MessageID) as like_count 
          FROM community_chat c 
          JOIN user u ON c.UserEmail = u.UserEmail 
          ORDER BY c.DatePosted DESC";
$messages = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Community Chat - JejakPeribahasa Admin</title>
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
                        <a class="nav-link" href="manageClerk.php">Manage Clerk</a>
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

    <!-- Main Content -->
    <div class="container mt-5 pt-4">
        <!-- Success Message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
    <h5 class="mb-0">Community Chat Management</h5>
</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Message</th>
                                <th>Type</th>
                                <th>Posted Date</th>
                                <th>Likes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($message = $messages->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message['UserName']); ?></td>
                                    <td><?php echo htmlspecialchars($message['Message']); ?></td>
                                    <td><?php echo ucfirst($message['MessageType']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($message['DatePosted'])); ?></td>
                                    <td><?php echo $message['like_count']; ?></td>
                                    <td>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                            <input type="hidden" name="action" value="deleteMessage">
                                            <input type="hidden" name="messageID" value="<?php echo $message['MessageID']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
</body>
</html>