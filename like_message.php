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
        // First, check if the like already exists
        $checkSql = "SELECT * FROM message_likes WHERE MessageID = ? AND UserEmail = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("is", $messageID, $userEmail);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            // Unlike the message
            $unlikeSql = "DELETE FROM message_likes WHERE MessageID = ? AND UserEmail = ?";
            $unlikeStmt = $conn->prepare($unlikeSql);
            $unlikeStmt->bind_param("is", $messageID, $userEmail);
            $unlike = $unlikeStmt->execute();
            
            // Decrement likes count
            $updateLikesSql = "UPDATE community_chat SET Likes = GREATEST(Likes - 1, 0) WHERE MessageID = ?";
            $updateLikesStmt = $conn->prepare($updateLikesSql);
            $updateLikesStmt->bind_param("i", $messageID);
            $updateLikesStmt->execute();
            
            // Get current likes count
            $countSql = "SELECT Likes FROM community_chat WHERE MessageID = ?";
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param("i", $messageID);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $likesCount = $countResult->fetch_assoc()['Likes'];
            
            $response = [
                'success' => $unlike,
                'action' => 'unlike',
                'likesCount' => $likesCount
            ];
        } else {
            // Like the message
            $likeSql = "INSERT INTO message_likes (MessageID, UserEmail) VALUES (?, ?)";
            $likeStmt = $conn->prepare($likeSql);
            $likeStmt->bind_param("is", $messageID, $userEmail);
            $like = $likeStmt->execute();
            
            // Increment likes count
            $updateLikesSql = "UPDATE community_chat SET Likes = Likes + 1 WHERE MessageID = ?";
            $updateLikesStmt = $conn->prepare($updateLikesSql);
            $updateLikesStmt->bind_param("i", $messageID);
            $updateLikesStmt->execute();
            
            // Get current likes count
            $countSql = "SELECT Likes FROM community_chat WHERE MessageID = ?";
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param("i", $messageID);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $likesCount = $countResult->fetch_assoc()['Likes'];
            
            $response = [
                'success' => $like,
                'action' => 'like',
                'likesCount' => $likesCount
            ];
        }

        // Commit the transaction
        $conn->commit();

        echo json_encode($response);
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        
        echo json_encode([
            'success' => false, 
            'message' => 'Error processing like: ' . $e->getMessage()
        ]);
    }

    exit();
}
?>