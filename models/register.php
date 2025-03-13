<?php
// Include database configuration
include_once("../config/config.php");
include_once(DIR_URL . "config/database.php");

$fullNameErr = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = htmlspecialchars(trim($_POST['fullName']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirmPassword = htmlspecialchars(trim($_POST['confirmPassword']));
    $phone = htmlspecialchars(trim($_POST['phone']));

    // Validation
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../Registration.php");
        exit();
    }

    // Check if full name starts with a space
     
    if (empty($fullName)) {
        $fullNameErr = "Full Name is required.";
    } elseif (preg_match('/^\s/g', $fullName)) {
        $fullNameErr = "Full Name cannot start with a space.";
    }
    
    if (empty($fullNameErr)) {
        
    } else {
              $_SESSION['error'] = $fullNameErr;
        header("Location: ../Registration.php");
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email address.";
        header("Location: ../Registration.php");
        exit();
    }

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../Registration.php");
        exit();
    }

    // Validate password complexity
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
    if (!preg_match($passwordPattern, $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.";
        header("Location: ../Registration.php");
        exit();
    }

    // Validate phone number (optional field)
    if (!empty($phone)) {
        $phonePattern = '/^\d{10}$/';
        if (!preg_match($phonePattern, $phone)) {
            $_SESSION['error'] = "Phone number must contain exactly 10 digits.";
            header("Location: ../Registration.php");
            exit();
        }
    }
     // Check if number already exists
     $query = "SELECT user_id FROM users WHERE contact_number = ?";
     $stmt = $conn->prepare($query);
     $stmt->bind_param("s", $phone);
     $stmt->execute();
     $result = $stmt->get_result();
 
     if ($result->num_rows > 0) {
         $_SESSION['error'] = "Number is already registered.";
         header("Location: ../Registration.php");
         exit();
     }

    // Check if email already exists
    $query = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email is already registered.";
        header("Location: ../Registration.php");
        exit();
    }

    // Hash password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database
    $sql = "INSERT INTO users (username, email, password, contact_number) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $fullName, $email, $hashedPassword, $phone);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful. Please log in.";
        header("Location: ../Registration.php");
        exit();
    } else {
        $_SESSION['error'] = "Database error: " . $stmt->error;
        header("Location: ../Registration.php");
        exit();
    }
}
?>
