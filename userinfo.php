<?php 
    include_once("config/config.php");
    include_once("config/database.php");

    
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    // Fetch user details from the session
    $user_id = htmlspecialchars($_SESSION['user_id']);
    $user_name = htmlspecialchars($_SESSION['user_name']);
    $user_email = htmlspecialchars($_SESSION['user_email']);

    // Fetch additional user details from the database (phone, birthdate, profile image)
    $query = "SELECT birthdate, contact_number, created_at, profile_image FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_details = $result->fetch_assoc();

    $contact_number = $user_details['contact_number'] ?? 'Not Available';
    $birthdate = $user_details['birthdate'] ?? 'Not Available';
    $profile_image = $user_details['profile_image'] ?? 'default.jpg'; // Default profile image if none is uploaded

    include_once(DIR_URL . "include/header.php");   
    include_once(DIR_URL . "include/sidebar.php");   
    include_once(DIR_URL . "include/topbar.php");
?>
<!-- User Profile Section -->
<div class="info-container">
    <div class="profile-header">
        <img src="<?= BASE_URL . "uploads/" . htmlspecialchars($profile_image) ?>" alt="Profile Image" width="100">
        <h2><?php echo $user_name; ?></h2>
        <p>User ID: <?php echo $user_id; ?></p>
    </div>

    <table class="table table-bordered border-primary">
        <thead class="table-dark">
            <tr>
                <th>Field</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Full Name</td>
                <td><?php echo $user_name; ?></td>
            </tr>
            <tr>
                <td>User ID</td>
                <td><?php echo $user_id; ?></td>
            </tr>
            <tr>
                <td>Email Address</td>
                <td><?php echo $user_email; ?></td>
            </tr>
            <tr>
                <td>Phone Number</td>
                <td><?php echo $contact_number; ?></td>
            </tr>
            <tr>
                <td>Date of Birth</td>
                <td><?php echo $birthdate; ?></td>
            </tr>
            <tr>
                <td>Account Created</td>
                <td><?php echo $user_details['created_at'] ?? 'Not Available'; ?></td>
            </tr>
        </tbody>
    </table>
</div>

<?php include_once(DIR_URL . "include/footer.php"); ?>
