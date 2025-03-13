<?php
include_once("config/config.php");
include_once("config/database.php");


if (!isset($_SESSION['user_id'])) {
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


// Fetch user details
$query = "SELECT username, email, birthdate, contact_number, profile_image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $contact_number = trim($_POST['contact_number']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $profile_image = $user['profile_image']; // Keep old image by default

    // Image Upload Handling
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = __DIR__ . "/uploads/";
        $image_name = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_image = $image_name;
        } else {
            $_SESSION['error'] = "Failed to upload image. Allowed types: JPG, JPEG, PNG, GIF.";
        }
    }

    if (empty($username) || empty($email) || empty($date_of_birth) || empty($contact_number)) {
        $_SESSION['error'] = "All fields except password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
    } elseif (!empty($password) && ($password !== $confirm_password)) {
        $_SESSION['error'] = "Passwords do not match.";
    } else {
        // Update user details
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET username = ?, email = ?, birthdate = ?, contact_number = ?, password = ?, profile_image = ? WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssssssi", $username, $email, $date_of_birth, $contact_number, $hashed_password, $profile_image, $user_id);
        } else {
            $updateQuery = "UPDATE users SET username = ?, email = ?, birthdate = ?, contact_number = ?, profile_image = ? WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sssssi", $username, $email, $date_of_birth, $contact_number, $profile_image, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully.";
            $_SESSION['user_name'] = $username;
            $_SESSION['user_email'] = $email;
        } else {
            $_SESSION['error'] = "Failed to update profile.";
        }
    }
}

include_once(DIR_URL . "include/header.php");
include_once(DIR_URL . "include/sidebar.php");
include_once(DIR_URL . "include/topbar.php");
?>

<div class="inc-container">
    <h1 class="title">Edit Profile</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Profile Image:</label>
            <div>
                <img src="<?= BASE_URL . "uploads/" . htmlspecialchars($user['profile_image']) ?>" alt="Profile Image" width="100">
            </div>
            <input type="file" name="profile_image">
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="form-group">
            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($user['birthdate']) ?>" required>
        </div>
        <div class="form-group">
            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($user['contact_number']) ?>" required>
        </div>
        <div class="form-group">
            <label for="password">New Password (leave blank if not changing):</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>
        <div class="actions">
            <button type="submit" class="inc-button">Update Profile</button>
        </div>
    </form>
</div>

<?php include_once(DIR_URL . "include/footer.php"); ?>
