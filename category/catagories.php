<?php

include_once("../config/config.php");
include_once(DIR_URL . "config/database.php");
include_once(DIR_URL . "models/category.php");

if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Fetch user details from the session
$user_id = htmlspecialchars($_SESSION['user_id']);
$user_name = htmlspecialchars($_SESSION['user_name']);
$user_email = htmlspecialchars($_SESSION['user_email']);
// Fetch additional user details from the database (profile image)
$query = "SELECT profile_image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_details = $result->fetch_assoc();
$profile_image = $user_details['profile_image'] ?? 'default.jpg'; // Default profile image if none is uploaded

$existingCategoryName = "";
if (isset($_GET['category_id'])) {
    $cat_id = intval($_GET['category_id']);
    $query = "SELECT category_name FROM categories WHERE category_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $cat_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $existingCategoryName = htmlspecialchars($row['category_name']);
    }
}


if (isset($_POST['submit'])) {
    $res = storeCategory($conn, $_POST);
    if (isset($res['success'])) {
        $_SESSION['success'] = "Category has been created successfully.";
    } else {
        $_SESSION['error'] = $res['error'];
    }

    // Redirect to avoid form resubmission
    header("Location: " . BASE_URL . "category/catagories.php");
    exit();
}



include_once(DIR_URL . "include/header.php");
include_once(DIR_URL . "include/sidebar.php");
include_once(DIR_URL . "include/topbar.php");
?>
<!-- Content Area -->
<div class="inc-container">
    <header>
        <h1>Manage Categories</h1>
    </header>

    <!-- Form Section -->
    <section class="form form-section">
        <h2>Add New Category</h2>

        <!-- Display Alerts -->
        <?php include_once(DIR_URL . "include/alerts.php"); ?>

        <!-- Form -->
        <form id="addCategoryForm" method="post" action="<?php echo BASE_URL ?>category/catagories.php">
            <div class="form-group">
                <label for="categoryName">Category Name:</label>
                <input type="text" id="categoryName" name="categoryName" placeholder="Enter category name" required>
            </div>
            <div class="form-group">
                <label for="categoryType">Category Type:</label>
                <select id="categoryType" name="categoryType" required>
                    <option value="">Select Type</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>
            <button type="submit" name="submit" class="btn btn-submit btn-primary">Add Category</button>
        </form>
    </section>

    <!-- Categories Table Section -->
    <section class="table-section">
        <h2>Existing Categories</h2>
        <table>
            <thead>
                <tr>
                    <th>Category ID</th>
                    <th>Category Name</th>
                    <th>Category Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
     // Fetch categories
$query = "SELECT * FROM categories WHERE user_id = ?";
$categoryStmt = $conn->prepare($query);
$categoryStmt->bind_param("i", $user_id);
$categoryStmt->execute();
$result = $categoryStmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['category_id']) . "</td>";
        echo "<td>" .($row['category_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category_type']) . "</td>";
        echo "<td>
                <a href='" . BASE_URL . "category/edit.php?id=" . $row['category_id'] . "' class='btn btn-info'>Edit</a>
                <a href='" . BASE_URL . "category/delete.php?id=" . $row['category_id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this category?\")'>Delete</a>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No categories found.</td></tr>";
}
                ?>
            </tbody>
        </table>
    </section>
</div>

<?php include_once(DIR_URL . "include/footer.php"); ?>
