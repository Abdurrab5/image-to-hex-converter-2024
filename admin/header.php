<?php
session_start(); // Start the session
//include 'function.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['username']) && !empty($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>image to Hex Web Application</title>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
     
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link href="../styles.css" rel="stylesheet">
    <!-- jQuery and Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    
    
    
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2287119757042775"
     crossorigin="anonymous"></script>
</head>
<body>
<header>
  <nav class="navbar navbar-expand-lg ">
    <a class="navbar-brand" href="index.php">
        <img src="../uploads/img9.jpg" alt="image to hex Logo" style="width: 40px; height: 40px; border-radius: 50%;">
       Image to Hex
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto text-center">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="uploadimages.php">Upload Images</a></li>
            <li class="nav-item"><a class="nav-link" href="library.php">Library</a></li>
           
        </ul>
  
        <ul class="navbar-nav mx-auto text-center">
            <?php if ($isLoggedIn){?>
               
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                <div class="dropdown-menu" aria-labelledby="userDropdown">
                    <a class="nav-link" href="profile.php">Profile</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-divider"></div>
                   
                    <div class="dropdown-divider"></div>
                    <a class="nav-link" href="logout.php">Logout</a>
                </div>
            </li>
            <?php }?>
        </ul>
        
           
        </div>
    </div>
</nav>
    </header>
     
</body>
</html>
