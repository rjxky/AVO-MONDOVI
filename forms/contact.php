<?php
require_once('wp-load.php');
$nome = $_POST["name"];
$cognome = $_POST["surname"];
$data_nascita = $_POST["datanascita"];
$luogo_nascita = $_POST["luogonascita"];
$numero_telefono = $_POST["phone"];

$destinatario = "ricky.marcarino@gmail.com";
$oggetto = "Nuova richiesta informazioni";
$messaggio =
    "Nome: " .
    $nome .
    "\r\n" .
    "Cognome: " .
    $cognome .
    "\r\n" .
    "Data di nascita: " .
    $data_nascita .
    "\r\n" .
    "Luogo di nascita: " .
    $luogo_nascita;
"Numero di telefono: " . $numero_telefono;
error_reporting(-1);
ini_set("display_errors", "On");
set_error_handler("var_dump");
$headers = array('Content-Type: text/html; charset=UTF-8');
$invio_email = wp_mail($destinatario, $oggetto, $messaggio, $headers);

// Verifica se l'invio è riuscito
if ($invio_email) {
    echo 'Email inviata con successo!';
} else {
    echo 'Errore nell\'invio dell\'email.';
?>   