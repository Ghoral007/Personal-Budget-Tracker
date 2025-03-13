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


    include_once(DIR_URL."include/header.php");   
    include_once(DIR_URL."include/sidebar.php");   
    include_once(DIR_URL."include/topbar.php");

?>

<div class="inc-container">
    <h2>Transactions</h2>
    <form id="transactionForm">
        <div class="form-group">
            <label for="transactionType">Transaction Type:</label>
            <select id="transactionType" required>
                <option value="">Select Type</option>
                <option value="Income">Income</option>
                <option value="Expense">Expense</option>
            </select>
        </div>
        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" id="amount" placeholder="Enter amount" required>
        </div>
        <div class="form-group">
            <label for="date">Transaction Date:</label>
            <input type="date" id="date" required>
        </div>
        <div class="form-group">
            <label for="notes">Notes:</label>
            <input type="text" id="notes" placeholder="Enter notes (optional)">
        </div>
        <button type="submit" class="btn btn-primary">Add Transaction</button>
    </form>

    <table>
        <thead>
            <tr>
                <th class="head">Transaction ID</th>
                <th class="head">Type</th>
                <th class="head">Amount</th>
                <th class="head">Date</th>
                <th class="head">Notes</th>
                <th class="head">Actions</th>
            </tr>
        </thead>
        <tbody id="transactionTable">
            <!-- Transactions will be added dynamically -->
        </tbody>
    </table>
</div>

<div class="inc-container">
    <h2>Recurring Transactions</h2>
    <form id="recurringForm">
        <div class="form-group">
            <label for="recurringType">Transaction Type:</label>
            <select id="recurringType" required>
                <option value="">Select Type</option>
                <option value="Income">Income</option>
                <option value="Expense">Expense</option>
            </select>
        </div>
        <div class="form-group">
            <label for="recurringAmount">Amount:</label>
            <input type="number" id="recurringAmount" placeholder="Enter amount" required>
        </div>
        <div class="form-group">
            <label for="interval">Recurrence Interval:</label>
            <select id="interval" required>
                <option value="">Select Interval</option>
                <option value="Daily">Daily</option>
                <option value="Weekly">Weekly</option>
                <option value="Monthly">Monthly</option>
            </select>
        </div>
        <div class="form-group">
            <label for="nextDue">Next Due Date:</label>
            <input type="date" id="nextDue" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Recurring Transaction</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Interval</th>
                <th>Next Due</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="recurringTable">
            <!-- Recurring transactions will be added dynamically -->
        </tbody>
    </table>
</div>
<?php include_once(DIR_URL."include/footer.php")   ?>
