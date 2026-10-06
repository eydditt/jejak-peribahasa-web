<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'user') {
    header("Location: index.php");
    exit();
}

include 'DBConn.php';

// Fetch all quizzes
$sql = "SELECT q.QuizID, q.Title, q.Description, qq.QuestionID, qq.Question, qq.CorrectAnswer, qq.Option1, qq.Option2, qq.Option3, qq.Option4 
        FROM quiz q 
        JOIN quiz_questions qq ON q.QuizID = qq.QuizID 
        ORDER BY q.QuizID, qq.QuestionID";
$result = $conn->query($sql);

$quizzes = [];
while ($row = $result->fetch_assoc()) {
    $quizID = $row['QuizID'];
    if (!isset($quizzes[$quizID])) {
        $quizzes[$quizID] = [
            'Title' => $row['Title'],
            'Description' => $row['Description'],
            'Questions' => []
        ];
    }
    $quizzes[$quizID]['Questions'][] = [
        'QuestionID' => $row['QuestionID'],
        'Question' => $row['Question'],
        'CorrectAnswer' => $row['CorrectAnswer'],
        'Options' => [$row['Option1'], $row['Option2'], $row['Option3'], $row['Option4']]
    ];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quizID = $_POST['quizID']; // Assuming you have a quizID field in the form
    $userEmail = $_SESSION['userEmail']; // Assuming UserEmail is stored in session
    $score = $_POST['score']; // The score you calculate from the submitted answers

    // Insert quiz attempt into the database
    $stmt = $conn->prepare("INSERT INTO quiz_attempts (QuizID, UserEmail, Score, DateAttempted) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("isd", $quizID, $userEmail, $score);
    $stmt->execute();
    $stmt->close();

    // Redirect to quiz result page with score
    header("Location: quiz_result.php?score=$score");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - JejakPeribahasa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
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
                        <a class="nav-link active" href="quiz.php">Quiz</a>
                    </li>

                    <li class="nav-item"><a class="nav-link active" href="editProfile.php">Edit Profile</a></li>
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
        <h2 class="mb-4">Quiz</h2>

        <?php if (empty($quizzes)): ?>
            <div class="alert alert-warning">No quiz available at this moment.</div>
        <?php else: ?>
            <form class="quizForm" method="POST" action="">
                <?php foreach ($quizzes as $quizID => $quiz): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($quiz['Title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($quiz['Description']); ?></p>

                            <?php foreach ($quiz['Questions'] as $question): ?>
                                <div class="mb-4">
                                    <p><strong><?php echo htmlspecialchars($question['Question']); ?></strong></p>
                                    <?php foreach ($question['Options'] as $index => $option): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="question_<?php echo $question['QuestionID']; ?>"
                                                id="option_<?php echo $question['QuestionID']; ?>_<?php echo $index; ?>"
                                                value="<?php echo htmlspecialchars($option); ?>">
                                            <label class="form-check-label"
                                                for="option_<?php echo $question['QuestionID']; ?>_<?php echo $index; ?>">
                                                <?php echo htmlspecialchars($option); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    <input type="hidden" name="correctAnswer_<?php echo $question['QuestionID']; ?>"
                                        value="<?php echo htmlspecialchars($question['CorrectAnswer']); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Submit Button -->
                <button type="submit" id="submitQuiz" class="btn btn-primary" disabled>Submit Answers</button>
            </form>
        <?php endif; ?>

        <div id="quizResult" class="mt-3"></div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> JejakPeribahasa. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const quizForm = document.querySelector('.quizForm');
            const submitButton = document.getElementById('submitQuiz');
            const quizResult = document.getElementById('quizResult');

            const checkCompletion = () => {
                const radioGroups = {};
                const inputs = quizForm.querySelectorAll('input[type="radio"]');

                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (!radioGroups[name]) {
                        radioGroups[name] = false;
                    }
                    if (input.checked) {
                        radioGroups[name] = true;
                    }
                });

                // Enable submit button if all questions are answered
                submitButton.disabled = Object.values(radioGroups).includes(false);
            };

            quizForm.addEventListener('change', checkCompletion); // Monitor input changes

            quizForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(quizForm);
                const answers = {};
                let correctCount = 0;
                let totalQuestions = 0;

                formData.forEach((value, key) => {
                    if (key.startsWith('question_')) {
                        const questionID = key.split('_')[1];
                        answers[questionID] = {
                            userAnswer: value,
                            correctAnswer: formData.get(`correctAnswer_${questionID}`)
                        };
                        totalQuestions++;
                    }
                });

                Object.keys(answers).forEach(questionID => {
                    if (answers[questionID].userAnswer === answers[questionID].correctAnswer) {
                        correctCount++;
                    }
                });

                const score = (correctCount / totalQuestions) * 100;
                quizResult.innerHTML = `<div class="alert alert-info">You scored ${score.toFixed(2)}% (${correctCount} out of ${totalQuestions} correct)</div>`;

                // Disable button after submission
                submitButton.disabled = true;

                // Create hidden fields for quizID and score
                const quizIDField = document.createElement('input');
                quizIDField.type = 'hidden';
                quizIDField.name = 'quizID';
                // Use the first quiz's ID
                quizIDField.value = <?php echo key($quizzes); ?>;
                quizForm.appendChild(quizIDField);

                const scoreField = document.createElement('input');
                scoreField.type = 'hidden';
                scoreField.name = 'score';
                scoreField.value = score;
                quizForm.appendChild(scoreField);

                // Submit the form to insert into the database
                quizForm.submit();
            });
        });
    </script>
</body>
</html>