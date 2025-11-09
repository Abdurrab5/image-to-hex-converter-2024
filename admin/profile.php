<?php
include '../includes/db.php';
include 'header.php';

// Ensure user is logged in
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (!empty($name) && !empty($password)) {
        // Hash the password
       // $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Update the user's information securely
        $stmt = $conn->prepare("UPDATE admin SET name = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $password, $user_id);

        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile: " . $conn->error;
        }

        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}

// Fetch the current user's details
$stmt = $conn->prepare("SELECT id, name, email, password FROM admin WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

?>
<div class="row">
    <div class="col-md-4">
        <div class="regcontainer">
            <span><?php echo htmlspecialchars($message); ?></span>
            <form method="POST" enctype="multipart/form-data" id="signupform">
                <h2>Update Profile</h2>
                <div class="inputBox">
                    <input type="text" name="name" placeholder="Enter Name"   required>
                </div>
                <div class="inputBox">
                    <input type="password" name="password" placeholder="Enter new password" required>
                </div>
                <div class="inputBox">
                    <button type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
    <table class="table" id="table">
    <thead>
        <tr>
            <th>User ID</th>
            <th>User Name</th>
            <th>Email</th>
            <th>Password</th>
           
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php echo htmlspecialchars($user['id']); ?></td>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['password']); ?></td>
            </tr>
    </tbody>
</table>


    </div>
</div>
<main></main>
<?php include '../footer.php'; ?>
