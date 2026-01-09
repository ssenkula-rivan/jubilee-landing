<?php
// Insurance Inquiry Form Handler
// Receives: fullName, email, phone, insuranceType, corporatePlan, smePlan, personalPlan, ageCategory, numberOfPeople, motivation

header('Content-Type: application/json');

// Configuration
$to_email = "george.kaggo@jubileeuganda.com";
$from_email = "noreply@jubileeuganda.com";

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Get form data
$fullName = isset($_POST['fullName']) ? htmlspecialchars(trim($_POST['fullName'])) : '';
$email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
$phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
$insuranceType = isset($_POST['insuranceType']) ? htmlspecialchars(trim($_POST['insuranceType'])) : '';
$corporatePlan = isset($_POST['corporatePlan']) ? htmlspecialchars(trim($_POST['corporatePlan'])) : '';
$smePlan = isset($_POST['smePlan']) ? htmlspecialchars(trim($_POST['smePlan'])) : '';
$personalPlan = isset($_POST['personalPlan']) ? htmlspecialchars(trim($_POST['personalPlan'])) : '';
$ageCategory = isset($_POST['ageCategory']) ? htmlspecialchars(trim($_POST['ageCategory'])) : '';
$numberOfPeople = isset($_POST['numberOfPeople']) ? htmlspecialchars(trim($_POST['numberOfPeople'])) : 'Not specified';
$motivation = isset($_POST['motivation']) ? htmlspecialchars(trim($_POST['motivation'])) : 'Not provided';

// Validate required fields
if (empty($fullName) || empty($email) || empty($phone) || empty($insuranceType)) {
    echo json_encode(["success" => false, "message" => "Please fill all required fields"]);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email address"]);
    exit;
}

// Determine selected plan
$selectedPlan = "";
if ($insuranceType === "Corporate" && !empty($corporatePlan)) {
    $selectedPlan = $corporatePlan;
} elseif ($insuranceType === "SME" && !empty($smePlan)) {
    $selectedPlan = $smePlan;
} elseif ($insuranceType === "Personal" && !empty($personalPlan)) {
    $selectedPlan = $personalPlan;
    if (!empty($ageCategory)) {
        $selectedPlan .= " - Age: $ageCategory";
    }
}

// Build email
$subject = "New Insurance Inquiry: $fullName - $insuranceType";

// Email headers
$headers = "From: $from_email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Email body
$body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { background: #c41e3a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .field { margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-left: 4px solid #c41e3a; }
        .label { font-weight: bold; color: #c41e3a; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; }
        .highlight { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class='header'>
        <h2>New Insurance Inquiry</h2>
        <p>$insuranceType Insurance</p>
    </div>
    <div class='content'>
        <div class='highlight'>
            <strong>Insurance Category:</strong> $insuranceType<br>
            <strong>Selected Plan:</strong> $selectedPlan<br>
            <strong>Number of People:</strong> $numberOfPeople
        </div>
        
        <h3 style='color: #c41e3a;'>Contact Information</h3>
        <div class='field'><span class='label'>Full Name:</span> $fullName</div>
        <div class='field'><span class='label'>Email:</span> <a href='mailto:$email'>$email</a></div>
        <div class='field'><span class='label'>Phone:</span> <a href='tel:$phone'>$phone</a></div>
        
        <h3 style='color: #c41e3a;'>Additional Message</h3>
        <div class='field'>$motivation</div>
    </div>
    <div class='footer'>
        Submitted via Jubilee Health Insurance Landing Page<br>
        <small>Please respond to this inquiry within 24 hours</small>
    </div>
</body>
</html>
";

// Send email
if (mail($to_email, $subject, $body, $headers)) {
    echo json_encode(["success" => true, "message" => "Inquiry submitted successfully! Our team will contact you within 24 hours."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to send inquiry. Please try again."]);
}
?>
