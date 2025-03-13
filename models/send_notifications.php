<?php
// Include database connection
include_once("../config/config.php");
include_once(DIR_URL . "config/database.php");

// Function to send notification
function sendNotification($userId, $message) {
    global $conn;

    // Store notification in the database
    $query = "INSERT INTO notifications (user_id, message, sent_at, is_read) VALUES (?, ?, NOW(), 0)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userId, $message);
    $stmt->execute();
}

// Check users who haven't logged in the last 6 hours
$inactiveUsersQuery = "SELECT user_id FROM users WHERE last_login < NOW() - INTERVAL 6 HOUR";
$result = $conn->query($inactiveUsersQuery);

while ($row = $result->fetch_assoc()) {
    $userId = $row['user_id'];
    sendNotification($userId, "Reminder: Please log in and update your budget.");
}

// Check users whose budget is below a certain threshold
$budgetQuery = "SELECT user_id, budget FROM users WHERE budget < 100"; // Example: Notify users with a budget below 100
$result = $conn->query($budgetQuery);

while ($row = $result->fetch_assoc()) {
    sendNotification($row['user_id'], "Warning: Your budget is running low.");
}

echo "Notifications sent successfully.";
?>

