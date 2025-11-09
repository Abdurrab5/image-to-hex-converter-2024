<?php
include 'includes/db.php';
include 'header.php';

// Initialize variables
$id = $file_name = $image = $date = "";

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch image details if `id` is provided in the URL
if (!empty($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input as an integer

    // Fetch image details from the database
    $sql = "SELECT id, file_name, file_path, uploaded_at
            FROM images
            WHERE user_id = ? AND id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_name = $row['file_name'];
        $image = $row['file_path'];
        $date = $row['uploaded_at'];
    } else {
        echo "<p class='error'>Image not found.</p>";
        exit();
    }
    $stmt->close();
}

// Handle form submission for image update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . uniqid('img_', true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
        $file_tmp = $_FILES["image"]["tmp_name"];
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate file type
        if (in_array($file_type, $allowed_types)) {
            // Delete old image file if it exists
            if (!empty($image) && file_exists($image)) {
                unlink($image);
            }

            // Move new file to the target directory
            if (move_uploaded_file($file_tmp, $target_file)) {
                // Update the database with new image details
                $query = "UPDATE images SET file_name = ?, file_path = ? WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ssii', $file_name, $target_file, $id, $user_id);

                if ($stmt->execute()) {
                    echo "<p class='success'>Image updated successfully!</p>";
                    header("Location: myimages.php");
                    exit();
                } else {
                    echo "<p class='error'>Error updating image: " . $stmt->error . "</p>";
                }
                $stmt->close();
            } else {
                echo "<p class='error'>Error uploading the file.</p>";
            }
        } else {
            echo "<p class='error'>Invalid file type. Allowed types: JPG, JPEG, PNG, GIF.</p>";
        }
    } else {
        echo "<p class='error'>Error with the uploaded file. Please try again.</p>";
    }
}

// Handle delete request
if (!empty($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Fetch the image path for deletion
    $sql = "SELECT file_path FROM images WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $delete_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_to_delete = $row['file_path'];

        // Delete the image file if it exists
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }

        // Delete the record from the database
        $delete_sql = "DELETE FROM images WHERE id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('ii', $delete_id, $user_id);

        if ($delete_stmt->execute()) {
            echo "<p class='success'>Image deleted successfully!</p>";
            header("Location: myimages.php");
            exit();
        } else {
            echo "<p class='error'>Error deleting image: " . $delete_stmt->error . "</p>";
        }
        $delete_stmt->close();
    } else {
        echo "<p class='error'>Image not found.</p>";
    }
    $stmt->close();
}
?>

<main>
    <div class="regcontainer">
        <span></span>
        <span></span>
        <span></span>
        <form method="POST" enctype="multipart/form-data" id="signupform">
            <h2>Image Edit Form</h2>

            <!-- Display current image -->
            <div class="inputBox">
                <img id="previewImage" 
                     src="<?php echo htmlspecialchars($image); ?>" 
                     alt="<?php echo htmlspecialchars($file_name); ?>" 
                     style="max-width: 100px; max-height: 100px;">
            </div>

            <!-- File upload input -->
            <div class="inputBox">
                <input type="file" name="image" id="imageInput" required>
            </div>

            <!-- Submit button -->
            <div class="inputBox">
                <button type="submit">Update Image</button>
            </div>
        </form>
    </div>

    <!-- JavaScript to handle image preview -->
    <script>
        document.getElementById('imageInput').addEventListener('change', function(event) {
            const file = event.target.files[0]; // Get the selected file
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Set the image source to the file content
                    document.getElementById('previewImage').src = e.target.result;
                };
                reader.readAsDataURL(file); // Read the file as a data URL
            }
        });
    </script>
</main>


<?php include 'footer.php'; ?>
