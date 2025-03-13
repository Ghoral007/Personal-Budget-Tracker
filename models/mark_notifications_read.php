<?php
include_once("../config/config.php");
include_once(DIR_URL . "config/database.php");

$userId = $_SESSION['user_id'];

$query = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
?>
