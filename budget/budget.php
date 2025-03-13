<?php 
include_once("../config/config.php");
include_once("../config/database.php");

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

// Fetch budgets dynamically
$budgetQuery = "SELECT b.budget_id, c.category_name, b.amount, b.start_date, b.end_date, 
                COALESCE(SUM(e.amount), 0) AS total_expenses 
                FROM budget b 
                LEFT JOIN expenses e ON b.user_id = e.user_id AND b.category_id = e.category_id
                JOIN categories c ON b.category_id = c.category_id
                WHERE b.user_id = ?
                GROUP BY b.budget_id, c.category_name, b.amount, b.start_date, b.end_date";

$budgetStmt = $conn->prepare($budgetQuery);
$budgetStmt->bind_param("i", $user_id);
$budgetStmt->execute();
$budgetResult = $budgetStmt->get_result();

// Fetch expense categories (only "Expense" types)
$categoryQuery = "SELECT category_id, category_name FROM categories WHERE category_type = 'Expense' AND user_id = ?";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$categories = $stmt->get_result();

include_once(DIR_URL."include/header.php");   
include_once(DIR_URL."include/sidebar.php");   
include_once(DIR_URL."include/topbar.php");
?>

<!-- Content Area -->
<div class="inc-container">
    <header>
        <h1>Budget Management</h1>
    </header>

    <section class="form-section">
        <h2>Set Budget</h2>
        <form action="<?php BASE_URL ?>process_budget.php" method="POST">
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
                <label for="budgetAmount">Amount:</label>
                <input type="number" id="budgetAmount" name="amount" placeholder="Enter budget amount" required>
            </div>
            <div class="form-group">
                <label for="startDate">Start Date:</label>
                <input type="date" id="startDate" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="endDate">End Date:</label>
                <input type="date" id="endDate" name="end_date" required>
            </div>
            <button type="submit" class="btn-submit">Set Budget</button>
        </form>
    </section>

    <section class="table-section">
        <h2>Current Budgets</h2>
        <table>
            <thead>
                <tr>
                    <th>Budget ID</th>
                    <th>Category</th>
                    <th>Initial Amount</th>
                    <th>Expenses</th>
                    <th>Remaining Amount</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $budgetResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['budget_id'] ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td>Rs.<?= $row['amount'] ?></td>
                        <td>Rs.<?= $row['total_expenses'] ?></td>
                        <td>Rs.<?= max(0, $row['amount'] - $row['total_expenses']) ?></td>
                        <td><?= $row['start_date'] ?></td>
                        <td><?= $row['end_date'] ?></td>
                        <td>
                            <a href="edit_budget.php?id=<?= $row['budget_id'] ?>" class="btn btn-edit btn-info">Edit</a>
                            <a href="delete_budget.php?id=<?= $row['budget_id'] ?>" class="btn btn-danger btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</div>

<?php include_once(DIR_URL."include/footer.php"); ?>
