<?php
include 'includes/db.php';
include 'header.php';

// Initialize variables
$image_id = "";
$hex_code = "";
$width = 0;
$height = 0;
$invert_color = 0;
$library = "";
$date = "";

$user_id = $_SESSION['user_id'] ?? null;

// Check for session and image ID
if (!$user_id) {
    echo "<div class='container mt-4'><p class='alert alert-danger text-center'>Unauthorized access.</p></div>";
    exit;
}

if (!empty($_GET['id'])) {
    $image_id = intval($_GET['id']); // Sanitize input

    // Fetch image details from database
    $sql = "SELECT * FROM hex_codes WHERE image_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $image_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $width = $row['width'];
        $height = $row['height']; // Fixed typo
        $invert_color = $row['invert_color']; 
        $library = $row['library'];
        $hex_code = $row['hex_code'];
        $date = $row['date'] ?? '';
        ?>
        <main>
            <div class="container mt-4">
                <div class="card shadow p-4">
                    <h1 class="text-center text-primary mb-4">Image Details</h1>
                    <div class="row">
                        <!-- Image Information -->
                        <div class="col-md-6 mb-3">
                            <h5><strong>Width:</strong> <?php echo htmlspecialchars($width); ?> px</h5>
                            <h5><strong>Height:</strong> <?php echo htmlspecialchars($height); ?> px</h5>
                            <h5>
                                <strong>Color:</strong> 
                                <?php echo $invert_color == 1 ? "Inverted" : "Original"; ?>
                            </h5>
                        </div>
                        <!-- Hex Code and Library -->
                        <div class="col-md-6">
                            <h5><strong>Library:</strong> <?php echo htmlspecialchars($library); ?></h5>
                            <h5><strong>Date Uploaded:</strong> <?php echo htmlspecialchars($date); ?></h5>
                            <h5>
                                <strong>Hex Code:</strong>
                                <span class="text-monospace d-block bg-light p-2 rounded">
                                    <?php echo nl2br(htmlspecialchars($hex_code)); ?>
                                </span>
                            </h5>
                        </div>
                    </div>
                    <!-- Button for Additional Actions -->
                    <div class="row mt-4">
                        <div class="col text-center">
                            <a href="image_library.php" class="btn btn-primary">Back to Library</a>
                            <a href="download_hex.php?id=<?php echo $image_id; ?>" class="btn btn-success">Download Hex Code</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php
    } else {
        echo "<div class='container mt-4'><p class='alert alert-danger text-center'>Image not found.</p></div>";
    }
    $stmt->close();
} else {
    echo "<div class='container mt-4'><p class='alert alert-warning text-center'>No image ID provided.</p></div>";
}
?>

<?php include 'footer.php'; ?>
