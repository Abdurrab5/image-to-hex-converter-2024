<?php
include '../includes/db.php';
include 'header.php';


 
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

?>
<main> <h1>Admin DashBoard</h1></main>
<a href="upload.php">Upload Images</a>
 