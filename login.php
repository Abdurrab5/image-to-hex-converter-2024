<?php
include 'includes/db.php';
include 'header.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            header("Location: index.php");
            exit();
        }
    }
    echo "Invalid email or password.";
}
?>
<main>
    <div class="logincontainer">
<span></span>
<span></span>
<span></span>
<form id="signinform" action="login.php" method="POST">
    <h2>
        Login
    </h2>
    <div class="inputBox">
        <input type="text" name="email" placeholder="username">
    </div>
    <div class="inputBox">
        <input type="password" name="password" placeholder="password">
    </div>
   
    <div class="inputBox group">
        <a href="#" >Forgot password</a>
        <a href="register.php" id="signup">Signup</a>
        
    </div>

    <div class="inputBox">
        <input type="submit" value="signin">
    </div>

</form>

</main>
<?php
 include 'footer.php';
?>