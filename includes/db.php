<?php
$host = 'localhost';
$user = 'root'; // Default username for local XAMPP/WAMP
$password = ''; // Default password for local XAMPP/WAMP
$dbname = 'image_to_hex';

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
