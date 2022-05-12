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
     * Utility function to send a the confirmation email to a new user
     *
     * @param  string   $email    The new users email.
     * @param  string   $name     The new users name.
     * @param  int      $id       The new users id.
     * @param  string   $code     The new users confirmation-code
     */
    static public function sendConfirmation(string $email, string $name, int $id, string $code): void
    {
        $mail = new PHPMailer(true);

        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $_ENV["SMTP_HOST"];                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $_ENV["SMTP_USER"];                     //SMTP username
        $mail->Password   = $_ENV["SMTP_PASS"];                     //SMTP password
        $mail->SMTPSecure = 'tls'; //PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('no-reply@' . $_ENV["API_PROD_DOMAIN"], 'SkyGateCaseStudy');
        $mail->addAddress($email, $name);     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Confirm your registration!';
        $mail->Body    = 'Please confirm  <b>in bold!</b>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
    }
}
