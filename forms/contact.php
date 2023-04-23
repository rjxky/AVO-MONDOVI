<?php
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;
 require 'vendor/autoload.php';

 $mail = PHPMailer()
 $mail->SMTPDebug = 2;                   // Enable verbose debug output              // TCP port to connect to
 $mail->isSMTP();
 $mail->Host = 'smtp.gmail.com';  //gmail SMTP server
 $mail->SMTPAuth = true; //to view proper logging details for success and error messages
 $mail->Host = 'smtp.gmail.com';  //gmail SMTP server
 $mail->Username = 'ricky.marcarino@gmail.com';   //email
 $mail->Password = 'cristianaseia' ;   //16 character obtained from app password created
 $mail->Port = 465;                    //SMTP port
 $mail->SMTPSecure = "ssl";

 //sender information
 $nome = $_POST['name'];
$cognome = $_POST['surname'];
$mail->setFrom($_POST['email'], $nome . " " . $cognome);

//receiver email address and name
$mail->addAddress('ricky.marcarino@gmail.com', 'Riccardo Marcarino'); 

// Add cc or bcc   
// $mail->addCC('email@mail.com');  
// $mail->addBCC('user@mail.com');  
 
 
$mail->isHTML(true);
 
$mail->Subject = 'PHPMailer SMTP test';
$mail->Body    = "<h4> PHPMailer the awesome Package </h4>
<b>PHPMailer is working fine for sending mail</b>
    <p> This is a tutorial to guide you on PHPMailer integration</p>";

// Send mail   
if (!$mail->send()) {
    echo 'Email not sent an error was encountered: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent.';
}

$mail->smtpClose();



$nome = $_POST['name'];
$cognome = $_POST['surname'];
$data_nascita = $_POST['datanascita'];
$luogo_nascita = $_POST['luogonascita'];
$numero_telefono = $_POST['phone'];
 
$destinatario = 'ricky.marcarino@gmail.com';
$oggetto = 'Nuova richiesta informazioni';
$messaggio = "Nome: " . $nome . "\r\n" .
            "Cognome: " . $cognome . "\r\n" .
            "Data di nascita: " . $data_nascita . "\r\n" .
            "Luogo di nascita: " . $luogo_nascita;
            "Numero di telefono: " . $numero_telefono;
 
$headers = 'From: ricky.marcarino@gmail.com' . "\r\n" .
           'Reply-To: ricky.marcarino@gmail.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();
 
           mail($destinatario, $oggetto, $messaggio, $headers)
 
?>