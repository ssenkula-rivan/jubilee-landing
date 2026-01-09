<?php
// Job Application Form Handler
// Receives: fullName, email, phone, education, experience, motivation, cv (file)

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
$education = isset($_POST['education']) ? htmlspecialchars(trim($_POST['education'])) : '';
$experience = isset($_POST['experience']) ? htmlspecialchars(trim($_POST['experience'])) : '';
$motivation = isset($_POST['motivation']) ? htmlspecialchars(trim($_POST['motivation'])) : 'Not provided';

// Validate required fields
if (empty($fullName) || empty($email) || empty($phone) || empty($education) || empty($experience)) {
    echo json_encode(["success" => false, "message" => "Please fill all required fields"]);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email address"]);
    exit;
}

// Handle CV upload
$cv_attachment = "";
$cv_filename = "";
$cv_type = "";

if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    $max_size = 10 * 1024 * 1024; // 10MB
    
    $file_type = $_FILES['cv']['type'];
    $file_size = $_FILES['cv']['size'];
    $file_name = $_FILES['cv']['name'];
    $file_tmp = $_FILES['cv']['tmp_name'];
    
    // Validate file type
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(["success" => false, "message" => "Invalid file type. Only PDF, JPG, PNG allowed"]);
        exit;
    }
    
    // Validate file size
    if ($file_size > $max_size) {
        echo json_encode(["success" => false, "message" => "File too large. Maximum 10MB allowed"]);
        exit;
    }
    
    $cv_attachment = file_get_contents($file_tmp);
    $cv_filename = $file_name;
    $cv_type = $file_type;
} else {
    echo json_encode(["success" => false, "message" => "Please upload your CV"]);
    exit;
}

// Build email
$subject = "New Job Application - Sales Agent: $fullName";
$boundary = md5(time());

// Email headers
$headers = "From: $from_email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

// Email body
$body = "--$boundary\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";

$body .= "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { background: #c41e3a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #c41e3a; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class='header'>
        <h2>New Job Application - Sales Agent</h2>
    </div>
    <div class='content'>
        <div class='field'><span class='label'>Full Name:</span> $fullName</div>
        <div class='field'><span class='label'>Email:</span> $email</div>
        <div class='field'><span class='label'>Phone:</span> $phone</div>
        <div class='field'><span class='label'>Education:</span> $education</div>
        <div class='field'><span class='label'>Experience:</span> $experience</div>
        <div class='field'><span class='label'>Motivation:</span><br>$motivation</div>
        <div class='field'><span class='label'>CV Attached:</span> $cv_filename</div>
    </div>
    <div class='footer'>
        Submitted via Jubilee Health Insurance Landing Page
    </div>
</body>
</html>
";

$body .= "\r\n\r\n--$boundary\r\n";

// Attach CV
$body .= "Content-Type: $cv_type; name=\"$cv_filename\"\r\n";
$body .= "Content-Disposition: attachment; filename=\"$cv_filename\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
$body .= chunk_split(base64_encode($cv_attachment));
$body .= "\r\n--$boundary--";

// Send email
if (mail($to_email, $subject, $body, $headers)) {
    echo json_encode(["success" => true, "message" => "Application submitted successfully! We will contact you soon."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to send application. Please try again."]);
}
?>
