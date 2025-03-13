<?php
include_once("../config/config.php");
include_once(DIR_URL . "config/database.php");
include_once(DIR_URL . "models/category.php");

session_start();

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



// Get the category ID from the GET request
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: " . BASE_URL . "category/catagories.php");
    exit();
}

// Fetch the category details for the logged-in user
$query = "SELECT * FROM categories WHERE category_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    $_SESSION['error'] = "Category not found or unauthorized access.";
    header("Location: " . BASE_URL . "category/catagories.php");
    exit();
}

// Handle form submission for updating the category
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryName = trim($_POST['categoryName']);
    $categoryType = trim($_POST['categoryType']);

    // Validate inputs
    if (empty($categoryName) || empty($categoryType)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Update category in the database
        $query = "UPDATE categories SET category_name = ?, category_type = ? WHERE category_id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $categoryName, $categoryType, $id, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Category updated successfully.";
            header("Location: " . BASE_URL . "category/catagories.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update category: " . $stmt->error;
        }
    }
}
?>
<?php
include_once(DIR_URL . "include/header.php");
include_once(DIR_URL . "include/sidebar.php");
include_once(DIR_URL . "include/topbar.php");
?>
<!-- Edit Category Form -->
<div class="inc-container">
    <section class="form-section">
        <h2>Edit Category</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="categoryName">Category Name:</label>
                <input type="text" id="categoryName" name="categoryName" value="<?= htmlspecialchars($category['category_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="categoryType">Category Type:</label>
                <select id="categoryType" name="categoryType" required>
                    <option value="">Select Type</option>
                    <option value="Income" <?= $category['category_type'] === 'Income' ? 'selected' : '' ?>>Income</option>
                    <option value="Expense" <?= $category['category_type'] === 'Expense' ? 'selected' : '' ?>>Expense</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Category</button>
        </form>
    </section>
</div>

<?php include_once(DIR_URL . "include/footer.php"); ?>
