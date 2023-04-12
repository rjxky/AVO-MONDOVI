<?php
 
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