<?php
include 'includes/db.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate if the hex input is provided
    if (isset($_POST['hex_code']) && !empty($_POST['hex_code'])) {
        $hexCode = $_POST['hex_code'];
        $width = isset($_POST['width']) ? intval($_POST['width']) : 100;
        $height = isset($_POST['height']) ? intval($_POST['height']) : 100;
        $invert = isset($_POST['invert']) && $_POST['invert'] === 'yes';

        try {
            // Convert Hex to Image
            $image = hexToImage($hexCode, $width, $height, $invert);

            // Save the generated image
            $outputFile = 'generated_image.png';
            imagepng($image, $outputFile);
            imagedestroy($image);

            echo "<p>Image generated successfully. <a href='$outputFile' download>Download Image</a></p>";
        } catch (Exception $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Please provide valid hex code.</p>";
    }
}

/*
 * Converts hex code to an image.
 *
 * @param string $hexCode  Hexadecimal pixel data (e.g., RRGGBB per pixel).
 * @param int    $width    Width of the image.
 * @param int    $height   Height of the image.
 * @param bool   $invert   Whether to invert the colors.
 *
 * @return resource GD image resource.
 * @throws Exception If hex code is invalid or image cannot be created.
 */
function hexToImage($hexCode, $width, $height, $invert = false)
{
    // Clean hex code (remove anything that's not a hex digit)
    $hexCode = preg_replace('/[^0-9A-Fa-f]/', '', $hexCode);

    // Calculate the number of pixels based on hex code length (2 characters per color channel per pixel)
    $pixelCount = strlen($hexCode) / 6; // Each pixel requires 6 characters (RRGGBB)

    // Check if the hex code has enough data for the specified dimensions
    if ($pixelCount < $width * $height) {
        throw new Exception('Hex code does not contain enough data for the specified dimensions.');
    }

    // Create a true color image
    $image = imagecreatetruecolor($width, $height);
    if (!$image) {
        throw new Exception('Unable to create image resource.');
    }

    // Loop through each pixel and set the color
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $index = ($y * $width + $x) * 6; // Index to start of the pixel's color in hex code

            // Extract RGB values from the hex string
            $r = hexdec(substr($hexCode, $index, 2));
            $g = hexdec(substr($hexCode, $index + 2, 2));
            $b = hexdec(substr($hexCode, $index + 4, 2));

            // Invert colors if requested
            if ($invert) {
                $r = 255 - $r;
                $g = 255 - $g;
                $b = 255 - $b;
            }

            // Allocate the color and set the pixel
            $color = imagecolorallocate($image, $r, $g, $b);
            imagesetpixel($image, $x, $y, $color);
        }
    }

    return $image;
}
?> 

<main>
    <h1>Hex Code to Image Conversion</h1>
    <form method="POST">
        <label for="hex_code">Hex Code (RRGGBB per pixel):</label><br>
        <textarea name="hex_code" id="hex_code" rows="10" cols="50" required></textarea><br><br>

        <label for="width">Width:</label>
        <input type="number" name="width" id="width" value="100" required><br><br>

        <label for="height">Height:</label>
        <input type="number" name="height" id="height" value="100" required><br><br>

        <label for="invert">Invert Colors:</label>
        <select name="invert" id="invert">
            <option value="no">No</option>
            <option value="yes">Yes</option>
        </select><br><br>

        <button type="submit">Convert Hex to Image</button>
    </form>
</main>  

<?php
include 'footer.php';
?>