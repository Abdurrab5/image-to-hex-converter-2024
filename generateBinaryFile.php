<?php
include 'includes/db.php';
include 'function.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle download actions using functions from function.php
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['binid']) ? intval($_GET['binid']) : (isset($_GET['cid']) ? intval($_GET['cid']) : null);

    if ($id !== null) {
        // Fetch hex code for the given ID and user
        $hex_code = fetchHexCode($conn, $id, $user_id);

        if ($hex_code) {
            if ($action == 'download' && isset($_GET['binid'])) {
                // Generate binary string file
                generateBinaryStringfile($hex_code);
                echo "<div class='alert alert-success text-center'>Download successful: Binary file generated.</div>";
            } elseif ($action == 'download' && isset($_GET['cid'])) {
                // Generate C array file
                generateCArrayfile($hex_code);
                echo "<div class='alert alert-success text-center'>Download successful: C Array file generated.</div>";
            } else {
                echo "<div class='alert alert-danger text-center'>Invalid action.</div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center'>Invalid record or permission denied.</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>No valid ID provided.</div>";
    }
}

// Handle binary data download through POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['binaryData'])) {
    $hexCode = trim($_POST['binaryData']);

    // Validate hex code using the function
    if (validateHexCode($hexCode)) {
        // Generate and download binary file
        generateBinaryStringfile($hexCode);
    } else {
        echo "<div class='alert alert-danger text-center'>Invalid hex code provided!</div>";
    }
}


?>
