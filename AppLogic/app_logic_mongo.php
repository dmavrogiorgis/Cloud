<?php
    header("Access-Control-Allow-Origin: *");

    /*CURL REQUEST FOR SELECTING ALL THE MOVIES OF A SPECIFIC CINEMA OWNER*/
    if($_GET['request_type'] == "GET_MOVIES" && isset($_GET['access_token']) && isset($_GET['user_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';

        $user_id = $_GET['user_id'];
        $request_type = $_GET['request_type'];
        
        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'user_id' => $user_id,
                                                ]);
        $movies_curl = curl_init();                                        
        curl_setopt($movies_curl, CURLOPT_URL, $curl_url);
        curl_setopt($movies_curl, CURLOPT_HTTPGET, true);
        curl_setopt($movies_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($movies_curl, CURLOPT_HTTPHEADER, array(
                                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                      ));                                  
        $movies_response = curl_exec($movies_curl);
        curl_close($movies_curl);
       
        echo $movies_response;
    }

    /* AJAX REQUEST FOR INSERT A NEW CINEMA NAME */
    if($_GET['request_type'] == "INSERT_A_CINEMA" && isset($_GET['access_token']) && isset($_GET['user_id']) && isset($_GET['cinema_name'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';

        $user_id = $_GET['user_id'];
        $cinema_name = $_GET['cinema_name'];
        $request_type = $_GET['request_type'];
        
        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'cinema_name' => $cinema_name,
                                                    'user_id' => $user_id,
                                                ]);
        $cinema_curl = curl_init();                                        
        curl_setopt($cinema_curl, CURLOPT_URL, $curl_url);
        curl_setopt($cinema_curl, CURLOPT_HTTPGET, true);
        curl_setopt($cinema_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cinema_curl, CURLOPT_HTTPHEADER, array(
                                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                      ));                                  
        $cinema_response = curl_exec($cinema_curl);
        curl_close($cinema_curl);
       
        echo $cinema_response;
    }

    /* AJAX REQUEST FOR INSERT AND UPDATE A MOVIE FOR A CINEMA OWNER */
    if(($_GET['request_type'] == "INSERT_A_MOVIE" || $_GET['request_type'] == "UPDATE_A_MOVIE") && isset($_GET['access_token']) && isset($_GET['user_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';

        $access_token = $_GET['access_token'];
        $user_id =  $_GET['user_id'];
        $request_type = $_GET['request_type'];
        
        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'user_id' => $user_id,
                                                    'movie_id' => $_GET['movie_id'],
                                                    'title' => $_GET['title'],
                                                    'start_date' => $_GET['start_date'],
                                                    'end_date' => $_GET['end_date'],
                                                    'cinema_id' => $_GET['cinema_id'],
                                                    'category' => $_GET['category']
                                                ]);
        $create_update_curl = curl_init();                                        
        curl_setopt($create_update_curl, CURLOPT_URL, $curl_url);
        curl_setopt($create_update_curl, CURLOPT_HTTPGET, true);
        curl_setopt($create_update_curl, CURLOPT_RETURNTRANSFER, true);   
        curl_setopt($create_update_curl, CURLOPT_HTTPHEADER, array(
                                                                'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                             ));                                     
        $create_update_response = curl_exec($create_update_curl);
        curl_close($create_update_curl);

        $movies = json_decode($create_update_response);
        $last_insert = end($movies);

        if($_GET['request_type']== "INSERT_A_MOVIE"){
            $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';
            
            $curl_url = $data_storage_url . "?" . http_build_query([
                                                        'request_type' => 'GET_A_CINEMA',
                                                        'user_id' => $user_id,
                                                        'cinema_name' => $last_insert->cinema_name
                                                    ]);
            $cinemas_curl = curl_init();                                        
            curl_setopt($cinemas_curl, CURLOPT_URL, $curl_url);
            curl_setopt($cinemas_curl, CURLOPT_HTTPGET, true);
            curl_setopt($cinemas_curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cinemas_curl, CURLOPT_HTTPHEADER, array(
                                                            'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                           ));                                  
            $cinemas_response = curl_exec($cinemas_curl);
            curl_close($cinemas_curl);

            $cinemas_id = json_decode($cinemas_response);
            
            $cur_date = new DateTime("now");
            $start_date = new DateTime($last_insert->start_date);
            $end_date = new DateTime($last_insert->end_date);

            $date_diff_coming = date_diff($start_date,$cur_date)->d;

            if($start_date > $cur_date && $date_diff_coming->d <= 10){
                $isComingSoon = true;
            }else{
                $isComingSoon = false;
            }

            if($start_date <=  $cur_date && $end_date >= $cur_date){
                $isPlaying = true;
            }else{
                $isPlaying = false;
            }

            $data_array = array(
                "id" => $last_insert->id,
                "type" => "Movie",
                "isComingSoon" => $isComingSoon,
                "isPlaying" => $isPlaying,
                "title" => $last_insert->title,
                "start_date" => $last_insert->start_date,
                "end_date" => $last_insert->end_date,
                "cinema_id" => $cinemas_id[0]->id,
                "category" => $last_insert->category,
            );

            /* CURL TO ORION FOR INSERTING A NEW ENTITY */
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://orion_pep_proxy:1027/v2/entities?options=keyValues',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data_array),
                CURLOPT_HTTPHEADER => array(
                                        'Content-Type: application/json', 
                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                      )
            ));
            $response = curl_exec($curl);
            curl_close($curl);

        }else if($_GET['request_type'] == "UPDATE_A_MOVIE"){
            $cur_date = new DateTime("now");
            $start_date = new DateTime($_GET['start_date']);
            $end_date = new DateTime($_GET['end_date']);

            $date_diff_coming = date_diff($start_date,$cur_date)->d;

            if($start_date > $cur_date && $date_diff_coming->d <= 10){
                $isComingSoon = true;
            }else{
                $isComingSoon = false;
            }

            if($start_date <=  $cur_date && $end_date >= $cur_date){
                $isPlaying = true;
            }else{
                $isPlaying = false;
            }

            $data_array = array(
                "isComingSoon" => $isComingSoon,
                "isPlaying" => $isPlaying,
                "title" => $_GET['title'],
                "start_date" => $_GET['start_date'],
                "end_date" => $_GET['end_date'],
                "cinema_id" => $_GET['cinema_id'],
                "category" => $_GET['category']
            );

            /* CURL TO ORION FOR UPDATING AN ENTITY */
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://orion_pep_proxy:1027/v2/entities/' . $_GET['movie_id'] . '/attrs?options=keyValues',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PATCH',
                CURLOPT_POSTFIELDS => json_encode($data_array),
                CURLOPT_HTTPHEADER => array(
                                        'Content-Type: application/json',
                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                      )
            ));
            $response = curl_exec($curl);
            curl_close($curl);
        }

        /* CREATING THE HTML TABLE BEFORE SENDING BACK */
        $output .= '
            <table class="table-bordered" id="dataTable" width="100%" cellspacing="0">
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
        ';
        foreach($movies as $movie) {
            $output .= '
                    <tr>
                        <th> ' . $movie->id . ' </th>
                        <th> ' . $movie->title . ' </th>
                        <th> ' . $movie->start_date . ' </th>
                        <th> ' . $movie->end_date . ' </th>
                        <th> ' . $movie->cinema_name . ' </th>
                        <th> ' . $movie->category . ' </th>
                        <td>
                            <button type="submit" id="' . $movie->id . '_' . $access_token . '_' . $user_id . '" name="edit_btn" class="btn-edit"> EDIT</button>
                        </td>
                        <td>
                            <button type="submit" id="' . $movie->id . '_' . $access_token . '_' . $user_id . '" name="delete_btn" class="btn-delete"> DELETE</button>
                        </td>
                    </tr>
            ';
        }
        $output .= '
                </tbody>
            </table>
        ';
        echo $output;
    }

    /*AJAX REQUEST FOR SELECTING THE MOVIE WE WANT TO UPDATE*/
    if($_GET['request_type'] == "GET_A_MOVIE" && isset($_GET['access_token'])  && isset($_GET['movie_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';

        $movie_id =  $_GET['movie_id'];
        $request_type = $_GET['request_type'];
        
        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'movie_id' => $movie_id
                                                ]);
        $movie_curl = curl_init();                                        
        curl_setopt($movie_curl, CURLOPT_URL, $curl_url);
        curl_setopt($movie_curl, CURLOPT_HTTPGET, true);
        curl_setopt($movie_curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($movie_curl, CURLOPT_HTTPHEADER, array(
                                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                     ));                                 
        $movie_response = curl_exec($movie_curl);
        curl_close($movie_curl);
       
        echo $movie_response;
    }
    /* AJAX REQUEST FOR DELETING A MOVIE FOR A CINEMA OWNER */
    if($_GET['request_type'] == "DELETE_A_MOVIE" && isset($_GET['access_token'])  && isset($_GET['movie_id'])  && isset($_GET['user_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';

        $request_type = $_GET['request_type'];
        $access_token = $_GET['access_token'];
        $movie_id = $_GET['movie_id'];
        $user_id =  $_GET['user_id'];        
        
        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'movie_id' => $movie_id,
                                                    'user_id' => $user_id
                                                ]);
        $delete_curl = curl_init();                                        
        curl_setopt($delete_curl, CURLOPT_URL, $curl_url);
        curl_setopt($delete_curl, CURLOPT_HTTPGET, true);
        curl_setopt($delete_curl, CURLOPT_RETURNTRANSFER, true);      
        curl_setopt($delete_curl, CURLOPT_HTTPHEADER, array(
                                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                      ));                           
        $delete_response = curl_exec($delete_curl);
        curl_close($delete_curl);
        
        /* CURL TO ORION FOR DELETING AN ENTITY */ 
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://orion_pep_proxy:1027/v2/entities/' . $movie_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
                                    'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                  )
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        //TODO DELETE ALL SUBSCRIPTIONS THAT HAVE BEEN CREATED ON THIS ENTITY
        
        $movies = json_decode($delete_response);
        
        /* CREATING THE HTML TABLE BEFORE SENDING BACK */
        $output .= '
        <table class="table-bordered" id="dataTable" width="100%" cellspacing="0">
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
        ';
        foreach($movies as $movie) {
            $output .= '
                    <tr>
                        <th> ' . $movie->id . ' </th>
                        <th> ' . $movie->title . ' </th>
                        <th> ' . $movie->start_date . ' </th>
                        <th> ' . $movie->end_date . ' </th>
                        <th> ' . $movie->cinema_name . ' </th>
                        <th> ' . $movie->category . ' </th>
                        <td>
                            <button type="submit" id="' . $movie->id . '_' . $access_token . '_' . $user_id . '" name="edit_btn" class="btn-edit"> EDIT</button>
                        </td>
                        <td>
                            <button type="submit" id="' . $movie->id . '_' . $access_token . '_' . $user_id . '" name="delete_btn" class="btn-delete"> DELETE</button>
                        </td>
                    </tr>
            ';
        }
        $output .= '
                </tbody>
            </table>
        ';
        echo $output;
    }

    /*CURL REQUEST FOR SELECTING ALL CINEMA NAMES OF AN OWNER*/
    if($_GET['request_type'] == "GET_CINEMAS" && isset($_GET['access_token']) && isset($_GET['user_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';

        $user_id = $_GET['user_id'];
        $request_type = $_GET['request_type'];
        
        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'user_id' => $user_id,
                                                ]);
        $cinemas_curl = curl_init();                                        
        curl_setopt($cinemas_curl, CURLOPT_URL, $curl_url);
        curl_setopt($cinemas_curl, CURLOPT_HTTPGET, true);
        curl_setopt($cinemas_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cinemas_curl, CURLOPT_HTTPHEADER, array(
                                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                       ));                                   
        $cinemas_response = curl_exec($cinemas_curl);
        curl_close($cinemas_curl);
       
        echo $cinemas_response;
    }
    
    /* CURL FOR SHOWING ALL USER MOVIES */
    if($_GET['request_type'] == "GET_USER_MOVIES" && isset($_GET['access_token']) && isset($_GET['user_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';

        $user_id = $_GET['user_id'];
        $request_type = $_GET['request_type'];;
        
        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'user_id' => $user_id,
                                                ]);
        $user_movies_curl = curl_init();                                        
        curl_setopt($user_movies_curl, CURLOPT_URL, $curl_url);
        curl_setopt($user_movies_curl, CURLOPT_HTTPGET, true);
        curl_setopt($user_movies_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($user_movies_curl, CURLOPT_HTTPHEADER, array(
                                                             'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                           ));                                  
        $user_movies_response = curl_exec($user_movies_curl);
        curl_close($user_movies_curl);
       
        echo $user_movies_response;
    }

    /* AJAX FOR ADDING OR DELETING A MOVIE FROM FAVOURITES */
    if($_GET['request_type'] == "ADD_REMOVE_FAVOURITES" && isset($_GET['access_token']) && isset($_GET['user_id']) && isset($_GET['movie_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';
        
        $movie_id = $_GET["movie_id"];
        $access_token = $_GET['access_token'];  
        $user_id = $_GET['user_id'];
        $like_movie = $_GET["like_movie"];
        $request_type = $_GET['request_type'];

        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'access_token' => $access_token,
                                                    'user_id' => $user_id,
                                                    'movie_id' => $movie_id,
                                                    'like_movie' => $like_movie,
                                                ]);
        $favourite_curl = curl_init();                                        
        curl_setopt($favourite_curl, CURLOPT_URL, $curl_url);
        curl_setopt($favourite_curl, CURLOPT_HTTPGET, true);
        curl_setopt($favourite_curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($favourite_curl, CURLOPT_HTTPHEADER, array(
                                                            'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                         ));                                
        $favourite_response = curl_exec($favourite_curl);
        curl_close($favourite_curl);
       
        /* CREATE AND DELETE SUBSCRIPTION FROM ORION */
        if($like_movie == "false"){
            
            $orion_data = array(
                "description" => $user_id,
                "subject" => array(
                    "entities" => array(
                        array(
                            "id" => $movie_id,
                            "type" => "Movie"
                        )
                    ),
                    "condition" => array(
                        "attrs" => array(
                            "isComingSoon",
                            "isPlaying"
                        ),
                    ),
                ),
                "notification" => array(
                    "http" => array(
                        "url" => "http://172.18.1.8/app_logic_orion.php"
                    ),
                    "attrs" => array(
                        "isComingSoon",
                        "isPlaying",
                        "title",
                        "start_date",
                        "end_date",
                        "category",
                        "cinema_id"
                    )
                ),
                "expires" => "2040-01-01T14:00:00.00Z",
                "throttling" => 5
            );

            //CURL TO ORION TO CREATE A NEW SUBSCRIPTION 
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://orion_pep_proxy:1027/v2/subscriptions',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($orion_data),
                CURLOPT_HTTPHEADER => array(
                                        'Content-Type: application/json',
                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                      )
            ));
            $response = curl_exec($curl);
            curl_close($curl);
        }else{
            /* CURL TO DATA STORAGE TO RETRIEVE THE SUB ID */
            $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';

            $curl_url = $data_storage_url . "?" . http_build_query([
                                                        'request_type' => 'GET_SUB_ID',
                                                        'access_token' => $access_token,
                                                        'user_id' => $user_id,
                                                        'movie_id' => $movie_id
                                                    ]);
            $curl = curl_init();                                        
            curl_setopt($curl, CURLOPT_URL, $curl_url);
            curl_setopt($curl, CURLOPT_HTTPGET, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                                                      'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                   ));                                
            $response = curl_exec($curl);
            curl_close($curl);

            $sub_id = json_decode($response,true);

            /* CURL TO ORION TO DELETE A SUBSCRIPTION*/
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://orion_pep_proxy:1027/v2/subscriptions/' . $sub_id["not_id"],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_HTTPHEADER => array(
                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                      )
            ));
            $response = curl_exec($curl);
            curl_close($curl);
        }

        echo $favourite_response;
    }

    /* SEARCH BY TITLE CATEGORY AND CINEMA NAME */
    if($_GET['request_type'] == "SEARCH_MOVIES" && isset($_GET['access_token']) && isset($_GET['user_id']) && isset($_GET['regex'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';
       
        $access_token = $_GET['access_token'];  
        $user_id = $_GET['user_id'];
        $regex = $_GET["regex"];
        $request_type = $_GET['request_type'];

        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'access_token' => $access_token,
                                                    'user_id' => $user_id,
                                                    'regex' => $regex,
                                                ]);
        $search_curl = curl_init();                                        
        curl_setopt($search_curl, CURLOPT_URL, $curl_url);
        curl_setopt($search_curl, CURLOPT_HTTPGET, true);
        curl_setopt($search_curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($search_curl, CURLOPT_HTTPHEADER, array(
                                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                      ));                                
        $search_response = curl_exec($search_curl);
        curl_close($search_curl);

        echo $search_response;
    }

    /* SEARCH BY A DATE RANGE */
    if($_GET['request_type'] == "SEARCH_MOVIES_BY_DATE" && isset($_GET['access_token']) && isset($_GET['user_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';
        
        $request_type = $_GET['request_type'];
        $access_token = $_GET['access_token'];  
        $user_id = $_GET['user_id'];
        $date_1 = $_GET["date_limit_1"];
        $date_2 = $_GET["date_limit_2"];
        
        //var_dump($_GET);
        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $request_type,
                                                    'access_token' => $access_token,
                                                    'user_id' => $user_id,
                                                    'date_limit_1' => $date_1,
                                                    'date_limit_2' => $date_2
                                                ]);
        $search_curl = curl_init();                                        
        curl_setopt($search_curl, CURLOPT_URL, $curl_url);
        curl_setopt($search_curl, CURLOPT_HTTPGET, true);
        curl_setopt($search_curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($search_curl, CURLOPT_HTTPHEADER, array(
                                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                                      ));                                 
        $search_response = curl_exec($search_curl);
        curl_close($search_curl);

        echo $search_response;
    }
?>