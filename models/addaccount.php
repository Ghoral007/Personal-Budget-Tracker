<?php
// Include necessary files for configuration and database connection
include_once("../config/config.php");
include_once("../config/database.php");

// Start the session and verify the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the logged-in user ID
    $user_id = htmlspecialchars($_SESSION['user_id']);

    // Sanitize and validate form inputs
    $account_name = trim($_POST['account_name']);
    $balance = floatval($_POST['balance']);

    if (empty($account_name)) {
        $error = "Account name is required.";
    } elseif ($balance < 0) {
        $error = "Balance cannot be negative.";
    } else {
        // Check if the account name is unique for this user
        $query = "SELECT COUNT(*) AS count FROM accounts WHERE user_id = ? AND account_name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('is', $user_id, $account_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $error = "An account with this name already exists. Please choose a different name.";
        } else {
            // Insert the new account into the database
            $query = "INSERT INTO accounts (user_id, account_name, balance, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('isd', $user_id, $account_name, $balance);
            
            if ($stmt->execute()) {
                // Redirect to the account management page
                $_SESSION['success'] = "The account has been added successfully.";
                header("Location: " . BASE_URL . "account/account.php");
                exit();
            } else {
                $error = "Database error: " . $stmt->error;
            }
        }
    }
}

// If there is an error, store it in the session to display later
if (isset($error)) {
    $_SESSION['error'] = $error;
    header("Location: " . BASE_URL . "account/account.php");
    exit();
}
?>
