<?php
include_once("../config/config.php");
include_once("../config/database.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$user_id = htmlspecialchars($_SESSION['user_id']);
$user_name = htmlspecialchars($_SESSION['user_name']);
$user_email = htmlspecialchars($_SESSION['user_email']);

// Fetch profile image
$query = "SELECT profile_image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_details = $result->fetch_assoc();
$profile_image = $user_details['profile_image'] ?? 'default.jpg';

// Check if budget_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid budget ID.");
}

$budget_id = intval($_GET['id']);

// Fetch current budget details
$query = "SELECT * FROM budget WHERE budget_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $budget_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$budget = $result->fetch_assoc();

if (!$budget) {
    die("Budget not found or unauthorized access.");
}

// Fetch categories for dropdown
$categoryQuery = "SELECT * FROM categories";
$categoryResult = $conn->query($categoryQuery);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_id = intval($_POST['category_id']);
    $amount = floatval($_POST['amount']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Update budget in database
    $updateQuery = "UPDATE budget SET category_id = ?, amount = ?, start_date = ?, end_date = ? WHERE budget_id = ? AND user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("idssii", $category_id, $amount, $start_date, $end_date, $budget_id, $user_id);

    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "budget/budget.php?success=Budget updated successfully");
        exit();
    } else {
        $error = "Failed to update budget.";
    }
}

include_once(DIR_URL . "include/header.php");
include_once(DIR_URL . "include/sidebar.php");
include_once(DIR_URL . "include/topbar.php");
?>

<div class="inc-container">
    <h2>Edit Budget</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="category">Category:</label>
            <select id="category" name="category_id" required>
                <?php while ($row = $categoryResult->fetch_assoc()): ?>
                    <option value="<?= $row['category_id']; ?>" <?= ($row['category_id'] == $budget['category_id']) ? "selected" : "" ?>>
                        <?= htmlspecialchars($row['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" value="<?= htmlspecialchars($budget['amount']); ?>" required>
        </div>
        <div class="form-group">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($budget['start_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($budget['end_date']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Budget</button>
        <a href="<?= BASE_URL ?>budget.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include_once(DIR_URL . "include/footer.php"); ?>
