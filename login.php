<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'DBConn.php';

$response = array(
    'status' => '',
    'message' => '',
    'redirect' => ''
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginId = $_POST['loginId'];
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    // Different query for admin to match both ID and password
    if ($userType === 'admin') {
        $sql = "SELECT * FROM admin WHERE AdminID = ? AND AdminPassword = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $loginId, $password);
    } elseif ($userType === 'user') {
        $sql = "SELECT * FROM user WHERE UserEmail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $loginId);
    } elseif ($userType === 'clerk') {
        $sql = "SELECT * FROM clerk WHERE ClerkID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $loginId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // For admin, direct password comparison
        if ($userType === 'admin') {
            // Set session variables
            $_SESSION['username'] = $loginId;
            $_SESSION['userType'] = 'admin';
            $response['redirect'] = 'AdminDashboard.php';
            $response['status'] = 'success';
            $response['message'] = 'Admin login successful!';
        } 
        // Existing password verification for user and clerk
        else {
            $hashedPassword = ($userType === 'user' ? $row['UserPassword'] : $row['ClerkPassword']);

            if (password_verify($password, $hashedPassword)) {
                if ($userType === 'user') {
                    $_SESSION['username'] = $row['UserName'];
                    $_SESSION['userType'] = 'user';
                    $_SESSION['userEmail'] = $row['UserEmail'];
                    $response['redirect'] = 'UserDashboard.php';
                } elseif ($userType === 'clerk') {
                    $_SESSION['clerkName'] = $row['ClerkName'];
                    $_SESSION['userType'] = 'clerk';
                    $_SESSION['clerkID'] = $row['ClerkID'];
                    $response['redirect'] = 'ClerkDashboard.php';
                }
                
                $response['status'] = 'success';
                $response['message'] = ucfirst($userType) . ' login successful!';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Invalid password.';
            }
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid ' . $userType . ' ID/email.';
    }
    
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>