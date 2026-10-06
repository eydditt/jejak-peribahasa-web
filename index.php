<?php
// Include the database connection
include 'DBConn.php';

// Pagination settings
$results_per_page = 10; // Number of results per page

// Get current page number
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1

// Calculate the offset for SQL LIMIT clause
$offset = ($page - 1) * $results_per_page;

// Check if search is applied
$searchQuery = "";
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
}

// Handle messages
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['messageType']) ? $_GET['messageType'] : '';

// First, get total number of records for pagination
$count_sql = "SELECT COUNT(*) as total FROM peribahasa WHERE PeriName LIKE ? OR PeriMean LIKE ?";
$count_stmt = $conn->prepare($count_sql);
$searchTerm = "%$searchQuery%";
$count_stmt->bind_param("ss", $searchTerm, $searchTerm);
$count_stmt->execute();
$total_results = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);

// Prepare the main SQL statement with LIMIT and OFFSET
$sql = "SELECT PeriID, PeriName, PeriMean FROM peribahasa 
        WHERE PeriName LIKE ? OR PeriMean LIKE ? 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

// Add wildcards to the search query and bind parameters
$stmt->bind_param("ssii", $searchTerm, $searchTerm, $results_per_page, $offset);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JejakPeribahasa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#loginForm').submit(function (e) {
                e.preventDefault();

                var loginId = $('#loginId').val();
                var password = $('#password').val();
                var userType = $('#userType').val();

                $.ajax({
                    type: 'POST',
                    url: 'login.php',
                    data: {
                        loginId: loginId,
                        password: password,
                        userType: userType
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            sessionStorage.setItem('loginMessage', response.message);
                            window.location.href = response.redirect;
                        } else {
                            $('#loginError').text(response.message).show();
                        }
                    },
                    error: function () {
                        $('#loginError').text('An error occurred. Please try again.').show();
                    }
                });
            });
        });
    </script>
</head>

<body>

    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">JejakPeribahasa</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn btn-primary text-white" data-bs-toggle="modal"
                            data-bs-target="#loginModal">Login</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn btn-success text-white" data-bs-toggle="modal"
                            data-bs-target="#registerModal">Register</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert"
            style="margin-top: 56px;">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>



    <!-- Auto Sliding Carousel Section -->
    <header id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/CBG1.jpg" class="d-block w-100" alt="Slide 1"
                    style="height: 300px; object-fit: cover;">
                <div class="carousel-caption d-none d-md-block">
                    <h1 class="h4">Welcome to JejakPeribahasa</h1>
                    <p class="lead">Discover the beauty and wisdom of Malay proverbs.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/CBG2.jpg" class="d-block w-100" alt="Slide 2"
                    style="height: 300px; object-fit: cover;">
                <div class="carousel-caption d-none d-md-block">
                    <h1 class="h4">Preserving Heritage</h1>
                    <p class="lead">Explore and preserve the cultural significance of Malay proverbs.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/CBG3.jpg" class="d-block w-100" alt="Slide 3"
                    style="height: 300px; object-fit: cover;">
                <div class="carousel-caption d-none d-md-block">
                    <h1 class="h4">Learn and Grow</h1>
                    <p class="lead">Enhance your understanding of Malay language and culture.</p>
                </div>
            </div>
        </div>
    </header>

    <!-- List of Peribahasa Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center">List of Peribahasa</h2>

            <!-- Search Form -->
            <div class="row mb-4">
                <div class="col-md-6 mx-auto">
                    <form class="d-flex" method="get" action="">
                        <input class="form-control me-2" type="text" name="search"
                            value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search Proverbs"
                            aria-label="Search">
                        <button class="btn btn-primary" type="submit">Go</button>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Proverb</th>
                        <th>Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['PeriID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['PeriName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['PeriMean']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center'>No proverbs found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <!-- Previous page link -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchQuery); ?>"
                                aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <!-- Page numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

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
    </section>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="loginModalLabel">WELCOME</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loginFormSection">
                        <form id="loginForm" method="POST">
                            <div class="mb-3">
                                <label for="loginId" class="form-label">Email / ID Number</label>
                                <input type="text" class="form-control" id="loginId" name="loginId" required>
                                <small class="form-text text-muted">Enter Email for users, ID for
                                    admin/clerk</small>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="userType" class="form-label">User Type</label>
                                <select class="form-select" id="userType" name="userType" required>
                                    <option value="user">User</option>
                                    <option value="admin">Manager</option>
                                    <option value="clerk">Clerk</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                            <div id="loginError" class="mt-3 text-danger" style="display:none;"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="registerModalLabel">Create an Account</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm" method="POST" action="register.php">
                        <div class="mb-3">
                            <label for="registerName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="registerName" name="registerName" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerIC" class="form-label">Email</label>
                            <input type="text" class="form-control" id="registerIC" name="registerIC" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="registerPassword" name="registerPassword"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="userRole" class="form-label">User Role</label>
                            <input type="text" class="form-control" id="userRole" name="userRole" value="user" readonly>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Register</button>
                    </form>
                    <div id="registerError" class="mt-3 text-danger" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-5">
        <div class="container">
            <h2>About JejakPeribahasa</h2>
            <p>
                JejakPeribahasa is a platform dedicated to preserving and promoting the rich heritage of Malay proverbs.
                Dive into a treasure trove of wisdom, cultural values, and literary beauty through the curated
                collection of proverbs.
                Learn their meanings, context, and significance to better understand Malay culture and language.
            </p>
            <p class="mt-3">&copy; <?php echo date("Y"); ?> JejakPeribahasa. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>