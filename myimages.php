<?php
include 'includes/db.php';
include 'header.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Image upload functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $target_dir = "uploads/";
    $file_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $query = "INSERT INTO images (user_id, file_name, file_path) VALUES ('$user_id', '$file_name', '$target_file')";
        if (mysqli_query($conn, $query)) {
            echo "<div class='alert alert-success'>Image uploaded successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Error uploading the file.</div>";
    }
}

// Delete image functionality
if (isset($_GET['id']) && $_GET['id'] != '') {
    $id = $_GET["id"];

    // SQL delete query
    $sql = "DELETE FROM images WHERE id = ?";

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param('i', $id); // 'i' indicates the parameter is an integer

    // Execute query
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Record deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting record: " . $conn->error . "</div>";
    }
}
?>
<main>
<div class="container my-5">
    <div class="row">
        <!-- Image upload form -->
        <div class="col-sm-4 ">
            <div class="card ">
            <div class="card-header text-center">
                <h2 class="mb-3">Image Upload Form</h2>
</div> <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="imageUploadForm">
                  
                    <div class="mb-3">
                         <input type="file" name="image" id="imageInput" accept="image/jpeg, image/png" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <img id="imagePreview" alt="Image Preview" class="img-fluid" style="display: none; max-width: 50%; margin-top: 15px;">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Upload</button>
                </form>
            </div>
        </div>
        </div>
        <!-- Display uploaded images -->
        <div class="col-md-8">
            <h2 class="mb-3">Uploaded Images</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image Name</th>
                        <th>Image</th>
                        <th>Date</th>
                        <th>Convert To Hex</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, file_name, file_path, uploaded_at FROM images WHERE user_id = $user_id ORDER BY uploaded_at DESC";
                    $resultimg = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($resultimg)) {
                        $id = $row['id'];
                        $file_name = $row['file_name'];
                        $image = $row['file_path'];
                        $date = $row['uploaded_at'];
                    ?>
                        <tr>
                            <td><?php echo $id; ?></td>
                            <td><?php echo $file_name; ?></td>
                            <td><img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($file_name); ?>" style="max-width: 100px; max-height: 100px;"></td>
                            <td><?php echo $date; ?></td>
                            <td>
                                <?php 
                                // Check if the image has been converted
                                $query = "SELECT * FROM hex_codes WHERE image_id = '$id'";
                                $result = mysqli_query($conn, $query);
                                if (mysqli_num_rows($result) > 0) {
                                    echo '<a href="hexcoderesult.php?id=' . $id . '" class="btn btn-sm btn-success" role="button">View Result</a>';
                                } else {
                                    echo '<a href="convert.php?id=' . $id . '" class="btn btn-sm btn-primary" role="button">Convert</a>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="editmyimages.php?id=<?php echo $id; ?>" class="btn btn-sm btn-info" role="button">Edit</a>
                            </td>
                            <td>
                                <a href="myimages.php?id=<?php echo $id; ?>" class="btn btn-sm btn-danger" role="button">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>
<script>
    // Image preview functionality
    document.getElementById('imageInput').addEventListener('change', function(event) {
        const imagePreview = document.getElementById('imagePreview');
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                imagePreview.src = e.target.result; // Set the image source
                imagePreview.style.display = 'block'; // Show the image preview
            };

            reader.readAsDataURL(file); // Read the file as a data URL
        } else {
            imagePreview.style.display = 'none'; // Hide the image preview if no file is selected
        }
    });
</script>

<?php
include 'footer.php';
?>
