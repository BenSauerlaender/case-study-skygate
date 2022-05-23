<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailUtilities
{
    /**
     * Utility function to send a the verification email to a new user
     *
     * @param  string   $email    The new users email.
     * @param  string   $name     The new users name.
     * @param  int      $id       The new users id.
     * @param  string   $code     The new users verification-code
     */
    static public function sendEmailChangeVerificationRequest(string $email, string $name, int $id, string $code): void
    {
        $domain = $_ENV["API_PROD_DOMAIN"];
        $prefix = $_ENV["API_PATH_PREFIX"];

        $subject = 'Verify your new Email!';

        $link = "https://{$domain}{$prefix}/users/{$id}/emailChange/{$code}";

        $htmlMsg    = "Please verify your new email by following this link: <a href=\"{$link}\">{$link}</a>";
        $plainMsg = "Please verify your new email by following this link: {$link}";

        self::sendEmail($email, $name, $subject, $plainMsg, $htmlMsg);
    }
    /**
     * Utility function to send a the verification email to a new user
     *
     * @param  string   $email    The new users email.
     * @param  string   $name     The new users name.
     * @param  int      $id       The new users id.
     * @param  string   $code     The new users verification-code
     */
    static public function sendVerificationRequest(string $email, string $name, int $id, string $code): void
    {

        $domain = $_ENV["API_PROD_DOMAIN"];
        $prefix = $_ENV["API_PATH_PREFIX"];

        $subject = 'Verify your registration!';

        $link = "https://{$domain}{$prefix}/users/{$id}/verify/{$code}";

        $htmlMsg    = "Please verify your registration by following this link: <a href=\"{$link}\">{$link}</a>";
        $plainMsg = "Please verify your registration by following this link: {$link}";

        self::sendEmail($email, $name, $subject, $plainMsg, $htmlMsg);
    }
    /**
     * Utility function to send an email to a user
     *
     * @param  string   $email      The new users email.
     * @param  string   $name       The new users name.
     * @param  string   $subject    The new users id.
     * @param  string   $plainMsg   The new users id.
     * @param  string   $htmlMsg    The new users id.

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
        $mail->SMTPSecure = 'tls'; //PHPMailer::ENCRYPTION_SMTPS;   //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        $domain = $_ENV["API_PROD_DOMAIN"];

        //Recipients
        $mail->setFrom("no-reply@{$domain}", "SkyGateCaseStudy");
        $mail->addAddress($email, $name);     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;

        $mail->Body    = $htmlMsg;
        $mail->AltBody = $plainMsg;

        $mail->send();
    }
}
