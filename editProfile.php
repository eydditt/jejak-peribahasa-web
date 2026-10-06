<?php
session_start();

// Check user login
if (!isset($_SESSION['username']) || !isset($_SESSION['userEmail']) || $_SESSION['userType'] !== 'user') {
    header("Location: index.php");
    exit();
}

include 'DBConn.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateUser'])) {
    $userEmail = $_POST['userEmail'];
    $userName = trim($_POST['userName']);
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate username
    if (empty($userName)) {
        $message = "Username cannot be empty";
        $messageType = "danger";
    } else {
        // Password change logic
        if (!empty($newPassword) || !empty($confirmPassword)) {
            // Retrieve current password
            $checkPass = "SELECT UserPassword FROM user WHERE UserEmail = ?";
            $stmt = $conn->prepare($checkPass);
            $stmt->bind_param("s", $userEmail);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            $stmt->close();

            // Validate password changes
            if (empty($currentPassword)) {
                $message = "Please enter your current password to change password";
                $messageType = "danger";
            } elseif (!password_verify($currentPassword, $userData['UserPassword'])) {
                $message = "Current password is incorrect";
                $messageType = "danger";
            } elseif (empty($newPassword)) {
                $message = "New password cannot be empty";
                $messageType = "danger";
            } elseif ($newPassword !== $confirmPassword) {
                $message = "New passwords do not match";
                $messageType = "danger";
            }
        }

        // Update user profile
        if (empty($message)) {
            try {
                if (!empty($newPassword)) {
                    // Hash new password
                    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    // Update with new password
                    $query = "UPDATE user SET UserName = ?, UserPassword = ? WHERE UserEmail = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sss", $userName, $hashedNewPassword, $userEmail);
                } else {
                    // Update without changing password
                    $query = "UPDATE user SET UserName = ? WHERE UserEmail = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ss", $userName, $userEmail);
                }

                if ($stmt->execute()) {
                    $_SESSION['username'] = $userName;
                    $message = "Profile updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating profile: " . $stmt->error;
                    $messageType = "danger";
                }
                $stmt->close();
            } catch (Exception $e) {
                $message = "Update failed: " . $e->getMessage();
                $messageType = "danger";
            }
        }
    }
}

// Fetch current user data
$userEmail = $_SESSION['userEmail'];
$query = "SELECT UserEmail, UserName, UserRole FROM user WHERE UserEmail = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - JejakPeribahasa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">JejakPeribahasa</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="UserDashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="favorites.php">My Favorites</a></li>
                    <li class="nav-item"><a class="nav-link" href="quiz.php">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link active" href="editProfile.php">Edit Profile</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h2 class="h4 mb-0">Edit Profile</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="userEmail" value="<?php echo htmlspecialchars($user['UserEmail']); ?>">

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['UserEmail']); ?>" disabled>
                                <div class="form-text text-muted">Email address cannot be changed</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="userName" class="form-control" value="<?php echo htmlspecialchars($user['UserName']); ?>" required>
                                <div class="invalid-feedback">Please enter your name</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['UserRole']); ?>" disabled>
                            </div>

                            <hr class="my-4">

                            <h5>Change Password</h5>
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="currentPassword" class="form-control">
                                <div class="form-text">Enter your current password to change it</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="newPassword" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirmPassword" class="form-control">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="updateUser" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                                <a href="UserDashboard.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
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
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>