<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userEmail'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include 'DBConn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageID = $_POST['messageID'];
    $userEmail = $_SESSION['userEmail'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // First, verify the message belongs to the user
        $checkSql = "SELECT * FROM community_chat WHERE MessageID = ? AND UserEmail = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("is", $messageID, $userEmail);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            // Message not found or not owned by user
            echo json_encode([
                'success' => false, 
                'message' => 'You are not authorized to delete this message'
            ]);
            exit();
        }

        // Delete associated likes first
        $deleteLikesSql = "DELETE FROM message_likes WHERE MessageID = ?";
        $deleteLikesStmt = $conn->prepare($deleteLikesSql);
        $deleteLikesStmt->bind_param("i", $messageID);
        $deleteLikesStmt->execute();

        // Delete the message
        $deleteSql = "DELETE FROM community_chat WHERE MessageID = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $messageID);
        $deleteResult = $deleteStmt->execute();

        // Commit the transaction
        $conn->commit();

        echo json_encode([
            'success' => $deleteResult,
            'message' => 'Message deleted successfully'
        ]);
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        
        echo json_encode([
            'success' => false, 
            'message' => 'Error deleting message: ' . $e->getMessage()
        ]);
    }

    exit();
}
?>