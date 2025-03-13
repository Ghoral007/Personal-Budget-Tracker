<?php 
/* To store the category */
function storeCategory($conn, $param) {
    extract($param);

    if (!isset($_SESSION['user_id'])) {
        return ["error" => "User is not logged in. Please log in to add a category."];
    }
    $user_id = $_SESSION['user_id'];
    
     /* Validation */
     if (empty($categoryName)) {
        return ["error" => "Category Name is required."];
    }
    if (empty($categoryType)) {
        return ["error" => "Category Type is required."];
    }
    if (!isset($user_id) || empty($user_id)) {
        return ["error" => "User ID is required to store a category."];
    }
    if (isCategoryUnique($conn, $user_id, $categoryName)) {
        return ["error" => "Category name already exists for this user."];
    }
   /* Use prepared statements to prevent SQL injection */
   $sql = "INSERT INTO categories (user_id, category_name, category_type) VALUES (?, ?, ?)";
   $stmt = $conn->prepare($sql);

   if ($stmt) {
       $stmt->bind_param("iss", $user_id, $categoryName, $categoryType);
       if ($stmt->execute()) {
           return ["success" => true];
       } else {
           return ["error" => "Database Error: " . $stmt->error];
       }
   } else {
       return ["error" => "Database Error: " . $conn->error];
   }
}

 

     /* Prepared statement for updating */
     $sql = "UPDATE categories SET category_name = ?, category_type = ? WHERE category_id = ?";
     $stmt = $conn->prepare($sql);
 
     if ($stmt) {
         $stmt->bind_param("ssi", $categoryName, $categoryType, $category_id);
 
         if ($stmt->execute()) {
             return ["success" => true];
         } else {
             return ["error" => "Database Error: " . $stmt->error];
         }
     } else {
         return ["error" => "Database Error: " . $conn->error];
     }

/* Check if category is unique for a user */
function isCategoryUnique($conn, $user_id, $categoryName) {
    $sql = "SELECT category_id FROM categories WHERE user_id = ? AND category_name = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("is", $user_id, $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
    return false; // Default to not unique if query fails
}
/* Delete Category Function */
function deleteCategory($conn, $id) {
    $sql = "DELETE FROM categories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return ["success" => true];
        } else {
            return ["error" => "Database Error: " . $stmt->error];
        }
    } else {
        return ["error" => "Database Error: " . $conn->error];
    }
}

function deleteincome($conn, $id) {
    // Get the income details (amount and account_id) before deletion
    $fetchQuery = "SELECT amount, account_id FROM income WHERE income_id = ?";
    $fetchStmt = $conn->prepare($fetchQuery);
    if ($fetchStmt) {
        $fetchStmt->bind_param("i", $id);
        $fetchStmt->execute();
        $incomeDetails = $fetchStmt->get_result()->fetch_assoc();

        if ($incomeDetails) {
            $amount = $incomeDetails['amount'];
            $account_id = $incomeDetails['account_id'];

            // Update the account balance
            $updateAccountQuery = "UPDATE accounts SET balance = balance - ? WHERE account_id = ?";
            $updateStmt = $conn->prepare($updateAccountQuery);
            $updateStmt->bind_param("di", $amount, $account_id);
            $updateStmt->execute();

            // Delete the income record
            $deleteQuery = "DELETE FROM income WHERE income_id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);

            if ($deleteStmt) {
                $deleteStmt->bind_param("i", $id);

                if ($deleteStmt->execute()) {
                    return ["success" => true];
                } else {
                    return ["error" => "Database Error: " . $deleteStmt->error];
                }
            } else {
                return ["error" => "Database Error: " . $conn->error];
            }
        } else {
            return ["error" => "Income not found"];
        }
    } else {
        return ["error" => "Database Error: " . $conn->error];
    }
}

function editincome($conn, $id, $newAmount, $newAccountId) {
    // Fetch the old income details
    $fetchQuery = "SELECT amount, account_id FROM income WHERE income_id = ?";
    $fetchStmt = $conn->prepare($fetchQuery);
    $fetchStmt->bind_param("i", $id);
    $fetchStmt->execute();
    $oldIncome = $fetchStmt->get_result()->fetch_assoc();

    if ($oldIncome) {
        $oldAmount = $oldIncome['amount'];
        $oldAccountId = $oldIncome['account_id'];

        // Update the old account balance
        $updateOldAccountQuery = "UPDATE accounts SET balance = balance - ? WHERE account_id = ?";
        $updateOldStmt = $conn->prepare($updateOldAccountQuery);
        $updateOldStmt->bind_param("di", $oldAmount, $oldAccountId);
        $updateOldStmt->execute();

        // Update the new account balance
        $updateNewAccountQuery = "UPDATE accounts SET balance = balance + ? WHERE account_id = ?";
        $updateNewStmt = $conn->prepare($updateNewAccountQuery);
        $updateNewStmt->bind_param("di", $newAmount, $newAccountId);
        $updateNewStmt->execute();

        // Update the income record
        $updateIncomeQuery = "UPDATE income SET amount = ?, account_id = ? WHERE income_id = ?";
        $updateIncomeStmt = $conn->prepare($updateIncomeQuery);
        $updateIncomeStmt->bind_param("dii", $newAmount, $newAccountId, $id);

        if ($updateIncomeStmt->execute()) {
            return ["success" => true];
        } else {
            return ["error" => "Database Error: " . $updateIncomeStmt->error];
        }
    } else {
        return ["error" => "Income not found"];
    }
}

