<?php 
    $errors = array();
    session_start();
    if (empty($_SESSION['access_token']) || empty($_SESSION['username']) || strcmp($_SESSION['role'],"USER")){
        header('location: unauthorized.php');
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title> User - Movies & Cinemas </title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="stylesheet" type="text/css" href="style_notification_bar.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script>
        function myFunction() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

        window.onclick = function(e) {
            if (!e.target.matches('.dropbtn')) {
                var myDropdown = document.getElementById("myDropdown");
                    if (myDropdown.classList.contains('show')) {
                    myDropdown.classList.remove('show');
                }
            }
        }
        function notification(){
            $("#notification_div").slideToggle(300);
        }
    </script>
</head>
<body class="user">
    <div class="navbar">
        <a href="user.php">Home-Movies & Cinemas</a>
        <div class="dropdown">
            <button class="dropbtn" onclick="myFunction()"> <?php echo $_SESSION['username'] . " (" . $_SESSION['role'] . ") " ?> 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content" id="myDropdown">
                <form class="logout_form" method="post" action="logout.php">
                    <button type="submit" name="logout" class="logout_button">Logout</button>
                </form>
            </div>
        </div> 
    </div>   
    <div id="wrapper">
        <div id="notification_div">

        <table id="notificationTable">
            <thead>
                <tr>
                    <th> Title </th>
                    <th> Start Date </th>
                    <th> End Date </th>
                    <th> Cinema name</th>
                    <th> Category </th>
                    <th> Description </th>
                </tr>
            </thead>
            <tbody>
                <?php  
                    $user_id = $_SESSION['user_id'];
                    $access_token = $_SESSION['access_token'];
                    $request_type = "GET_ALL_NOTIFICATIONS";

                    $app_logic_url = 'http://172.18.1.8/app_logic_notifications.php';

                    $curl_url = $app_logic_url . "?" . http_build_query([
                                                            'request_type' => $request_type,
                                                            'access_token' => $access_token,
                                                            'user_id' => $user_id,
                                                        ]);
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $curl_url);
                    curl_setopt($curl, CURLOPT_HTTPGET, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                                 
                    $response = curl_exec($curl);
                    curl_close($curl);
                    
                    $data_array = json_decode($response,true);

                    if(is_array($data_array) || is_object($data_array)){
                        foreach($data_array as $movie) {
                ?>
                <tr>
                    <td> <?php echo  $movie["title"] ?> </td>
                    <td> <?php echo  $movie["start_date"] ?> </td>
                    <td> <?php echo  $movie["end_date"] ?> </td>
                    <td> <?php echo  $movie["cinema_name"] ?> </td>
                    <td> <?php echo  $movie["category"] ?> </td>
                    <td> <?php if($movie["isComingSoon"] == true && $movie["isPlaying"] == false){
                                    echo "This movie will be availiable in less than 10 days!";
                               }else if($movie["isComingSoon"] == false && $movie["isPlaying"] == true){
                                    echo "This movie is availiable! Have fun!";
                               }else{
                                   echo "This movie is not availiable! Sorry!";
                               }
                        ?> 
                    </td>
                </tr>
                <?php
                        }
                    }else{
                        array_push($errors, "There are no notifications yet!");
                    }
                ?>
            </tbody>
        </table>
            <br>
            
        </div>

        <p id="show_notification" onclick='notification();'>Click To Show/Hide Notification Bar</p>
    </div>
    <div>
        <input type="text" id="searchBar" placeholder="Search for movies.. You can use title or cinema name or category" title="">
    </div>
    <div class="date_search">
        <label id="search_by_date" > Search Movies within a given date range.. </label>
        <input type="date" id="date_limit_1" name="date_limit_1" >
        <input type="date" id="date_limit_2" name="date_limit_2" >
        <input type="submit" id="submit_date_limit" value="Search">
    </div>
    <div class="movie-div" id="movie_table">
        <table id="movieTable">
            <?php  
                $access_token = $_SESSION['access_token'];
                $user_id = $_SESSION['user_id'];
                $request_type = "GET_USER_MOVIES";

                $app_logic_url = 'http://172.18.1.8/app_logic_mongo.php';

                $curl_url = $app_logic_url . "?" . http_build_query([
                                                        'request_type' => $request_type,
                                                        'access_token' => $access_token,
                                                        'user_id' => $user_id,
                                                    ]);
                $user_movies_curl = curl_init();
                curl_setopt($user_movies_curl, CURLOPT_URL, $curl_url);
                curl_setopt($user_movies_curl, CURLOPT_HTTPGET, true);
                curl_setopt($user_movies_curl, CURLOPT_RETURNTRANSFER, true);                                 
                $user_movies_response = curl_exec($user_movies_curl);
                curl_close($user_movies_curl);
                
                $data_array = json_decode($user_movies_response,true);
            ?>
            <thead>
                <tr>
                    <td> Movie ID </td>
                    <td> Title </td>
                    <td> Start Date </td>
                    <td> End Date </td>
                    <td> Cinema name</td>
                    <td> Category </td>
                    <td> Add to favourites </td>
                </tr>
            </thead>
            <tbody>
                <?php 
                    if(is_array($data_array) || is_object($data_array)){
                        foreach($data_array as $movie) {
                ?>
                <tr>
                    <th> <?php echo  $movie["id"] ?> </th>
                    <th> <?php echo  $movie["title"] ?> </th>
                    <th> <?php echo  $movie["start_date"] ?> </th>
                    <th> <?php echo  $movie["end_date"] ?> </th>
                    <th> <?php echo  $movie["cinema_name"] ?> </th>
                    <th> <?php echo  $movie["category"] ?> </th>
                    <td>
                        <i id="<?php if($movie["user_id"] == NULL){ 
                                        echo $movie["id"] . "_" . $_SESSION['access_token'] . "_" . $_SESSION['user_id'] . "_" . "false"; 
                                    }else{ 
                                        echo $movie["id"] . "_" . $_SESSION['access_token'] . "_" . $_SESSION['user_id'] . "_" . "true"; 
                                    } 
                                ?>" 
                           class="<?php if($movie["user_id"] == NULL){ 
                                            echo "btn-fav"; 
                                        }else{ 
                                            echo "btn-fav press"; 
                                        }
                                    ?>">
                        </i>
                    </td>
                </tr>
                <?php
                        }
                    }else{
                        array_push($errors, "There are no movies registered!");
                    }
                ?>
            </tbody>
            
        </table>
    </div>

    <!--************************************************************SCRIPTS************************************************************-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>

        setInterval(ajax_call, 5000); // Send an ajax every 5 sec

        function ajax_call(){
            var access_token = '<?php echo $_SESSION['access_token']?>';
            var user_id = '<?php echo $_SESSION['user_id']?>';

            $.ajax({
                url: 'http://172.18.1.8/app_logic_notifications.php',
                method: "GET",
                data: {
                    request_type: 'GET_NEW_NOTIFICATION',
                    access_token: access_token,
                    user_id: user_id
                },
                success: function(data) {
                    var session_user_id = '<?php echo $_SESSION['user_id']?>';

                    var new_notif = JSON.parse(data);

                    for(var i=0; i< new_notif.length; i++){
                        var table = document.getElementById('notificationTable').getElementsByTagName('tbody')[0];
                        var row = table.insertRow(0);
                        var cell1 = row.insertCell(0);
                        var cell2 = row.insertCell(1);
                        var cell3 = row.insertCell(2);
                        var cell4 = row.insertCell(3);
                        var cell5 = row.insertCell(4);
                        var cell6 = row.insertCell(5);

                        cell1.innerHTML = new_notif[i].title;
                        cell2.innerHTML = new_notif[i].start_date;
                        cell3.innerHTML = new_notif[i].end_date;
                        cell4.innerHTML = new_notif[i].cinema_name;
                        cell5.innerHTML = new_notif[i].category;

                        if(new_notif[i].isComingSoon && !(new_notif[i].isPlaying)){
                            cell6.innerHTML = "This movie will be availiable in less than 10 days!";
                        }else if(!(new_notif[i].isComingSoon) && new_notif[i].isPlaying){
                            cell6.innerHTML = "This movie is availiable! Have fun!";
                        }else{
                            cell6.innerHTML = "This movie is not availiable! Sorry!";
                        }
                    }
                }
            });
        }

        $(document).ready(function() {
            $(document).on('click', '.btn-fav', function() {

                var query_info = $(this).attr("id").split('_');
                var movie_id = query_info[0];
                var access_token = query_info[1];
                var user_id = query_info[2];
                var like_movie = query_info[3];

                $.ajax({
                    url: 'http://172.18.1.8/app_logic_mongo.php',
                    method: "GET",
                    data: {
                        request_type: 'ADD_REMOVE_FAVOURITES',
                        movie_id: movie_id,
                        access_token: access_token,
                        user_id: user_id,
                        like_movie: like_movie
                    },
                    success: function(data) {
                        $('#movieTable').html(data);
                    }
                });
            });

            $('#searchBar').keyup(function(){
                var user_id = '<?php echo $_SESSION['user_id']?>';
                var access_token = '<?php echo $_SESSION['access_token']?>';
                var regex = $(this).val();
                
                $.ajax({
                    url: 'http://172.18.1.8/app_logic_mongo.php',
                    method: "GET",
                    data: {
                        request_type: 'SEARCH_MOVIES',
                        user_id: user_id,
                        access_token: access_token,
                        regex: regex
                    },
                    success: function(data) {
                        $('#movieTable').html(data);
                    }
                });
            });

            $(document).on('click', '#submit_date_limit', function() {
                event.preventDefault();
                if ($('#date_limit_1').val() == '') {
                    alert("Date No1 Required");
                } else if ($('#date_limit_2').val() == '') {
                    alert("Date No2 Required");
                }else {
                    var user_id = '<?php echo $_SESSION['user_id']?>';
                    var access_token = '<?php echo $_SESSION['access_token']?>';

                    $.ajax({
                        url: 'http://172.18.1.8/app_logic_mongo.php',
                        method: "GET",
                        data: {
                            request_type: 'SEARCH_MOVIES_BY_DATE',
                            user_id: user_id,
                            access_token: access_token,
                            date_limit_1: $('#date_limit_1').val(),
                            date_limit_2: $('#date_limit_2').val()
                        },
                        success: function(data) {
                            $('#movieTable').html(data);
                        }
                    });
                }
            });
            
        });
    </script>
</body>
</html> 