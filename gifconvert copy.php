<?php
include 'includes/db.php';
include 'header.php';

set_time_limit(300); // Increase max execution time to 5 minutes
ini_set('memory_limit', '512M'); // Increase memory limit

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imageFile'])) {
    $file = $_FILES['imageFile'];

    // Check for errors during upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('Error: Failed to upload file.');
    }

    // Validate file type (GIF only)
    $fileType = mime_content_type($file['tmp_name']);
    if ($fileType !== 'image/gif') {
        die('Error: Only GIF files are allowed.');
    }

    // Upload directory setup
    $uploadDir = 'uploads/';
    $framesDir = 'frames/';
    $hexDir = 'hex/';
    $zipDir = 'zips/';

    if (!file_exists($framesDir)) mkdir($framesDir, 0777, true);
    if (!file_exists($hexDir)) mkdir($hexDir, 0777, true);
    if (!file_exists($zipDir)) mkdir($zipDir, 0777, true);

    // Load GIF file
    $gifFile = $file['tmp_name'];
    $gif = imagecreatefromgif($gifFile);
    if (!$gif) {
        die('Error: Failed to process GIF file.');
    }

    // Process GIF frames
    $frames = [];
    $frameIndex = 0;
    $skipFrames = 10; // Process every 10th frame
    $maxFrames = 100; // Max number of frames to process

    do {
        if ($frameIndex % $skipFrames === 0) {
            // Save frame as PNG
            $frameFilename = $framesDir . "frame_$frameIndex.png";
            imagepng($gif, $frameFilename);

            // Extract hex data
            ob_start();
            imagegif($gif);
            $frameData = ob_get_clean();
            $hexData = bin2hex($frameData);

            // Save hex data
            $hexFileName = $hexDir . "frame_$frameIndex.txt";
            file_put_contents($hexFileName, $hexData);

            // Add to frames array
            $frames[] = [
                'index' => $frameIndex,
                'hex' => $hexData,
                'file' => $frameFilename
            ];
        }
        $frameIndex++;
        if ($frameIndex >= $maxFrames) break;
    } while (imagegif($gif) && imagecopy($gif, $gif, 0, 0, 0, 0, imagesx($gif), imagesy($gif)));

    // Free memory
    imagedestroy($gif);

    // Generate ZIP files for frames and hex data
    $framesZip = $zipDir . 'frames.zip';
    $hexZip = $zipDir . 'hex_data.zip';

    // Zip Frames
    $zip = new ZipArchive();
    if ($zip->open($framesZip, ZipArchive::CREATE) === TRUE) {
        foreach ($frames as $frame) {
            $zip->addFile($frame['file'], basename($frame['file']));
        }
        $zip->close();
    }

    // Zip Hex Data
    $zipHex = new ZipArchive();
    if ($zipHex->open($hexZip, ZipArchive::CREATE) === TRUE) {
        foreach ($frames as $frame) {
            $hexFile = $hexDir . "frame_{$frame['index']}.txt";
            $zipHex->addFile($hexFile, basename($hexFile));
        }
        $zipHex->close();
    }

    // Display Results
    echo "<h2>GIF Frames and HEX Data Processed</h2>";
    echo "<p><strong>Total Frames Processed:</strong> " . count($frames) . "</p>";
    echo "<h3>Download Links:</h3>";
    echo "<ul>
            <li><a href='$framesZip' class='btn btn-success'>Download Frames (ZIP)</a></li>
            <li><a href='$hexZip' class='btn btn-info'>Download Hex Data (ZIP)</a></li>
          </ul>";

} else {
    // Display the form for GET requests
    ?>
    <!-- HTML Form -->
    <main>
    <div class="container mt-4">
        <h2 class="text-center">Convert GIF to Frames and Hex</h2>
        <form action="gifconvert.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="gifInput">Select a GIF File</label>
                <input type="file" class="form-control" id="gifInput" name="imageFile" accept="image/gif" required>
            </div>
            <button type="submit" class="btn btn-primary">Convert GIF</button>
        </form>
    </div>
    </main>
    <?php
}
?>



<?php
include 'footer.php';
?>