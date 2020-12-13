<?php include('server.php'); 
    if (empty($_SESSION['username']) || strcmp($_SESSION['role'],"ADMIN")){
        header('location: login.php');
    }

    
?>

<!DOCTYPE html>
<html>
<head>
    <title> Admin - Movies & Cinemas </title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="admin">
    <div class="navbar">
        <a href="admin.php">Home-Movies & Cinemas</a>
        <div class="dropdown">
            <button class="dropbtn" onclick="myFunction()"> <?php echo $_SESSION['username'] . " (" . $_SESSION['role'] . ") " ?> 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content" id="myDropdown">
                <form class="logout_form" method="post" action="admin.php">
                    <button type="submit" name="logout" class="logout_button">Logout</button>
                </form>
            </div>
        </div> 
    </div>

    <!-- ************************************************************USER TABLE************************************************************-->
    <div class="table-div" id="user_table">
        <?php  
             $sql = "SELECT * FROM `Users`";
             $result = $conn->query($sql);
        ?>
        <table class="table-bordered" id="dataTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th> User ID </th>
                    <th> Name </th>
                    <th> Surname </th>
                    <th> Username </th>
                    <th> Password</th>
                    <th> Email </th>
                    <th> Role</th>
                    <th> Confirmed</th>
                    <th> EDIT </th>
                    <th> DELETE </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                ?>
                <tr>
                    <th> <?php echo $row["user_id"]; ?> </th>
                    <th> <?php echo $row["name"]; ?> </th>
                    <th> <?php echo $row["surname"]; ?> </th>
                    <th> <?php echo $row["username"]; ?> </th>
                    <th> <?php echo $row["password"]; ?></th>
                    <th> <?php echo $row["email"]; ?> </th>
                    <th> <?php echo $row["role"]; ?> </th>
                    <th> <?php echo $row["confirmed"]; ?></th>
                    <td>
                        <button  type="submit" id="<?php echo $row["user_id"]; ?>" name="edit_btn" class="btn-edit"> EDIT</button>
                    </td>
                    <td>
                        <button type="submit" id="<?php echo $row["user_id"]; ?>" name="delete_btn" class="btn-delete"> DELETE</button>
                    </td>
                </tr>
                <?php
                        }
                    }else{
                        array_push($errors, "There are no users yet!");
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
            <div class="input-data">
                <label>ID</label>
                <input type="text" placeholder="ID" id="user_id" name="user_id" disabled>
            </div>
            <div class="input-data">
                <label>Name</label>
                <input type="text" placeholder="Name" id="name" name="name" >
            </div>
            <div class="input-data">
                <label>Surname</label>
                <input type="text" placeholder="Surname" id="surname" name="surname" >
            </div>
            <div class="input-data">
                <label>Username</label>
                <input type="text" placeholder="Username" id="username" name="username" >
            </div>
            <div class="input-data">
                <label>Password</label>
                <input type="text" placeholder="Password" id="password" name="password" >
            </div>
            <div class="input-data">
                <label>Email</label>
                <input type="text" placeholder="Email" id="email" name="email" >
            </div>
            <div class="input-data">
                <label>Role</label>
                <select class="roles" id="role" name="role" >
                    <option value=""></option>
                    <option value="ADMIN">ADMIN</option>
                    <option value="CINEMAOWNER">CINEMA OWNER</option>
                    <option value="USER">USER</option>
                </select>
            </div>
            <div class="input-data">
                <label>Confirmed</label>
                <select class="roles" id="confirmed" name="confirmed" >
                    <option value="0"> 0 </option>
                    <option value="1"> 1 </option> 
                </select>
            </div>
                <button  type="submit" id="update" name="update" class="btn-update"> UPDATE </button>  
        </div>
    </div>
    
    <!--************************************************************SCRIPTS************************************************************-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
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

        $(document).ready(function() {
            $(document).on('click', '.btn-edit', function() {
                
                var user_id = $(this).attr("id");
                $.ajax({
                    url: "server.php",
                    method: "POST",
                    data: {
                        user_edit: true,
                        user_id: user_id
                    },
                    dataType: "json",
                    success: function(data) {
                        $('#user_id').val(data.user_id);
                        $('#name').val(data.name);
                        $('#surname').val(data.surname);
                        $('#username').val(data.username);
                        $('#password').val(data.password);
                        $('#email').val(data.email);
                        $('#role').val(data.role);
                        $('#confirmed').val(data.confirmed);

                        document.querySelector('.bg-modal-update').style.display = "flex";
                    }
                });
                
            });

            $('#update_form').on('click', '.btn-update', function(event) {
                event.preventDefault();
                if ($('#user_id').val() == "") {
                    alert("User ID Required");
                } else if ($('#name').val() == '') {
                    alert("User Name Required");
                } else if ($('#surname').val() == '') {
                    alert("User Surame Required");
                } else if ($('#username').val() == '') {
                    alert("User Username Required");
                } else if ($('#password').val() == '') {
                    alert("User Password Required");
                } else if ($('#email').val() == '') {
                    alert("User Email Required");
                } else if ($('#role').val() == '') {
                    alert("User Role Required");
                }else if ($('#confirmed').val() == '') {
                    alert("User Confirmed Required");
                }else {
                    $.ajax({
                        url: "server.php",
                        method: "POST",
                        data: {
                            user_update: true, 
                            user_id: $('#user_id').val(),
                            name: $('#name').val(),
                            surname: $('#surname').val(),
                            username: $('#username').val(),
                            password: $('#password').val(),
                            email: $('#email').val(),
                            role: $('#role').val(),
                            confirmed: $('#confirmed').val()
                        },
                        success: function(data) {
                            document.querySelector('.bg-modal-update').style.display = "none";
                            $('#dataTable').html(data);
                        }
                    });
                }
            });

            $(document).on('click', '.btn-delete', function(event) {

                var confirm_delete = confirm("Are you sure you want to delete this user?");

                if(confirm_delete){
                    var user_del_id = $(this).attr("id");
                
                    $.ajax({
                        url: "server.php",
                        method: "POST",
                        data: {
                            user_delete: true,
                            user_id: user_del_id
                        },
                        success: function(data) {
                            $('#dataTable').html(data);
                        }
                    });
                }
            });
        });

        document.querySelector('.close-update').addEventListener("click", function() {
            document.querySelector('.bg-modal-update').style.display = "none";
        });
    </script>
</body>
</html> 