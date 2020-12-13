<?php 
    if($_GET['request_type'] == "GET_INFO" && isset($_GET['email']) && isset($_GET['password'])){
        $client_id = '74812fd6-51be-43f0-96e3-65034ee2f190';        //Client ID
        $client_secret = '77af3ae8-0787-4814-96c5-4e81f5b0e709';    //Client Secret

        $keyrock_token_url = 'http://172.18.1.5:3005/oauth2/token';

        $encrypted_id_secret = base64_encode($client_id . ":" . $client_secret);

        $token_curl = curl_init();
        curl_setopt($token_curl, CURLOPT_URL, $keyrock_token_url);
        curl_setopt($token_curl, CURLOPT_POST, true);
        curl_setopt($token_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($token_curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $encrypted_id_secret, 
                                                           'Content-Type: application/x-www-form-urlencoded'
                                                     ));
        curl_setopt($token_curl, CURLOPT_POSTFIELDS, http_build_query([
                                                        'grant_type' => 'password',
                                                        'username' => $_GET['email'],
                                                        'password' => $_GET['password']
                                                    ]));                                         
        $token_response = curl_exec($token_curl);
        curl_close($token_curl);

        $token_response_dec = json_decode($token_response, true);

        if(strcmp(strval($token_response_dec), "Invalid grant: user credentials are invalid")){
            /* NOW WE ASK FOR USER INFO IF IT EXISTS*/
            $user_info_url = 'http://172.18.1.5:3005/user';

            $access_token = $token_response_dec['access_token'];

            $u_url =  $user_info_url . "?" . http_build_query([
                                                'access_token' => $access_token
                                            ]);

            $user_curl = curl_init();
            curl_setopt($user_curl, CURLOPT_URL, $u_url);
            curl_setopt($user_curl, CURLOPT_HTTPGET, true);
            curl_setopt($user_curl, CURLOPT_RETURNTRANSFER, true);                                 
            $user_response = curl_exec($user_curl);
            curl_close($user_curl);

            $user_response_dec = json_decode($user_response, true);

            $data = array(
                "access_token" => $access_token,
                "user_id" => $user_response_dec["id"],
                "username" =>  $user_response_dec["username"],
                "role" => $user_response_dec["roles"][0]["name"]
            );
            echo json_encode($data);
        }else {
            $data = array();
            echo json_encode($data);
        }
    }

    if($_GET['request_type'] == "CREATE_NEW_USER" && isset($_GET['username']) && isset($_GET['email']) && isset($_GET['role']) && isset($_GET['password'])){
        
        $app_id = "74812fd6-51be-43f0-96e3-65034ee2f190";
        $data = array(
            "name"=> "admin@test.com",
            "password"=> "1234"
        );
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://172.18.1.5:3005/v1/auth/tokens',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => TRUE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json'
            )
        ));
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE); 
        $header = substr($response, 0, $header_size);
        curl_close($curl);
    
        $headers = [];
        $header = rtrim($header);
        $data = explode("\n",$header);
        $headers['status'] = $data[0];
        array_shift($data);
    
        foreach($data as $part){
            $middle = explode(":",$part,2);
            if ( !isset($middle[1]) ) { 
                $middle[1] = null; 
            }
    
            $headers[trim($middle[0])] = trim($middle[1]);
        }
        $token = $headers['X-Subject-Token'];

        $user_info = array(
            "user" => array(
                "username" => $_GET['username'],
                "email" => $_GET['email'],
                "password" => $_GET['password']
            )
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://172.18.1.5:3005/v1/users',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($user_info),
            CURLOPT_HTTPHEADER => array(
                                    'Content-Type:application/json',
                                    'X-Auth-token:' . $token
                                )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $user_info = json_decode($response,true);

        $user_id = $user_info['user']['id'];

        if($_GET['role'] == "CINEMAOWNER"){
            $role_id = "b3ada708-8c4b-4815-a894-d55aa4c755ef";
        }else if($_GET['role'] == "USER"){
            $role_id = "06410ff2-2db2-4f47-9c9c-679b64c7baad";
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://172.18.1.5:3005/v1/applications/' . $app_id . '/users/' . $user_id . '/roles/' . $role_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                                    'Content-Type:application/json',
                                    'X-Auth-token:' . $token
                                )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        echo $response;
    }
?>