<!-- Sidebar -->
<div class="sidebar d-flex flex-column p-3">
<h5 class="text-center">Personal Budget Tracker</h5>
    <!-- User Info Section -->
    <div class="user-info">
    <img src="<?= BASE_URL . "uploads/" . htmlspecialchars($profile_image) ?>" alt="Profile Image" width="100">
        <h5 class="mt-2"><?php echo $user_name; ?></h5>
        <p class="text-secondary">ID: <?php echo $user_id; ?></p>
    </div>

    <!-- Navigation Links -->
    <ul class="nav nav-pills flex-column mb-auto">
    <li>
            <a href="<?php echo BASE_URL ?>dashboard.php" class="nav-link">
                <i class="fa fa-home"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL?>userinfo.php" class="nav-link">
                <i class="fa fa-user-circle"></i> User Info
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL ?>account/account.php" class="nav-link">
                <i class="fa fa-wallet"></i> Manage Accounts
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL?>category/catagories.php" class="nav-link">
                <i class="fa fa-list"></i> Categories
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL?>budget/budget.php" class="nav-link">
                <i class="fa fa-calendar-alt"></i> Budgets
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL ?>income/trackincome.php" class="nav-link">
                <i class="fa fa-money-bill"></i> Track Income
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL?>expenses/expense.php" class="nav-link">
                <i class="fa fa-credit-card"></i> Track Expenses
            </a>
        </li>
      
        <!-- <li>
            <a href="<?php echo BASE_URL?>transactions/transactions.php" class="nav-link">
            <i class="fa-solid fa-exchange-alt"></i> Transactions
            </a>
        </li> -->
        <li>
            <a href="<?php echo BASE_URL?>setting/setting.php" class="nav-link">
                <i class="fa fa-cogs"></i> Settings
            </a>
        </li>
    </ul>
</div>