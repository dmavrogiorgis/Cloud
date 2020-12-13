<?php
    session_start();

    $errors = array();
    $successes = array();

    if (empty($_SESSION['access_token']) || empty($_SESSION['username']) || strcmp($_SESSION['role'],"CINEMAOWNER")){
        session_destroy();
        header('location: unauthorized.php');
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title> Owner - Movies & Cinemas </title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!--************************************************************SCRIPTS************************************************************-->
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
    </script>
</head>
<body class="owner">
    <div class="navbar">
        <a href="owner.php">Home-Movies & Cinemas</a> 
            <button  type="submit" id="add_btn" name="add_btn" class="btn-add"> Add a Movie </button>
            <button  type="submit" id="cin_add_btn" name="cin_add_btn" class="btn-add-cin"> Add a Cinema </button>
        <div class="dropdown">
            <button class="dropbtn" onclick="myFunction();"> <?php echo $_SESSION['username'] . " (" . $_SESSION['role'] . ") " ?> 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content" id="myDropdown">
                <form class="logout_form" method="post" action="logout.php">
                    <button type="submit" name="logout" class="logout_button">Logout</button>
                </form>
            </div>
        </div> 
    </div>

    <!-- ************************************************************MOVIE TABLE************************************************************-->
    <div class="table-div" id="user_table">
        
        <table class="table-bordered" id="dataTable" width="100%" cellspacing="0">
            <?php  
                $access_token = $_SESSION['access_token'];
                $user_id = $_SESSION['user_id'];
                $request_type = "GET_MOVIES";

                $app_logic_url = 'http://app_logic/app_logic_mongo.php';

                $curl_url = $app_logic_url . "?" . http_build_query([
                                                        'request_type' => $request_type,
                                                        'access_token' => $access_token,
                                                        'user_id' => $user_id,
                                                    ]);
                $movies_curl = curl_init();
                curl_setopt($movies_curl, CURLOPT_URL, $curl_url);
                curl_setopt($movies_curl, CURLOPT_HTTPGET, true);
                curl_setopt($movies_curl, CURLOPT_RETURNTRANSFER, true);                                 
                $movies_response = curl_exec($movies_curl);
                curl_close($movies_curl);
                
                $data_array = json_decode($movies_response,true);
            ?>
            <thead>
                <tr>
                    <th> Movie ID </th>
                    <th> Title </th>
                    <th> Start Date </th>
                    <th> End Date </th>
                    <th> Cinema Name</th>
                    <th> Category </th>
                    <th> EDIT </th>
                    <th> DELETE </th>
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
                        <button type="submit" id="<?php echo $movie["id"] . "_" . $_SESSION['access_token'] . "_" . $_SESSION['user_id']; ?>" name="edit_btn" class="btn-edit"> EDIT</button>
                    </td>
                    <td>
                        <button type="submit" id="<?php echo $movie["id"] . "_" . $_SESSION['access_token'] . "_" . $_SESSION['user_id']; ?>" name="delete_btn" class="btn-delete"> DELETE</button>
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
        <?php include('error_success.php'); ?>          
    </div>

    <!--************************************************************UPDATE MODAL FORM************************************************************-->
    <div class="bg-modal-update">
	    <div id="update_form" class="modal-contents">
            <div class="close-update">+</div>
            <div> 
                <h2> UPDATE MOVIE FORM </h2>
            </div>
            <div class="input-data">
                <label>Movie ID</label>
                <input type="text" placeholder="Movie ID" id="movie_id" name="movie_id" disabled>
            </div>
            <div class="input-data">
                <label>Title</label>
                <input type="text" placeholder="Title" id="title" name="title" >
            </div>
            <div class="input-data">
                <label>Start date</label>
                <input type="date" placeholder="Start date" id="start_date" name="start_date" >
            </div>
            <div class="input-data">
                <label>End date</label>
                <input type="date" placeholder="End date" id="end_date" name="end_date" >
            </div>
            <div class="input-data">
                <label>Ciname Name</label>
                <select class="roles" placeholder="Ciname Name" id="cinema_name" name="cinema_name" value="TROLLL">
                    <option value=""> </option>
                    <?php 
                        $access_token = $_SESSION['access_token'];
                        $user_id = $_SESSION['user_id'];
                        $request_type = "GET_CINEMAS";
        
                        $app_logic_url = 'http://app_logic/app_logic_mongo.php';
        
                        $curl_url = $app_logic_url . "?" . http_build_query([
                                                                'request_type' => $request_type,
                                                                'access_token' => $access_token,
                                                                'user_id' => $user_id,
                                                            ]);
                        $cinemas_curl = curl_init();
                        curl_setopt($cinemas_curl, CURLOPT_URL, $curl_url);
                        curl_setopt($cinemas_curl, CURLOPT_HTTPGET, true);
                        curl_setopt($cinemas_curl, CURLOPT_RETURNTRANSFER, true);                                 
                        $cinemas_response = curl_exec($cinemas_curl);
                        curl_close($cinemas_curl);
                        
                        $data_array = json_decode($cinemas_response,true);

                        if(count($data_array) > 0){
                            foreach($data_array as $cinema) { 
                    ?>
                    <option value="<?php echo $cinema["id"] ?>"> <?php echo $cinema["name"] ?> </option>
                    <?php
                            }
                        } 
                    ?>
                </select>
            </div>
            <div class="input-data">
                <label>Category</label>
                <input type="text" placeholder="Category" id="category" name="category" >
            </div>
                <button  type="submit" id="update_<?php echo $_SESSION['access_token'] . "_" . $_SESSION['user_id']; ?>" name="update" class="btn-update"> UPDATE </button>  
        </div>
    </div>

    <!--************************************************************INSERT MODAL FORM************************************************************-->
    <div class="bg-modal-insert">
	    <div id="insert_form" class="modal-contents">
            <div class="close-insert">+</div>
            <div> 
                <h2> INSERT MOVIE FORM </h2>
            </div>
            <div class="input-data">
                <label>Title</label>
                <input type="text" placeholder="Title" id="insert_title" name="insert_title" >
            </div>
            <div class="input-data">
                <label>Start date</label>
                <input type="date" placeholder="Start date" id="insert_start_date" name="insert_start_date" >
            </div>
            <div class="input-data">
                <label>End date</label>
                <input type="date" placeholder="End date" id="insert_end_date" name="insert_end_date" >
            </div>
            <div class="input-data">
                <label>Ciname Name</label>
                <select class="roles" placeholder="Ciname Name" id="insert_cinema_name" name="insert_cinema_name" >
                    <option value=""> </option>
                    <?php 
                        $access_token = $_SESSION['access_token'];
                        $user_id = $_SESSION['user_id'];
                        $request_type = "GET_CINEMAS";
        
                        $app_logic_url = 'http://172.18.1.8/app_logic_mongo.php';
        
                        $curl_url = $app_logic_url . "?" . http_build_query([
                                                                'request_type' => $request_type,
                                                                'access_token' => $access_token,
                                                                'user_id' => $user_id,
                                                            ]);
                        $cinemas_curl = curl_init();
                        curl_setopt($cinemas_curl, CURLOPT_URL, $curl_url);
                        curl_setopt($cinemas_curl, CURLOPT_HTTPGET, true);
                        curl_setopt($cinemas_curl, CURLOPT_RETURNTRANSFER, true);                                 
                        $cinemas_response = curl_exec($cinemas_curl);
                        curl_close($cinemas_curl);
                        
                        $data_array = json_decode($cinemas_response,true);

                        if(count($data_array) > 0){
                            foreach($data_array as $cinema) { 
                    ?>
                    <option value="<?php echo $cinema["id"] ?>"> <?php echo $cinema["name"] ?> </option>
                    <?php
                            }
                        } 
                    ?>
                </select>
            </div>
            <div class="input-data">
                <label>Category</label>
                <input type="text" placeholder="Category" id="insert_category" name="category" >
            </div>
                <button  type="submit" id="insert_<?php echo $_SESSION['access_token'] . "_" . $_SESSION['user_id']; ?>" name="insert" class="btn-insert"> INSERT </button>  
        </div>
    </div>

    <!-- *****************************************************INSERT CINEMA MODAL******************************************************-->
    <div class="bg-modal-insert-cin">
	    <div id="insert_cinema_form" class="modal-contents">
            <div class="close-insert-cin">+</div>
            <div> 
                <h2> INSERT CINEMA FORM </h2>
            </div>
            <div class="input-data">
                <label>Name</label>
                <input type="text" placeholder="Name" id="insert_cin_name" name="insert_cin_name" >
            </div> 
                <button  type="submit" id="insertcin_<?php echo $_SESSION['access_token'] . "_" . $_SESSION['user_id']; ?>" name="insert_cin" class="btn-insert-cin"> INSERT </button>  
        </div>
    </div>

    <!--************************************************************SCRIPTS************************************************************-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.btn-add', function() {
                document.querySelector('.bg-modal-insert').style.display = "flex";
            });

            $(document).on('click', '.btn-add-cin', function() {
                document.querySelector('.bg-modal-insert-cin').style.display = "flex";
            });
            
            /* AJAX REQUEST FOR INSERTING  A NEW CINEMA NAME */
            $('#insert_cinema_form').on('click', '.btn-insert-cin', function(event) {
                event.preventDefault();
                if ($('#insert_cin_name').val() == '') {
                    alert("Cinema Name Required");
                }else {                 
                    var query_info = $(this).attr("id").split('_');
                    var access_token = query_info[1];
                    var user_id = query_info[2];

                    $.ajax({
                        url:  'http://172.18.1.8/app_logic_mongo.php',
                        method: "GET",
                        data: {
                            request_type: 'INSERT_A_CINEMA',
                            access_token: access_token,
                            user_id: user_id,
                            cinema_name: $('#insert_cin_name').val()
                        },
                        success: function(data) {
                            var new_cinema = JSON.parse(data);
                            $('#insert_cinema_name').append('<option value="' + new_cinema[0].id + '" > ' + new_cinema[0].name + ' </option>');
                            $('#cinema_name').append('<option value="' + new_cinema[0].id + '" > ' + new_cinema[0].name + ' </option>');
                            document.querySelector('.bg-modal-insert-cin').style.display = "none";
                        }
                    });
                }
            });

            /* AJAX REQUEST FOR INSERTING A NEW MOVIE AND REFRESH THE TABLE */
            $('#insert_form').on('click', '.btn-insert', function(event) {
                event.preventDefault();
                if ($('#insert_title').val() == '') {
                    alert("Movie Title Required");
                } else if ($('#insert_start_date').val() == '') {
                    alert("Movie Start date Required");
                } else if ($('#insert_end_date').val() == '') {
                    alert("Movie End date Required");
                } else if ($('#insert_cinema_name').val() == '') {
                    alert("Movie Cinema name Required");
                } else if ($('#insert_category').val() == '') {
                    alert("Movie Category Required");
                }else {                 
                    var query_info = $(this).attr("id").split('_');
                    var access_token = query_info[1];
                    var user_id = query_info[2];

                    $.ajax({
                        url:  'http://172.18.1.8/app_logic_mongo.php',
                        method: "GET",
                        data: {
                            request_type: 'INSERT_A_MOVIE',
                            access_token: access_token,
                            user_id: user_id,
                            title: $('#insert_title').val(),
                            start_date: $('#insert_start_date').val(),
                            end_date: $('#insert_end_date').val(),
                            cinema_id: $('#insert_cinema_name').val(),
                            category: $('#insert_category').val()
                        },
                        success: function(data) {
                            document.querySelector('.bg-modal-insert').style.display = "none";
                            $('#dataTable').html(data);
                        }
                    });
                }
            });

            /*AJAX REQUEST FOR FILLING UPDATE MODAL FORMS*/
            $(document).on('click', '.btn-edit', function() {
                var query_info = $(this).attr("id").split('_');
                var movie_id = query_info[0];
                var access_token = query_info[1];
                
                $.ajax({
                    url: 'http://172.18.1.8/app_logic_mongo.php',  
                    method: "GET",
                    data: {
                        request_type: 'GET_A_MOVIE',
                        access_token: access_token,
                        movie_id: movie_id
                    },
                    dataType: "json",
                    success: function(data) {
                        $('#movie_id').val(data[0].movie_id);
                        $('#title').val(data[0].title);
                        $('#start_date').val(data[0].start_date);
                        $('#end_date').val(data[0].end_date);
                        $('#cinema_name').val(data[0].cinema_name);
                        $('#category').val(data[0].category);

                        document.querySelector('.bg-modal-update').style.display = "flex";
                    },
                }); 
            });
            /* AJAX REQUEST FOR UPDATING A MOVIE */
            $('#update_form').on('click', '.btn-update', function(event) {
                event.preventDefault();
                if ($('#movie_id').val() == "") {
                    alert("Movie ID Required");
                } else if ($('#title').val() == '') {
                    alert("Movie Title Required");
                } else if ($('#start_date').val() == '') {
                    alert("Movie Start date Required");
                } else if ($('#end_date').val() == '') {
                    alert("Movie End date Required");
                } else if ($('#cinema_name').val() == '') {
                    alert("Movie Cinema name Required");
                } else if ($('#category').val() == '') {
                    alert("Movie Category Required");
                }else {
                    var query_info = $(this).attr("id").split('_');
                    var access_token = query_info[1];
                    var user_id = query_info[2];

                    $.ajax({
                        url: 'http://172.18.1.8/app_logic_mongo.php', 
                        method: "GET",
                        data: {
                            request_type: 'UPDATE_A_MOVIE',
                            access_token: access_token,
                            user_id: user_id,
                            movie_id: $('#movie_id').val(),
                            title: $('#title').val(),
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val(),
                            cinema_id: $('#cinema_name').val(),
                            category: $('#category').val()
                        },
                        success: function(data) {
                            document.querySelector('.bg-modal-update').style.display = "none";
                            $('#dataTable').html(data);
                        }
                    });
                }
            });
            /* AJAX REQUEST FOR DELETING A MOVIE */
            $(document).on('click', '.btn-delete', function() {     
            
                var confirm_delete = confirm("Are you sure you want to delete this movie?");

                    if(confirm_delete){
                        var query_info = $(this).attr("id").split('_');
                        var movie_id = query_info[0];
                        var access_token = query_info[1];
                        var user_id = query_info[2];

                        $.ajax({
                            url: 'http://172.18.1.8/app_logic_mongo.php',
                            method: "GET",
                            data: {
                                request_type: 'DELETE_A_MOVIE',
                                movie_id: movie_id,
                                access_token: access_token,
                                user_id: user_id
                            },
                            success: function(data) {
                                $('#dataTable').html(data);
                            }
                        });
                    }
            });
        });

        document.querySelector('.close-insert').addEventListener("click", function() {
            document.querySelector('.bg-modal-insert').style.display = "none";
        });

        document.querySelector('.close-insert-cin').addEventListener("click", function() {
            document.querySelector('.bg-modal-insert-cin').style.display = "none";
        });

        document.querySelector('.close-update').addEventListener("click", function() {
            document.querySelector('.bg-modal-update').style.display = "none";
        });
    </script>    
</body>
</html> 