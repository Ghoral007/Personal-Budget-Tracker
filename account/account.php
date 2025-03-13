<?php
 include_once("../config/config.php");
 include_once("../config/database.php");

 if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: " . BASE_URL . "index.php");
    exit();
}

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



// Fetch accounts from the database
$query = "SELECT * FROM accounts WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();




include_once(DIR_URL."include/header.php");   
include_once(DIR_URL."include/sidebar.php");   
include_once(DIR_URL."include/topbar.php");
?>

   <div class="inc-container">
        <header>
            <h1>Manage Accounts</h1>
        </header>
        <section>
            <h2>Add New Account</h2>
            <?php include_once(DIR_URL . "include/alerts.php"); ?>
            <form action="<?php echo BASE_URL ?>models/addaccount.php" method="POST">
                <div class="form-group">
                    <label for="account_name">Account Name:</label>
                    <input type="text" id="account_name" name="account_name" placeholder="Enter account name" required>
                </div>
                <div class="form-group">
                    <label for="balance">Initial Balance:</label>
                    <input type="number" id="balance" name="balance" step="0.01" placeholder="Enter initial balance" required>
                </div>
                <button type="submit" class="btn-submit">Add Account</button>
            </form>
        </section>
        <section>
            <h2>Your Accounts</h2>
            <table class="accounts-table">
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th>Balance</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['account_name']); ?></td>
                            <td><?php echo number_format($row['balance'], 2); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <a href="editaccount.php?account_id=<?php echo $row['account_id']; ?>">Edit</a> |
                                <a href="deletaccount.php?account_id=<?php echo $row['account_id']; ?>" onclick="return confirm('Are you sure you want to delete this account?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

        
    </div>
    <?php include_once(DIR_URL."include/footer.php")   ?>