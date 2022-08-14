<?php 
session_start();
include('dbcon.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

function sendemail_verify($name, $email, $verify_token) {
        $mail = new PHPMailer(true);
        try {
                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = 'include your email';                     //SMTP username
                $mail->Password   = 'include your password';                               //SMTP password
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

            $subject = "Email Verification from DoublesTech";
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

if(isset($_POST['register_btn'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $verify_token = md5(rand());


    //Email Exists or not
    $check_email_query = "SELECT email FROM users WHERE email='$email' LIMIT 1";
    $check_email_query_run = mysqli_query($con, $check_email_query);

    if(mysqli_num_rows($check_email_query_run) >0)
    {
        $_SESSION['status'] = "Email Id already Exists";
        header("location: register.php");
    }
    else
    {
        //Insert User / Register User Data
        $query = "INSERT INTO users (name, phone, email, password, verify_token) VALUES ('$name', ' $phone', '$email', '$password', '$verify_token')";
        $query_run = mysqli_query($con, $query);

        if($query_run)
        {
            sendemail_verify($name, $email, $verify_token);

            $_SESSION['status'] = "Registration Successful...! Please verify your Email Adress";
            header("location: register.php");
        }
        else
        {
            $_SESSION['status'] = "Registration Failed";
            header("location: register.php");
        }
    }
}
