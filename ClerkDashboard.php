<?php
session_start();

// Check if clerk is logged in
if (!isset($_SESSION['clerkID'])) {
    header("Location: login.php");
    exit();
}

include 'DBConn.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'deleteUser':
                $userEmail = $_POST['userEmail'];
                // Delete associated records first
                $conn->query("DELETE FROM favorites WHERE UserEmail = '$userEmail'");
                $conn->query("DELETE FROM message_likes WHERE UserEmail = '$userEmail'");
                $conn->query("DELETE FROM quiz_attempts WHERE UserEmail = '$userEmail'");
                $conn->query("DELETE FROM community_chat WHERE UserEmail = '$userEmail'");
                // Finally delete user
                $conn->query("DELETE FROM user WHERE UserEmail = '$userEmail'");
                break;

            case 'deletePeribahasa':
                $periID = $_POST['periID'];
                // Delete associated records first
                $conn->query("DELETE FROM favorites WHERE PeriID = '$periID'");
                // Then delete peribahasa
                $conn->query("DELETE FROM peribahasa WHERE PeriID = '$periID'");
                break;

            case 'editPeribahasa':
                $periID = $_POST['periID'];
                $periName = $_POST['periName'];
                $periMean = $_POST['periMean'];
                $ContohAyat = $_POST['ContohAyat'];
                $categoryID = $_POST['categoryID'];

                $stmt = $conn->prepare("UPDATE peribahasa SET PeriName = ?, PeriMean = ?, ContohAyat = ?, CategoryID = ? WHERE PeriID = ?");
                $stmt->bind_param("ssssi", $periName, $periMean, $ContohAyat, $categoryID, $periID);
                $stmt->execute();
                
                // Redirect to refresh the page after edit
                header("Location: ClerkDashboard.php");
                exit();
                break;

            case 'addPeribahasa':
                $periName = $_POST['periName'];
                $periMean = $_POST['periMean'];
                $ContohAyat = $_POST['ContohAyat'];
                $categoryID = $_POST['categoryID'];

                $stmt = $conn->prepare("INSERT INTO peribahasa (PeriName, PeriMean, ContohAyat, CategoryID) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $periName, $periMean, $ContohAyat, $categoryID);
                $stmt->execute();
                break;

            case 'addQuiz':
                $title = $_POST['title'];
                $description = $_POST['description'];
                $clerkID = $_SESSION['clerkID'];

                // Insert quiz
                $stmt = $conn->prepare("INSERT INTO quiz (Title, Description, ClerkID) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $title, $description, $clerkID);
                $stmt->execute();
                $quizID = $conn->insert_id;

                // Insert questions
                foreach ($_POST['questions'] as $q) {
                    $stmt = $conn->prepare("INSERT INTO quiz_questions (QuizID, Question, CorrectAnswer, Option1, Option2, Option3, Option4) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssss", $quizID, $q['question'], $q['correct'], $q['opt1'], $q['opt2'], $q['opt3'], $q['opt4']);
                    $stmt->execute();
                }
                break;

            case 'deleteChat':
                $messageID = $_POST['messageID'];
                // Delete likes first
                $conn->query("DELETE FROM message_likes WHERE MessageID = '$messageID'");
                // Then delete message
                $conn->query("DELETE FROM community_chat WHERE MessageID = '$messageID'");
                break;

            case 'deleteQuiz':
                $quizID = $_POST['quizID'];
                // Delete associated records first (if any)
                $conn->query("DELETE FROM quiz_attempts WHERE QuizID = '$quizID'");
                $conn->query("DELETE FROM quiz_questions WHERE QuizID = '$quizID'");
                // Then delete the quiz
                $conn->query("DELETE FROM quiz WHERE QuizID = '$quizID'");
                break;
        }
    }
}

// Fetch data for each section
$users = $conn->query("SELECT * FROM user WHERE UserRole = 'user' ORDER BY UserName");
$peribahasa = $conn->query("SELECT p.*, c.Category FROM peribahasa p JOIN category c ON p.CategoryID = c.CategoryID");
$categories = $conn->query("SELECT * FROM category");
$chats = $conn->query("SELECT c.*, u.UserName FROM community_chat c JOIN user u ON c.UserEmail = u.UserEmail ORDER BY c.DatePosted DESC");
$quizzes = $conn->query("SELECT q.*, COUNT(qa.AttemptID) as AttemptCount FROM quiz q LEFT JOIN quiz_attempts qa ON q.QuizID = qa.QuizID WHERE q.ClerkID = '{$_SESSION['clerkID']}' GROUP BY q.QuizID");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clerk Dashboard - JejakPeribahasa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #2b542c;
            border-bottom: 2px solid #2b542c;
        }
        .nav-tabs .nav-link {
            color: #2b542c;
        }
        .nav-tabs .nav-link:hover {
            color: #d4edda;
        }
        .action-buttons button {
            margin-right: 5px;
        }
        .table td {
            vertical-align: middle;
        }
        .card-header {
            background-color: #2b542c;
            color: #ffffff;
        }
        .btn-primary {
            background-color: #2b542c;
            border-color: #2b542c;
        }
        .btn-primary:hover {
            background-color: #d4edda;
            border-color: #d4edda;
            color: #2b542c;
        }
        #sortPeribahasa {
            width: auto;
            display: inline-block;
            margin-right: 10px;
        }
    </style>
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
                        <a class="nav-link active" href="ClerkDashboard.php">Dashboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['clerkName']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5 pt-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#users">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#peribahasa">
                    <i class="bi bi-book"></i> Peribahasa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#quizzes">
                    <i class="bi bi-question-circle"></i> Quizzes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#chat">
                    <i class="bi bi-chat-dots"></i> Community Chat
                </a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Users Tab -->
            <div class="tab-pane fade show active" id="users">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">User Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Username</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['UserEmail']); ?></td>
                                            <td><?php echo htmlspecialchars($user['UserName']); ?></td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="deleteUser">
                                                    <input type="hidden" name="userEmail" value="<?php echo $user['UserEmail']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peribahasa Tab -->
            <div class="tab-pane fade" id="peribahasa">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Peribahasa Management</h5>
                        <div>
                            <select id="sortPeribahasa" class="form-select me-2" style="width: auto;">
                                <option value="id_asc">Sort by ID (Ascending)</option>
                                <option value="id_desc">Sort by ID (Descending)</option>
                                <option value="category_asc">Sort by Category (Ascending)</option>
                                <option value="category_desc">Sort by Category (Descending)</option>
                            </select>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPeribahasaModal">
                                <i class="bi bi-plus"></i> Add New Peribahasa
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="peribahasaTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Peribahasa</th>
                                        <th>Meaning</th>
                                        <th>Category</th>
                                        <th>Sentence</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($peri = $peribahasa->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $peri['PeriID']; ?></td>
                                            <td><?php echo htmlspecialchars($peri['PeriName']); ?></td>
                                            <td><?php echo htmlspecialchars($peri['PeriMean']); ?></td>
                                            <td><?php echo htmlspecialchars($peri['Category']); ?></td>
                                            <td><?php echo htmlspecialchars($peri['ContohAyat']); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm me-1" onclick="editPeribahasa(<?php echo $peri['PeriID']; ?>, '<?php echo addslashes($peri['PeriName']); ?>', '<?php echo addslashes($peri['PeriMean']); ?>', '<?php echo addslashes($peri['ContohAyat']); ?>', '<?php echo $peri['CategoryID']; ?>')">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="deletePeribahasa">
                                                    <input type="hidden" name="periID" value="<?php echo $peri['PeriID']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this peribahasa?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

         <!-- Quizzes Tab -->
         <div class="tab-pane fade" id="quizzes">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Quiz Management</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                            <i class="bi bi-plus"></i> Create New Quiz
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Created Date</th>
                                        <th>Attempts</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($quiz['Title']); ?></td>
                                            <td><?php echo htmlspecialchars($quiz['Description']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($quiz['DateCreated'])); ?></td>
                                            <td><?php echo $quiz['AttemptCount']; ?></td>
                                            <td>
                                                <button class="btn btn-info btn-sm me-1" onclick="viewQuizDetails(<?php echo $quiz['QuizID']; ?>)">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="deleteQuiz">
                                                    <input type="hidden" name="quizID" value="<?php echo $quiz['QuizID']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this quiz?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Quiz Modal -->
            <div class="modal fade" id="addQuizModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Quiz</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" id="quizForm">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Quiz Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                </div>
                                <div id="questionsContainer">
                                    <div class="question mb-3">
                                        <h6>Question 1</h6>
                                        <div class="mb-3">
                                            <label class="form-label">Question Text</label>
                                            <input type="text" class="form-control" name="questions[0][question]" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Correct Answer</label>
                                            <input type="text" class="form-control" name="questions[0][correct]" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Option 1</label>
                                            <input type="text" class="form-control" name="questions[0][opt1]" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Option 2</label>
                                            <input type="text" class="form-control" name="questions[0][opt2]" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Option 3</label>
                                            <input type="text" class="form-control" name="questions[0][opt3]" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Option 4</label>
                                            <input type="text" class="form-control" name="questions[0][opt4]" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-secondary me-2" onclick="addQuestion()">Add Another Question</button>
                                <input type="hidden" name="action" value="addQuiz">
                                <button type="submit" class="btn btn-primary">Create Quiz</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
<!-- Community Chat Tab -->
<div class="tab-pane fade" id="chat">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Community Chat Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Message</th>
                                        <th>Posted Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($chat = $chats->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($chat['UserName']); ?></td>
                                            <td><?php echo htmlspecialchars($chat['Message']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($chat['DatePosted'])); ?></td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="deleteChat">
                                                    <input type="hidden" name="messageID" value="<?php echo $chat['MessageID']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this message?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Peribahasa Modal -->
    <div class="modal fade" id="addPeribahasaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Peribahasa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="periName" class="form-label">Peribahasa</label>
                            <input type="text" class="form-control" id="periName" name="periName" required>
                        </div>
                        <div class="mb-3">
                            <label for="periMean" class="form-label">Meaning</label>
                            <textarea class="form-control" id="periMean" name="periMean" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="ContohAyat" class="form-label">Sentence</label>
                            <textarea class="form-control" id="ContohAyat" name="ContohAyat" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="categoryID" class="form-label">Category</label>
                            <select class="form-select" id="categoryID" name="categoryID" required>
                                <?php 
                                // Reset the categories result pointer
                                $categories->data_seek(0);
                                while ($category = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['CategoryID']; ?>">
                                        <?php echo htmlspecialchars($category['Category']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <input type="hidden" name="action" value="addPeribahasa">
                        <button type="submit" class="btn btn-primary">Add Peribahasa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Peribahasa Modal -->
    <div class="modal fade" id="editPeribahasaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Peribahasa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="hidden" id="editPeriID" name="periID">
                        <div class="mb-3">
                            <label for="editPeriName" class="form-label">Peribahasa</label>
                            <input type="text" class="form-control" id="editPeriName" name="periName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPeriMean" class="form-label">Meaning</label>
                            <textarea class="form-control" id="editPeriMean" name="periMean" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editContohAyat" class="form-label">Sentence</label>
                            <textarea class="form-control" id="editContohAyat" name="ContohAyat" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryID" class="form-label">Category</label>
                            <select class="form-select" id="editCategoryID" name="categoryID" required>
                                <?php 
                                // Reset the categories result pointer
                                $categories->data_seek(0);
                                while ($category = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['CategoryID']; ?>">
                                        <?php echo htmlspecialchars($category['Category']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <input type="hidden" name="action" value="editPeribahasa">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to handle edit peribahasa
        function editPeribahasa(periID, periName, periMean, contohAyat, categoryID) {
            document.getElementById('editPeriID').value = periID;
            document.getElementById('editPeriName').value = periName;
            document.getElementById('editPeriMean').value = periMean;
            document.getElementById('editContohAyat').value = contohAyat;
            document.getElementById('editCategoryID').value = categoryID;
            
            new bootstrap.Modal(document.getElementById('editPeribahasaModal')).show();
        }

        // Sorting functionality
        document.getElementById('sortPeribahasa').addEventListener('change', function() {
            const sortValue = this.value;
            const table = document.getElementById('peribahasaTable');
            const rows = Array.from(table.querySelectorAll('tbody tr'));

            rows.sort((a, b) => {
                const aId = parseInt(a.cells[0].textContent);
                const bId = parseInt(b.cells[0].textContent);
                const aCategory = a.cells[3].textContent.toLowerCase();
                const bCategory = b.cells[3].textContent.toLowerCase();

                switch (sortValue) {
                    case 'id_asc':
                        return aId - bId;
                    case 'id_desc':
                        return bId - aId;
                    case 'category_asc':
                        return aCategory.localeCompare(bCategory);
                    case 'category_desc':
                        return bCategory.localeCompare(aCategory);
                    default:
                        return 0;
                }
            });

            const tbody = table.querySelector('tbody');
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        });
    </script>
</body>
</html>