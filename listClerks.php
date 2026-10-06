<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'DBConn.php';

$result = $conn->query("SELECT ClerkID, ClerkName FROM clerk");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Clerks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-3">List of Clerks</h2>
        <button class="btn btn-primary mb-3" onclick="window.print()">Print</button>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Clerk ID</th>
                    <th>Name</th>
                    <th>User Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ClerkID']); ?></td>
                    <td><?php echo htmlspecialchars($row['ClerkName']); ?></td>
                    <td>CLERK</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
