<?php 
    require 'vendor/autoload.php';
        
    use MongoDB\BSON\ObjectID;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\Regex;
    use MongoDB\Client;

    $user = "root";
    $pwd = 'rootpassword';
    $mongoclient = new Client("mongodb://${user}:${pwd}@mongo_db:27017");   

    $collection = $mongoclient->cloud_mongo_db->Movies;

    $query = [];
    $options = [];

    $cursor = $collection->find($query, $options);

    foreach ($cursor as $document){
        $cur_date = new DateTime("now");
        $start_date = new DateTime($document->start_date->toDateTime()->format('Y-m-d'));
        $end_date = new DateTime($document->end_date->toDateTime()->format('Y-m-d'));

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
            "id" => $document->_id->__toString(),
            "type" => "Movie",
            "isComingSoon" => $isComingSoon,
            "isPlaying" => $isPlaying,
            "title" => $document->title,
            "start_date" => $document->start_date->toDateTime()->format('Y-m-d'),
            "end_date" => $document->end_date->toDateTime()->format('Y-m-d'),
            "cinema_id" => $document->cinema_id->__toString(),
            "category" => $document->category
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://172.18.1.11:1026/v2/entities/' . $document->_id->__toString(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://172.18.1.11:1026/v2/entities?options=keyValues',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data_array),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        ));
        $response = curl_exec($curl);
        curl_close($curl);
    }
?>