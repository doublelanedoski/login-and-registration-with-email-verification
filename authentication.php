<?php
session_start();

if(!isset($_SESSION['authenticated']))
{
    $_SESSION['status'] = "please Login to Access User Dashboard.!";
    header('location: login.php');
    exit(0);
}



?>