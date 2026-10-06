I'll provide the full UserDashboard.php code, completing the script from where it was left off:

```php
<?php
session_start();

// Check if user is logged in and has correct user type
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'user') {
    header("Location: index.php");
    exit();
}

// Include the database connection
include 'DBConn.php';

// Pagination settings
$results_per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $results_per_page;

// Check if search is applied
$searchQuery = isset($_GET['search']) ? $_GET['search'] : "";

// First, get total number of records for pagination
$count_sql = "SELECT COUNT(*) as total FROM peribahasa p 
              LEFT JOIN category c ON p.CategoryID = c.CategoryID 
              WHERE p.PeriName LIKE ? OR p.PeriMean LIKE ?";
$count_stmt = $conn->prepare($count_sql);
$searchTerm = "%$searchQuery%";
$count_stmt->bind_param("ss", $searchTerm, $searchTerm);
$count_stmt->execute();
$total_results = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);


$total_pages = max(1, ceil($total_results / $results_per_page));
$page = min(max(1, $page), $total_pages);
$offset = ($page - 1) * $results_per_page;

// Prepare the main SQL statement with pagination
$sql = "SELECT p.PeriID, p.PeriName, p.PeriMean, p.ContohAyat, c.Category, 
               IF(f.FavoriteID IS NOT NULL, 1, 0) as isFavorite
        FROM peribahasa p 
        LEFT JOIN category c ON p.CategoryID = c.CategoryID 
        LEFT JOIN favorites f ON p.PeriID = f.PeriID AND f.UserEmail = ?
        WHERE p.PeriName LIKE ? OR p.PeriMean LIKE ?
        ORDER BY p.PeriID ASC  /* Add this line */
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssii", $_SESSION['userEmail'], $searchTerm, $searchTerm, $results_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - JejakPeribahasa</title>
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
                        <a class="nav-link" href="quiz.php">Quiz</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="editProfile.php">Edit Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#chatModal">
                            <i class="bi bi-chat-dots"></i> Community Chat
                            <span class="badge bg-light text-dark" id="unreadCount"></span>
                        </a>
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

    <!-- Success Message Container -->
    <div id="messageContainer" style="margin-top: 56px;"></div>

    <!-- Main Content -->
    <div class="container mt-5 pt-4">
        <div class="row mb-4">
            <div class="col-md-6 mx-auto">
                <form class="d-flex" method="get">
                    <input class="form-control me-2" type="text" name="search"
                        value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search Proverbs">
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
            </div>
        </div>

        <!-- Proverbs Table -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Proverb</th>
                            <th>Meaning</th>
                            <th>Category</th>
                            <th>Contoh Ayat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['PeriID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['PeriName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['PeriMean']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Category']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ContohAyat']) . "</td>";
                                // Add the new column here
                                echo "<td>";
                                if ($row['isFavorite']) {
                                    echo "<button class='btn btn-primary btn-sm' disabled>
                    <i class='bi bi-heart-fill'></i> Added to Favorites
                  </button>";
                                } else {
                                    echo "<button class='btn btn-outline-primary btn-sm add-favorite' 
                    data-peri-id='" . $row['PeriID'] . "'>
                    <i class='bi bi-heart'></i> Add to Favorites
                  </button>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No proverbs found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

         <!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
           <!-- Previous page link -->
<li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
    <a class="page-link" href="<?php echo ($page > 1) ? '?page=' . ($page - 1) . '&search=' . htmlspecialchars(urlencode($searchQuery)) : '#'; ?>" 
       <?php echo ($page <= 1) ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
        <span aria-hidden="true">&laquo;</span>
    </a>
</li>

            <!-- Page numbers -->
            <?php
            // Calculate range of pages to show
            $range = 2;
            $start_page = max(1, $page - $range);
            $end_page = min($total_pages, $page + $range);

            // Show first page if we're not starting at 1
            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($searchQuery) . '">1</a></li>';
                if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            // Show page numbers
            for ($i = $start_page; $i <= $end_page; $i++) {
                $activeClass = ($page == $i) ? 'active' : '';
                echo '<li class="page-item ' . $activeClass . '">';
                echo '<a class="page-link" href="?page=' . $i . '&search=' . urlencode($searchQuery) . '">' . $i . '</a>';
                echo '</li>';
            }

            // Show last page if we're not ending at total_pages
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . urlencode($searchQuery) . '">' . $total_pages . '</a></li>';
            }
            ?>

            <!-- Next page link -->
            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link"
                    href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchQuery); ?>"
                    aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
        </div>
    </div>

    <!-- Community Chat Modal -->
    <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #2b542c; color: white;">
                    <h5 class="modal-title" id="chatModalLabel">Community Discussion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-12">
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-success active" data-filter="all">All</button>
                                        <button class="btn btn-outline-success" data-filter="meaning">Questions</button>
                                        <button class="btn btn-outline-success" data-filter="suggestion">Answers</button>
                                    </div>
                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#askQuestionModal">
                                        <i class="bi bi-plus-circle"></i> Chat
                                    </button>
                                </div>
                            </div>
                            <div class="chat-messages p-3" style="height: 400px; overflow-y: auto;">
                                <div id="chatMessages" class="d-flex flex-column gap-3">
                                    <!-- Messages will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ask Question Modal -->
    <div class="modal fade" id="askQuestionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Start Chatting </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="questionForm">
                        <div class="mb-3">
                            <label class="form-label">Your Chat:</label>
                            <textarea class="form-control" rows="3" required name="message"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Chat Type:</label>
                            <select class="form-select" name="questionType" required>
                                <option value="question">Ask About Question</option>
                                <option value="answer">Answering</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Post Chat</button>
                    </form>
                </div>
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
    function loadChatMessages(filter = 'all') {
        fetch(`get_messages.php?filter=${filter}`)
            .then(response => response.json())
            .then(data => {
                const chatContainer = document.getElementById('chatMessages');
                chatContainer.innerHTML = '';

                data.forEach(message => {
    const div = document.createElement('div');
    div.className = 'card mb-2';
    div.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h6 class="card-subtitle mb-2 text-muted">${message.UserName}</h6>
                <small class="text-muted">${message.DatePosted}</small>
            </div>
            <p class="card-text">${message.Message}</p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge ${message.MessageType === 'question' ? 'bg-primary' : 'bg-success'}">
                    ${message.MessageType === 'question' ? 'Question' : 'Answer'}
                </span>
                <button class="btn btn-sm ${message.UserLiked ? 'btn-primary' : 'btn-outline-primary'} like-button" data-message-id="${message.MessageID}">
                    <i class="bi ${message.UserLiked ? 'bi-heart-fill' : 'bi-heart'}"></i> ${message.Likes || 0}
                </button>
            </div>
        </div>
    `;

    // Add like event listener
    const likeButton = div.querySelector('.like-button');
    likeButton.addEventListener('click', () => {
        likeMessage(message.MessageID);
    });

    chatContainer.appendChild(div);
});
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }

    function likeMessage(messageID) {
    fetch('like_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `messageID=${messageID}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Find the like button for this message
            const likeButton = document.querySelector(`.like-button[data-message-id="${messageID}"]`);
            
            // Update likes count and icon
            if (data.action === 'like') {
                likeButton.innerHTML = `<i class="bi bi-heart-fill"></i> ${data.likesCount}`;
                likeButton.classList.remove('btn-outline-primary');
                likeButton.classList.add('btn-primary');
            } else {
                likeButton.innerHTML = `<i class="bi bi-heart"></i> ${data.likesCount}`;
                likeButton.classList.remove('btn-primary');
                likeButton.classList.add('btn-outline-primary');
            }
        }
    })
    .catch(error => {
        console.error('Error liking message:', error);
    });
}

    document.addEventListener('DOMContentLoaded', function () {
        // Question form submission
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('post_question.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload messages
                    loadChatMessages();
                    
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('askQuestionModal')).hide();
                    
                    // Reset form
                    this.reset();
                } else {
                    alert('Failed to post message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        // Filter buttons for chat messages
        document.querySelectorAll('[data-filter]').forEach(button => {
            button.addEventListener('click', (e) => {
                // Remove active class from all buttons
                document.querySelectorAll('[data-filter]').forEach(btn => 
                    btn.classList.remove('active')
                );
                
                // Add active class to clicked button
                e.target.classList.add('active');
                
                // Load messages with selected filter
                loadChatMessages(e.target.dataset.filter);
            });
        });

        // Initial load of chat messages
        loadChatMessages();

        // Auto-refresh chat when modal is open
        setInterval(() => {
            const chatModal = document.getElementById('chatModal');
            if (chatModal.classList.contains('show')) {
                loadChatMessages();
            }
        }, 10000);

        // Check for new messages
        function checkNewMessages() {
            fetch('check_new_messages.php')
            .then(response => response.json())
            .then(data => {
                const unreadCount = document.getElementById('unreadCount');
                if (data.count > 0) {
                    unreadCount.textContent = data.count;
                    unreadCount.style.display = 'inline';
                } else {
                    unreadCount.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error checking new messages:', error);
            });
        }

        // Check for new messages every 30 seconds
        setInterval(checkNewMessages, 30000);
        
        // Initial check for new messages
        checkNewMessages();
    });

    document.addEventListener('DOMContentLoaded', function () {
    // Handle adding favorites
    document.querySelectorAll('.add-favorite').forEach(button => {
        button.addEventListener('click', function () {
            const periId = this.dataset.periId;

            fetch('manage_favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&periId=${periId}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Favorites response:', data); // Debugging log
                
                if (data.success) {
                    // Change button appearance
                    this.classList.replace('btn-outline-primary', 'btn-primary');
                    this.innerHTML = '<i class="bi bi-heart-fill"></i> Added to Favorites';
                    this.disabled = true;

                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        Added to favorites successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.getElementById('messageContainer').appendChild(alert);
                } else {
                    // Show error message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-warning alert-dismissible fade show';
                    alert.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.getElementById('messageContainer').appendChild(alert);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    Error adding to favorites. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.getElementById('messageContainer').appendChild(alert);
            });
        });
    });
});
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>