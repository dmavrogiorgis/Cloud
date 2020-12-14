<?php
    require 'vendor/autoload.php';

    use MongoDB\BSON\ObjectID;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\Client;

    $user = "root";
    $pwd = 'rootpassword';
    $mongoclient = new Client("mongodb://mongo_db:27017");    

    if($_GET['request_type'] == "GET_NEW_NOTIFICATION" && isset($_GET['user_id'])){
        $collection = $mongoclient->cloud_mongo_db->Notifications;
        
        $user_id = $_GET['user_id'];

        $options = [
            'allowDiskUse' => TRUE
        ];
        
        $pipeline = [
            [
                '$project' => [
                    '_id' => 0,
                    'Notifications' => '$$ROOT'
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'Notifications.not_id',
                    'from' => 'Subscriptions',
                    'foreignField' => 'not_id',
                    'as' => 'Subscriptions'
                ]
            ],
            [
                '$unwind' => [
                    'path' => '$Subscriptions',
                    'preserveNullAndEmptyArrays' => FALSE
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'Notifications.cinema_id',
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
                    'Notifications.isRead' => FALSE,
                    'Subscriptions.user_id' => $user_id
                ]
            ],
            [
                '$project' => [
                    'Notifications.not_id' => '$Notifications.not_id',
                    'Notifications.movie_id' => '$Notifications.movie_id',
                    'Notifications.isComingSoon' => '$Notifications.isComingSoon',
                    'Notifications.isPlaying' => '$Notifications.isPlaying',
                    'Notifications.title' => '$Notifications.title',
                    'Notifications.start_date' => '$Notifications.start_date',
                    'Notifications.end_date' => '$Notifications.end_date',
                    'Cinemas.name' => '$Cinemas.name',
                    'Notifications.category' => '$Notifications.category',
                    '_id' => 0
                ]
            ]
        ];
        
        $cursor = $collection->aggregate($pipeline, $options);
        $res_arr_values = array();

        foreach ($cursor as $document) {

            $cursor_update = $collection->updateMany(
                [ 
                    'not_id' => $document->Notifications->not_id,
                    'movie_id' => $document->Notifications->movie_id
                ],
                [ 
                    '$set' => [ 
                        'isRead' => true
                    ]
                ]
            );

            $data = array (
                "title" => $document->Notifications->title,
                "start_date" =>  $document->Notifications->start_date,
                "end_date" =>  $document->Notifications->end_date,
                "cinema_name" =>  $document->Cinemas->name,
                "category" =>  $document->Notifications->category,
                'isComingSoon' => $document->Notifications->isComingSoon,
                'isPlaying' => $document->Notifications->isPlaying
            );
            array_push($res_arr_values, $data);
        }
        echo json_encode($res_arr_values);
    }

    if($_GET['request_type'] == "GET_ALL_NOTIFICATIONS" && isset($_GET['user_id'])){
        $collection = $mongoclient->cloud_mongo_db->Notifications;
        
        $user_id = $_GET['user_id'];

        $options = [
            'allowDiskUse' => TRUE
        ];
        
        $pipeline = [
            [
                '$project' => [
                    '_id' => 0,
                    'Notifications' => '$$ROOT'
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'Notifications.not_id',
                    'from' => 'Subscriptions',
                    'foreignField' => 'not_id',
                    'as' => 'Subscriptions'
                ]
            ],
            [
                '$unwind' => [
                    'path' => '$Subscriptions',
                    'preserveNullAndEmptyArrays' => FALSE
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'Notifications.cinema_id',
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
                    'Notifications.isRead' => TRUE,
                    'Subscriptions.user_id' => $user_id
                ]
            ],
            [
                '$sort' => [
                    'Notifications._id' => -1
                ]
            ],
            [
                '$project' => [
                    'Notifications.not_id' => '$Notifications.not_id',
                    'Notifications.isComingSoon' => '$Notifications.isComingSoon',
                    'Notifications.isPlaying' => '$Notifications.isPlaying',
                    'Notifications.title' => '$Notifications.title',
                    'Notifications.start_date' => '$Notifications.start_date',
                    'Notifications.end_date' => '$Notifications.end_date',
                    'Cinemas.name' => '$Cinemas.name',
                    'Notifications.category' => '$Notifications.category',
                    '_id' => 0
                ]
            ]
        ];
        
        $cursor = $collection->aggregate($pipeline, $options);
        $res_arr_values = array();

        foreach ($cursor as $document) {
            $data = array (
                "title" => $document->Notifications->title,
                "start_date" =>  $document->Notifications->start_date,
                "end_date" =>  $document->Notifications->end_date,
                "cinema_name" =>  $document->Cinemas->name,
                "category" =>  $document->Notifications->category,
                'isComingSoon' => $document->Notifications->isComingSoon,
                'isPlaying' => $document->Notifications->isPlaying
            );
            array_push($res_arr_values, $data);
        }
        echo json_encode($res_arr_values);
    }
?>