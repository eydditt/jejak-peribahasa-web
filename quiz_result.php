<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'user') {
    header("Location: index.php");
    exit();
}

include 'DBConn.php';

// Get the score from the URL (it should be passed in the query string)
if (isset($_GET['score'])) {
    $score = floatval($_GET['score']);
} else {
    // If no score is passed, redirect to quiz page
    header("Location: quiz.php");
    exit();
}

// Fetch the most recent quiz if no quizID is provided
$quizID = isset($_POST['quizID']) ? intval($_POST['quizID']) : null;

if ($quizID === null) {
    // Fetch the latest quiz ID from the database
    $quizQuery = "SELECT QuizID FROM quiz ORDER BY QuizID DESC LIMIT 1";
    $result = $conn->query($quizQuery);
    
    if ($result && $row = $result->fetch_assoc()) {
        $quizID = $row['QuizID'];
    } else {
        // No quizzes found
        header("Location: quiz.php");
        exit();
    }
}

$userEmail = $_SESSION['userEmail'];

$stmt = $conn->prepare("INSERT INTO quiz_attempts (QuizID, UserEmail, Score, DateAttempted) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("isd", $quizID, $userEmail, $score);

try {
    $stmt->execute();
} catch (Exception $e) {
    // Log the error or handle it appropriately
    error_log("Quiz attempt insertion failed: " . $e->getMessage());
}
$stmt->close();

// Calculate the result message
$scorePercentage = $score;  // Score is already a percentage
$resultMessage = "";

if ($scorePercentage >= 80) {
    $resultMessage = "Excellent!";
} elseif ($scorePercentage >= 60) {
    $resultMessage = "Good Job!";
} elseif ($scorePercentage >= 40) {
    $resultMessage = "Needs Improvement!";
} else {
    $resultMessage = "Better Luck Next Time!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - JejakPeribahasa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
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
                    <li class="nav-item">
                        <a class="nav-link" href="UserDashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="favorites.php">My Favorites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quiz.php">Quiz</a>
                    </li>
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

    <div class="container mt-5 pt-4">
        <h2 class="mb-4">Quiz Result</h2>

        <div class="alert alert-info">
            <strong>Your Score:</strong> <?php echo number_format($score, 2); ?>%
        </div>

        <div class="alert alert-success">
            <strong>Result: </strong> <?php echo $resultMessage; ?>
        </div