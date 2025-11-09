<?php
include 'includes/db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, name, email, password, api_key FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// API endpoint
$apiUrl = "http://localhost/image_to_hex/api/convert.php";
$filename = "676934f053ed1_milk4.jpg";

// User's API key
$apiKey = htmlspecialchars($user['api_key']);

// Append parameters to the API URL
$apiUrlWithParams = $apiUrl . "?file=" . urlencode($filename);

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrlWithParams);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $apiKey",
]);

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
    curl_close($ch);
    exit;
}

// Close cURL
curl_close($ch);

// Decode API response
$responseData = json_decode($response, true);

// Output the response
if ($responseData) {
    echo "<pre>";
    print_r($responseData);
    echo "</pre>";
} else {
    echo "Invalid API response.";
}
?>
