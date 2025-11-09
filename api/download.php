<?php
require_once '../includes/db.php';
require_once 'auth.php';
authenticate($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = intval($_GET['id'] ?? 0);

    $stmt = $conn->prepare("SELECT hex_code FROM hex_results WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($hexCode);
    $stmt->fetch();
    $stmt->close();

    if (!$hexCode) {
        http_response_code(404);
        echo json_encode(['error' => 'Hex result not found']);
    } else {
        header('Content-Type: text/plain');
        echo $hexCode;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
