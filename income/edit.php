<?php
include_once("../config/config.php");
include_once(DIR_URL . "config/database.php");


if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Fetch user details from the session
$user_id = htmlspecialchars($_SESSION['user_id']);
$user_name = htmlspecialchars($_SESSION['user_name']);
$user_email = htmlspecialchars($_SESSION['user_email']);


// Fetch user details from the session
$user_id = htmlspecialchars($_SESSION['user_id']);

// Get the income ID from the GET request
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: " . BASE_URL . "income/incomes.php");
    exit();
}

// Fetch the income details for the logged-in user
$query = "SELECT * FROM income WHERE income_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$income = $result->fetch_assoc();

if (!$income) {
    $_SESSION['error'] = "Income not found or unauthorized access.";
    header("Location: " . BASE_URL . "income/trackincome.php");
    exit();
}

// Fetch accounts and categories for the dropdowns
$accountQuery = "SELECT account_id, account_name FROM accounts WHERE user_id = ?";
$accountStmt = $conn->prepare($accountQuery);
$accountStmt->bind_param("i", $user_id);
$accountStmt->execute();
$accounts = $accountStmt->get_result();

$categoryQuery = "SELECT category_id, category_name FROM categories WHERE user_id = ?";
$categoryStmt = $conn->prepare($categoryQuery);
$categoryStmt->bind_param("i", $user_id);
$categoryStmt->execute();
$categories = $categoryStmt->get_result();

// Handle form submission for updating income
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = trim($_POST['amount']);
    $category_id = trim($_POST['category_id']);
    $account_id = trim($_POST['account_id']);
    $date = trim($_POST['date']);
    $notes = trim($_POST['notes']);

    // Validate inputs
    if (empty($amount) || empty($category_id) || empty($account_id) || empty($date)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Fetch the old income details to adjust account balance
        $oldAccountId = $income['account_id'];
        $oldAmount = $income['amount'];

        // Adjust the old account balance
        $adjustOldQuery = "UPDATE accounts SET balance = balance - ? WHERE account_id = ?";
        $adjustOldStmt = $conn->prepare($adjustOldQuery);
        $adjustOldStmt->bind_param("di", $oldAmount, $oldAccountId);
        $adjustOldStmt->execute();

        // Adjust the new account balance
        $adjustNewQuery = "UPDATE accounts SET balance = balance + ? WHERE account_id = ?";
        $adjustNewStmt = $conn->prepare($adjustNewQuery);
        $adjustNewStmt->bind_param("di", $amount, $account_id);
        $adjustNewStmt->execute();

        // Update the income record
        $updateQuery = "UPDATE income SET amount = ?, category_id = ?, account_id = ?, date = ?, notes = ? WHERE income_id = ? AND user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("disssii", $amount, $category_id, $account_id, $date, $notes, $id, $user_id);

        if ($updateStmt->execute()) {
            $_SESSION['success'] = "Income updated successfully.";
            header("Location: " . BASE_URL . "income/trackincome.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update income: " . $updateStmt->error;
        }
    }
}

include_once(DIR_URL . "include/header.php");
include_once(DIR_URL . "include/sidebar.php");
include_once(DIR_URL . "include/topbar.php");
?>

<!-- Edit Income Form -->
<div class="inc-container">
    <section class="form-section">
        <h2>Edit Income</h2>
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
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" value="<?= htmlspecialchars($income['amount']) ?>" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <option value="<?= $category['category_id'] ?>" <?= $income['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['category_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="account_id">Account:</label>
                <select id="account_id" name="account_id" required>
                    <option value="">Select Account</option>
                    <?php while ($account = $accounts->fetch_assoc()): ?>
                        <option value="<?= $account['account_id'] ?>" <?= $income['account_id'] == $account['account_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($account['account_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" value="<?= htmlspecialchars($income['date']) ?>" required>
            </div>
            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes"><?= htmlspecialchars($income['notes']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Income</button>
        </form>
    </section>
</div>

<?php include_once(DIR_URL . "include/footer.php"); ?>
