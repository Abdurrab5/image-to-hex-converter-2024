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
$file_name = "";
$file_path = "";
$date = "";

$user_id = $_SESSION['user_id'];

if (!empty($_GET['id'])) {
    $image_id = intval($_GET['id']); // Sanitize input

    // Fetch image details from database
    $sql = "SELECT id, user_id, file_name, file_path, uploaded_at FROM images WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $image_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_name = $row['file_name'];
        $file_path = $row['file_path'];
        $date = $row['uploaded_at'];
        $user_id = $row['user_id'];
    } else {
        echo "<p class='error'>Image not found.</p>";
        exit();
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_id = intval($_POST['image_id']);
    $width = intval($_POST['resize_width']);
    $height = intval($_POST['resize_height']);
    $invert_color = isset($_POST['invert_colors']) ? 1 : 0;
    $library = $_POST['target_library'];

    // Process image and generate hex code
    $image = processImage($file_path, $width, $height, $invert_color);
    $hex_code = convertToHex($image, $library);

    // Insert hex code into database
    $query = "INSERT INTO hex_codes (image_id, width, height, invert_color, library, hex_code, user_id) 
              VALUES (?, ?, ?, ?, ?, ?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiiisss', $image_id, $width, $height, $invert_color, $library, $hex_code,$user_id);

    if ($stmt->execute()) {
        echo "<p class='success'>Hex code generated successfully!</p>";
        header("Location: myimages.php");
    } else {
        echo "<p class='error'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>
<main>
    <form method="POST" enctype="multipart/form-data" id="imageForm">
        <h1>Image Conversion Form</h1>

        <!-- Image Details -->
        <label for="image_id">Image ID:</label>
        <input type="text" name="image_id" id="image_id" value="<?php echo $image_id; ?>" readonly><br><br>

        <label>Original Image Preview:</label><br>
        <img src="<?php echo $file_path; ?>" alt="Image Preview" style="max-width: 100%; border: 1px solid #ddd;"><br><br>

        <!-- Resize Width & Height -->
        <label for="resize_width">Resize Width:</label>
        <input type="number" name="resize_width" id="resize_width" placeholder="e.g., 128"><br><br>

        <label for="resize_height">Resize Height:</label>
        <input type="number" name="resize_height" id="resize_height" placeholder="e.g., 64"><br><br>

        <!-- Invert Colors -->
        <label for="invert_colors">Invert Colors:</label>
        <input type="checkbox" name="invert_colors" id="invert_colors"><br><br>

        <!-- Target Library -->
        <label for="target_library">Target Library:</label>
            <select name="target_library" id="target_library">
                <option value="default">Default</option>
                <option value="adafruit">Adafruit GFX Library</option>
                <option value="u8g2">U8g2 Library</option>
                <option value="tft_espi">TFT_eSPI Library</option>
                <option value="liquidcrystal">liquid Crystal Display Library</option>
            </select><br><br>

        <!-- Submit Button -->
        <button type="submit" name="convert">Convert to Hex</button>
    </form>
</main>

<?php
// Process Image Function
function processImage($imagePath, $resizeWidth, $resizeHeight, $invertColors)
{
    $imageInfo = getimagesize($imagePath);
    $imageType = $imageInfo[2];

    // Load image based on type
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($imagePath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($imagePath);
            break;
        default:
            die("Unsupported image type.");
    }

    // Resize if needed
    if ($resizeWidth > 0 && $resizeHeight > 0) {
        $resizedImage = imagecreatetruecolor($resizeWidth, $resizeHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $resizeWidth, $resizeHeight, imagesx($image), imagesy($image));
        imagedestroy($image);
        $image = $resizedImage;
    }

    // Invert colors if requested
    if ($invertColors) {
        imagefilter($image, IMG_FILTER_NEGATE);
    }

    return $image;
}

// Convert Image to Hex
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
                    $hexOutput .= sprintf("%02X%02X%02X ", $r, $g, $b);
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
                    $hexOutput .= sprintf("%02X%02X%02X ", $r, $g, $b);
                    break;
            }
        }
        $hexOutput .= "\n";
    }

    return $hexOutput;
}
?>

<?php
include 'footer.php';
?>