<?php
session_start();

$isLoggedIn = isset($_SESSION['username']) && !empty($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Image to Hex Web Application</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">
            <img src="uploads/img9.jpg" alt="Image to Hex Logo" class="img-fluid" style="width: 40px; height: 40px; border-radius: 50%;">
            Image to Hex
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto text-center">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="library.php">Library</a></li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item"><a class="nav-link" href="multiple_image_convert.php">Multiple Images Convert</a></li>
                    <li class="nav-item"><a class="nav-link" href="gifconvert.php">Gif Convert</a></li>
                    <li class="nav-item"> <a class="nav-link" href="imageConvert.php">Image Convert</a></li>
                    <li class="nav-item"> <a class="nav-link" href="send_api_post.php">Api Post</a></li>
                    <li class="nav-item"> <a class="nav-link" href="send_api_get.php">Api Get</a></li>
                <?php endif; ?>
               </ul>
            <ul class="navbar-nav mx-auto text-center">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a  class="dropdown-item" href="myimages.php">My Images</a></li>
                            <li><a  class="dropdown-item" href="mygifimages.php">My Gif Images</a></li>
                            <li><a  class="dropdown-item" href="myhexcode.php">My Images Hexcodes</a></li>
                            <li><a  class="dropdown-item" href="mygifhexcode.php">My Gif Images Hexcodes</a></li>
                            <li><a  class="dropdown-item" href="libraryhexcode.php">LibraryImages Hexcodes</a></li>
                            <li><a  class="dropdown-item" href="library_gif_hexcode.php">Library Gif Images Hexcodes</a></li>
                          
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                       
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>
</body>
</html>
