<?php
include_once("../config/config.php");
include_once("../config/database.php");

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$expense_id = $_GET['id'] ?? null;

if (!$expense_id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: " . BASE_URL . "expenses/expense.phpp");
    exit();
}

// Get the expense details before deleting (to refund the amount to the account)
$query = "SELECT amount, account_id FROM expenses WHERE expense_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $expense_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$expense = $result->fetch_assoc();

if (!$expense) {
    $_SESSION['error'] = "Expense not found or unauthorized access.";
    header("Location: " . BASE_URL . "expenses/expense.php");
    exit();
}

// Refund the amount to the account
$updateAccountQuery = "UPDATE accounts SET balance = balance + ? WHERE account_id = ? AND user_id = ?";
$updateAccountStmt = $conn->prepare($updateAccountQuery);
$updateAccountStmt->bind_param("dii", $expense['amount'], $expense['account_id'], $user_id);
$updateAccountStmt->execute();

// Delete the expense
$deleteQuery = "DELETE FROM expenses WHERE expense_id = ? AND user_id = ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->bind_param("ii", $expense_id, $user_id);

if ($deleteStmt->execute()) {
    $_SESSION['success'] = "Expense deleted successfully and amount refunded to account.";
} else {
    $_SESSION['error'] = "Failed to delete expense.";
}

header("Location: " . BASE_URL . "expenses/expense.php");
exit();
?>
