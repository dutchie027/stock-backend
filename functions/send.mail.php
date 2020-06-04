<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function SendSMTPMail($to, $from, $subject, $content)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = "smtp.sendgrid.net";
        $mail->Port = 465;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "ssl";
        $mail->Username = "apikey";
        $mail->isHTML(true);
        $mail->Password = SG_SMTP_API;
        $mail->SMTPDebug = 0;
        $mail->From = $from;
        $mail->FromName = "Find A Hydrant";
        $mail->AddAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $content;
        $mail->send();
    } catch (Exception $e) {
        $GLOBALS['g_log']->error("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
