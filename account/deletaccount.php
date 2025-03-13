<?php
include_once("../config/config.php");
include_once("../config/database.php");;

if (isset($_GET['account_id'])) {
    $account_id = $_GET['account_id'];

    $query = "DELETE FROM accounts WHERE account_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $account_id);

    if ($stmt->execute()) {
        header('Location: account.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
