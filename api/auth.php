<?php
function validateApiKey($conn, $apiKey) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE api_key = ?");
    $stmt->bind_param("s", $apiKey);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function authenticate($conn) {
    $headers = getallheaders();
    $apiKey = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if (!$apiKey || !validateApiKey($conn, $apiKey)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}
?>
