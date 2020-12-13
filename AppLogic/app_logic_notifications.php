<?php
    header("Access-Control-Allow-Origin: *");
    
    if($_GET['request_type'] == "GET_NEW_NOTIFICATION" && isset($_GET['access_token']) && isset($_GET['user_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage_notifications.php';

        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $_GET['request_type'],
                                                    'user_id' => $_GET['user_id']
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

        echo $response;
    }

    if($_GET['request_type'] == "GET_ALL_NOTIFICATIONS" && isset($_GET['access_token']) && isset($_GET['user_id'])){
        $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage_notifications.php';

        $curl_url = $data_storage_url . "?" . http_build_query([
                                                    'request_type' => $_GET['request_type'],
                                                    'user_id' => $_GET['user_id']
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

        echo $response;
    }
?>