<?php
include_once("config/config.php");
include_once("config/database.php");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
?>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible"
    content="IE=edge">
    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">
    <title>PBT Login Form</title>
    <link rel="stylesheet" href="./assets/css/logstyle.css">
    <script src="https://kit.fontawesome.com/be3ac931eb.js" crossorigin="anonymous"></script>
  </head>
<body background="./assets/images/1.jpg.jpg">
  <div class="container">
    <h2>Login</h2>
    <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
    <form method="POST" action="<?php echo BASE_URL ?>models/login.php">
      <div class="from-group">
      <input class="inputreg" type="remail" id="email" name="email"  required>
      <label for="">Email</label>
      <i class="fa-solid fa-envelope"></i>
      </div>
      <div class="from-group">
      <input class="inputreg" type="password" id="password" name="password" required>
        <label for="">Password</label>
        <i class="fa-solid fa-key"></i>
      </div>
      <P><input type="checkbox">Remember Me    <a href="forget.php">Forget Password</a></P>
      <button type="submit" class="btn-submit">Login</button>
      <P>Don't have an account?   <a href="Registration.php"> Register</a></P>
    </form>
  </div>
</body>
</html>