<?php

include_once "/opt/configs/config.php";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$query = "SELECT *, 
shares * close_price AS current_worth,
shares * purchase_price AS original_worth,
(shares * close_price) - (shares * purchase_price) AS gain_loss,
DATEDIFF(now(),purchase_date) AS days_owned,
((shares * close_price) - (shares * purchase_price)) / DATEDIFF(now(),purchase_date) AS daily_change
FROM user_stock us, stock_price sp, stock s, user_contact uc
WHERE us.stock_id=sp.stock_id
AND s.stock_id=us.stock_id
AND us.user_id=uc.user_id
AND stock_price_id=(SELECT stock_price_id FROM stock_price 
WHERE stock_id=us.stock_id ORDER BY stock_date DESC LIMIT 1)";

if ($stocks = $GLOBALS['mysqli']->query($query)) {
    while ($row = $stocks->fetch_assoc()) {
        $msg = $row['stock_name'] . "\n";
        $msg .= "Opening Price: " . $row['open_price'] . "\n";
        $msg .= "Closing Price: " . $row['close_price']. "\n";
        $msg .= "Current Worth: " . $row['current_worth']. " (".$row['gain_loss'].")\n";
        SendPushover($msg);
    }
}

function SendPushover($message)
{
    curl_setopt_array($ch = curl_init(), array(
        CURLOPT_URL => "https://api.pushover.net/1/messages.json",
        CURLOPT_POSTFIELDS => array(
            "token" => PO_APP_TOKEN,
            "user" => PO_USER_TOKEN,
            "sound" => "cashregister",
            "message" => $message,
        ),
    ));
    curl_exec($ch);
    curl_close($ch);
}
