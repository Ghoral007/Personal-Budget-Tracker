<?php 
include_once("config/config.php");
include_once("config/database.php");


if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$user_id = htmlspecialchars($_SESSION['user_id']);
$user_name = htmlspecialchars($_SESSION['user_name']);
$user_email = htmlspecialchars($_SESSION['user_email']);

// Fetch user profile image
$query = "SELECT profile_image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_details = $result->fetch_assoc();
$profile_image = $user_details['profile_image'] ?? 'default.jpg';

// Fetch total income
$incomeQuery = "SELECT COALESCE(SUM(amount), 0) AS total_income FROM income WHERE user_id = ?";
$stmt = $conn->prepare($incomeQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$incomeResult = $stmt->get_result()->fetch_assoc();
$total_income = $incomeResult['total_income'];

// Fetch total expenses
$expenseQuery = "SELECT COALESCE(SUM(amount), 0) AS total_expenses FROM expenses WHERE user_id = ?";
$stmt = $conn->prepare($expenseQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$expenseResult = $stmt->get_result()->fetch_assoc();
$total_expenses = $expenseResult['total_expenses'];

// Fetch total balance from accounts table
$balanceQuery = "SELECT COALESCE(SUM(balance), 0) AS total_balance FROM accounts WHERE user_id = ?";
$stmt = $conn->prepare($balanceQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$balanceResult = $stmt->get_result()->fetch_assoc();
$total_balance = $balanceResult['total_balance'];

// Fetch recent transactions (income + expenses)
$transactionsQuery = "
    (SELECT date, 'Income' AS type, amount, category_id, notes FROM income WHERE user_id = ?)
    UNION 
    (SELECT date, 'Expense' AS type, amount, category_id, notes FROM expenses WHERE user_id = ?)
    ORDER BY date DESC LIMIT 5";
$stmt = $conn->prepare($transactionsQuery);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$transactions = $stmt->get_result();

include_once(DIR_URL."include/header.php");   
include_once(DIR_URL."include/sidebar.php");   
include_once(DIR_URL."include/topbar.php");
?>

<!-- Content Area -->
<div class="content">
    <div class="container"> 
        <h2>Dashboard</h2>
        <h3>Welcome, <?php echo $user_name; ?>!</h3>
        <p>Your registered email: <?php echo $user_email; ?></p>

        <div class="row mt-4">
            <!-- Total Income -->
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Income</h5>
                        <p class="card-text">Rs. <?php echo number_format($total_income, 2); ?></p>
                    </div>
                </div>
            </div>
            <!-- Total Expenses -->
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Expenses</h5>
                        <p class="card-text">Rs. <?php echo number_format($total_expenses, 2); ?></p>
                    </div>
                </div>
            </div>
            <!-- Total Balance -->
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Balance</h5>
                        <p class="card-text">Rs. <?php echo number_format($total_balance, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="table-container mt-4">
            <h3>Recent Transactions</h3>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td>Rs. <?php echo number_format($row['amount'], 2); ?></td>
                            <td>
                                <?php 
                                $categoryQuery = "SELECT category_name FROM categories WHERE category_id = ?";
                                $stmt = $conn->prepare($categoryQuery);
                                $stmt->bind_param("i", $row['category_id']);
                                $stmt->execute();
                                $categoryResult = $stmt->get_result()->fetch_assoc();
                                echo htmlspecialchars($categoryResult['category_name'] ?? 'Uncategorized');
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <hr width="100%"/>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-7">
            <canvas id="incomeExpenseChart"></canvas>
        </div>
        <div class="col-md-4">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
<script>
    // Fetch data dynamically from PHP
    const incomeData = <?php echo json_encode([$total_income]); ?>;
    const expenseData = <?php echo json_encode([$total_expenses]); ?>;

    const ctx1 = document.getElementById('incomeExpenseChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: ['Current'],
            datasets: [
                { label: 'Income', data: incomeData, backgroundColor: 'green' },
                { label: 'Expenses', data: expenseData, backgroundColor: 'red' }
            ]
        }
    });

    // Fetch category-wise expense data
    <?php
    $categoryExpenseQuery = "SELECT c.category_name, SUM(e.amount) AS total FROM expenses e 
                             JOIN categories c ON e.category_id = c.category_id WHERE e.user_id = ? 
                             GROUP BY c.category_name";
    $stmt = $conn->prepare($categoryExpenseQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $categoryExpenses = $stmt->get_result();
    $categories = [];
    $categoryValues = [];
    while ($row = $categoryExpenses->fetch_assoc()) {
        $categories[] = $row['category_name'];
        $categoryValues[] = $row['total'];
    }
    ?>
    const categoryLabels = <?php echo json_encode($categories); ?>;
    const categoryData = <?php echo json_encode($categoryValues); ?>;

    const ctx2 = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{ data: categoryData, backgroundColor: ['blue', 'orange', 'purple', 'green', 'red','white','yellow','maroon','orange'] }]
        }
    });
</script>

<?php include_once(DIR_URL."include/footer.php"); ?>
