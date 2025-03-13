<?php
include_once("config/config.php");
include_once("config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = trim($_POST['phone']); 
    
    // Remove non-numeric characters
    $phone = preg_replace('/\D/', '', $phone);

    // Remove leading zero (if any) but do NOT add +977 
    if (substr($phone, 0, 1) == "0") {
        $phone = substr($phone, 1);
    }

    // Check if the phone number exists in the database
    $query = "SELECT user_id FROM users WHERE contact_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $otp = rand(100000, 999999);
        $expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Store OTP in database using the original format (without +977)
        $query = "INSERT INTO otp_verifications (user_id, contact_number, otp, expires_at) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $user['user_id'], $phone, $otp, $expires_at);
        $stmt->execute();

        $_SESSION['phone'] =$phone;
        // Send OTP via WhatsApp (Adding +977 for sending purposes)
        $formattedPhone = "+977" . $phone;
        $message = "Your OTP for password reset is: $otp. It is valid for 5 minutes.";
        $response = sendWhatsAppMessage($formattedPhone, $message);

        if ($response) {
            header("Location: verify_otp.php?success=OTP sent successfully!");
            exit();
        } else {
            header("Location: forget.php?error=Failed to send OTP. Try again!");
            exit();
        }
    } else {
        header("Location: forget.php?error=Phone number not found!");
        exit();
    }
}

// Function to send WhatsApp message using Twilio API
function sendWhatsAppMessage($phone, $message) {
    $account_sid = "ACb639a91bfaae18d380ed7e86f17f4f87";
    $auth_token = "a67d2a6577ba928e3eded7b5a0025514";
    $whatsapp_number = "+14155238886";

    $url = "https://api.twilio.com/2010-04-01/Accounts/$account_sid/Messages.json";
    
    $data = [
        "To" => "whatsapp:+977$phone", // WhatsApp requires country code
        "From" => "whatsapp:$whatsapp_number",
        "Body" => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "$account_sid:$auth_token");
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}
?>
