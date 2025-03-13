<?php
include_once("config/config.php");
include_once("config/database.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Personal Budget Tracker</title>
    <link rel="stylesheet" href="./assets/css/logstyle.css">
    <style>
        .message{
   padding-left: 20px;
   color: white;

}
    </style>
</head>
<body background="./assets/images/1.jpg.jpg">
    <div class="registration-container">
        <div class="registration-form">
            <h2 class="reg">Register</h2>
          <p>Create your account to start tracking your finances.</p>
           
            <form id="registrationForm" method="POST" action="<?php echo BASE_URL ?>./models/register.php">
                <!-- Full Name -->
                <div class="message"><?php include_once(DIR_URL . "include/alerts.php"); ?></div> 
                <div class="form-group">
                    <label for="fullName">Full Name:</label>
                    <input class="inputreg" type="fullName" id="fullName" name="fullName" placeholder="Enter your full name" required>
                </div>

                <!-- Email -->
                <div class="message"><?php include_once(DIR_URL . "include/alerts.php"); ?></div> 
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input class="inputreg" type="remail" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <!-- Password -->
                <div class="message"><?php include_once(DIR_URL . "include/alerts.php"); ?></div> 
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input class="inputreg" type="password" id="password" name="password" placeholder="Create a password" required>
                </div>

                <!-- Confirm Password -->
                <div class="message"><?php include_once(DIR_URL . "include/alerts.php"); ?></div> 
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input class="inputreg" type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                </div>

                <!-- Optional Phone Number -->
                <div class="message"><?php include_once(DIR_URL . "include/alerts.php"); ?></div> 
                <div class="form-group">
                    <label for="phone">Phone Number :</label>
                    <input class="inputreg" type="phone" id="phone" name="phone" placeholder="Enter your phone number" required>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit ">Register</button>
            </form>

            <div class="redirect-link">
                <p>Already have an account? <a href="index.php">Login here</a>.</p>
            </div>
        </div>
    </div>
    <script>
        const fullNameField = document.getElementById("fullName");
        fullNameField.addEventListener("keydown",function(event){
            if (event.key == " "&& this.selectionStart === 0){
                event.preventDefault();
            }
            if (event.key >="0" && event.key<="9"){
                event.preventDefault();
            }
        });
        fullNameField.addEventListener("input", function(){
            this.value = this.valurereplace(/^\s+/, '');
        });
        </script>
</body>
</html>
