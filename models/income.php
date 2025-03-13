<?php
include_once("../config/config.php");
include_once("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $incomeAmount = floatval($_POST['incomeAmount']);
    $incomeCategory = intval($_POST['incomeCategory']);
    $accountId = intval($_POST['account']);
    $incomeDate = $_POST['incomeDate'];
    $incomeNotes = trim($_POST['incomeNotes']);

    // Insert income record
    $query = "INSERT INTO income (user_id, category_id, account_id, amount, date, notes) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiidss', $user_id, $incomeCategory, $accountId, $incomeAmount, $incomeDate, $incomeNotes);

    if ($stmt->execute()) {
        // Update account balance
        $updateQuery = "UPDATE accounts SET balance = balance + ? WHERE account_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('di', $incomeAmount, $accountId);
        $updateStmt->execute();

        // Redirect back to the income management page
        header("Location: " . BASE_URL . "income/trackincome.php");
        exit();
    } else {
        // Handle database error
        $_SESSION['error_message'] = "Failed to add income: " . $stmt->error;
        header("Location: " . BASE_URL . "income/trackincome.php");
        exit();
    }
}
?>
