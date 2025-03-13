<?php
include_once("../config/config.php");
include_once("../config/database.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $query = "INSERT INTO budget (user_id, category_id, amount, start_date, end_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issss", $user_id, $category, $amount, $start_date, $end_date);
    
    if ($stmt->execute()) {
        header("Location: budget.php?success=Budget added");
    } else {
        header("Location: budget.php?error=Failed to add budget");
    }
}
?>
