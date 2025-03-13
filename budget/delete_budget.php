<?php
include_once("../config/config.php");
include_once("../config/database.php");

session_start();

if (isset($_GET['id'])) {
    $budget_id = $_GET['id'];
    $query = "DELETE FROM budget WHERE budget_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $budget_id);
    if ($stmt->execute()) {
        header("Location: budget.php?success=Budget deleted");
    } else {
        header("Location: budget.php?error=Failed to delete");
    }
}
?>
