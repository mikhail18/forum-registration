<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = 'localhost';
$dbname = 'berlin_poetry_forum';
$username = 'your_db_username';
$password = 'your_db_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validation functions
function validateName($name) {
    if (empty(trim($name))) {
        return 'Name is required';
    }
    if (strlen(trim($name)) < 2) {
        return 'Name must be at least 2 characters';
    }
    if (!preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/', $name)) {
        return 'Name contains invalid characters';
    }
    return null;
}

function validateEmail($email) {
    if (empty(trim($email))) {
        return 'Email is required';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email format';
    }
    return null;
}

function validateWhatsApp($phone) {
    if (empty(trim($phone))) {
        return 'WhatsApp number is required';
    }
    $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
    if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
        return 'Invalid phone number format';
    }
    return null;
}

// Get and sanitize input data
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');
$city = trim($_POST['city'] ?? '');
$country = trim($_POST['country'] ?? '');

// Validate all fields
$errors = [];

if ($error = validateName($firstName)) {
    $errors['firstName'] = $error;
}

if ($error = validateName($lastName)) {
    $errors['lastName'] = $error;
}

if ($error = validateEmail($email)) {
    $errors['email'] = $error;
}

if ($error = validateWhatsApp($whatsapp)) {
    $errors['whatsapp'] = $error;
}

if ($error = validateName($city)) {
    $errors['city'] = $error;
}

if ($error = validateName($country)) {
    $errors['country'] = $error;
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    // Insert new member
    $stmt = $pdo->prepare("
        INSERT INTO members (first_name, last_name, email, whatsapp_number, city, country, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $firstName,
        $lastName,
        $email,
        $whatsapp,
        $city,
        $country
    ]);

    $memberId = $pdo->lastInsertId();

    // Log successful registration
    error_log("New member registered: ID $memberId, Email: $email");

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'member_id' => $memberId
    ]);

} catch (PDOException $e) {
    error_log("Database error during registration: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}
?>