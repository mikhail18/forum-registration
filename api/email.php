<?php
// Email configuration
$emailConfig = [
    'smtp_host' => 'your-smtp-server.com',
    'smtp_port' => 587,
    'smtp_username' => 'service@ugetra.org',
    'smtp_password' => 'titanM!c3d4f6g5',
    'from_email' => 'service@ugetra.org',
    'from_name' => 'Berlin Poetry Forum'
];

function sendConfirmationEmail($member) {
    global $emailConfig;
    
    $language = $member['language'];
    $firstName = $member['first_name'];
    $lastName = $member['last_name'];
    $email = $member['email'];
    
    // Get email content based on language
    $emailContent = getEmailContent($language, $firstName, $lastName);
    
    // Email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $emailConfig['from_name'] . ' <' . $emailConfig['from_email'] . '>',
        'Reply-To: ' . $emailConfig['from_email'],
        'X-Mailer: PHP/' . phpversion()
    ];
    
    try {
        // Send email using PHP's mail() function
        $success = mail(
            $email,
            $emailContent['subject'],
            $emailContent['body'],
            implode("\r\n", $headers)
        );
        
        if ($success) {
            error_log("Confirmation email sent successfully to: $email");
            return true;
        } else {
            error_log("Failed to send confirmation email to: $email");
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        return false;
    }
}

function getEmailContent($language, $firstName, $lastName) {
    $fullName = $firstName . ' ' . $lastName;
    
    if ($language === 'ru') {
        return [
            'subject' => 'Добро пожаловать в Berlin Poetry Forum!',
            'body' => getRussianEmailTemplate($fullName)
        ];
    } else {
        return [
            'subject' => 'Welcome to Berlin Poetry Forum!',
            'body' => getEnglishEmailTemplate($fullName)
        ];
    }
}

function getEnglishEmailTemplate($fullName) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome to Berlin Poetry Forum</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .footer { text-align: center; margin-top: 30px; padding: 20px; background: #333; color: white; border-radius: 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .highlight { color: #667eea; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>📚 Berlin Poetry Forum</h1>
            <p>Where words come alive in the heart of Berlin</p>
        </div>
        
        <div class="content">
            <h2>Welcome, ' . htmlspecialchars($fullName) . '!</h2>
            
            <p>We are thrilled to welcome you to the <span class="highlight">Berlin Poetry Forum</span> community! Your registration has been successfully completed.</p>
            
            <p>As a member of our vibrant poetry community, you will:</p>
            <ul>
                <li>🎤 Participate in weekly poetry readings and open mic nights</li>
                <li>🤝 Connect with fellow poetry enthusiasts from around the world</li>
                <li>🌟 Share your own poetry and receive constructive feedback</li>
            </ul>

            <p>If you have any questions or need assistance, please don\'t hesitate to reach out to us.</p>
            
            <p><strong>The Berlin Poetry Forum Team</strong></p>
        </div>
        
        <div class="footer">
            <p>© 2025 Berlin Poetry Forum | Berlin, Germany</p>
            <p>You received this email because you registered for our poetry  forum.</p>
        </div>
    </body>
    </html>';
}

function getRussianEmailTemplate($fullName) {
    return '
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Добро пожаловать в Berlin Poetry Forum</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .footer { text-align: center; margin-top: 30px; padding: 20px; background: #333; color: white; border-radius: 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .highlight { color: #667eea; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>📚 Berlin Poetry Forum</h1>
            <p>Где слова оживают в сердце Берлина</p>
        </div>
        
        <div class="content">
            <h2>Добро пожаловать, ' . htmlspecialchars($fullName) . '!</h2>
            
            <p>Мы рады приветствовать вас в сообществе <span class="highlight">Berlin Poetry Forum</span>! Ваша регистрация успешно завершена.</p>
            
            <p>Как член нашего яркого поэтического сообщества, вы сможете:</p>
            <ul>
                <li>🎤 Участвовать в ежедневных поэтических чтениях и открытых микрофонах</li>
                 <li>🤝 Общаться с единомышленниками со всего мира</li>
                <li>🌟 Делиться своими стихами и получать конструктивную обратную связь</li>
            </ul>
            
            <p>Если у вас есть вопросы или вам нужна помощь, не стесняйтесь обращаться к нам.</p>
            
           <p><strong>Команда Berlin Poetry Forum</strong></p>
        </div>
        
        <div class="footer">
            <p>© 2025 Berlin Poetry Forum | Берлин, Германия</p>
            <p>Вы получили это письмо, потому что зарегистрировались для участия.</p>
        </div>
    </body>
    </html>';
}

// Alternative function using PHPMailer (if you prefer SMTP)
function sendConfirmationEmailSMTP($member) {
    global $emailConfig;
    
    // Uncomment and use this if you have PHPMailer installed
    /*
    require_once 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $emailConfig['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $emailConfig['smtp_username'];
        $mail->Password   = $emailConfig['smtp_password'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $emailConfig['smtp_port'];
        $mail->CharSet    = 'UTF-8';
        
        // Recipients
        $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
        $mail->addAddress($member['email'], $member['first_name'] . ' ' . $member['last_name']);
        
        // Content
        $emailContent = getEmailContent($member['language'], $member['first_name'], $member['last_name']);
        $mail->isHTML(true);
        $mail->Subject = $emailContent['subject'];
        $mail->Body    = $emailContent['body'];
        
        $mail->send();
        error_log("SMTP confirmation email sent successfully to: " . $member['email']);
        return true;
        
    } catch (Exception $e) {
        error_log("SMTP email sending error: {$mail->ErrorInfo}");
        return false;
    }
    */
    
    return false; // Remove this when implementing PHPMailer
}
?>