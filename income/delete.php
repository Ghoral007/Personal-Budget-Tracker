<?php
include_once("../config/config.php");
include_once(DIR_URL . "config/database.php");
include_once(DIR_URL . "models/category.php");

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: " . BASE_URL . "income/trackincome.php");
    exit();
}

$res = deleteincome($conn, $id);
if (isset($res['success'])) {
    $_SESSION['success'] = "Income deleted successfully.";
} else {
    $_SESSION['error'] = $res['error'];
}

header("Location: " . BASE_URL . "income/trackincome.php");
exit();
?>