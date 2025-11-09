<?php
include 'includes/db.php';
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
    <!-- Filter Buttons -->
    <div class="mb-4 text-center">
        <div class="btn-group" role="group" aria-label="Filter Buttons">
            <a href="?filter=all" class="btn btn-sm btn-outline-success <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
            <a href="?filter=gif" class="btn btn-sm btn-outline-primary <?php echo $filter === 'gif' ? 'active' : ''; ?>">GIF Images</a>
            <a href="?filter=images" class="btn btn-sm btn-outline-primary <?php echo $filter === 'images' ? 'active' : ''; ?>">Images</a>
        </div>
    </div>

    <!-- Image Cards -->
    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $file_name = htmlspecialchars($row['file_name']);
                $file_path = htmlspecialchars($row['file_path']);
                $extension = htmlspecialchars($row['extension']);
        ?>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4">
                    <div class="card shadow h-100">
                        <img 
                            src="library/<?php echo $file_path; ?>" 
                            class="card-img-top" 
                            alt="<?php echo $file_name; ?>" 
                            style="height: 200px; object-fit: cover;"
                            onerror="this.src='placeholder.jpg';">
                        <div class="card-body">
                            <h5 class="card-title text-truncate"><?php echo $file_name; ?></h5>
                            <p class="card-text text-muted">File Type: <?php echo strtoupper($extension); ?></p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="<?php echo $extension === 'gif' ? 'librarygifimagesconvert.php' : 'libraryimageconvert.php'; ?>?id=<?php echo $row['lib_id']; ?>" class="btn btn-sm btn-warning">Convert</a>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<div class="col-12 text-center"><p>No images found.</p></div>';
        }
        ?>
    </div>
</main>


<?php include 'footer.php'; ?>
