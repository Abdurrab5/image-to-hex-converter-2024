<?php
include 'includes/db.php';
include 'header.php';

set_time_limit(300); // Increase max execution time to 5 minutes
ini_set('memory_limit', '512M'); // Increase memory limit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imageFile'])) {
    $file = $_FILES['imageFile'];

    // Check for errors during upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('<div class="alert alert-danger">Error: Failed to upload file.</div>');
    }

    // Validate file type (GIF only)
    $fileType = mime_content_type($file['tmp_name']);
    if ($fileType !== 'image/gif') {
        die('<div class="alert alert-danger">Error: Only GIF files are allowed.</div>');
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
        die('<div class="alert alert-danger">Error: Failed to process GIF file.</div>');
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

    // Display HEX codes
    echo "<div class='container mt-5'>
            <div class='card'>
                <div class='card-header bg-info text-white text-center'>
                    <h3>Extracted HEX Data</h3>
                </div>
                <div class='card-body'>
                    <div style='max-height: 300px; overflow-y: auto;'>
                        <ul class='list-group'>";
    foreach ($frames as $frame) {
        echo "<li class='list-group-item'>Frame {$frame['index']}: <code>{$frame['hex']}</code></li>";
    }
    echo "        </ul>
                    </div>
                </div>
            </div>
          </div>";

    // Display Download Links
    echo "<div class='container mt-5'>
            <div class='card'>
                <div class='card-header text-center bg-success text-white'>
                    <h3>GIF Frames and HEX Data Processed</h3>
                </div>
                <div class='card-body'>
                    <p><strong>Total Frames Processed:</strong> " . count($frames) . "</p>
                    <h5>Download Links:</h5>
                    <ul class='list-group'>
                        <li class='list-group-item'>
                            <a href='$framesZip' class='btn btn-success w-100'>Download Frames (ZIP)</a>
                        </li>
                        <li class='list-group-item'>
                            <a href='$hexZip' class='btn btn-info w-100'>Download Hex Data (ZIP)</a>
                        </li>
                    </ul>
                </div>
            </div>
          </div>";
}else {
    // Display the form for GET requests
    ?>
    <!-- HTML Form -->
 <main class="card shadow">
<div class="container">
    <div class="card">
        <div class="card-header text-center">
            <h3>Convert GIF to Frames and Hex</h3>
        </div>
        <div class="card-body">
            <form action="gifconvert.php" method="POST" enctype="multipart/form-data">
            <div class="mt-3">
                        <img id="imagePreview" alt="Image Preview" class="img-fluid" style="display: none; max-width: 50%; margin-top: 15px;">
                    </div>
            <div class="mt-2">
                    <label for="gifInput" class="form-label">Select a GIF File</label>
                    <input type="file" class="form-control" id="gifInput" name="imageFile" accept="image/gif" required>
                </div>
               
                <button type="submit" class="btn btn-primary w-100">Convert GIF</button>
            </form>
        </div>
    </div>
</div>
</main>

    <?php
}
?>

<script>
    document.getElementById('gifInput').addEventListener('change', function(event) {
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