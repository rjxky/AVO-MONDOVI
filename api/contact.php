<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';

$wpConfigPath = __DIR__ . '/../wp-config.php';
if (is_file($wpConfigPath)) {
    require_once $wpConfigPath;
}

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Metodo non consentito.',
    ]);
    exit;
}

function post_value(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function smtp_setting(string $envName, string $constantName, $default = '')
{
    $envValue = getenv($envName);
    if ($envValue !== false && $envValue !== '') {
        return $envValue;
    }

    if (defined($constantName)) {
        return constant($constantName);
    }

    return $default;
}

$name = post_value('name');
$surname = post_value('surname');
$email = post_value('email');
$birthDate = post_value('datanascita');
$birthPlace = post_value('luogonascita');
$phone = post_value('phone');

if (
    $name === '' ||
    $surname === '' ||
    $email === '' ||
    $birthDate === '' ||
    $birthPlace === '' ||
    $phone === ''
) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'Compila tutti i campi obbligatori.',
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'Indirizzo email non valido.',
    ]);
    exit;
}

$smtpHost = (string) smtp_setting('SMTP_HOST', 'SMTP_HOST', 'smtp.libero.it');
$smtpPort = (int) smtp_setting('SMTP_PORT', 'SMTP_PORT', 465);
$smtpUsername = (string) smtp_setting('SMTP_USERNAME', 'SMTP_USERNAME', 'avomondovi@libero.it');
$smtpPassword = (string) smtp_setting('SMTP_PASSWORD', 'SMTP_PASSWORD', '');
$smtpFromEmail = (string) smtp_setting('SMTP_FROM_EMAIL', 'SMTP_FROM_EMAIL', 'avomondovi@libero.it');
$smtpFromName = (string) smtp_setting('SMTP_FROM_NAME', 'SMTP_FROM_NAME', 'AVO Mondovi');
$smtpToEmail = (string) smtp_setting('CONTACT_TO_EMAIL', 'CONTACT_TO_EMAIL', 'avomondovi@libero.it');
$smtpSecure = strtolower((string) smtp_setting('SMTP_SECURE', 'SMTP_SECURE', 'ssl'));

if (
    $smtpHost === '' ||
    $smtpUsername === '' ||
    $smtpPassword === '' ||
    $smtpFromEmail === '' ||
    $smtpToEmail === ''
) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Configurazione email incompleta sul server.',
    ]);
    exit;
}

$subject = 'Nuova richiesta informazioni AVO Mondovi';
$bodyLines = [
    'Nome: ' . $name,
    'Cognome: ' . $surname,
    'Email: ' . $email,
    'Data di nascita: ' . $birthDate,
    'Luogo di nascita: ' . $birthPlace,
    'Numero di telefono: ' . $phone,
];

$body = implode("\r\n", $bodyLines);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->Port = $smtpPort;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;
    $mail->CharSet = 'UTF-8';

    if ($smtpSecure === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mail->setFrom($smtpFromEmail, $smtpFromName);
    $mail->addAddress($smtpToEmail);
    $mail->addReplyTo($email, $name . ' ' . $surname);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AltBody = $body;
    $mail->send();

    echo json_encode([
        'ok' => true,
        'message' => 'La tua richiesta è stata inviata. Grazie!',
    ]);
} catch (Exception $exception) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Errore nell\'invio dell\'email.',
        'detail' => $mail->ErrorInfo,
    ]);
}
