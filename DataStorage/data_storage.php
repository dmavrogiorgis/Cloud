<?php
    require 'vendor/autoload.php';
    
    use MongoDB\BSON\ObjectID;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\Regex;
    use MongoDB\Client;

    $user = "root";
    $pwd = 'rootpassword';
    $mongoclient = new Client("mongodb://mongo_db:27017");    
    
    /* AJAX REQUEST FOR INSERT A NEW CINEMA NAME */
    if($_GET['request_type'] == "INSERT_A_CINEMA" && isset($_GET['user_id']) && isset($_GET['cinema_name'])){
        $collection = $mongoclient->cloud_mongo_db->Cinemas;

        $user_id = $_GET['user_id'];
        $cinema_name = $_GET['cinema_name'];

        $options = [];

        $query = [
            'name' => $cinema_name,
            'owner_id' => $user_id,
        ];
        $cursor = $collection->insertOne($query, $options);

        $query = [
            'name' => $cinema_name,
            'owner_id' => $user_id
        ];

        $options = [
            'projection' => [
                '_id' => '$_id',
                'name' => '$name',
                'owner_id' => '$owner_id'
            ]
        ];

        $cursor = $collection->findOne($query, $options);
        $res_arr_values = array();

        $data = array (
            'id' => $cursor->_id->__toString(),
            'name' => $cursor->name,
            'owner_id' => $cursor->owner_id
        );
        array_push($res_arr_values, $data);

        echo json_encode($res_arr_values);
    }

    /* SELECT AND DISPLAY ALL MOVIES FOR A SPECIFIC CINEMA OWNER */
    if($_GET['request_type'] == "GET_MOVIES"  && isset($_GET['user_id'])){
        $collection = $mongoclient->cloud_mongo_db->Movies;

        $user_id = $_GET['user_id'];

        $options = [
            'allowDiskUse' => TRUE
        ];
        
        $pipeline = [
            [
                '$project' => [
                    '_id' => 0,
                    'Movies' => '$$ROOT'
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'Movies.cinema_id',
                    'from' => 'Cinemas',
                    'foreignField' => '_id',
                    'as' => 'Cinemas'
                ]
            ],
            [
                '$unwind' => [
                    'path' => '$Cinemas',
                    'preserveNullAndEmptyArrays' => FALSE
                ]
            ],
            [
                '$match' => [
                    'Cinemas.owner_id' => $user_id
                ]
            ],
            [
                '$sort' => [
                    'Movies._id' => 1
                ]
            ],
            [
                '$project' => [
                    'Movies._id' => '$Movies._id',
                    'Movies.title' => '$Movies.title',
                    'Movies.start_date' => '$Movies.start_date',
                    'Movies.end_date' => '$Movies.end_date',
                    'Movies.cinema_id' => '$Movies.cinema_id',
                    'Movies.category' => '$Movies.category',
                    'Cinemas.name' => '$Cinemas.name',
                    '_id' => 0
                ]
            ]
        ];
    
        $cursor = $collection->aggregate($pipeline, $options);
        $res_arr_values = array();

        foreach ($cursor as $document) {
            $data = array (
                "id" => $document->Movies->_id->__toString(),
                "title" => $document->Movies->title,
                "start_date" =>  $document->Movies->start_date->toDateTime()->format('Y-m-d'),
                "end_date" =>  $document->Movies->end_date->toDateTime()->format('Y-m-d'),
                "cinema_name" =>  $document->Cinemas->name,
                "category" =>  $document->Movies->category
            );
            array_push($res_arr_values, $data);
        }
        echo json_encode($res_arr_values);
    }

    /* INSERT OR UPDATE A MOVIE FOR A CINEMA OWNER */
    if(($_GET['request_type'] == "INSERT_A_MOVIE" || $_GET['request_type'] == "UPDATE_A_MOVIE") && isset($_GET['user_id'])){
        $collection = $mongoclient->cloud_mongo_db->Movies;
        
        $user_id = $_GET['user_id'];
        $title = $_GET['title'];  
        $start_date = $_GET['start_date'];  
        $end_date = $_GET['end_date'];  
        $cinema_id = $_GET['cinema_id'];
        $category = $_GET['category'];

        /* INSERT QUERY */
        if($_GET['request_type'] == "INSERT_A_MOVIE"){

            $options = [];

            $query = [
                'title' => $title,
                'start_date' => new UTCDateTime(strtotime($start_date)*1000),
                'end_date' => new UTCDateTime(strtotime($end_date)*1000),
                'category' => $category,
                'cinema_id' => new ObjectID($cinema_id)
            ];
            $cursor = $collection->insertOne($query, $options);
        }

        /* UPDATE QUERY */
        if($_GET['request_type'] == "UPDATE_A_MOVIE"){
            $movie_id = $_GET['movie_id'];
            $cursor = $collection->updateOne(
                [ 
                    '_id' => new ObjectID($movie_id) 
                ],
                [ 
                    '$set' => [ 
                        'title' => $title,
                        'start_date' => new UTCDateTime(strtotime($start_date)*1000),
                        'end_date' => new UTCDateTime(strtotime($end_date)*1000),
                        'category' => $category,
                        'cinema_id' => new ObjectID($cinema_id)   
                    ]
                ]
            );
        }

        /* SELECT THE MOVIES ONCE AGAIN TO REFRESH THE PAGE */
        $options = [
            'allowDiskUse' => TRUE
        ];
        
        $pipeline = [
            [
                '$project' => [
                    '_id' => 0,
                    'Movies' => '$$ROOT'
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'Movies.cinema_id',
                    'from' => 'Cinemas',
                    'foreignField' => '_id',
                    'as' => 'Cinemas'
                ]
            ],
            [
                '$unwind' => [
                    'path' => '$Cinemas',
                    'preserveNullAndEmptyArrays' => FALSE
                ]
            ],
            [
                '$match' => [
                    'Cinemas.owner_id' => $user_id
                ]
            ],
            [
                '$sort' => [
                    'Movies._id' => 1
                ]
            ],
            [
                '$project' => [
                    'Movies._id' => '$Movies._id',
                    'Movies.title' => '$Movies.title',
                    'Movies.start_date' => '$Movies.start_date',
                    'Movies.end_date' => '$Movies.end_date',
                    'Movies.cinema_id' => '$Movies.cinema_id',
                    'Movies.category' => '$Movies.category',
                    'Cinemas.name' => '$Cinemas.name',
                    '_id' => 0
                ]
            ]
        ];
    
        $cursor = $collection->aggregate($pipeline, $options);
        $res_arr_values = array();

        foreach ($cursor as $document) {
            $data = array (
                "id" => $document->Movies->_id->__toString(),
                "title" => $document->Movies->title,
                "start_date" =>  $document->Movies->start_date->toDateTime()->format('Y-m-d'),
                "end_date" =>  $document->Movies->end_date->toDateTime()->format('Y-m-d'),
                "cinema_name" =>  $document->Cinemas->name,
                "category" =>  $document->Movies->category
            );
            array_push($res_arr_values, $data);
        }
        echo json_encode($res_arr_values);
    }

    /* SELECT AND DISPLAY TO A MODAL FORM A MOVIE TO UPDATE */
    if($_GET['request_type'] == "GET_A_MOVIE" && isset($_GET['movie_id'])){
        $collection = $mongoclient->cloud_mongo_db->Movies;
        
        $movie_id = $_GET['movie_id'];
        
        $query = [
            '_id' => new ObjectID($movie_id)
        ];

        $options = [];

        $cursor = $collection->find($query, $options);

        $res_arr_values = array();

        foreach ($cursor as $document) {
            $data = array (
                        'movie_id' => $document->_id->__toString(),
                        'title' => $document->title,
                        'start_date' => $document->start_date->toDateTime()->format('Y-m-d'),
                        'end_date' => $document->end_date->toDateTime()->format('Y-m-d'),
                        'cinema_name' => $document->cinema_id->__toString(),
                        'category' => $document->category
            );
            array_push($res_arr_values, $data);
        }
        echo json_encode($res_arr_values);
    }

    /* DELETE A MOVIE FOR A SPECIFIC CINEMA OWNER */
    if($_GET['request_type'] == "DELETE_A_MOVIE"  && isset($_GET['movie_id'])  && isset($_GET['user_id'])){
        $collection = $mongoclient->cloud_mongo_db->Movies;

        $access_token = $_GET['access_token'];
        $movie_id = $_GET['movie_id'];
        $user_id = $_GET['user_id'];

        $query = [
            '_id' => new ObjectID($movie_id)
        ];

        $cursor = $collection->deleteOne($query);

        /* SELECT THE MOVIES ONCE AGAIN TO REFRESH THE PAGE */
        $options = [
            'allowDiskUse' => TRUE
        ];
        
        $pipeline = [
            [
                '$project' => [
                    '_id' => 0,
                    'Movies' => '$$ROOT'
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'Movies.cinema_id',
                    'from' => 'Cinemas',
                    'foreignField' => '_id',
                    'as' => 'Cinemas'
                ]
            ],
            [
                '$unwind' => [
                    'path' => '$Cinemas',
                    'preserveNullAndEmptyArrays' => FALSE
                ]
            ],
            [
                '$match' => [
                    'Cinemas.owner_id' => $user_id
                ]
            ],
            [
                '$sort' => [
                    'Movies._id' => 1
                ]
            ],
            [
                '$project' => [
                    'Movies._id' => '$Movies._id',
                    'Movies.title' => '$Movies.title',
                    'Movies.start_date' => '$Movies.start_date',
                    'Movies.end_date' => '$Movies.end_date',
                    'Movies.cinema_id' => '$Movies.cinema_id',
                    'Movies.category' => '$Movies.category',
                    'Cinemas.name' => '$Cinemas.name',
                    '_id' => 0
                ]
            ]
        ];
    
        $cursor = $collection->aggregate($pipeline, $options);
        $res_arr_values = array();

        foreach ($cursor as $document) {
            $data = array (
                "id" => $document->Movies->_id->__toString(),
                "title" => $document->Movies->title,
                "start_date" =>  $document->Movies->start_date->toDateTime()->format('Y-m-d'),
                "end_date" =>  $document->Movies->end_date->toDateTime()->format('Y-m-d'),
                "cinema_name" =>  $document->Cinemas->name,
                "category" =>  $document->Movies->category
            );
            array_push($res_arr_values, $data);
        }
        echo json_encode($res_arr_values);
    }
    
    /* SELECT ALL POSSIBLE CINEMAS FOR SELECT TAG IN MODAL FORMS */
    if($_GET['request_type'] == "GET_CINEMAS" && isset($_GET['user_id'])){  
        $collection = $mongoclient->cloud_mongo_db->Cinemas;
        
        $user_id = $_GET['user_id'];

        $query = [
            'owner_id' => $user_id
        ];

        $options = [
            'sort' => [
                '_id' => 1
            ],
            'projection' => [
                '_id' => '$_id',
                'name' => '$name'
            ]
        ];
        $cursor = $collection->find($query, $options);

        $res_arr_values = array();

        foreach ($cursor as $document) {
            $data = array (
                'id' => $document->_id->__toString(),
                "name" => $document->name
            );
            array_push($res_arr_values, $data);
        }
        echo json_encode($res_arr_values);
    }

    /* SELECT THE ID OF THE NEW MOVIE ENTITY FOR ORION */
    if($_GET['request_type'] == "GET_A_CINEMA" && isset($_GET['user_id']) && isset($_GET['cinema_name'])){  
        $collection = $mongoclient->cloud_mongo_db->Cinemas;
        
        $user_id = $_GET['user_id'];
        $cinema_name = $_GET['cinema_name'];

        $query = [
            'owner_id' => $user_id,
            'name' => $cinema_name
        ];

        $options = [
            'projection' => [
                '_id' => '$_id'
            ]
        ];

        $cursor = $collection->findOne($query, $options);
        $res_arr_values = array();

        foreach ($cursor as $document) {
            $data = array (
                'id' => $document->__toString()
            );
            array_push($res_arr_values, $data);
        }
        echo json_encode($res_arr_values);
    }

    /* SELECT AND DISPLAY ALL MOVIES FOR A SPECIFIC USER */
    if($_GET['request_type'] == "GET_USER_MOVIES"  && isset($_GET['user_id'])){
        $collection_fav = $mongoclient->cloud_mongo_db->Favourites;
        $collection_cin = $mongoclient->cloud_mongo_db->Cinemas;
        $collection = $mongoclient->cloud_mongo_db->Movies;

        $user_id = $_GET['user_id'];
        
        $query = [];

        $options = [
            'sort' => [
                '_id' => 1
            ],
            'projection' => [
                '_id' => '$_id',
                'title' => '$title',
                'start_date' => '$start_date',
                'end_date' => '$end_date',
                'cinema_id' => '$cinema_id',
                'category' => '$category'
            ]
        ];

        $cursor = $collection->find($query, $options);
        $res_arr_values = array();

        foreach ($cursor as $document) {
            /* SELECT FAVOURITES */
            $options = [];
            $query = [
                'movie_id' => $document->_id,
                'user_id' => $user_id
            ];

            $cursor_fav = $collection_fav->findOne($query, $options);
            
            /* SELECT CINEMAS */
            $query = [
                '_id' => $document->cinema_id
            ];

            $options = [
                'projection' => [
                    'name' => '$name',
                    '_id' => 0
                ]
            ];

            $cursor_cin = $collection_cin->findOne($query,$options);
         
            $data = array (
                "id" => $document->_id->__toString(),
                "title" => $document->title,
                "start_date" =>  $document->start_date->toDateTime()->format('Y-m-d'),
                "end_date" =>  $document->end_date->toDateTime()->format('Y-m-d'),
                "cinema_name" =>  $cursor_cin->name,
                "category" =>  $document->category,
                "user_id" =>  $cursor_fav->user_id
            );
            array_push($res_arr_values, $data);
        }
        echo json_encode($res_arr_values);
    }

    if($_GET['request_type'] == "ADD_REMOVE_FAVOURITES" && isset($_GET['access_token']) && isset($_GET['user_id']) && isset($_GET['movie_id']) && isset($_GET['like_movie'])){
        $collection_fav = $mongoclient->cloud_mongo_db->Favourites;
        $collection_cin = $mongoclient->cloud_mongo_db->Cinemas;
        $collection = $mongoclient->cloud_mongo_db->Movies;

        $movie_id = $_GET["movie_id"]; 
        $access_token = $_GET['access_token'];
        $user_id = $_GET['user_id']; 
        $like_movie = $_GET["like_movie"];

        if($like_movie == "false"){
            $options = [];

            $query = [
                'user_id' => $user_id,
                'movie_id' => new ObjectID($movie_id),
            ];
            $cursor_fav = $collection_fav->insertOne($query, $options);

        }else if($like_movie == "true"){
            $cursor_fav = $collection_fav->deleteOne([
                'user_id' => $user_id,
                'movie_id' => new ObjectID($movie_id)
            ]);
        }

        $query = [];

        $options = [
            'sort' => [
                '_id' => 1
            ],
            'projection' => [
                '_id' => '$_id',
                'title' => '$title',
                'start_date' => '$start_date',
                'end_date' => '$end_date',
                'cinema_id' => '$cinema_id',
                'category' => '$category'
            ]
        ];

        $cursor = $collection->find($query, $options);

        /* CREATING THE HTML TABLE */
        $output .= '
            <table id="movieTable">
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
        ';
        foreach($cursor as $document) {
            $options = [];
            $query = [
                'movie_id' => $document->_id,
                'user_id' => $user_id
            ];

            $cursor_fav = $collection_fav->findOne($query, $options);
            
            $query = [
                '_id' => $document->cinema_id
            ];

            $options = [
                'projection' => [
                    'name' => '$name',
                    '_id' => 0
                ]
            ];

            $cursor_cin = $collection_cin->findOne($query,$options);

            /* CREATING THE HTML TABLE */
            $output .= '
                <tr>
                    <th> ' . $document->_id->__toString() . ' </th>
                    <th> ' . $document->title . ' </th>
                    <th> ' . $document->start_date->toDateTime()->format('Y-m-d') . ' </th>
                    <th> ' . $document->end_date->toDateTime()->format('Y-m-d') . ' </th>
                    <th> ' . $cursor_cin->name . ' </th>
                    <th> ' . $document->category. ' </th>
                    <td>
                        <i id="'; 
                        if($cursor_fav->user_id == NULL){ 
                            $output .= $document->_id->__toString() . "_" . $access_token . "_" . $user_id . "_" . "false" ; 
                        }else{ 
                            $output .= $document->_id->__toString() . "_" . $access_token . "_" . $user_id . "_" . "true"; 
                        } 
                        $output .= '" class="'; 
                        if($cursor_fav->user_id == NULL){ 
                            $output .= "btn-fav"; 
                        }else{ 
                            $output .= "btn-fav press"; 
                        } 
                        $output .= '">
                        </i>
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

    /* AJAX FOR SEARCHING MOVIES BY TITLE, CINEMA NAME AND CATEGORY */
    if($_GET['request_type'] == "SEARCH_MOVIES" && isset($_GET['access_token']) && isset($_GET['user_id']) && isset($_GET['regex'])){
        $collection_fav = $mongoclient->cloud_mongo_db->Favourites;
        $collection = $mongoclient->cloud_mongo_db->Movies;
        
        $access_token = $_GET['access_token'];  
        $user_id = $_GET['user_id'];
        $regex = $_GET["regex"];

        $vowels = array(".", "(", ")");
        $regex = str_replace(".", "\\.", $regex);
        $regex = str_replace("(", "\\(", $regex);
        $regex = str_replace(")", "\\)", $regex);

        $options = [
            'allowDiskUse' => TRUE
        ];

        $pipeline = [
            [
                '$project' => [
                    '_id' => 0,
                    'Movies' => '$$ROOT'
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'Movies.cinema_id',
                    'from' => 'Cinemas',
                    'foreignField' => '_id',
                    'as' => 'Cinemas'
                ]
            ],
            [
                '$unwind' => [
                    'path' => '$Cinemas',
                    'preserveNullAndEmptyArrays' => FALSE
                ]
            ],
            [
                '$match' => [
                    '$or' => [
                        [
                            'Movies.title' => new Regex('^.*' . $regex . '.*$', 'i')
                        ],
                        [
                            'Cinemas.name' => new Regex('^.*' . $regex . '.*$', 'i')
                        ],
                        [
                            'Movies.category' => new Regex('^.*' . $regex . '.*$', 'i')
                        ]
                    ]
                ]
            ],
            [
                '$sort' => [
                    'Movies._id' => 1
                ]
            ],
            [
                '$project' => [
                    'Movies._id' => '$Movies._id',
                    'Movies.title' => '$Movies.title',
                    'Movies.start_date' => '$Movies.start_date',
                    'Movies.end_date' => '$Movies.end_date',
                    'Cinemas.name' => '$Cinemas.name',
                    'Movies.category' => '$Movies.category',
                    '_id' => 0
                ]
            ]
        ];

        $cursor = $collection->aggregate($pipeline, $options);

        /* CREATING THE HTML TABLE */
        $output .= '
            <table id="movieTable">
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
        ';
        foreach($cursor as $document) {
            $query = [
                'movie_id' => $document->Movies->_id,
                'user_id' => $user_id
            ];

            $cursor_fav = $collection_fav->findOne($query, $options);

            /* CREATING THE HTML TABLE */
            $output .= '
                <tr>
                    <th> ' . $document->Movies->_id->__toString() . ' </th>
                    <th> ' . $document->Movies->title . ' </th>
                    <th> ' . $document->Movies->start_date->toDateTime()->format('Y-m-d') . ' </th>
                    <th> ' . $document->Movies->end_date->toDateTime()->format('Y-m-d') . ' </th>
                    <th> ' . $document->Cinemas->name . ' </th>
                    <th> ' . $document->Movies->category. ' </th>
                    <td>
                        <i id="'; 
                        if($cursor_fav->user_id == NULL){ 
                            $output .= $document->Movies->_id->__toString() . "_" . $access_token . "_" . $user_id . "_" . "false" ; 
                        }else{ 
                            $output .= $document->Movies->_id->__toString() . "_" . $access_token . "_" . $user_id . "_" . "true"; 
                        } 
                        $output .= '" class="'; 
                        if($cursor_fav->user_id == NULL){ 
                            $output .= "btn-fav"; 
                        }else{ 
                            $output .= "btn-fav press"; 
                        } 
                        $output .= '">
                        </i>
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

    if($_GET['request_type'] == "SEARCH_MOVIES_BY_DATE" && isset($_GET['access_token']) && isset($_GET['user_id'])){
        $collection_fav = $mongoclient->cloud_mongo_db->Favourites;
        $collection = $mongoclient->cloud_mongo_db->Movies;
        
        $access_token = $_GET['access_token'];  
        $user_id = $_GET['user_id'];
        $date_1 = $_GET["date_limit_1"];
        $date_2 = $_GET["date_limit_2"];
        
        $options = [
            'allowDiskUse' => TRUE
        ];
        
        $pipeline = [
            [
                '$project' => [
                    '_id' => 0,
                    'Movies' => '$$ROOT'
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'Movies.cinema_id',
                    'from' => 'Cinemas',
                    'foreignField' => '_id',
                    'as' => 'Cinemas'
                ]
            ],
            [
                '$unwind' => [
                    'path' => '$Cinemas',
                    'preserveNullAndEmptyArrays' => FALSE
                ]
            ],
            [
                '$match' => [
                    '$and' => [
                        [
                            '$and' => [
                                [
                                    'Movies.start_date' => [
                                        '$gte' => new UTCDateTime(strtotime($date_1)*1000)
                                    ]
                                ],
                                [
                                    'Movies.start_date' => [
                                        '$lte' => new UTCDateTime(strtotime($date_2)*1000)
                                    ]
                                ]
                            ]
                        ],
                        [
                            '$and' => [
                                [
                                    'Movies.end_date' => [
                                        '$gte' => new UTCDateTime(strtotime($date_1)*1000)
                                    ]
                                ],
                                [
                                    'Movies.end_date' => [
                                        '$lte' => new UTCDateTime(strtotime($date_2)*1000)
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                '$sort' => [
                    'Movies._id' => 1
                ]
            ],
            [
                '$project' => [
                    'Movies._id' => '$Movies._id',
                    'Movies.title' => '$Movies.title',
                    'Movies.start_date' => '$Movies.start_date',
                    'Movies.end_date' => '$Movies.end_date',
                    'Cinemas.name' => '$Cinemas.name',
                    'Movies.category' => '$Movies.category',
                    '_id' => 0
                ]
            ]
        ];
        
        $cursor = $collection->aggregate($pipeline, $options);

        /* CREATING THE HTML TABLE */
        $output .= '
            <table id="movieTable">
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
        ';
        foreach ($cursor as $document) {
            $query = [
                'movie_id' => $document->Movies->_id,
                'user_id' => $user_id
            ];

            $cursor_fav = $collection_fav->findOne($query, $options);

            /* CREATING THE HTML TABLE */
            $output .= '
                <tr>
                    <th> ' . $document->Movies->_id->__toString() . ' </th>
                    <th> ' . $document->Movies->title . ' </th>
                    <th> ' . $document->Movies->start_date->toDateTime()->format('Y-m-d') . ' </th>
                    <th> ' . $document->Movies->end_date->toDateTime()->format('Y-m-d') . ' </th>
                    <th> ' . $document->Cinemas->name . ' </th>
                    <th> ' . $document->Movies->category. ' </th>
                    <td>
                        <i id="'; 
                        if($cursor_fav->user_id == NULL){ 
                            $output .= $document->Movies->_id->__toString() . "_" . $access_token . "_" . $user_id . "_" . "false" ; 
                        }else{ 
                            $output .= $document->Movies->_id->__toString() . "_" . $access_token . "_" . $user_id . "_" . "true"; 
                        } 
                        $output .= '" class="'; 
                        if($cursor_fav->user_id == NULL){ 
                            $output .= "btn-fav"; 
                        }else{ 
                            $output .= "btn-fav press"; 
                        } 
                        $output .= '">
                        </i>
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

    if($_GET['request_type'] == "GET_SUB_ID" && isset($_GET['user_id']) && isset($_GET['movie_id'])){
        $collection_sub = $mongoclient->cloud_mongo_db->Subscriptions;

        $deletedsubscription = $collection_sub->findOneAndDelete(
            [ 
                'movie_id' => $_GET['movie_id'],
                'user_id' => $_GET['user_id'] 
            ],
            [
                'projection' => [
                    'not_id' => 1,
                    '_id' => 0
                ],
            ]
        );

        echo json_encode($deletedsubscription);
    }
    
    if($_GET['notification'] == "NOTIFICATION"){
        $data = json_decode(file_get_contents('php://input'), true);
    
        $collection_not = $mongoclient->cloud_mongo_db->Notifications;
        $collection_sub = $mongoclient->cloud_mongo_db->Subscriptions;

        $options = [];

        $query = [
            'not_id' => $data['not_id'],
        ];
        $cursor = $collection_sub->findOne($query, $options);

        if($cursor != null){    //NEW NOTIFICATION FROM ORION
            $options = [];

            $query = [
                'not_id' => $data['not_id'],
                'isRead' => false,
                'isComingSoon' => $data['isComingSoon'],
                'isPlaying' => $data['isPlaying'],
                'movie_id' => $data['movie_id'],
                'title' => $data['title'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'category' => $data['category'],
                'cinema_id' => new ObjectID($data['cinema_id'])
            ];
            $cursor = $collection_not->insertOne($query, $options);
        }else{  //CREATE NEW SUBSCRIPTION
            $curl_url = 'http://172.18.1.8/app_logic_orion.php' . "?" . http_build_query([
                                                                            'request_type' => 'GET_SUB_ID',
                                                                            'sub_id' => $data['not_id']
                                                                        ]);
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            $array = json_decode($response,true);

            $options = [];

            $query = [
                'not_id' => $data['not_id'],
                'movie_id' => $array['movie_id'],
                'user_id' => $array['user_id']
            ];
            $cursor = $collection_sub->insertOne($query, $options);

            $options = [];

            $query = [
                'not_id' => $data['not_id'],
                'isRead' => false,
                'movie_id' => $data['movie_id'],
                'isComingSoon' => $data['isComingSoon'],
                'isPlaying' => $data['isPlaying'],
                'title' => $data['title'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'category' => $data['category'],
                'cinema_id' => new ObjectID($data['cinema_id'])
            ];
            $cursor = $collection_not->insertOne($query, $options);
        }
    }
?>