<!-- Top Bar -->
 <!-- Logo -->

 <?php

$name_parts = explode(" ", trim($user_name)); // Split name into parts
$initials = "";

if (count($name_parts) >= 2) {
    // Get first letter of first name and last name (surname)
    $initials = strtoupper(substr($name_parts[0], 0, 1)) . strtoupper(substr(end($name_parts), 0, 1));
} elseif (count($name_parts) == 1) {
    // If only one name is present, take the first letter of it
    $initials = strtoupper(substr($name_parts[0], 0, 1));
}

// Fetch unread notifications for the logged-in user
$userId = $_SESSION['user_id'];  // Assuming you store user_id in session

$notificationQuery = "SELECT id, message, sent_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY sent_at DESC";
$notificationStmt = $conn->prepare($notificationQuery);
$notificationStmt->bind_param("i", $userId);
$notificationStmt->execute();
$notificationResult = $notificationStmt->get_result();
$notifications = $notificationResult->fetch_all(MYSQLI_ASSOC);
$unreadCount = count($notifications);

?>
 <div class="topbar">
    <!-- Notification Bell -->
<div class="dropdown">
    <button class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="badge bg-danger"><?php echo $unreadCount; ?></span>
        <?php endif; ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <?php if ($unreadCount > 0): ?>
            <?php foreach ($notifications as $notification): ?>
                <li>
                    <a class="dropdown-item" href="#">
                        <?php echo htmlspecialchars($notification['message']); ?>
                        <br>
                        <small class="text-muted"><?php echo date('d M, H:i', strtotime($notification['sent_at'])); ?></small>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><a class="dropdown-item text-muted" href="#">No new notifications</a></li>
        <?php endif; ?>
    </ul>
</div>

    <!-- Search Bar -->
    <div class="input-group my-3 my-lg-0">
                    <input type="text" class="form-control" placeholder="Search....." aria-describedby="button-addon2">
                    <button class="btn btn-outline-secondary btn-primary text-white" type="button" id="button-addon2"><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>

    <!-- Quick Add Buttons -->
    <a href="<?php echo BASE_URL ?>income/addincome.php"> <button class="btn me-3">
        <i class="fa fa-plus-circle"></i> Add Income
    </button>
    <a href="<?php echo BASE_URL ?>expenses/addexpences.php"> <button class="btn me-3">
        <i class="fa fa-minus-circle"></i> Add Expense
    </button></a>

    <!-- User Dropdown -->
    <div class="dropdown">
        <button class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-user"></i> <?php echo $initials; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?php echo BASE_URL?>editprofile.php">Edit Profile</a></li>
            <li><a class="dropdown-item" href="<?php echo BASE_URL?>userinfo.php">View Profile</a></li>
            <li><a class="dropdown-item" href="<?php echo BASE_URL?>report/report.php">Report</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL?>./models/logout.php">Logout</a></li>
        </ul>
    </div>
</div>
<script>
document.querySelector('.dropdown-toggle').addEventListener('click', function() {
    fetch('mark_notifications_read.php', {
        method: 'POST'
    }).then(response => response.text()).then(data => {
        if (data === 'success') {
            document.querySelector('.badge').style.display = 'none'; // Hide badge
        }
    });
});
</script>

<!-- Top Bar End-->
