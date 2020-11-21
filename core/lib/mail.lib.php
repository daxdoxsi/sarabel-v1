<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require PATH_THIRD_PARTY.'phpmailer/Exception.php';
require PATH_THIRD_PARTY.'phpmailer/PHPMailer.php';
require PATH_THIRD_PARTY.'phpmailer/SMTP.php';

function send_mail($from_email, $from_name, $to = [], $subject, $html_message, $addCC = [], $addBCC = []){

    global $config;

    //Create a new PHPMailer instance
    $mail = new PHPMailer;

    //Tell PHPMailer to use SMTP
    $mail->isSMTP();

    // Enable SMTP debugging
    // SMTP::DEBUG_OFF = off (for production use)
    // SMTP::DEBUG_CLIENT = client messages
    // SMTP::DEBUG_SERVER = client and server messages
    $mail->SMTPDebug = ($config['mail.debug'] ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF );

    //Set the hostname of the mail server
    $mail->Host = $config['mail.hostname'];

    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $mail->Port = $config['mail.port'];

    //Set the encryption mechanism to use - STARTTLS or SMTPS
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    //Custom connection options
    //Note that these settings are INSECURE
    /*$mail->SMTPOptions = array(
        'ssl' => [
            'verify_peer' => true,
            'verify_depth' => 3,
            'allow_self_signed' => true,
            'peer_name' => $config['mail.hostname'],
            'cafile' => $config['mail.cafile'],
        ],
    );*/

    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;

    //Username to use for SMTP authentication - use full email address for gmail
    $mail->Username = $config['mail.username'];

    //Password to use for SMTP authentication
    $mail->Password = $config['mail.password'];;

    //Set who the message is to be sent from
    $mail->setFrom($from_email, $from_name);

    //Set who the message is to be sent to
    if (is_array($to) && count($to) > 0) {
        foreach($to as $email => $name) {
            $mail->addAddress($email, $name);
        }
    }

    //Set who the message is to be sent to
    if (is_array($addCC) && count($addCC) > 0) {
        foreach($addCC as $email => $name) {
            $mail->addCC($email, $name);
        }
    }

    //Set who the message is to be sent to
    if (is_array($addBCC) && count($addBCC) > 0) {
        foreach($addBCC as $email => $name) {
            $mail->addBCC($email, $name);
        }
    }

    //Set the subject line
    $mail->Subject = $subject;

    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->msgHTML($html_message, __DIR__);

    //Send the message, check for errors
    if (!$mail->send()) {
        error('Mailer Error: ' . $mail->ErrorInfo, 'Mail not send', 'development');
        error('Sorry, we are having problems with the email system.', 'Email error', 'production');
    } else {
        set_flash('Message sent!');
    }

}