<?php
    if(isset($_POST)){
        $orion_request = file_get_contents('php://input');

        $notification = json_decode($orion_request);
        //file_put_contents('php://stdout', print_r(print_r("\n",true),true));
        file_put_contents('php://stdout', print_r($notification,true));
    
        if($notification->subscriptionId != NULL){
            $notification_data = array(
                'not_id' => $notification->subscriptionId,
                'movie_id' => $notification->data[0]->id,
                'isComingSoon' => $notification->data[0]->isComingSoon->value,
                'isPlaying' => $notification->data[0]->isPlaying->value,
                'title' => $notification->data[0]->title->value,
                'start_date' => $notification->data[0]->start_date->value,
                'end_date' => $notification->data[0]->end_date->value,
                'category' => $notification->data[0]->category->value,
                'cinema_id' => $notification->data[0]->cinema_id->value
            );
    
            $data_storage_url = 'http://data_storage_pep_proxy:1029/data_storage.php';
    
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $data_storage_url . "?" . http_build_query([
                                                                'notification' => 'NOTIFICATION'
                                                            ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($notification_data),
                CURLOPT_HTTPHEADER => array(
                                        'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                      )
            ));
            $response = curl_exec($curl);
            curl_close($curl);
        }
    }

    if($_GET['request_type'] == "GET_SUB_ID"){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://orion_pep_proxy:1027/v2/subscriptions/' . $_GET['sub_id'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                                    'X-Auth-Token: pep_proxy_867aed52-b842-4325-8ddd-8cd48831f134'
                                  )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $array = json_decode($response,true);
        $info = array(
            'movie_id' => $array['subject']['entities'][0]['id'],
            'user_id' => $array['description']
        );
        echo json_encode($info);
    }
?>