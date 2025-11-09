<?php
require_once '../includes/db.php';
require_once 'auth.php';
authenticate($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $file = $_GET['file'] ?? null;

    if (!$file || !file_exists("../uploads/" . $file)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file']);
        exit;
    }

    // Conversion logic
    function imageToHex($filePath) {
        $image = file_get_contents($filePath);
        return bin2hex($image);
    }

    $filePath = "../uploads/" . $file;
    $hexCode = imageToHex($filePath);

    // Save the hex to database
    $stmt = $conn->prepare("INSERT INTO hex_results (user_id, file_path, hex_code) VALUES (?, ?, ?)");
    $userId = 1; // Replace with logged-in user ID
    $stmt->bind_param("iss", $userId, $file, $hexCode);
    $stmt->execute();

    echo json_encode(['success' => 'Conversion successful', 'hex' => $hexCode]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
