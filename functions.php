<?php

function sendRequest($url, $data, $headers=[]) {
    $ch = curl_init($url);
    if(!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    } else {
        curl_setopt($ch, CURLOPT_HEADER, false);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resultQuery = curl_exec($ch);
    
   /* $err = curl_error($ch);
    echo "ERR:".$err."+";
    $httpcode = curl_getinfo($ch);
    echo "\n".serialize($httpcode);  */
    
    curl_close($ch);

    

    return json_decode($resultQuery,true);
}

?>