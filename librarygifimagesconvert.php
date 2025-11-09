<?php
include 'includes/db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    die("<div class='alert alert-danger text-center'>Unauthorized access. Please <a href='login.php'>login</a>.</div>");
}

$user_id = $_SESSION['user_id'];

// Increase execution and memory limits
set_time_limit(300); 
ini_set('memory_limit', '512M');

// Convert image frame to hex
function imageToHex($imagePath) {
    if (!file_exists($imagePath)) {
        throw new Exception("File not found: $imagePath");
    }
    $image = file_get_contents($imagePath);
    return bin2hex($image);
}


// Extract GIF frames
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


// Main handler
if (isset($_GET['id'])) {
    $lib_Id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT file_path FROM library WHERE lib_id = ?");
    $stmt->bind_param("i", $lib_Id);
    $stmt->execute();
    $stmt->bind_result($gifFilePath);
    $stmt->fetch();
    $stmt->close();

    if ($gifFilePath) {
        $absoluteGifPath = realpath(str_replace('../', '', $gifFilePath));
        if ($absoluteGifPath && file_exists($absoluteGifPath)) {
            try {
                $outputFolder = __DIR__ . "/frames/" . $lib_Id;
                $framePaths = extractGifFrames($absoluteGifPath, $outputFolder);

                if (!empty($framePaths)) {
                    $stmt = $conn->prepare("INSERT INTO library_gif_frames (lib_id, frame_number, hex_code, user_id) VALUES (?, ?, ?, ?)");

                    foreach ($framePaths as $index => $framePath) {
                        $hexCode = imageToHex($framePath);
                        $stmt->bind_param("iiss", $lib_Id, $index, $hexCode, $user_id);
                        $stmt->execute();
                    }

                    $stmt->close();
                    echo "<div class='alert alert-success text-center'>GIF (ID: $lib_Id) converted to hex codes and frames stored successfully!</div>";
                } else {
                    echo "<div class='alert alert-warning text-center'>No frames were extracted from the GIF.</div>";
                }
            } catch (Exception $e) {
                error_log("Error processing GIF ID $lib_Id: " . $e->getMessage());
                echo "<div class='alert alert-danger text-center'>Error processing GIF: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center'>GIF file not found at path: $absoluteGifPath</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>No GIF file found for the provided ID.</div>";
    }
} else {
    echo "<div class='alert alert-warning text-center'>No GIF ID provided.</div>";
}

include 'footer.php';
?>
