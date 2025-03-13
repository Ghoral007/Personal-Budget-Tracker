<?php
include_once("../config/config.php");
include_once(DIR_URL . "config/database.php");
include_once(DIR_URL . "models/category.php");

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: " . BASE_URL . "category/catagories.php");
    exit();
}

$res = deleteCategory($conn, $id);
if (isset($res['success'])) {
    $_SESSION['success'] = "Category deleted successfully.";
} else {
    $_SESSION['error'] = $res['error'];
}

header("Location: " . BASE_URL . "category/catagories.php");
exit();
