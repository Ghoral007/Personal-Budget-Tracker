<?php  

include_once("../config/config.php");
include_once("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

$query = "SELECT profile_image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_details = $result->fetch_assoc();
$profile_image = $user_details['profile_image'] ?? 'default.jpg';

$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-t');

$income_query = "SELECT SUM(amount) as total_income FROM income WHERE user_id = ? AND date BETWEEN ? AND ?";
$expense_query = "SELECT SUM(amount) as total_expense FROM expenses WHERE user_id = ? AND date BETWEEN ? AND ?";

$stmt = $conn->prepare($income_query);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$total_income = $stmt->get_result()->fetch_assoc()['total_income'] ?? 0;

$stmt = $conn->prepare($expense_query);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$total_expense = $stmt->get_result()->fetch_assoc()['total_expense'] ?? 0;

$remaining_budget = $total_income - $total_expense;

$report_query = "SELECT e.date, 'Expense' as type, c.category_name, e.amount, e.notes 
                 FROM expenses e 
                 JOIN categories c ON e.category_id = c.category_id 
                 WHERE e.user_id = ? AND e.date BETWEEN ? AND ?
                 UNION 
                 SELECT i.date, 'Income' as type, c.category_name, i.amount, i.notes 
                 FROM income i 
                 JOIN categories c ON i.category_id = c.category_id 
                 WHERE i.user_id = ? AND i.date BETWEEN ? AND ?
                 ORDER BY date DESC";
$stmt = $conn->prepare($report_query);
$stmt->bind_param("ississ", $user_id, $start_date, $end_date, $user_id, $start_date, $end_date);
$stmt->execute();
$report_result = $stmt->get_result();
 // For Chart.
function getCategoryNames($conn, $user_id, $table) {
    $query = "SELECT c.category_name FROM categories c 
              JOIN $table t ON c.category_id = t.category_id 
              WHERE t.user_id = ? GROUP BY c.category_name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category_name'];
    }
    
    return $categories;
}

function getCategoryAmounts($conn, $user_id, $table, $start_date, $end_date) {
    $query = "SELECT SUM(t.amount) AS total, c.category_name FROM $table t 
              JOIN categories c ON c.category_id = t.category_id 
              WHERE t.user_id = ? AND t.date BETWEEN ? AND ? 
              GROUP BY c.category_name";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $user_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $amounts = [];
    while ($row = $result->fetch_assoc()) {
        $amounts[] = $row['total'];
    }
    
    return $amounts;
}
include_once(DIR_URL."include/header.php");   
include_once(DIR_URL."include/sidebar.php");   
include_once(DIR_URL."include/topbar.php");
?>

<div class="inc-container">
    <header>
        <h1>Reports</h1>
    </header>
    <form method="post">
        <label>From: <input type="date" name="start_date" value="<?php echo $start_date; ?>" required></label>
        <label>To: <input type="date" name="end_date" value="<?php echo $end_date; ?>" required></label>
        <button class="btn btn-primary" type="submit">Filter</button>
    </form>
    <section class="report-summary">
        <h2>Summary</h2>
        <div class="summary-details">
            <div class="summary-item">
                <h3>Total Income</h3>
                <p>Rs.<?php echo number_format($total_income, 2); ?></p>
            </div>
            <div class="summary-item">
                <h3>Total Expenses</h3>
                <p>Rs.<?php echo number_format($total_expense, 2); ?></p>
            </div>
            <div class="summary-item">
                <h3>Remaining Budget</h3>
                <p>Rs.<?php echo number_format($remaining_budget, 2); ?></p>
            </div>
        </div>
    </section>
    <section class="report-charts">
    <h2>Charts & Insights</h2>
    
    <div class="chart-wrapper">
        <div class="chart-box">
            <h3>Income Chart</h3>
            <canvas id="incomeChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Expense Chart</h3>
            <canvas id="expenseChart"></canvas>
        </div>
    </div>
</section>

    <section class="report-details">
        <h2>Detailed Report</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['type']; ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td>Rs.<?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['notes']); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>
    <section class="export-section">
        <h2>Export Report</h2>
            <form method="POST" action="export.php">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
            <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
            <button type="submit" name="export_pdf" class="btn-submit">Export as PDF</button>
            <button type="submit" name="export_csv" class="btn-submit">Export as CSV</button>
        </form>
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var ctxIncome = document.getElementById("incomeChart").getContext("2d");
            var ctxExpense = document.getElementById("expenseChart").getContext("2d");

            var incomeData = {
                labels: <?php echo json_encode(getCategoryNames($conn, $user_id, "income")); ?>,
                datasets: [{
                    data: <?php echo json_encode(getCategoryAmounts($conn, $user_id, "income", $start_date, $end_date)); ?>,
                    backgroundColor: ["#4CAF50", "#FFC107", "#2196F3", "#9C27B0", "#FF5722"]
                }]
            };

            var expenseData = {
                labels: <?php echo json_encode(getCategoryNames($conn, $user_id, "expenses")); ?>,
                datasets: [{
                    data: <?php echo json_encode(getCategoryAmounts($conn, $user_id, "expenses", $start_date, $end_date)); ?>,
                    backgroundColor: ["#FF5733", "#C70039", "#900C3F", "#581845", "#FF0000"]
                }]
            };

            new Chart(ctxIncome, {
                type: "pie",
                data: incomeData,
                options: { responsive: true }
            });

            new Chart(ctxExpense, {
                type: "pie",
                data: expenseData,
                options: { responsive: true }
            });
        });
    </script>
<?php include_once(DIR_URL."include/footer.php"); ?>
