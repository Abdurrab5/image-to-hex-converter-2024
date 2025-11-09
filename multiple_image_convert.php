<?php
include 'header.php';
include 'includes/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handling uploaded images
    if (isset($_FILES['image']) && $_FILES['image']['error'][0] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        $resultFiles = [];
        $allowedTypes = ['image/jpeg', 'image/png'];

        foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
            $originalFileName = basename($_FILES['image']['name'][$key]);
            $uniqueName = uniqid() . "_" . $originalFileName;
            $targetFile = $targetDir . $uniqueName;
            $mimeType = mime_content_type($tmpName);

            if (!in_array($mimeType, $allowedTypes)) {
                die("Unsupported file type. Only JPEG, PNG are allowed.");
            }

            if (move_uploaded_file($tmpName, $targetFile)) {
                $resizeWidth = isset($_POST['resize_width']) ? intval($_POST['resize_width']) : null;
                $resizeHeight = isset($_POST['resize_height']) ? intval($_POST['resize_height']) : null;
                $invertColors = isset($_POST['invert_colors']) && $_POST['invert_colors'] === 'on';
                $targetLibrary = $_POST['target_library'] ?? 'default';

                // Process image
                $processedImage = processImage($targetFile, $resizeWidth, $resizeHeight, $invertColors);

                // Convert to hex
                $hexCode = convertToHex($processedImage, $targetLibrary);

                // Get final dimensions
                $finalWidth = imagesx($processedImage);
                $finalHeight = imagesy($processedImage);
                $invertColorsText = $invertColors ? 'Yes' : 'No';
                // Save result to a file
                $documentContent = <<<EOD


Image Details:
Original Name: {$originalFileName}
Original Size: {$_FILES['image']['size'][$key]} bytes
Selected Resize: {$resizeWidth}x{$resizeHeight}
Final Dimensions: {$finalWidth}x{$finalHeight}
Invert Colors: {$invertColorsText}
Target Library: {$targetLibrary}

Hex Code:
{$hexCode}
EOD;

                $resultFileName = $targetDir . uniqid() . "_result.txt";
                file_put_contents($resultFileName, $documentContent);

                // Store result file name
                $resultFiles[] = $resultFileName;

                
            } else {
                die("Error uploading the image.");
            }
        }

        // Display results
        echo "<main style='background-color: #343a40; color: #fff;'>";
        echo "<h1>Processed Image Results</h1>";
        foreach ($resultFiles as $file) {
            echo "<p><a href='{$file}' download>Download Result File</a></p>";
        }
        echo "</main>";
    }
}

function processImage($imagePath, $resizeWidth, $resizeHeight, $invertColors) {
    $imageInfo = getimagesize($imagePath);
    $imageType = $imageInfo[2];

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

    if ($resizeWidth && $resizeHeight) {
        $resizedImage = imagecreatetruecolor($resizeWidth, $resizeHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $resizeWidth, $resizeHeight, imagesx($image), imagesy($image));
        imagedestroy($image);
        $image = $resizedImage;
    }

    if ($invertColors) {
        imagefilter($image, IMG_FILTER_NEGATE);
    }

    return $image;
}

function convertToHex($image, $targetLibrary) {
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
    <div class="container my-5">
       
        <form method="POST" enctype="multipart/form-data" id="imageForm" class="row g-3">
        <div class="card-header text-center">
            <h3>Image to Hex Converter</h3>
        </div>
       
            <div class="col-md-12">
                <label for="image" class="form-label">Upload Images:</label>
                <input type="file" name="image[]" id="image" class="form-control" required accept="image/jpeg, image/png" multiple>
                <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                    <h5>Image Previews:</h5>
                    <div id="imagePreviews" class="d-flex flex-wrap gap-3"></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <label for="resize_width" class="form-label">Resize Width:</label>
                <input type="number" name="resize_width" id="resize_width" class="form-control" placeholder="e.g., 128">
            </div>
            
            <div class="col-md-6">
                <label for="resize_height" class="form-label">Resize Height:</label>
                <input type="number" name="resize_height" id="resize_height" class="form-control" placeholder="e.g., 64">
            </div>

            <div class="col-md-12">
                <label for="invert_colors" class="form-label">Invert Colors:</label>
                <div class="form-check">
                    <input type="checkbox" name="invert_colors" id="invert_colors" class="form-check-input">
                    <label class="form-check-label" for="invert_colors">Enable Color Inversion</label>
                </div>
            </div>

            <div class="col-md-12">
            <label for="target_library">Target Library:</label>
            <select name="target_library" id="target_library">
                <option value="default">Default</option>
                <option value="adafruit">Adafruit GFX Library</option>
                <option value="u8g2">U8g2 Library</option>
                <option value="tft_espi">TFT_eSPI Library</option>
                <option value="liquidcrystal">liquid Crystal Display Library</option>
            </select><br><br>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary w-100">Convert to Hex</button>
            </div>
        </form>

        <div id="invertedImagePreviewContainer" class="mt-4" style="display: none;">
            <h5>Inverted Image Preview:</h5>
            <img id="invertedImagePreview" src="" alt="Inverted Preview" class="img-fluid border">
        </div>
    </div>

    <script>
        const imageInput = document.getElementById("image");
        const imagePreviewContainer = document.getElementById("imagePreviewContainer");
        const imagePreviews = document.getElementById("imagePreviews");

        // Handle image upload and preview
        imageInput.addEventListener("change", function () {
            const files = Array.from(this.files);

            if (files.length > 0) {
                imagePreviewContainer.style.display = "block";
                imagePreviews.innerHTML = ''; // Clear previous previews

                files.forEach((file) => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = new Image();
                        img.onload = function () {
                            const originalDimensions = `${img.width} x ${img.height}`;

                            // Create original image preview
                            const wrapper = createImagePreview(img, originalDimensions);
                            imagePreviews.appendChild(wrapper);
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        // Create a preview wrapper with dimensions
        function createImagePreview(img, dimensions) {
            const wrapper = document.createElement("div");
            wrapper.classList.add("previewWrapper", "text-center");
            wrapper.style.width = "120px";
            wrapper.appendChild(img);
            const text = document.createElement("p");
            text.textContent = `Dimensions: ${dimensions}`;
            wrapper.appendChild(text);
            return wrapper;
        }
    </script>
 

</main>
