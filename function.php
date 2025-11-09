<?php
// Function to validate hex code
function validateHexCode($hexCode) {
    return preg_match('/^[0-9A-Fa-f]+$/', $hexCode);
}
function fetchHexCode($conn, $id, $user_id) {
    $hex_code="";
    $sql = "SELECT hex_code FROM hex_codes WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql); // Use the $conn object
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    $stmt->bind_result($hex_code);
    $stmt->fetch();
    $stmt->close();

    return $hex_code;
}
function fetchframeHexCode($conn, $id, $user_id) {
    $hex_code="";
    $sql = "SELECT hex_code FROM gif_frames WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql); // Use the $conn object
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    $stmt->bind_result($hex_code);
    $stmt->fetch();
    $stmt->close();

    return $hex_code;
}
function fetchlibraryHexCode($conn, $id, $user_id) {
    $hex_code="";
    $sql = "SELECT hex_code FROM library_hex_code WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql); // Use the $conn object
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    $stmt->bind_result($hex_code);
    $stmt->fetch();
    $stmt->close();

    return $hex_code;
}
function fetchlibrarygifHexCode($conn, $id, $user_id) {
    $hex_code="";
    $sql = "SELECT hex_code FROM library_gif_frames WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql); // Use the $conn object
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    $stmt->bind_result($hex_code);
    $stmt->fetch();
    $stmt->close();

    return $hex_code;
}
function generateCArrayfile($hexData) {
    $fileName = "image_data_array.txt";
    $content = generateCArray($hexData);
    outputFile($fileName, $content, 'text/plain');
}

function generateCArray($hexData) {
    $formatted = "const uint8_t image_data[] = {\n";
    $formatted .= implode(", ", array_map(fn($hex) => "0x" . strtoupper($hex), str_split($hexData, 2)));
    $formatted .= "\n};";
    return $formatted;
}

function generateBinaryStringfile($hexData) {
    $fileName = "binary_data.txt";
    $content = generateBinaryString($hexData);
    outputFile($fileName, $content, 'text/plain');
}

function generateBinaryString($hexData) {
    $binaryString = "";
    foreach (str_split($hexData, 2) as $hex) {
        $binaryString .= sprintf("%08b", hexdec($hex)) . "\n"; // Convert hex to binary
    }
    return $binaryString;
}

function outputFile($fileName, $content, $contentType) {
    if (!file_put_contents($fileName, $content)) {
        echo "<div class='alert alert-danger text-center'>Error creating file.</div>";
        exit;
    }

    header("Content-Type: $contentType");
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    header("Content-Length: " . filesize($fileName));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    readfile($fileName);
    unlink($fileName); // Cleanup after download
    exit;
}
