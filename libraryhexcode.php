<?php
include 'includes/db.php';
include 'header.php';

// User must be logged in to access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle delete request
if (isset($_GET['id']) && $_GET['id'] != '') {
    $id = intval($_GET["id"]); // Sanitize input

    // SQL delete query
    $sql = "DELETE FROM library_hex_code WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id); // 'i' indicates the parameter is an integer

    // Execute query
    if ($stmt->execute()) {
        echo "<div class='alert alert-success text-center'>Record deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error deleting record: " . $conn->error . "</div>";
    }
    $stmt->close();
}
?>

<main>
    <div class="container mt-4">
        <div class="card shadow p-4">
            <h1 class="text-center text-primary mb-4">My Hex Codes</h1>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Library Image</th>
                            <th>Width</th>
                            <th>Height</th>
                            <th>Color Invert</th>
                            <th>Library</th>
                            <th>Hex Codes</th>
                            <th>Actions</th>
                            <th>Download File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch user-specific data
                        $sql = "SELECT id, lib_id, width, height, invert_color, library, hex_code
                                FROM library_hex_code
                                WHERE user_id = $user_id";

                        $resultimg = mysqli_query($conn, $sql);
                        if ($resultimg->num_rows > 0) {
                            while ($row = mysqli_fetch_assoc($resultimg)) {
                                $id = $row['id'];
                                $image_id = $row['lib_id'];
                                $width = $row['width'];
                                $height = $row['height']; // Fixed typo
                                $invert_color = $row['invert_color'];
                                $library = $row['library'];
                                $hex_code = $row['hex_code'];
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($id); ?></td>
                                    <td><?php echo htmlspecialchars($image_id); ?></td>
                                    <td><?php echo htmlspecialchars($width); ?> px</td>
                                    <td><?php echo htmlspecialchars($height); ?> px</td>
                                    <td><?php echo $invert_color == 1 ? "Inverted" : "Original"; ?></td>
                                    <td><?php echo htmlspecialchars($library); ?></td>
                                    <td style="max-width: 300px; white-space: nowrap; overflow: auto;"><?php echo htmlspecialchars($hex_code); ?></td>
                                    <td>
                                        <a href="libraryhexcode.php?id=<?php echo $id; ?>" class="btn btn-sm btn-danger" role="button" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                    </td>
                                    <td>
                                    <a href="generate_library_binary_File.php?action=download&binid=<?php echo $id; ?>" class="btn btn-sm btn-success" role="button">Binary Code</a>
                                    <a href="generate_library_binary_File.php?action=download&cid=<?php echo $id; ?>" class="btn btn-sm btn-primary" role="button">C Array Code</a>
                                     </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
include 'footer.php';
?>
