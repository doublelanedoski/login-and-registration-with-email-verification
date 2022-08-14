<?php
session_start();
include('dbcon.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

function send_password_reset($get_name, $get_email, $token)
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

                $mail->setFrom('your email', $get_name);
                $mail->addAddress($get_email);     //Add a recipient
             
                $email_template = "<html>
                <head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
                    <meta name='viewport' content='width=device-width'>
                    
                    <title>Thanks for choosing Us</title>
                
                </head>
                <body>
                    
                    <h2>You are  receiving this email because we received a password reset request for your account</h2>
                    <h5>reset your password to login with the below given link</h5>
                    <br/><br/>
                    <a href='http://localhost:8012/register-login-with-verification/password-change.php?token=$token&email=$get_email'>Click Me</a>
                            </body>
                </html>
                ";

            $subject = "Reset Password Notification";
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


if(isset($_POST['password_reset_link']))
{
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $token =md5(rand());

    $check_email = "SELECT email FROM  users WHERE email='$email' LIMIT 1";
    $check_email_run = mysqli_query($con, $check_email);

    if(mysqli_num_rows($check_email_run) >0)
    {
        $row = mysqli_fetch_array($check_email_run);
        $get_name = $row['name'];
        $get_email = $row['email'];

        $update_token = "UPDATE users  SET verify_token='$token' WHERE email='$get_email' LIMIT 1 ";
        $update_token_run = mysqli_query($con, $update_token);

        if($update_token_run)
        {
            send_password_reset($get_name, $get_email, $token);
            $_SESSION['status'] = "We E-mailed you a password reset link";
            header("Location: password-reset.php");
            exit(0);
        }
        else
        {
            $_SESSION['status'] = "Something went wrong. #1 ";
            header("Location: password-reset.php");
            exit(0);
        }
    }
    else
    {
        $_SESSION['status'] = "No Email Found";
        header("Location: password-reset.php");
        exit(0);
    }
}




if(isset($_POST['password_update']))
{
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $new_password = mysqli_real_escape_string($con, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

    $token = mysqli_real_escape_string($con, $_POST['password_token']);

    if(!empty($token))
    {
        if(!empty($email) && !empty($new_password) && !empty($confirm_password))
        {
            //Checking token is valid or not
            $check_token = "SELECT verify_token FROM users WHERE verify_token='$token' LIMIT 1";
            $check_token_run = mysqli_query($con, $check_token);

            if(mysqli_num_rows($check_token_run) > 0)
            {
                if($new_password == $confirm_password)
                {
                    $update_password = "UPDATE users SET password='$new_password' WHERE verify_token='$token' LIMIT 1 ";
                    $update_password_run = mysqli_query($con, $update_password);

                    if($update_password)
                    {
                        $new_token = md5(rand());
                        $update_to_new_token = "UPDATE users SET verify_token='$new_token' WHERE verify_token='$token' LIMIT 1 ";
                        $update_to_new_token_run = mysqli_query($con, $update_to_new_token);

                        $_SESSION['status'] = "New Password Successfully Updated";
                        header("Location: login.php");
                        exit(0);
                    }
                    else
                    {
                        $_SESSION['status'] = "Did not update password. Something went wrong";
                        header("Location: password-change.php?token=$token&email=$email");
                        exit(0);
                    }
                }
                    else
                    {
                        $_SESSION['status'] = "Password and Confirm Password does not match ";
                        header("Location: password-change.php?token=$token&email=$email");
                        exit(0);
                    }
            }
                    else
                    {
                        $_SESSION['status'] = "Invalid Token";
                        header("Location: password-change.php?token=$token&email=$email");
                        exit(0);
                    }
            }
                    else
                    {
                        $_SESSION['status'] = "All Field are Mandatory";
                        header("Location: password-change.php?token=$token&email=$email");
                        exit(0);
                    }
            }
                    else
                    {
                        $_SESSION['status'] = "No Token Available";
                        header("Location: password-change.php");
                        exit(0);
                    }
}

?>