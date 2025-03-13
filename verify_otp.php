<?php

include_once("config/config.php");
include_once("config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = trim($_POST['otp']); // Get the OTP from user
    $phone = $_SESSION['phone']; // Retrieve stored phone number

    if (empty($phone) || empty($entered_otp)) {
        header("Location: verify_otp.php?error=Invalid request!");
        exit();
    }

    // Retrieve OTP from the database
    $query = "SELECT otp, expires_at FROM otp_verifications WHERE contact_number = ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $otp_record = $result->fetch_assoc();

    if ($otp_record) {
        $stored_otp = $otp_record['otp'];
        $expires_at = strtotime($otp_record['expires_at']); // Convert to timestamp

        // Check if OTP matches and is not expired
        if ($stored_otp == $entered_otp) {
            if (time() <= $expires_at) {
                header("Location: reset_password.php?phone=$phone&success=OTP verified. Reset your password.");
                exit();

            } else {
                header("Location: verify_otp.php?error=OTP expired. Request a new one.");
                exit();
            }
        } else {
            header("Location: verify_otp.php?error=Invalid OTP. Try again.");
            exit();
        }
    } else {
        header("Location: verify_otp.php?error=No OTP found for this number.");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="./assets/css/logstyle.css">
    <script src="https://kit.fontawesome.com/be3ac931eb.js" crossorigin="anonymous"></script>
</head>
<body style="background: url('./assets/images/1.jpg.jpg') no-repeat center center fixed; background-size: cover;">

    <div class="container">
        <h2>Verify OTP</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <form action="verify_otp.php" method="post">
            <label for="otp"><i class="fa fa-key"></i> Enter OTP:</label>
            <input class="inputreg" type="text" name="otp" placeholder="Enter 6-digit OTP" required>

            <button type="submit" class="btn btn-submit"><i class="fa fa-check-circle"></i> Verify OTP</button>
            <div class="redirect-link">
                <p>If you know the password, <a href="index.php">Login here</a>.</p>
            </div>
        </form>
    </div>

</body>
</html>
