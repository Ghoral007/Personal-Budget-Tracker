<?php
include_once("../config/config.php"); // Include BASE_URL
include_once("../config/database.php"); // Include database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        header("Location: " . BASE_URL . "index.php?error=" . urlencode("Please fill in all fields."));
        exit();
    }

    // Prepare SQL query
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];

            // Redirect to dashboard
            header("Location: " . BASE_URL . "dashboard.php");
            exit();
        } else {
            header("Location: " . BASE_URL . "index.php?error=" . urlencode("Invalid password."));
            exit();
        }
    } else {
        header("Location: " . BASE_URL . "index.php?error=" . urlencode("No user found with this email."));
        exit();
    }
} else {
    header("Location: " . BASE_URL . "index.php");
    exit();
}
?>