<?php
include 'includes/db.php';
include 'header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $api_key = bin2hex(random_bytes(32)); // Generate unique API key

    $query = "INSERT INTO users (name, email, password, api_key) VALUES ('$name', '$email', '$password', '$api_key')";

    if (mysqli_query($conn, $query)) {
        echo "Registration successful! Your API Key is: $api_key";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<main >
<div class="regcontainer">
<span></span>
<span></span>
<span></span>
<form id="signupform" method="POST">
    <h2>
        Registration
    </h2>
    <div class="inputBox">
    <input type="text" name="name" placeholder="Name" required>
    </div>
    <div class="inputBox">
    <input type="email" name="email" placeholder="Email" required>
    </div>

    <div class="inputBox">
    <input type="password" name="password" placeholder="Password" required>
    </div>
     
    

    <div class="inputBox">
        <input type="submit" value="Register Account">
    </div>
    <div class="inputBox group">
    <a href="login.php">Already Have an Account? <b id="signin">Login</b>
</div>
</form>
   
  
</main>

<?php
 include 'footer.php';
?>