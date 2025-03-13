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


if (!isset($_GET['account_id'])) {
    header('Location: account.php');
    exit();
}

$account_id = $_GET['account_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_name = $_POST['account_name'];
    $balance = $_POST['balance'];

    $query = "UPDATE accounts SET account_name = ?, balance = ? WHERE account_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sdi', $account_name, $balance, $account_id);

    if ($stmt->execute()) {
        header('Location: account.php');
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    $query = "SELECT * FROM accounts WHERE account_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();
}
include_once(DIR_URL."include/header.php");   
include_once(DIR_URL."include/sidebar.php");   
include_once(DIR_URL."include/topbar.php");
?>

    <div class="inc-container">
        <h1>Edit Account</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="account_name">Account Name:</label>
                <input type="text" id="account_name" name="account_name" value="<?php echo htmlspecialchars($account['account_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="balance">Balance:</label>
                <input type="number" id="balance" name="balance" step="0.01" value="<?php echo $account['balance']; ?>" required>
            </div>
            <button type="submit" class="btn-submit">Save Changes</button>
        </form>
    </div>
    <?php include_once(DIR_URL."include/footer.php")   ?>
