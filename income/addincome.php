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
<!-- Content Area -->

    <div class="modal-content">
        <h2>Add Income</h2>
        <form id="addIncomeForm" method="post" action="<?php echo BASE_URL ?>models/income.php">
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
            <button type="submit" name="submit" class="btn-submit">Add Income</button>
            <button type="button" class="btn-cancel" onclick="closeModal('addIncomeModal')">Cancel</button>
        </form>
    </div>

<?php include_once(DIR_URL."include/footer.php")   ?>