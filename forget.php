<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PBT Forgot Password</title>
    <link rel="stylesheet" href="./assets/css/logstyle.css">
    <script src="https://kit.fontawesome.com/be3ac931eb.js" crossorigin="anonymous"></script>
</head>
<body style="background: url('./assets/images/1.jpg.jpg') no-repeat center center fixed; background-size: cover;">

    <div class="container">
        <h2>Forgot Password</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <p class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></p>
        <?php endif; ?>

        <form action="forgotpw.php" method="post">
            <label for="phone"><i class="fa fa-phone"></i> Enter your registered phone number:</label>
            <input class="inputreg" type="text" name="phone" placeholder="+97798XXXXXXXX" required>
            <p> </p>
            <button type="submit" class=" btn btn-submit"><i class=" fa fa-check-circle"></i> Send OTP</button>
            <div class="redirect-link">
                <p>If you know the password,<a href="index.php">Login here</a>.</p>
            </div>
        </form>
    </div>

</body>
</html>
