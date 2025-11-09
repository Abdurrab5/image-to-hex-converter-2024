<?php
include '../includes/db.php';
include 'header.php';

// Determine filter based on button click
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build SQL query based on filter
switch ($filter) {
    case 'gif':
        $sql = "SELECT * FROM library WHERE extension = 'gif'";
        break;
    case 'images':
        $sql = "SELECT * FROM library WHERE extension IN ('jpg', 'jpeg', 'png')";
        break;
    default:
        $sql = "SELECT * FROM library";
        break;
}

$result = $conn->query($sql);
?>

<main class="container">
    <!-- Buttons for filtering -->
    <div class="mb-3">
        <a href="?filter=all" class="btn btn-sm btn-success">ALL</a>
        <a href="?filter=gif" class="btn btn-sm btn-primary">Gif Images</a>
        <a href="?filter=images" class="btn btn-sm btn-primary">Images</a>
    </div>

    <!-- Display images in card format -->
    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $file_name = htmlspecialchars($row['file_name']);
                $file_path = htmlspecialchars($row['file_path']);
                $extension = htmlspecialchars($row['extension']);
        ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $file_path; ?>" class="card-img-top" alt="<?php echo $file_name; ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $file_name; ?></h5>
                            <p class="card-text">File Type: <?php echo strtoupper($extension); ?></p>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<p class="text-center">No images found.</p>';
        }
        ?>
    </div>
</main>

<?php include 'footer.php'; ?>
