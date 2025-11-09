<?php
include 'includes/db.php';
include 'header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p class='error'>Unauthorized access. Please log in.</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

if (!empty($_GET['id'])) {
    $gif_id = intval($_GET['id']); // Sanitize input

    // Fetch image details from database
    $sql = "SELECT * FROM gif_frames WHERE gif_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $gif_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>GIF Details</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    background-color: #f8f9fa;
                }
                .container {
                    margin-top: 30px;
                }
                .card {
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .card-header {
                    background-color: #007bff;
                    color: white;
                    font-weight: bold;
                    padding: 15px;
                    border-bottom: 1px solid #ddd;
                }
                .card-body {
                    padding: 20px;
                }
                .frame-item {
                    background-color: #fff;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    margin-bottom: 15px;
                    padding: 10px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }
                .hex-code {
                    font-family: monospace;
                    color: #ff5733;
                    word-break: break-word;
                }
            </style>
        </head>
        <body>
        <main class="container">
            <div class="card">
                <div class="card-header">GIF Hex Code Details</div>
                <div class="card-body">
                    <h4 class="mb-4">GIF ID: <?php echo htmlspecialchars($gif_id); ?></h4>
                    <div class="row">
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            $frame_number = $row['frame_number'];
                            $hex_code = $row['hex_code'];
                            ?>
                            <div class="col-md-4">
                                <div class="frame-item">
                                    <h5>Frame Number: <?php echo htmlspecialchars($frame_number); ?></h5>
                                    <p class="hex-code">Hex Code: <?php echo htmlspecialchars($hex_code); ?></p>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>

        <?php
    } else {
        echo "<p class='error'>No frames found for the provided GIF ID.</p>";
    }
    $stmt->close();
} else {
    echo "<p class='error'>Invalid GIF ID.</p>";
}

 
include 'footer.php';
?>