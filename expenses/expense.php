<?php 

include_once("../config/config.php");
include_once("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
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



// Fetch expense categories (only "Expense" types)
$categoryQuery = "SELECT category_id, category_name FROM categories WHERE category_type = 'Expense' AND user_id = ?";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$categories = $stmt->get_result();

// Fetch accounts for the dropdown
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

    // Validate inputs
    if (empty($amount) || empty($account_id) || empty($category_id) || empty($date)) {
        $_SESSION['error'] = "All fields are required except notes.";
    } else {
        // Deduct amount from the account
        $updateAccountQuery = "UPDATE accounts SET balance = balance - ? WHERE account_id = ? AND user_id = ?";
        $updateAccountStmt = $conn->prepare($updateAccountQuery);
        $updateAccountStmt->bind_param("dii", $amount, $account_id, $user_id);
        if ($updateAccountStmt->execute()) {
            // Insert expense record
            $insertExpenseQuery = "INSERT INTO expenses (user_id, amount, account_id, category_id, date, notes) VALUES (?, ?, ?, ?, ?, ?)";
            $insertExpenseStmt = $conn->prepare($insertExpenseQuery);
            $insertExpenseStmt->bind_param("idiiss", $user_id, $amount, $account_id, $category_id, $date, $notes);

            if ($insertExpenseStmt->execute()) {
                $_SESSION['success'] = "Expense added successfully.";
            } else {
                $_SESSION['error'] = "Failed to add expense: " . $insertExpenseStmt->error;
            }
        } else {
            $_SESSION['error'] = "Failed to deduct amount from account: " . $updateAccountStmt->error;
        }
    }
}

// Fetch expense records for the table
$expenseQuery = "SELECT e.expense_id, e.amount, c.category_name, e.date, e.notes 
                 FROM expenses e
                 JOIN categories c ON e.category_id = c.category_id
                 WHERE e.user_id = ?";
$expenseStmt = $conn->prepare($expenseQuery);
$expenseStmt->bind_param("i", $user_id);
$expenseStmt->execute();
$expenses = $expenseStmt->get_result();

include_once(DIR_URL . "include/header.php");   
include_once(DIR_URL . "include/sidebar.php");   
include_once(DIR_URL . "include/topbar.php");
?>

<!-- Content Area -->
<div class="inc-container">
    <h1 class="title">Track Expenses</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Add Expense Form -->
    <form method="post">
        <div class="form-group">
            <label for="amount">Amount (in currency):</label>
            <input type="number" id="amount" name="amount" placeholder="Enter expense amount" required>
        </div>
        <div class="form-group">
            <label for="account">Account:</label>
            <select id="account" name="account" required>
                <option value="">-- Select Account --</option>
                <?php while ($account = $accounts->fetch_assoc()): ?>
                    <option value="<?= $account['account_id'] ?>">
                        <?= htmlspecialchars($account['account_name']) ?> (Balance: Rs. <?= $account['balance'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="">Select Category</option>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?= $category['category_id'] ?>">
                        <?= htmlspecialchars($category['category_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
        </div>
        <div class="form-group">
            <label for="notes">Notes:</label>
            <textarea class="note" name="notes" rows="2" placeholder="Optional notes about the expense"></textarea>
        </div>
        <div class="actions">
            <button type="submit" class="inc-button">Add Expense</button>
        </div>
    </form>

    <!-- Expense Records Table -->
    <table class="table">
        <thead>
            <tr>
                <th>Amount</th>
                <th>Category</th>
                <th>Date</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($expense = $expenses->fetch_assoc()): ?>
                <tr>
                    <td>Rs. <?= $expense['amount'] ?></td>
                    <td><?= htmlspecialchars($expense['category_name']) ?></td>
                    <td><?= htmlspecialchars($expense['date']) ?></td>
                    <td><?= htmlspecialchars($expense['notes']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $expense['expense_id'] ?>" class="btn" style="background-color: #ffc107;">Edit</a>
                        <a href="delete.php?id=<?= $expense['expense_id'] ?>" class="btn" style="background-color: #dc3545;">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include_once(DIR_URL . "include/footer.php"); ?>
