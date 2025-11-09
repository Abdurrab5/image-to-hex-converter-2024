<?php
include 'includes/db.php';
include 'header.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, name, email, password, api_key FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$apiUrl = "http://localhost/image_to_hex/api/upload.php";
$apiKey = htmlspecialchars($user['api_key']); // Replace with your generated API key
$imagePath = "D:\movies pics\cowmilk.jpg"; // Path to the image you want to upload

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $apiKey",
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    "image" => new CURLFile($imagePath)
]);

// Execute the request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
} else {
    // Print the response
    echo "Server Response: " . $response;
}

// Close cURL
curl_close($ch);
?>