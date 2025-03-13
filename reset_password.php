<?php
include_once("config/config.php");
include_once("config/database.php");

if (!isset($_GET['phone'])) {
    header("Location: forgotpw.php");
    exit();
}

$phone = $_GET['phone'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Password pattern: At least 8 characters, 1 uppercase, 1 lowercase, 1 digit, 1 special character
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

    if (!preg_match($passwordPattern, $new_password)) {
        $error = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update password in database
        $query = "UPDATE users SET password = ? WHERE contact_number = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $hashed_password, $phone);
        
        if ($stmt->execute()) {
            // Delete OTP after successful reset
            $delete_query = "DELETE FROM otp_verifications WHERE contact_number = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("s", $phone);
            $delete_stmt->execute();

            // Redirect to login with success message
            header("Location: index.php?success=Password reset successfully! Please log in.");
            exit();
        } else {
            $error = "Something went wrong! Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="./assets/css/logstyle.css">
    <script src="https://kit.fontawesome.com/be3ac931eb.js" crossorigin="anonymous"></script>
</head>
<body style="background: url('./assets/images/1.jpg.jpg') no-repeat center center fixed; background-size: cover;">

    <div class="container">
        <h2>Reset Password</h2>

        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="reset_password.php?phone=<?php echo urlencode($phone); ?>" method="post">
            <label for="new_password"><i class="fa fa-lock"></i> New Password:</label>
            <input class="inputreg" type="password" name="new_password" placeholder="Enter new password" required>

            <label for="confirm_password"><i class="fa fa-lock"></i> Confirm Password:</label>
            <input class="inputreg" type="password" name="confirm_password" placeholder="Confirm new password" required>

            <button type="submit"><i class="fa fa-save"></i> Reset Password</button>
        </form>
    </div>

</body>
</html>
