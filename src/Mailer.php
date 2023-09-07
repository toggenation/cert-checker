<?php
// Copyright (c) 2023 James McDonald
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT


namespace Toggenation;


//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    use LogTrait;

    private function setSubject($days, $url)
    {
        if ($days > 0) {
            $subject = "The SSL Certificate for {$url} will expire in {$days} days";
        } else {
            $days = abs($days);
            $subject = "The SSL Certificate for {$url} has been expired for {$days} days";
        }

        return $subject;
    }

    public function send($days, $url)
    {
        $mail = new PHPMailer(true);

        $subject = $this->setSubject($days, $url);

        try {
            //Server settings
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;                   //Enable verbose debug output with SMTP::DEBUG_SERVER
            $mail->isSMTP();                                       //Send using SMTP
            $mail->Host       = $_ENV['SMTP_HOST'];                //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                              //Enable SMTP authentication
            $mail->Username   = $_ENV['SMTP_USER'];                //SMTP username
            $mail->Password   = $_ENV['SMTP_PASS'];                //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    //Enable implicit TLS encryption
            $mail->Port       = $_ENV['SMTP_PORT'];                //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom(
                $_ENV['SMTP_FROM_EMAIL'],
                $_ENV['SMTP_FROM_NAME']
            );
            $mail->addAddress(
                $_ENV['SMTP_NOTIFY_EMAIL'],
                $_ENV['SMTP_NOTIFY_NAME']
            );     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo($_ENV['SMTP_REPLYTO'], $_ENV['SMTP_REPLYTO_NAME']);
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $subject;
            $mail->AltBody = $subject;

            $mail->send();

            $this->log("Mail sent: " . $subject);
        } catch (Exception $e) {
            $this->log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", LOG_ERR);
        }
    }
}
