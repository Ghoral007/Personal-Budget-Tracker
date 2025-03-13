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



// Fetch expense categories (only "income" types)
$categoryQuery = "SELECT category_id, category_name FROM categories WHERE category_type = 'Income' AND user_id = ?";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$categories = $stmt->get_result();
 
     // Fetch accounts from the database
     $accountQuery = "SELECT account_id, account_name, balance FROM accounts WHERE user_id = ?";
     $accountStmt = $conn->prepare($accountQuery);
     $accountStmt->bind_param('i', $user_id);
     $accountStmt->execute();
     $accounts = $accountStmt->get_result();
 

    include_once(DIR_URL."include/header.php");   
    include_once(DIR_URL."include/sidebar.php");   
    include_once(DIR_URL."include/topbar.php");

?>
<!-- Top Bar End-->
<div class="inc-container">
        <h1>Track Income</h1>
        <?php include_once(DIR_URL . "include/alerts.php"); ?>
        <!-- Add Income Form -->
        <form id="add-income-form" method="post" action="<?php echo BASE_URL ?>models/income.php">
            <div class="form-group">
            <label for="incomeAmount">Amount:</label>
                <input type="number" id="incomeAmount" name="incomeAmount" placeholder="Enter amount" required>
            </div>

            <div class="form-group">
            <label for="incomeCategory">Category:</label>
                <select id="incomeCategory" name="incomeCategory" required>
                <option value="">-- Select Category --</option>
                    <?php while ($category = $categories->fetch_assoc()) { ?>
                        <option value="<?php echo $category['category_id']; ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php } ?>
                </select>
            </div>

            <div class="form-group">
            <label for="account">Account</label>
                <select id="account" name="account">
                <option value="">-- Select Account --</option>
                    <?php while ($account = $accounts->fetch_assoc()) { ?>
                    <option value="<?php echo $account['account_id']; ?>">
                        <?php echo htmlspecialchars($account['account_name']); ?> (Rs. <?php echo number_format($account['balance'], 2); ?>)
                    </option>
                <?php } ?>
                </select>
            </div>

            <div class="form-group">
            <label for="incomeDate">Date:</label>
            <input type="date" id="incomeDate" name="incomeDate" required>
            </div>

            <div class="form-group">
            <label for="incomeNotes">Notes:</label>
            <textarea id="incomeNotes" name="incomeNotes" placeholder="Add notes (optional)"></textarea>
            </div>

            <div class="actions">
                <button class="inc-button" type="submit">Add Income</button>
            </div>
        </form>

        <!-- Income Records Table -->
        <table>
            <thead>
                <tr><th>S.N.</th>
                    <th>Date</th>
                    <th>Source</th>
                    <th>Account</th>
                    <th>Amount</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            // Fetch income records
            $incomeQuery = "
                SELECT i.income_id, i.date, c.category_name, a.account_name, i.amount, i.notes 
                FROM income i
                INNER JOIN categories c ON i.category_id = c.category_id
                INNER JOIN accounts a ON i.account_id = a.account_id
                WHERE i.user_id = ?
                ORDER BY i.date DESC";
            $incomeStmt = $conn->prepare($incomeQuery);
            $incomeStmt->bind_param('i', $user_id);
            $incomeStmt->execute();
            $incomes = $incomeStmt->get_result();

            while ($income = $incomes->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($income['income_id']); ?></td>
                    <td><?php echo htmlspecialchars($income['date']); ?></td>
                    <td><?php echo htmlspecialchars($income['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($income['account_name']); ?></td>
                    <td>Rs. <?php echo number_format($income['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($income['notes']); ?></td>
                    <td>
                    <a href="<?php echo BASE_URL . 'income/edit.php?id=' . $income['income_id']; ?>" class="btn btn-info">Edit</a>
                    <a href="<?php echo BASE_URL . 'income/delete.php?id=' . $income['income_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this Income?')">Delete</a>
                </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

<?php include_once(DIR_URL."include/footer.php")   ?>

