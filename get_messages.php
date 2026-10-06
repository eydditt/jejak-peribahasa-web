<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userEmail'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include 'DBConn.php';

// Get filter from GET parameter (optional)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$userEmail = $_SESSION['userEmail'];

// Prepare SQL to fetch messages with user like status
$sql = "SELECT 
            c.MessageID, 
            c.UserEmail, 
            u.UserName, 
            c.Message, 
            c.MessageType, 
            c.DatePosted, 
            c.Likes,
            CASE WHEN ml.UserEmail IS NOT NULL THEN 1 ELSE 0 END AS UserLiked
        FROM community_chat c
        JOIN user u ON c.UserEmail = u.UserEmail
        LEFT JOIN message_likes ml ON c.MessageID = ml.MessageID AND ml.UserEmail = ?";

// Add filter conditions
switch ($filter) {
    case 'meaning':
        $sql .= " WHERE c.MessageType = 'question'";
        break;
    case 'suggestion':
        $sql .= " WHERE c.MessageType = 'answer'";
        break;
    default:
        $sql .= " WHERE 1=1"; // all messages
}

// Order by most recent first
$sql .= " ORDER BY c.DatePosted DESC LIMIT 50";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    // Convert date to a more readable format
    $row['DatePosted'] = date('M d, Y H:i', strtotime($row['DatePosted']));
    
    // Convert UserLiked to boolean
    $row['UserLiked'] = (bool)$row['UserLiked'];
    
    $messages[] = $row;
}

$stmt->close();
$conn->close();

// Return messages as JSON
header('Content-Type: application/json');
echo json_encode($messages);
exit();
?>