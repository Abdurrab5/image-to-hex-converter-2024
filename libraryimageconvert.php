<?php
include 'includes/db.php';
include 'header.php';

// Check if the user is logged in

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "<p class='error'>User not logged in. Please log in to continue.</p>";
    exit();
}

// Initialize variables
$image_id = $hex_code = $file_name = $file_path = "";
$width = $height = $invert_color = 0;
$library = "default";

// Fetch image details if `id` is provided
if (!empty($_GET['id'])) {
    $lib_id = intval($_GET['id']); // Sanitize input

    $sql = "SELECT lib_id, file_name, file_path, extension FROM library WHERE lib_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $lib_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_id = $row['lib_id'];
        $file_name = $row['file_name'];
        $file_path = 'library/' . $row['file_path']; // Adjust for library path
    } else {
        echo "<p class='error'>Image not found.</p>";
        exit();
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $image_id = intval($_POST['image_id']);
    $width = filter_input(INPUT_POST, 'resize_width', FILTER_VALIDATE_INT);
    $height = filter_input(INPUT_POST, 'resize_height', FILTER_VALIDATE_INT);
    $invert_color = isset($_POST['invert_colors']) ? 1 : 0;
    $library = htmlspecialchars($_POST['target_library'], ENT_QUOTES, 'UTF-8');

    // Validate inputs
    if ($width <= 0 || $height <= 0) {
        echo "<p class='error'>Invalid dimensions for resizing.</p>";
        exit();
    }

    // Process image and generate hex code
    try {
        $image = processImage($file_path, $width, $height, $invert_color);
        $hex_code = convertToHex($image, $library);

        // Insert the generated hex code into the database
        $query = "INSERT INTO library_hex_code (lib_id, width, height, invert_color, library, hex_code, user_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiiissi', $image_id, $width, $height, $invert_color, $library, $hex_code, $user_id);

        if ($stmt->execute()) {
            echo "<p class='success'>Hex code generated and saved successfully!</p>";
            header("Location: myimages.php");
            exit();
        } else {
            echo "<p class='error'>Database error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
}
?>
<main>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Image Conversion Form</h1>
        <form method="POST" enctype="multipart/form-data" id="imageForm" class="p-4 border rounded bg-light">
            <!-- Image ID -->
            <div class="form-group mb-3">
                <label for="image_id">Image ID</label>
                <input type="text" name="image_id" id="image_id" class="form-control" 
                       value="<?php echo htmlspecialchars($image_id); ?>" readonly>
            </div>

            <!-- Image Preview -->
            <div id="imagePreviewContainer" class="mb-4 text-center">
                <h4>Original Image Preview</h4>
                <img id="imagePreview" src="<?php echo htmlspecialchars($file_path); ?>" alt="Preview" 
                     class="img-fluid border">
                <p id="imageDimensions"></p>
            </div>

            <!-- Resizing Options -->
            <div class="row">
                <div class="col-md-6">
                    <label for="resize_width">Resize Width</label>
                    <input type="number" name="resize_width" id="resize_width" class="form-control" 
                           placeholder="e.g., 128" required>
                </div>
                <div class="col-md-6">
                    <label for="resize_height">Resize Height</label>
                    <input type="number" name="resize_height" id="resize_height" class="form-control" 
                           placeholder="e.g., 64" required>
                </div>
            </div>

            <!-- Invert Colors -->
            <div class="form-group form-check mt-3">
                <input type="checkbox" name="invert_colors" id="invert_colors" class="form-check-input">
                <label for="invert_colors" class="form-check-label">Invert Colors</label>
            </div>

            <!-- Target Library -->
            <div class="form-group mt-3">
            <label for="target_library">Target Library:</label>
            <select name="target_library" id="target_library">
                <option value="default">Default</option>
                <option value="adafruit">Adafruit GFX Library</option>
                <option value="u8g2">U8g2 Library</option>
                <option value="tft_espi">TFT_eSPI Library</option>
                <option value="liquidcrystal">liquid Crystal Display Library</option>
            </select><br><br>
            </div>

            <!-- Submit Button -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Convert to Hex</button>
            </div>
        </form>
    </div>
</main>

<?php
/**
 * Process Image
 */
function processImage($imagePath, $resizeWidth, $resizeHeight, $invertColors)
{
    $imageInfo = getimagesize($imagePath);
    if (!$imageInfo) {
        throw new Exception("Invalid image file.");
    }

    // Load image
    $imageType = $imageInfo[2];
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($imagePath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($imagePath);
            break;
        default:
            throw new Exception("Unsupported image type.");
    }

    // Resize image
    $resizedImage = imagecreatetruecolor($resizeWidth, $resizeHeight);
    imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $resizeWidth, $resizeHeight, imagesx($image), imagesy($image));
    imagedestroy($image);

    // Invert colors
    if ($invertColors) {
        imagefilter($resizedImage, IMG_FILTER_NEGATE);
    }

    return $resizedImage;
}

/**
 * Convert Image to Hex
 */
function convertToHex($image, $targetLibrary)
{
    $width = imagesx($image);
    $height = imagesy($image);
    $hexOutput = "";

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            switch ($targetLibrary) {
                case 'adafruit':
                    $hexOutput .= sprintf("0x%02X%02X%02X, ", $r, $g, $b);
                    break;
                case 'u8g2':
                    $hexOutput .= sprintf("%02X%02X%02X", $r, $g, $b);
                    break;
                 case 'tft_espi':
                        // Add specific formatting for TFT_eSPI Library
                        $hexOutput .= sprintf("0x%02X%02X%02X, ", $r, $g, $b);  // Example formatting
                        break;
                 case 'liquidcrystal':
                        // Add specific formatting for LiquidCrystal Library
                        $hexOutput .= sprintf("0x%02X%02X%02X", $r, $g, $b);  // Adjust for LiquidCrystal
                        break;
                    
                default:
                    $hexOutput .= sprintf("%02X%02X%02X", $r, $g, $b);
                    break;
            }
        }
        $hexOutput .= "\n";
    }

    return $hexOutput;
}
?>
