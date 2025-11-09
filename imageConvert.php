<?php
include 'includes/db.php';
include 'header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handling uploaded image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        $originalFileName = basename($_FILES['image']['name']);
        $targetFile = $targetDir . uniqid() . "_" . $originalFileName;

        $allowedTypes = ['image/jpeg', 'image/png'];
        $mimeType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            die("Unsupported file type. Only JPEG, PNG, and GIF are allowed.");
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;

            // Handle optional fields
            $resizeWidth = isset($_POST['resize_width']) ? intval($_POST['resize_width']) : null;
            $resizeHeight = isset($_POST['resize_height']) ? intval($_POST['resize_height']) : null;
            $invertColors = isset($_POST['invert_colors']) && $_POST['invert_colors'] === 'on';
            $targetLibrary = isset($_POST['target_library']) ? $_POST['target_library'] : 'default';

            // Process image
            $processedImage = processImage($imagePath, $resizeWidth, $resizeHeight, $invertColors);

            // Convert to hex
            $hexCode = convertToHex($processedImage, $targetLibrary);

            // Get image dimensions after processing
            $finalWidth = imagesx($processedImage);
            $finalHeight = imagesy($processedImage);

            // Save details and hex code to a document file
            $documentContent = "Image Details:\n";
            $documentContent .= "Original Name: {$originalFileName}\n";
            $documentContent .= "Original Size: {$_FILES['image']['size']} bytes\n";
            $documentContent .= "Selected Resize: {$resizeWidth}x{$resizeHeight}\n";
            $documentContent .= "Final Dimensions: {$finalWidth}x{$finalHeight}\n";
            $documentContent .= "Invert Colors: " . ($invertColors ? "Yes" : "No") . "\n";
            $documentContent .= "Target Library: {$targetLibrary}\n";
            $documentContent .= "\nHex Code:\n{$hexCode}";

            $resultFileName = $targetDir . uniqid() . "_result.txt";
            file_put_contents($resultFileName, $documentContent);

       // Output result (hex code display and download link)
echo "<div class='container mt-5'>";
echo "<div class='row justify-content-center'>";
echo "<div class='col-12 col-md-8 col-lg-6'>";
echo "<div class='card' style='background-color: #343a40; color: #fff; border-radius: 10px; padding: 20px;'>";

// Title and Image Details Section
echo "<h1 class='text-center mb-4'>Image Details</h1>";
echo "<h3>Original Name: <span class='text-warning'>{$originalFileName}</span></h3>";
echo "<h3>Original Size: <span class='text-warning'>{$_FILES['image']['size']} bytes</span></h3>";
echo "<h3>Selected Resize: <span class='text-warning'>{$resizeWidth}x{$resizeHeight}</span></h3>";
echo "<h3>Final Dimensions: <span class='text-warning'>{$finalWidth}x{$finalHeight}</span></h3>";
echo "<h3>Invert Colors: <span class='text-warning'>" . ($invertColors ? 'Yes' : 'No') . "</span></h3>";
echo "<h3>Target Library: <span class='text-warning'>{$targetLibrary}</span></h3>";

// Hex Conversion Section
echo "<h3 class='mt-4'>Hex Conversion Result</h3>";
echo "<textarea class='form-control' style='height: 200px;' readonly>" . htmlspecialchars($hexCode) . "</textarea>";

// Download Link
echo "<div class='text-center mt-4'>";
echo "<a href='{$resultFileName}' class='btn btn-success' download>Download Result File</a>";
echo "</div>";

echo "</div>"; // Card
echo "</div>"; // Column
echo "</div>"; // Row
echo "</div>"; // Container

        } else {
            die("Error uploading the image.");
        }
    }
}
// Image processing function
function processImage($imagePath, $resizeWidth, $resizeHeight, $invertColors)
{
    $imageInfo = getimagesize($imagePath);
    $imageType = $imageInfo[2];

    // Create image resource
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

    // Resize image if dimensions provided
    if ($resizeWidth && $resizeHeight) {
        $resizedImage = imagecreatetruecolor($resizeWidth, $resizeHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $resizeWidth, $resizeHeight, imagesx($image), imagesy($image));
        imagedestroy($image);
        $image = $resizedImage;
    }

    // Invert colors if requested
    if ($invertColors) {
        imagefilter($image, IMG_FILTER_NEGATE);  // Invert colors
    }

    return $image;
}


// Convert image to hex function
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

<main >
 
        <form method="POST" enctype="multipart/form-data" id="imageForm">
             <h1>Image Convert Form</h1>
            <label for="image" class="inputBox">Upload Image:(jpg,png)</label>
            <input type="file" name="image" id="image" required accept="image/jpeg, image/png"><br><br>

            <div id="imagePreviewContainer" style="display: none;">
                <h4>Original Image Preview:</h4>
                <img id="imagePreview" src="" alt="Preview" style="max-width: 100%; border: 1px solid #ddd;"><br>
                <span id="imageDimensions"></span>
            </div>



            <label for="resize_width">Resize Width:</label>
            <input type="number" name="resize_width" id="resize_width" placeholder="e.g., 128"><br><br>

            <label for="resize_height">Resize Height:</label>
            <input type="number" name="resize_height" id="resize_height" placeholder="e.g., 64"><br><br>

            <div id="invertedImagePreviewContainer" style="display: none;">
                <h4>Inverted Image Preview:</h4>
                <img id="invertedImagePreview" src="" alt="Inverted Preview" style="max-width: 100%; border: 1px solid #ddd;">
            </div><br>

            <label for="invert_colors">Invert Colors:</label>
            <button type="button" id="invertButton">Invert</button><br><br>

            <label for="invert_colors">Invert Colors:</label>
            <input type="checkbox" name="invert_colors" id="invert_colors"><br><br>

            <label for="target_library">Target Library:</label>
            <select name="target_library" id="target_library">
                <option value="default">Default</option>
                <option value="adafruit">Adafruit GFX Library</option>
                <option value="u8g2">U8g2 Library</option>
                <option value="tft_espi">TFT_eSPI Library</option>
                <option value="liquidcrystal">liquid Crystal Display Library</option>
            </select><br><br>

            <button type="submit">Convert to Hex</button>
        </form>
 
</main>

<script>
  const imageInput = document.getElementById("image");
const imagePreview = document.getElementById("imagePreview");
const imagePreviewContainer = document.getElementById("imagePreviewContainer");
const imageDimensions = document.getElementById("imageDimensions");
const invertButton = document.getElementById("invertButton");
const invertedImagePreviewContainer = document.getElementById("invertedImagePreviewContainer");
const invertedImagePreview = document.getElementById("invertedImagePreview");
const resizeWidthInput = document.getElementById("resize_width");
const resizeHeightInput = document.getElementById("resize_height");

let originalImageSrc = ""; // To store original image source

// Handle image upload and preview
imageInput.addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            originalImageSrc = e.target.result; // Store original image src
            imagePreview.src = originalImageSrc;

            const img = new Image();
            img.onload = function () {
                imagePreviewContainer.style.display = "block";
                imageDimensions.textContent = `Original Dimensions: ${img.width} x ${img.height}`;
            };
            img.src = originalImageSrc;
        };

        reader.readAsDataURL(file);
    }
});

// Handle "Invert" button click and toggle checkbox
invertButton.addEventListener("click", function () {
    if (!originalImageSrc) {
        alert("Please upload an image first.");
        return;
    }

    // Toggle invert checkbox
    const invertCheckbox = document.getElementById("invert_colors");
    invertCheckbox.checked = !invertCheckbox.checked;

    // Optionally show the preview of the inverted image
    createInvertedPreview(originalImageSrc);
});

// Create an inverted image preview
function createInvertedPreview(imageSrc) {
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    const img = new Image();

    img.onload = function () {
        canvas.width = img.width;
        canvas.height = img.height;
        context.drawImage(img, 0, 0);

        // Get image data and invert colors
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;

        for (let i = 0; i < data.length; i += 4) {
            data[i] = 255 - data[i]; // Red
            data[i + 1] = 255 - data[i + 1]; // Green
            data[i + 2] = 255 - data[i + 2]; // Blue
        }

        context.putImageData(imageData, 0, 0);

        // Set the inverted image preview
        invertedImagePreviewContainer.style.display = "block";
        invertedImagePreview.src = canvas.toDataURL();
    };

    img.src = imageSrc;
}

// Handle resizing of images
function resizeImage(imageSrc, width, height, targetElement) {
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    const img = new Image();

    img.onload = function () {
        canvas.width = width;
        canvas.height = height;
        context.drawImage(img, 0, 0, width, height);
        targetElement.src = canvas.toDataURL();
    };

    img.src = imageSrc;
}

resizeWidthInput.addEventListener("input", function () {
    const width = parseInt(this.value);
    const height = parseInt(resizeHeightInput.value);
    if (width > 0 && height > 0) {
        resizeImage(originalImageSrc, width, height, imagePreview);
    }
});

resizeHeightInput.addEventListener("input", function () {
    const width = parseInt(resizeWidthInput.value);
    const height = parseInt(this.value);
    if (width > 0 && height > 0) {
        resizeImage(originalImageSrc, width, height, imagePreview);
    }
});
// Create an inverted image preview with resizing option
function createInvertedPreview(imageSrc, width, height) {
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    const img = new Image();

    img.onload = function() {
        // Resize the image to the specified width and height
        canvas.width = width || img.width;
        canvas.height = height || img.height;
        context.drawImage(img, 0, 0, canvas.width, canvas.height);

        // Get image data and invert colors
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;

        for (let i = 0; i < data.length; i += 4) {
            data[i] = 255 - data[i]; // Red
            data[i + 1] = 255 - data[i + 1]; // Green
            data[i + 2] = 255 - data[i + 2]; // Blue
        }

        context.putImageData(imageData, 0, 0);

        // Set the inverted image preview
        invertedImagePreviewContainer.style.display = "block";
        invertedImagePreview.src = canvas.toDataURL();
    };

    img.src = imageSrc;
}

// Handle resizing of images
resizeWidthInput.addEventListener("input", function() {
    const width = parseInt(this.value);
    const height = parseInt(resizeHeightInput.value);
    if (width > 0 && height > 0) {
        // Resize original image and inverted image
        resizeImage(originalImageSrc, width, height, imagePreview);
        createInvertedPreview(originalImageSrc, width, height); // Add resizing for inverted image
    }
});

resizeHeightInput.addEventListener("input", function() {
    const width = parseInt(resizeWidthInput.value);
    const height = parseInt(this.value);
    if (width > 0 && height > 0) {
        // Resize original image and inverted image
        resizeImage(originalImageSrc, width, height, imagePreview);
        createInvertedPreview(originalImageSrc, width, height); // Add resizing for inverted image
    }
});

</script>

 