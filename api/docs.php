<?php
header('Content-Type: application/json');
echo json_encode([
    'endpoints' => [
        'POST /api/upload' => 'Upload an image',
        'GET /api/convert' => 'Convert image to hex',
        'GET /api/download/{id}' => 'Download hex result',
    ],
    'authentication' => 'Include your API key in the Authorization header',
    'example_request' => [
        'Authorization' => 'Bearer YOUR_API_KEY',
        'POST /api/upload' => [
            'image' => 'Upload an image file',
        ],
    ],
]);
?>
