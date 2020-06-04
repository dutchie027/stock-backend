<?php

function SendPushover($message, $token)
{
    curl_setopt_array($ch = curl_init(), array(
        CURLOPT_URL => "https://api.pushover.net/1/messages.json",
        CURLOPT_POSTFIELDS => array(
            "token" => PO_APP_TOKEN,
            "user" => $token,
            "sound" => "cashregister",
            "message" => $message,
        ),
    ));
    curl_exec($ch);
    curl_close($ch);
}
