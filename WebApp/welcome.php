<?  
    session_start();

    $errors = array();
    $successes = array();
    
    if(isset($_POST['login'])){
        $user_email = $_POST['email'];
        $user_pass = $_POST['password'];
        $request_type = "GET_INFO";

        $app_logic_url = 'http://172.18.1.8/app_logic_login.php';

        $curl_url = $app_logic_url . "?" . http_build_query([
                                            'request_type' => $request_type,
                                            'email' => $user_email,
                                            'password' => $user_pass
                                           ]);
        $login_curl = curl_init();
        curl_setopt($login_curl, CURLOPT_URL, $curl_url);
        curl_setopt($login_curl, CURLOPT_HTTPGET, true);
        curl_setopt($login_curl, CURLOPT_RETURNTRANSFER, true);                                 
        $login_response = curl_exec($login_curl);
        curl_close($login_curl);
        
        $data_array = json_decode($login_response);

        if(!empty($data_array)){
                $user_access_token = $data_array->access_token;
                $user_id = $data_array->user_id;
                $user_username = $data_array->username;
                $user_role = $data_array->role;

            if (!strcmp($user_role, "CINEMAOWNER")){
                $_SESSION['access_token'] = $user_access_token;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user_username;
                $_SESSION['role'] = $user_role;
                
                header('location: owner.php');
    
            }else if (!strcmp($user_role, "USER")){
                $_SESSION['access_token'] = $user_access_token;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user_username;
                $_SESSION['role'] = $user_role;
    
                header('location: user.php');

            }else{  
                header('location: welcome.php');
            }
        }else{
            array_push($errors, "Invalid Email or Password!");
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title> Sign In - Movies & Cinemas </title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="style_keyrock.css" />
</head>
<body class="login-register">
    <div class="header_h1">
            <h2>Sign In</h2>
    </div>
    <form class="login_register_form" method="post" action="welcome.php">
        <?php include('error_success.php'); ?>
        <div class="input-data">
            <label>Email</label>
            <input type="text" pattern="[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*" placeholder="example@test.com" name="email" required>
        </div>
        <div class="input-data">
            <label>Password</label>
            <input type="password" placeholder="Password" name="password" required>
        </div>
        <div class="input-data">
            <button type="submit" name="login" class="button">Sign In</button>
        </div>
        <div class="row">
            <p> Not a member? <a href="sign_up.php">Sign Up</a>
        </div>
        
    </form>
</body>
</html> 
