<?php
include 'includes/db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}
$user_id = $_SESSION['user_id'];
echo $user_id;

// Increase execution and memory limits
set_time_limit(300); 
ini_set('memory_limit', '512M');

// Convert image to hex
function imageToHex($imagePath) {
    if (!file_exists($imagePath)) {
        throw new Exception("File not found: $imagePath");
    }
    $image = file_get_contents($imagePath);
    return bin2hex($image);
}

// Extract GIF frames using GD
function extractGifFrames($gifPath, $outputFolder, $skipFrames = 10, $maxFrames = 100) {
    if (!file_exists($gifPath)) {
        throw new Exception("GIF file not found: $gifPath");
    }

    if (!is_dir($outputFolder)) {
        if (!mkdir($outputFolder, 0777, true) && !is_dir($outputFolder)) {
            throw new Exception("Failed to create output directory: $outputFolder");
        }
    }

    try {
        $frames = [];
        $frameIndex = 0;

        // Open GIF file
        $gif = imagecreatefromgif($gifPath);
        if (!$gif) {
            throw new Exception("Failed to open GIF file: $gifPath");
        }

        // Create a temporary image resource
        $tempImage = imagecreatetruecolor(imagesx($gif), imagesy($gif));

        // Iterate through GIF frames
        for ($i = 0; $i < $maxFrames; $i++) {
            if (!imagegif($gif)) {
                break;
            }

            if ($i % $skipFrames === 0) {
                $outputFramePath = $outputFolder . "/frame_" . $frameIndex . ".png";
                if (imagepng($tempImage, $outputFramePath)) {
                    $frames[] = $outputFramePath;
                    $frameIndex++;
                }
            }

            // Advance to the next frame
            if (!imagegif($gif)) {
                break;
            }
        }

        imagedestroy($gif);
        imagedestroy($tempImage);

    } catch (Exception $e) {
        throw new Exception("Error processing GIF frames: " . $e->getMessage());
    }

    return $frames;
}

// Main GIF conversion handler
if (isset($_GET['id'])) {
    $gifId = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT file_path FROM gif_images WHERE id = ?");
    $stmt->bind_param("i", $gifId);
    $stmt->execute();
    $stmt->bind_result($gifFilePath);
    $stmt->fetch();
    $stmt->close();

    if ($gifFilePath && file_exists($gifFilePath)) {
        try {
            $outputFolder = __DIR__ . "/frames/" . $gifId; // Use relative path
            $framePaths = extractGifFrames(__DIR__ . '/' . $gifFilePath, $outputFolder); // Relative path for GIF file

            if ($framePaths) {
                $query = "INSERT INTO gif_frames (gif_id, frame_number, hex_code, user_id) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);

                foreach ($framePaths as $index => $framePath) {
                    $hexCode = imageToHex($framePath);
                    $stmt->bind_param("iiss", $gifId, $index, $hexCode, $user_id);
                    $stmt->execute();
                }
                $stmt->close();

                echo "GIF (ID: $gifId) converted to hex and frames stored successfully!";
            } else {
                echo "No frames were extracted from the GIF.";
            }
        } catch (Exception $e) {
            error_log("Error processing GIF ID $gifId: " . $e->getMessage());
            echo "Error processing GIF: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "GIF file not found for the provided ID.";
    }
} else {
    echo "No GIF ID provided.";
}
?>
