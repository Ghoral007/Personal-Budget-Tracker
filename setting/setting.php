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

// Fetch user details
$query = "SELECT username, email, contact_number, profile_image, currency, theme FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_details = $result->fetch_assoc();

$full_name = htmlspecialchars($user_details['full_name'] ?? '');
$email = htmlspecialchars($user_details['email'] ?? '');
$phone = htmlspecialchars($user_details['phone'] ?? '');
$profile_image = htmlspecialchars($user_details['profile_image'] ?? 'default.jpg');
$currency = htmlspecialchars($user_details['currency'] ?? 'NPR');
$theme = htmlspecialchars($user_details['theme'] ?? 'light');

include_once(DIR_URL."include/header.php");   
include_once(DIR_URL."include/sidebar.php");   
include_once(DIR_URL."include/topbar.php");
?>

<div class="inc-container">
    <header class="sheader">
        <h1 class="sh1">Settings</h1>
    </header>

    <section class="settings-section">
        <h2>Personal Information</h2>
        <form id="personalInfoForm" method="post" action="update_settings.php">
            <div class="form-group">
                <label for="fullName">Full Name:</label>
                <input type="text" id="fullName" name="full_name" value="<?php echo $full_name; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>">
            </div>
            <button type="submit" class="btn-submit">Update Information</button>
        </form>
    </section>

    <section class="preferences-section">
        <h2>Preferences</h2>
        <form id="preferencesForm" method="post" action="update_preferences.php">
            <div class="form-group">
                <label for="currency">Default Currency:</label>
                <select id="currency" name="currency">
                    <option value="USD" <?php echo ($currency == 'USD') ? 'selected' : ''; ?>>USD</option>
                    <option value="EUR" <?php echo ($currency == 'EUR') ? 'selected' : ''; ?>>EUR</option>
                    <option value="NPR" <?php echo ($currency == 'NPR') ? 'selected' : ''; ?>>NPR</option>
                    <option value="INR" <?php echo ($currency == 'INR') ? 'selected' : ''; ?>>INR</option>
                </select>
            </div>
            <div class="form-group">
                <label for="theme">Theme:</label>
                <select id="theme" name="theme">
                    <option value="light" <?php echo ($theme == 'light') ? 'selected' : ''; ?>>Light</option>
                    <option value="dark" <?php echo ($theme == 'dark') ? 'selected' : ''; ?>>Dark</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Save Preferences</button>
        </form>
    </section>

    <section class="password-section">
        <h2>Change Password</h2>
        <form id="changePasswordForm" method="post" action="update_password.php">
            <div class="form-group">
                <label for="currentPassword">Current Password:</label>
                <input type="password" id="currentPassword" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="newPassword">New Password:</label>
                <input type="password" id="newPassword" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password:</label>
                <input type="password" id="confirmPassword" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-submit">Change Password</button>
        </form>
    </section>

    <section class="danger-section">
        <h2>Danger Zone</h2>
        <form method="post" action="delete_account.php">
            <button type="submit" class="btn-delete-account">Delete Account</button>
        </form>
    </section>
</div>

<?php include_once(DIR_URL."include/footer.php"); ?>
