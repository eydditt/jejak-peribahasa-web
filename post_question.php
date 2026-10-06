<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userEmail'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include 'DBConn.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Validate inputs
$message = trim($_POST['message'] ?? '');
$messageType = $_POST['questionType'] ?? '';
$parentMessageID = isset($_POST['parentMessageID']) ? intval($_POST['parentMessageID']) : null;

// Check for empty message
if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

// Validate message type
$validMessageTypes = ['question', 'answer'];
if (!in_array($messageType, $validMessageTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid message type']);
    exit();
}

// Prepare SQL to insert message
$sql = "INSERT INTO community_chat (UserEmail, Message, MessageType, ParentMessageID) 
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $_SESSION['userEmail'], $message, $messageType, $parentMessageID);

try {
    $result = $stmt->execute();
    
    if ($result) {
        // Get the ID of the last inserted message
        $lastId = $stmt->insert_id;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Message posted successfully',
            'messageId' => $lastId
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to post message'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
exit();
?>