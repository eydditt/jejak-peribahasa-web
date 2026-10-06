<?php
session_start();

// Check if user is logged in and has correct user type
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Include the database connection
include 'DBConn.php';

// Initialize message variable
$message = '';

// Handle form submission for adding a new quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addQuiz'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $clerkID = $_SESSION['clerkID'];

    // Insert the new quiz into the database
    $query = "INSERT INTO quiz (Title, Description, ClerkID) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $title, $description, $clerkID);

    if ($stmt->execute()) {
        $quizID = $conn->insert_id; // Get the newly inserted quiz ID
        // Now handle the questions and options
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $question) {
                $stmt = $conn->prepare("INSERT INTO quiz_questions (QuizID, Question, CorrectAnswer, Option1, Option2, Option3, Option4) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssss", $quizID, $question['question'], $question['correct'], $question['opt1'], $question['opt2'], $question['opt3'], $question['opt4']);
                $stmt->execute();
            }
        }
        $message = "Quiz added successfully!";
    } else {
        $message = "Error adding quiz: " . $stmt->error;
    }

    $stmt->close();
}

// Handle delete quiz functionality
if (isset($_GET['delete'])) {
    $quizID = $_GET['delete'];

    // Delete associated records first
    $conn->query("DELETE FROM quiz_questions WHERE QuizID = '$quizID'");
    $conn->query("DELETE FROM quiz_attempts WHERE QuizID = '$quizID'");
    
    // Then delete the quiz itself
    $query = "DELETE FROM quiz WHERE QuizID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quizID);

    if ($stmt->execute()) {
        $message = "Quiz deleted successfully!";
    } else {
        $message = "Error deleting quiz: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all quizzes
$query = "SELECT q.*, COUNT(qa.AttemptID) as AttemptCount FROM quiz q LEFT JOIN quiz_attempts qa ON q.QuizID = qa.QuizID WHERE q.ClerkID = '{$_SESSION['clerkID']}' GROUP BY q.QuizID";
$result = $conn->query($query);
$quizzes = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - JejakPeribahasa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">JejakPeribahasa Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="AdminDashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manageUser.php">Manage User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manageQuiz.php">Manage Quiz</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Success Message Container -->
    <div id="messageContainer" style="margin-top: 56px;">
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="container mt-5 pt-4">
        <h2>Manage Quizzes</h2>

        <!-- Add Quiz Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Add New Quiz</h5>
            </div>
            <div class="card-body">
                <form id="addQuizForm" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Quiz Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Quiz Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>

                    <!-- Question Inputs -->
                    <div class="mb-3" id="questionsContainer">
                        <div class="question-group mb-3">
                            <label for="question" class="form-label">Question</label>
                            <input type="text" class="form-control" name="questions[0][question]" required>
                            <label for="correct" class="form-label">Correct Answer</label>
                            <input type="text" class="form-control" name="questions[0][correct]" required>
                            <label for="option1" class="form-label">Option 1</label>
                            <input type="text" class="form-control" name="questions[0][opt1]" required>
                            <label for="option2" class="form-label">Option 2</label>
                            <input type="text" class="form-control" name="questions[0][opt2]" required>
                            <label for="option3" class="form-label">Option 3</label>
                            <input type="text" class="form-control" name="questions[0][opt3]" required>
                            <label for="option4" class="form-label">Option 4</label>
                            <input type="text" class="form-control" name="questions[0][opt4]" required>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary" id="addQuestionBtn">Add Another Question</button>
                    <button type="submit" name="addQuiz" class="btn btn-primary">Add Quiz</button>
                </form>
            </div>
        </div>

        <!-- Quizzes Table -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Quiz List</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Quiz Title</th>
                            <th>Description</th>
                            <th>Attempts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quizzes as $quiz): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quiz['Title']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['Description']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['AttemptCount']); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $quiz['QuizID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this quiz?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
        let questionIndex = 1;

        document.getElementById('addQuestionBtn').addEventListener('click', function() {
            let questionGroup = document.createElement('div');
            questionGroup.classList.add('question-group', 'mb-3');
            questionGroup.innerHTML = `
                <label for="question" class="form-label">Question</label>
                <input type="text" class="form-control" name="questions[${questionIndex}][question]" required>
                <label for="correct" class="form-label">Correct Answer</label>
                <input type="text" class="form-control" name="questions[${questionIndex}][correct]" required>
                <label for="option1" class="form-label">Option 1</label>
                <input type="text" class="form-control" name="questions[${questionIndex}][opt1]" required>
                <label for="option2" class="form-label">Option 2</label>
                <input type="text" class="form-control" name="questions[${questionIndex}][opt2]" required>
                <label for="option3" class="form-label">Option 3</label>
                <input type="text" class="form-control" name="questions[${questionIndex}][opt3]" required>
                <label for="option4" class="form-label">Option 4</label>
                <input type="text" class="form-control" name="questions[${questionIndex}][opt4]" required>
            `;
            document.getElementById('questionsContainer').appendChild(questionGroup);
            questionIndex++;
        });
    </script>
</body>
</html>
