<?php
 session_start();
    if(!(isset($_SESSION['login_id'])))
    {
        header("location:login.php");
    }
    else
    {
        $firstname = $_SESSION['login_username'];
    }

    ?>