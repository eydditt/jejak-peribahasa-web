<?php
// favorites.php - Main favorites page
session_start();

if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'user') {
    header("Location: index.php");
    exit();
}

include 'DBConn.php';

// Pagination settings
$results_per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Get user's favorites with pagination
$sql = "SELECT p.PeriID, p.PeriName, p.PeriMean, c.Category, f.DateAdded 
        FROM favorites f
        JOIN peribahasa p ON f.PeriID = p.PeriID
        LEFT JOIN category c ON p.CategoryID = c.CategoryID
        WHERE f.UserEmail = ?
        ORDER BY f.DateAdded DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $_SESSION['userEmail'], $results_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get total favorites count for pagination
$count_sql = "SELECT COUNT(*) as total FROM favorites WHERE UserEmail = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("s", $_SESSION['userEmail']);
$count_stmt->execute();
$total_results = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - JejakPeribahasa</title>
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
                        <a class="nav-link active" href="favorites.php">My Favorites</a>
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
        <h2 class="mb-4">My Favorite Peribahasa</h2>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Proverb</th>
                            <th>Meaning</th>
                            <th>Category</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['PeriName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['PeriMean']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Category']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['DateAdded'])); ?></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm remove-favorite"
                                            data-peri-id="<?php echo $row['PeriID']; ?>">
                                            <i class="bi bi-heart-fill"></i> Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No favorites added yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Handle removing favorites
            document.querySelectorAll('.remove-favorite').forEach(button => {
                button.addEventListener('click', function () {
                    const periId = this.dataset.periId;
                    if (confirm('Are you sure you want to remove this proverb from your favorites?')) {
                        removeFavorite(periId, this);
                    }
                });
            });

            // Function to remove favorite
            function removeFavorite(periId, button) {
                fetch('manage_favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&periId=${periId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the table row
                            button.closest('tr').remove();

                            // If no more rows, add "No favorites" message
                            const tbody = document.querySelector('tbody');
                            if (tbody.children.length === 0) {
                                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No favorites added yet</td></tr>';
                            }
                        } else {
                            alert('Error removing favorite. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error removing favorite. Please try again.');
                    });
            }
        });
    </script>
</body>
</html>