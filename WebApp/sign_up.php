<?php 
    $errors = array();
    $successes = array();


    if(isset($_POST['register'])){
        $request_type = "CREATE_NEW_USER";

        $app_logic_url = 'http://172.18.1.8/app_logic_login.php';

        $curl_url = $app_logic_url . "?" . http_build_query([
                                            'request_type' => $request_type,
                                            'username' => $_POST['username'],
                                            'email' => $_POST['email'],
                                            'role' => $_POST['role'],
                                            'password' => $_POST['password']
                                           ]);
        $register_curl = curl_init();
        curl_setopt($register_curl, CURLOPT_URL, $curl_url);
        curl_setopt($register_curl, CURLOPT_HTTPGET, true);
        curl_setopt($register_curl, CURLOPT_RETURNTRANSFER, true);                                 
        $register_response = curl_exec($register_curl);
        curl_close($register_curl);
        
        $data_array = json_decode($register_response);

        if($data_array->role_user_assignments->user_id != NULL){
            header('location: welcome.php');
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title> Register - Movies & Cinemas </title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="stylesheet" href="style_keyrock.css" />
</head>
<body class="login-register">
    <div class="header_h1">
            <h2>Sign Up</h2>
    </div>
    <form class="login_register_form" method="post" action="sign_up.php">
        <?php include('error_success.php'); ?>
        <div class="input-data">
            <label>Username</label>
            <input type="text" placeholder="Username" name="username" required>
        </div>
        <div class="input-data">
            <label>Email</label>
            <input type="text" pattern="[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*" placeholder="example@test.com" name="email" required>
        </div>
        <div class="input-data">
            <label>Password</label>
            <input type="password" placeholder="Password" id="password" name="password" onkeyup="check();" required>
            <input type="password" placeholder="Confirm password" id="confirm_password" name="confirm_password" onkeyup='check();' required>
            <span id='message'></span>
        </div>
        <div class="input-data">
            <label>Role</label>
            <select class="roles" name="role" required>
                <option value=""></option>
                <option value="CINEMAOWNER">CINEMA OWNER</option>
                <option value="USER">USER</option>
            </select>
        </div>
        <div class="input-data">
            <button type="submit" id="register" name="register" class="button">Sign Up</button>
        </div>
        <div class="row">
            <p> Aready a member? <a href="welcome.php">Sign In</a>
        </div>
    </form>

    <script>
        var check = function() {
            if (document.getElementById('password').value == document.getElementById('confirm_password').value) {
                document.getElementById('register').disabled = false;
                document.getElementById('message').style.color = 'green';
                document.getElementById('message').innerHTML = 'matching';
            } else {
                document.getElementById('register').disabled = true;
                document.getElementById('message').style.color = 'red';
                document.getElementById('message').innerHTML = 'not matching';
            }
    }
    </script>
</body>
</html> 