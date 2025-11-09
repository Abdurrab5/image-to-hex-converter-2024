<?php
include '../includes/db.php';
include 'header.php';

// User must login to access this page 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_dir = "../library/";
    $file_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;
    $extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($extension, $allowed_extensions)) {
        echo "Error: Only JPG, JPEG, PNG, and GIF files are allowed.";
    } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Use prepared statements to insert data into the database
        $query = $conn->prepare("INSERT INTO library (file_name, file_path, extension) VALUES (?, ?, ?)");
        $query->bind_param("sss", $file_name, $target_file, $extension);

        if ($query->execute()) {
            echo "Image uploaded successfully!";
        } else {
            echo "Database Error: " . $conn->error;
        }
    } else {
        echo "Error uploading the file.";
    }
}

// Handle image deletion
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input

    // SQL delete query
    $sql = "DELETE FROM library WHERE lib_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo "Record deleted successfully.";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>

<div class="row">
    <div class="col-4">
        <div class="regcontainer">
            <span></span>
            <span></span>
            <span></span>
            <form method="POST" enctype="multipart/form-data" id="signupform">
                <h2>Image Upload Form</h2>
                <img id="imagePreview" alt="Image Preview" style="max-width: 50%; height: 100px; margin-top: 15px; display: none;">
                <div class="inputBox">
                    <input type="file" name="image" id="imageInput" accept="image/jpeg, image/png, image/gif" required>
                </div>
                <div class="inputBox">
                    <button type="submit">Upload</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-8">
        <table class="table" id="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image Name</th>
                    <th>Image</th>
                    <th>Extension</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT lib_id, file_name, file_path, extension FROM library";
                $resultimg = $conn->query($sql);

                while ($row = $resultimg->fetch_assoc()) {
                    $id = htmlspecialchars($row['lib_id']);
                    $file_name = htmlspecialchars($row['file_name']);
                    $image = htmlspecialchars($row['file_path']);
                    $extension = htmlspecialchars($row['extension']);
                ?>
                    <tr>
                        <td><?php echo $id; ?></td>
                        <td><?php echo $file_name; ?></td>
                        <td>
                            <img src="<?php echo $image; ?>" alt="<?php echo $file_name; ?>" style="max-width: 100px; max-height: 100px;">
                        </td>
                        <td><?php echo $extension; ?></td>
                        <td>
                            <a href="editlibraryimages.php?id=<?php echo $id; ?>" class="btn btn-sm btn-info" role="button">Edit</a>
                        </td>
                        <td>
                            <a href="uploadimages.php?id=<?php echo $id; ?>" class="btn btn-sm btn-danger" role="button">Delete</a>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
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

<?php include 'footer.php'; ?>
