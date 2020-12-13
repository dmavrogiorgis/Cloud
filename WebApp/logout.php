<?php 
    session_start();

    if(!isset($_SESSION['access_token'])){
        header('location: welcome.php');
    }

    if(isset($_POST['logout'])){
        unset($_SESSION['access_token']);
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        session_destroy();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title> Logout - Movies & Cinemas </title>
    <link rel="stylesheet" href="style_keyrock.css" />
</head>
<body class="logout_div">
    <div class="row">
        <a name="logout" class="logout btn" href=welcome.php> Go Back To Sign In Form</a>
    </div>

</body>
</html> 