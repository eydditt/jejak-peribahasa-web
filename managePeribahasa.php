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

// Handle form submission for adding a new peribahasa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addPeribahasa'])) {
    $periName = $_POST['periName'];
    $periMean = $_POST['periMean'];
    $categoryID = $_POST['categoryID'];
    $contohAyat = $_POST['contohAyat'];

    // Insert the new peribahasa into the database
    $query = "INSERT INTO peribahasa (PeriName, PeriMean, CategoryID, ContohAyat) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $periName, $periMean, $categoryID, $contohAyat);

    if ($stmt->execute()) {
        $message = "Peribahasa added successfully!";
    } else {
        $message = "Error adding peribahasa: " . $stmt->error;
    }

    $stmt->close();
}

// Handle edit peribahasa functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updatePeribahasa'])) {
    $periID = $_POST['periID'];
    $periName = $_POST['periName'];
    $periMean = $_POST['periMean'];
    $categoryID = $_POST['categoryID'];
    $contohAyat = $_POST['contohAyat'];

    // Update peribahasa details in the database
    $query = "UPDATE peribahasa SET PeriName = ?, PeriMean = ?, CategoryID = ?, ContohAyat = ? WHERE PeriID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $periName, $periMean, $categoryID, $contohAyat, $periID);

    if ($stmt->execute()) {
        $message = "Peribahasa updated successfully!";
        // Redirect to avoid form resubmission
        header("Location: managePeribahasa.php");
        exit();
    } else {
        $message = "Error updating peribahasa: " . $stmt->error;
    }

    $stmt->close();
}

// Handle delete peribahasa functionality
if (isset($_GET['delete'])) {
    $periID = $_GET['delete'];

    // Delete peribahasa from the database
    $query = "DELETE FROM peribahasa WHERE PeriID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $periID);

    if ($stmt->execute()) {
        $message = "Peribahasa deleted successfully!";
    } else {
        $message = "Error deleting peribahasa: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all peribahasa from the database
$query = "SELECT * FROM peribahasa";
$result = $conn->query($query);
$peribahasas = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Peribahasa - JejakPeribahasa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        th.actions,
        td.actions {
            width: 150px;
        }
    </style>
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
                        <a class="nav-link" href="manageClerk.php">Manage Clerk</a>
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
        <h2>Manage Peribahasa</h2>

        <!-- Add Peribahasa Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Add New Peribahasa</h5>
            </div>
            <div class="card-body">
                <form id="addPeribahasaForm" method="POST">
                    <div class="mb-3">
                        <label for="periName" class="form-label">Peribahasa Name</label>
                        <input type="text" class="form-control" id="periName" name="periName" required>
                    </div>
                    <div class="mb-3">
                        <label for="periMean" class="form-label">Meaning</label>
                        <input type="text" class="form-control" id="periMean" name="periMean" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoryID" class="form-label">Category</label>
                        <select class="form-select" id="categoryID" name="categoryID" required>
                            <option value="pepatah">pepatah</option>
                            <option value="bidalan">bidalan</option>
                            <option value="perumpamaan">perumpamaan</option>
                            <option value="kiasan">kiasan</option>
                            <option value="katakatahikmat">katakatahikmat</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="contohAyat" class="form-label">Contoh Ayat</label>
                        <input type="text" class="form-control" id="contohAyat" name="contohAyat" required>
                    </div>
                    <button type="submit" name="addPeribahasa" class="btn btn-primary">Add Peribahasa</button>
                </form>
            </div>
        </div>

        <!-- Peribahasa Table -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Peribahasa List</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Peribahasa ID</th>
                            <th>Peribahasa Name</th>
                            <th>Meaning</th>
                            <th>Category</th>
                            <th>Contoh Ayat</th>
                            <th class="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peribahasas as $peribahasa): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($peribahasa['PeriID']); ?></td>
                                <td><?php echo htmlspecialchars($peribahasa['PeriName']); ?></td>
                                <td><?php echo htmlspecialchars($peribahasa['PeriMean']); ?></td>
                                <td><?php echo htmlspecialchars($peribahasa['CategoryID']); ?></td>
                                <td><?php echo htmlspecialchars($peribahasa['ContohAyat']); ?></td>
                                <td class="action-buttons">
                                    <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPeribahasaModal<?php echo $peribahasa['PeriID']; ?>">Edit</a>
                                    <a href="?delete=<?php echo $peribahasa['PeriID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this peribahasa?');">Delete</a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
<div class="modal fade" id="editPeribahasaModal<?php echo $peribahasa['PeriID']; ?>" tabindex="-1" aria-labelledby="editPeribahasaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPeribahasaModalLabel">Edit Peribahasa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="periID" value="<?php echo htmlspecialchars($peribahasa['PeriID']); ?>">
                    <div class="mb-3">
                        <label for="periName<?php echo $peribahasa['PeriID']; ?>" class="form-label">Peribahasa Name</label>
                        <input type="text" class="form-control" id="periName<?php echo $peribahasa['PeriID']; ?>" name="periName" value="<?php echo htmlspecialchars($peribahasa['PeriName']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="periMean<?php echo $peribahasa['PeriID']; ?>" class="form-label">Meaning</label>
                        <textarea class="form-control" id="periMean<?php echo $peribahasa['PeriID']; ?>" name="periMean" rows="3" required><?php echo htmlspecialchars($peribahasa['PeriMean']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="categoryID<?php echo $peribahasa['PeriID']; ?>" class="form-label">Category</label>
                        <select class="form-select" id="categoryID<?php echo $peribahasa['PeriID']; ?>" name="categoryID" required>
                            <option value="pepatah" <?php echo ($peribahasa['CategoryID'] == 'pepatah') ? 'selected' : ''; ?>>Pepatah</option>
                            <option value="bidalan" <?php echo ($peribahasa['CategoryID'] == 'bidalan') ? 'selected' : ''; ?>>Bidalan</option>
                            <option value="perumpamaan" <?php echo ($peribahasa['CategoryID'] == 'perumpamaan') ? 'selected' : ''; ?>>Perumpamaan</option>
                            <option value="kiasan" <?php echo ($peribahasa['CategoryID'] == 'kiasan') ? 'selected' : ''; ?>>Kiasan</option>
                            <option value="katakatahikmat" <?php echo ($peribahasa['CategoryID'] == 'katakatahikmat') ? 'selected' : ''; ?>>Kata-kata Hikmat</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="contohAyat<?php echo $peribahasa['PeriID']; ?>" class="form-label">Contoh Ayat</label>
                        <textarea class="form-control" id="contohAyat<?php echo $peribahasa['PeriID']; ?>" name="contohAyat" rows="3" required><?php echo htmlspecialchars($peribahasa['ContohAyat']); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="updatePeribahasa" class="btn btn-primary">Update Peribahasa</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
</body>

</html>