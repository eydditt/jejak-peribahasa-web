<?php
session_start();

// Like a guardian at the gates of ancient wisdom
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'DBConn.php';

// Gather the scrolls of user knowledge with updated fields
$result = $conn->query("SELECT UserEmail, UserName, UserRole FROM user");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        /* Print-specific styles */
        @media print {
            .btn-primary {
                display: none;
            }
            .container {
                width: 100%;
                max-width: none;
            }
            .table {
                border: 1px solid #dee2e6;
            }
            .table td, .table th {
                border: 1px solid #dee2e6;
            }
        }
        
        /* General styles */
        .table th {
            background-color: #f8f9fa;
        }
        .container {
            padding-top: 20px;
            padding-bottom: 40px;
        }
        .btn-primary {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-3">List of Users</h2>
        <button class="btn btn-primary mb-3" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Email</th>  <!-- Changed from User IC -->
                    <th>Name</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['UserEmail']); ?></td>
                    <td><?php echo htmlspecialchars($row['UserName']); ?></td>
                    <td>USER</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>