<?php
// Include the database connection
include 'DBConn.php';

// Variables for error and success messages
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $registerName = htmlspecialchars(trim($_POST['registerName']));
    $registerEmail = filter_var(trim($_POST['registerIC']), FILTER_SANITIZE_EMAIL); // Sanitize email
    $registerPassword = $_POST['registerPassword'];
    $userRole = $_POST['userRole'];

    // Validate email
    if (!filter_var($registerEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format. Please enter a valid email address.';
        $messageType = 'danger';
    } else {
        // Hash the password
        $hashedPassword = password_hash($registerPassword, PASSWORD_DEFAULT);

        // Check if email already exists
        if ($userRole == 'user') {
            $sql = "SELECT UserEmail FROM user WHERE UserEmail = ?";
        } else if ($userRole == 'clerk') {
            $sql = "SELECT ClerkEmail FROM clerk WHERE ClerkEmail = ?";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $registerEmail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email already exists
            $message = 'Oops! This Email already exists. Please try another.';
            $messageType = 'danger';
        } else {
            // Prepare insert statement based on user role
            if ($userRole == 'user') {
                $sql = "INSERT INTO user (UserEmail, UserName, UserPassword, UserRole) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $registerEmail, $registerName, $hashedPassword, $userRole);
            } else if ($userRole == 'clerk') {
                $sql = "INSERT INTO clerk (ClerkEmail, ClerkName, ClerkPassword) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $registerEmail, $registerName, $hashedPassword);
            }

            try {
                if ($stmt->execute()) {
                    // Success message
                    $message = 'Congratulations! You have successfully registered. You can now log in.';
                    $messageType = 'success';
                } else {
                    // Database error
                    $message = 'Oops! Something went wrong. Please try again later.';
                    $messageType = 'danger';
                }
            } catch (Exception $e) {
                // Catch any unexpected errors
                $message = 'Registration failed: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }

        // Close the statement
        $stmt->close();
    }

    // Redirect back to index with message
    header("Location: index.php?message=" . urlencode($message) . "&messageType=" . urlencode($messageType));
    exit();
}
?>