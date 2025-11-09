<?php
require_once '../includes/db.php';
require_once 'auth.php';
authenticate($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Image upload failed']);
        exit;
    }

    $targetDir = '../uploads/';
    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        // Optionally save file info to the database
        $stmt = $conn->prepare("INSERT INTO uploads (user_id, file_path) VALUES (?, ?)");
        $userId = 1; // Replace with logged-in user ID
        $stmt->bind_param("is", $userId, $fileName);
        $stmt->execute();

        echo json_encode(['success' => 'Image uploaded', 'file' => $fileName]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save file']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
