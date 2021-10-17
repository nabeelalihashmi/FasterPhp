<?php

namespace Core\Classes;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require('config/mail.php');

class Mailer
{
    
    private static $_instance = null;

    public function __construct()
    {
        self::$_instance = new PHPMailer(true);
    }
    public static function instance()
    {

        if (self::$_instance == null) {
            new self();
        }

        return self::$_instance;
    }

    public static function newinstance()
    {
        return new PHPMailer(true);
    }

    public static function smtpMail($subject, $to, $body, $altBody, $from ,$password)
    {

        $mail = self::newinstance();
        $mail->IsSMTP();
        $mail->SMTPDebug = intval(SMTP_DEBUG);
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = SMTP_SECURE;
        
        $mail->Host       = SMTP_HOST;
        $mail->Port       = SMTP_PORT;


        $mail->setFrom($from, EMAIL_SENDER);
        $mail->Username = $from;
        $mail->Password = $password;

        foreach($to as $address => $nick) {
            $mail->addAddress($address, $nick);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody =  $altBody;

        if ($mail->send()) {
            return true;
        }
        return false;
    }

    public static function sendMail($subject, $to, $body, $altBody, $from)
    {

        $mail = self::newinstance();
        
        $mail->setFrom($from, EMAIL_SENDER);
        $mail->Username = $from;

        foreach($to as $address => $nick) {
            $mail->addAddress($address, $nick);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody =  $altBody;

        if ($mail->send()) {
            return true;
        }
        return false;
    }

    public static function mail($subject, $to, $body, $altBody, $from = null, $password = null) {
        if (EMAIL_METHOD == 'SMTP') {
            return self::smtpMail($subject, $to, $body, $altBody, $from ?? EMAIL_FROM, $password ?? EMAIL_PASS);
        } 
        return self::sendMail($subject, $to, $body, $altBody, $from ?? EMAIL_FROM);
    }

}


// use PHPMailer\PHPMailer\PHPMailer;
// require 'vendor/autoload.php';
// $mail = new PHPMailer;
// $mail->isSMTP();
// $mail->SMTPDebug = 2;
// $mail->Host = 'smtp.hostinger.com';
// $mail->Port = 587;
// $mail->SMTPAuth = true;
// $mail->Username = 'test@hostinger-tutorials.com';
// $mail->Password = 'YOUR PASSWORD HERE';
// $mail->setFrom('test@hostinger-tutorials.com', 'Your Name');
// $mail->addReplyTo('test@hostinger-tutorials.com', 'Your Name');
// $mail->addAddress('example@email.com', 'Receiver Name');
// $mail->Subject = 'Testing PHPMailer';
// $mail->msgHTML(file_get_contents('message.html'), __DIR__);
// $mail->Body = 'This is a plain text message body';
// //$mail->addAttachment('test.txt');
// if (!$mail->send()) {
//     echo 'Mailer Error: ' . $mail->ErrorInfo;
// } else {
//     echo 'The email message was sent.';
// }