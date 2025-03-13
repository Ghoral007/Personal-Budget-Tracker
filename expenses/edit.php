<?php
include_once("../config/config.php");
include_once("../config/database.php");

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}
 // Fetch user details from the session
 $user_id = htmlspecialchars($_SESSION['user_id']);
 $user_name = htmlspecialchars($_SESSION['user_name']);
 $user_email = htmlspecialchars($_SESSION['user_email']);

 // Fetch additional user details from the database (profile image)
 $query = "SELECT profile_image FROM users WHERE user_id = ?";
 $stmt = $conn->prepare($query);
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $result = $stmt->get_result();
 $user_details = $result->fetch_assoc();
 $profile_image = $user_details['profile_image'] ?? 'default.jpg'; // Default profile image if none is uploaded


$user_id = $_SESSION['user_id'];
$expense_id = $_GET['id'] ?? null;

if (!$expense_id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: " . BASE_URL . "expenses/expense.php");
    exit();
}

// Fetch expense details
$query = "SELECT * FROM expenses WHERE expense_id = ? AND user_id = ?";
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

// Fetch categories and accounts
$categoryQuery = "SELECT category_id, category_name FROM categories WHERE category_type = 'Expense' AND user_id = ?";
$categoryStmt = $conn->prepare($categoryQuery);
$categoryStmt->bind_param("i", $user_id);
$categoryStmt->execute();
$categories = $categoryStmt->get_result();

$accountQuery = "SELECT account_id, account_name, balance FROM accounts WHERE user_id = ?";
$accountStmt = $conn->prepare($accountQuery);
$accountStmt->bind_param("i", $user_id);
$accountStmt->execute();
$accounts = $accountStmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = trim($_POST['amount']);
    $account_id = trim($_POST['account']);
    $category_id = trim($_POST['category']);
    $date = trim($_POST['date']);
    $notes = trim($_POST['notes']);

    if (empty($amount) || empty($account_id) || empty($category_id) || empty($date)) {
        $_SESSION['error'] = "All fields are required except notes.";
    } else {
        $oldAmount = $expense['amount'];
        $oldAccountId = $expense['account_id'];

        // Refund the previous amount to the old account
        $refundQuery = "UPDATE accounts SET balance = balance + ? WHERE account_id = ? AND user_id = ?";
        $refundStmt = $conn->prepare($refundQuery);
        $refundStmt->bind_param("dii", $oldAmount, $oldAccountId, $user_id);
        $refundStmt->execute();

        // Deduct the new amount from the selected account
        $updateAccountQuery = "UPDATE accounts SET balance = balance - ? WHERE account_id = ? AND user_id = ?";
        $updateAccountStmt = $conn->prepare($updateAccountQuery);
        $updateAccountStmt->bind_param("dii", $amount, $account_id, $user_id);
        $updateAccountStmt->execute();

        // Update expense details
        $updateExpenseQuery = "UPDATE expenses SET amount = ?, account_id = ?, category_id = ?, date = ?, notes = ? WHERE expense_id = ? AND user_id = ?";
        $updateExpenseStmt = $conn->prepare($updateExpenseQuery);
        $updateExpenseStmt->bind_param("diissii", $amount, $account_id, $category_id, $date, $notes, $expense_id, $user_id);

        if ($updateExpenseStmt->execute()) {
            $_SESSION['success'] = "Expense updated successfully.";
            header("Location: " . BASE_URL . "expenses/expense.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update expense.";
        }
    }
}

include_once(DIR_URL . "include/header.php");
include_once(DIR_URL . "include/sidebar.php");
include_once(DIR_URL . "include/topbar.php");
?>

<div class="inc-container">
    <h1 class="title">Edit Expense</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="amount">Amount (in currency):</label>
            <input type="number" id="amount" name="amount" value="<?= htmlspecialchars($expense['amount']) ?>" required>
        </div>
        <div class="form-group">
            <label for="account">Account:</label>
            <select id="account" name="account" required>
                <?php while ($account = $accounts->fetch_assoc()): ?>
                    <option value="<?= $account['account_id'] ?>" <?= $expense['account_id'] == $account['account_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($account['account_name']) ?> (Balance: Rs. <?= $account['balance'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?= $category['category_id'] ?>" <?= $expense['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($expense['date']) ?>" required>
        </div>
        <div class="form-group">
            <label for="notes">Notes:</label>
            <textarea class="note" name="notes" rows="2"><?= htmlspecialchars($expense['notes']) ?></textarea>
        </div>
        <div class="actions">
            <button type="submit" class="inc-button">Update Expense</button>
        </div>
    </form>
</div>

<?php include_once(DIR_URL . "include/footer.php"); ?>
