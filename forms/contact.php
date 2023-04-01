<?php
  $name = $_POST['name'];
  $surname = $_POST['surname'];
  $date = $_POST['datanascita'];
  $visitor_email = $_POST['email'];

  $email_from = 'yourname@yourwebsite.com';

	$email_subject = "New Form submission";

	$email_body = "You have received a new message from the user $name.\n".
                            "Here is the message:\n $message".
?>