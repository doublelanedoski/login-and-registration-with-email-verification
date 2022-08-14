<?php
session_start();
include('dbcon.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

function resend_email_verify($name, $email, $verify_token)
{
    $mail = new PHPMailer(true);
        try {
                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = 'your email';                     //SMTP username
                $mail->Password   = 'your password';                               //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                //Recipients

                $mail->setFrom('your email', $name);
                $mail->addAddress($email, '');     //Add a recipient
             
                $email_template = "<html>
                <head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
                    <meta name='viewport' content='width=device-width'>
                    
                    <title>Thanks sdfkljskdfjlsk</title>
                
                </head>
                <body>
                    
                    <h2>You have Registered with DoublesTech</h2>
                    <h5>Verify your email address to login with the below given link</h5>
                    <br/><br/>
                    <a href='http://localhost:8012/register-login-with-verification/verify-email.php?token=$verify_token'>Click Me</a>
                            </body>
                </html>
                ";

            $subject = "Resend - Email Verification from DoublesTech";
            $mail->isHTML(true);     
            $body = $email_template;                             //Set email format to HTML
            $mail->Subject =$subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            echo 'Message has been sent'; //  remove this if it works
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"; // i left this so you'll see the error it throws if somethis is wrong. remove it if it works
        }


}

if(isset($_POST['resend_email_verify_btn']))
{
    if(!empty(trim($_POST['email'])))
    {
        $email = mysqli_real_escape_string($con, $_POST['email']);

        $checkemail_query = "SELECT * FROM users WHERE email='$email' LIMIT 1 ";
        $checkemail_query_run = mysqli_query($con, $checkemail_query);

        if(mysqli_num_rows($checkemail_query_run) > 0)
        {
            $row = mysqli_fetch_array($checkemail_query_run);
            if($row['verify_status'] =="0")
            {

                $name = $row['name'];
                $email = $row['email'];
                $verify_token = $row['verify_token'];

                resend_email_verify($name,$email,$verify_token);

                $_SESSION['status'] = "Verification Email Link has been sent to your email address..!";
                header("Location: register.php");
                exit(0);
            }
            else
            {
                $_SESSION['status'] = "Email already verified. Please login";
                header("Location: login.php");
                exit(0);
            }
        }
        else
        {
            $_SESSION['status'] = "Email is not Registered. Please Register now..!";
            header("Location: register.php");
            exit(0);
        }
    }
    else
    {
        $_SESSION['status'] = "Please enter the email field";
        header("Location: resend-email-verification.php");
        exit(0);
    }
}

?>