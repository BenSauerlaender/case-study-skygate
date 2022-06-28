<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Utilities;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Static functions to send emails to the users
 */
class MailSender
{
    /**
     * Sends a verification email to verify an email change
     *
     * @param  string   $email    The users new email.
     * @param  string   $name     The users name.
     * @param  int      $id       The users id.
     * @param  string   $code     The users email change verification-code
     */
    static public function sendEmailChangeVerificationRequest(string $email, string $name, int $id, string $code): void
    {
        $subject = 'Verify your new Email!';

        $domain = $_ENV["APP_PROD_DOMAIN"];
        $protocol = $_ENV["ENVIRONMENT"] === "PRODUCTION" ? "https://" : "";

        //link to verify the change
        $link = "{$protocol}{$domain}/change-email?userID={$id}&code={$code}";

        $htmlMsg = "Please verify your new email by following this link: <a href=\"{$link}\">{$link}</a>";
        $plainMsg = "Please verify your new email by following this link: {$link}";

        self::sendEmail($email, $name, $subject, $plainMsg, $htmlMsg);
    }
    /**
     * Sends a verification email to verify a new user
     *
     * @param  string   $email    The new users email.
     * @param  string   $name     The new users name.
     * @param  int      $id       The new users id.
     * @param  string   $code     The new users verification-code
     */
    static public function sendVerificationRequest(string $email, string $name, int $id, string $code): void
    {
        $subject = 'Verify your registration!';

        $domain = $_ENV["APP_PROD_DOMAIN"];
        $protocol = $_ENV["ENVIRONMENT"] === "PRODUCTION" ? "https://" : "";

        //link to verify the registration
        $link = "{$protocol}{$domain}/verify-user?userID={$id}&code={$code}";

        $htmlMsg    = "Please verify your registration by following this link: <a href=\"{$link}\">{$link}</a>";
        $plainMsg = "Please verify your registration by following this link: {$link}";

        self::sendEmail($email, $name, $subject, $plainMsg, $htmlMsg);
    }
    /**
     * Sends an email to a user
     *
     * @param  string   $email      The users email.
     * @param  string   $name       The users name.
     * @param  string   $subject    The users id.
     * @param  string   $plainMsg   The plain text email content
     * @param  string   $htmlMsg    The html email content

     */
    static private function sendEmail(string $email, string $name, string $subject, string $plainMsg, string $htmlMsg): void
    {
        $mail = new PHPMailer(true);

        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $_ENV["SMTP_HOST"];                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $_ENV["SMTP_USER"];                     //SMTP username
        $mail->Password   = $_ENV["SMTP_PASS"];                     //SMTP password
        $mail->SMTPSecure = 'tls';                                  //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`


        //Recipient
        $mail->setFrom("no-reply@test.de", "SkyGateCaseStudy");
        $mail->addAddress($email, $name);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;

        $mail->Body    = $htmlMsg;
        $mail->AltBody = $plainMsg;

        $mail->send();
    }
}
