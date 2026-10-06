<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userEmail'])) {
    echo json_encode(['count' => 0]);
    exit();
}

include 'DBConn.php';

// Create message_views table if not exists
$createTableSql = "CREATE TABLE IF NOT EXISTS message_views (
    UserIC VARCHAR(12) PRIMARY KEY,
    LastChecked DATETIME NOT NULL
)";
$conn->query($createTableSql);

// Check for new messages since last check
$sql = "SELECT COUNT(*) as new_message_count 
        FROM community_chat c
        WHERE c.DatePosted > IFNULL(
            (SELECT MAX(LastChecked) 
             FROM message_views 
             WHERE UserIC = ?), 
            DATE_SUB(NOW(), INTERVAL 24 HOUR)
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['userEmail']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Update the last checked time
$updateSql = "INSERT INTO message_views (UserIC, LastChecked) 
              VALUES (?, NOW()) 
              ON DUPLICATE KEY UPDATE LastChecked = NOW()";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("s", $_SESSION['userEmail']);
$updateStmt->execute();

$stmt->close();
$updateStmt->close();
$conn->close();

// Return the count of new messages
echo json_encode(['count' => intval($row['new_message_count'])]);
exit();
?>