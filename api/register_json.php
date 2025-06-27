<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// JSON file configuration
$dataFile = 'members.json';
$lockFile = 'members.lock';

// Function to read members from JSON file
function readMembers($dataFile) {
    if (!file_exists($dataFile)) {
        // Create empty JSON file if it doesn't exist
        if (file_put_contents($dataFile, '[]', LOCK_EX) === false) {
            throw new Exception('Failed to create data file');
        }
        return [];
    }
    
    $jsonData = file_get_contents($dataFile);
    if ($jsonData === false) {
        throw new Exception('Failed to read data file');
    }
    
    $data = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data in file');
    }
    
    return $data ?? [];
}

// Function to write members to JSON file with file locking
function writeMembers($dataFile, $lockFile, $members) {
    // Create lock file to prevent concurrent writes
    $lockHandle = fopen($lockFile, 'w');
    if (!$lockHandle || !flock($lockHandle, LOCK_EX)) {
        throw new Exception('Could not acquire file lock');
    }
    
    try {
        $jsonData = json_encode($members, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonData === false) {
            throw new Exception('Failed to encode JSON data');
        }
        
        if (file_put_contents($dataFile, $jsonData, LOCK_EX) === false) {
            throw new Exception('Failed to write data file');
        }
    } finally {
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
    }
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

function validateLanguage($language) {
    $allowedLanguages = ['ru', 'en'];
    if (!in_array($language, $allowedLanguages)) {
        return 'Language must be either "ru" or "en"';
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
$language = trim($_POST['language'] ?? 'en');

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

if ($error = validateLanguage($language)) {
    $errors['language'] = $error;
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    // Read existing members
    $members = readMembers($dataFile);
    
    // Check if email already exists
    foreach ($members as $member) {
        if (strtolower($member['email']) === strtolower($email)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }
    }

    // Generate unique member ID
    $memberId = count($members) > 0 ? max(array_column($members, 'id')) + 1 : 1;

    // Create new member record
    $newMember = [
        'id' => $memberId,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'whatsapp_number' => $whatsapp,
        'city' => $city,
        'country' => $country,
        'language' => $language,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Add new member to array
    $members[] = $newMember;

    // Write updated members to file
    writeMembers($dataFile, $lockFile, $members);

    // Send confirmation email
    require_once 'email.php';
    sendConfirmationEmail($newMember);

    // Log successful registration
    error_log("New member registered: ID $memberId, Email: $email");

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'member_id' => $memberId
    ]);

} catch (Exception $e) {
    error_log("File operation error during registration: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}
?>