<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'user') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include 'DBConn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $periId = $_POST['periId'] ?? '';
    $userEmail = $_SESSION['userEmail'];
    
    // Add debugging error logging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    try {
        if ($action === 'add') {
            // Check if already favorited with debug output
            $check_sql = "SELECT * FROM favorites WHERE UserEmail = ? AND PeriID = ?";
            $check_stmt = $conn->prepare($check_sql);
            
            if (!$check_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $check_stmt->bind_param("si", $userEmail, $periId);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                // Add to favorites
                $sql = "INSERT INTO favorites (UserEmail, PeriID) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("si", $userEmail, $periId);
                $success = $stmt->execute();
                
                if (!$success) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Added to favorites successfully',
                    'userEmail' => $userEmail,
                    'periId' => $periId
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Already in favorites'
                ]);
            }
        } elseif ($action === 'remove') {
            // Remove from favorites
            $sql = "DELETE FROM favorites WHERE UserEmail = ? AND PeriID = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("si", $userEmail, $periId);
            $success = $stmt->execute();
            
            if (!$success) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Removed from favorites successfully'
            ]);
        } else {
            throw new Exception("Invalid action specified");
        }
    } catch (Exception $e) {
        // Log the error and return a generic error message
        error_log("Favorites Error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred: ' . $e->getMessage(),
            'userEmail' => $userEmail,
            'periId' => $periId
        ]);
    }
    
    // Close statements
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($stmt)) $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>