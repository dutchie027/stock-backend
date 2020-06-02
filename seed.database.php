<?php

include_once "/opt/configs/config.php";

# Simply the ticker symbol you want to seed the database with 20 years of data with
$symbol = "";

# This is the stock_id from the stock table
$sid = "";

$api = "https://www.alphavantage.co/query";
$url = $api . "?function=TIME_SERIES_DAILY_ADJUSTED&symbol=".$symbol."&apikey=".AV_API."&outputsize=full";

$result = file_get_contents($url);
$ja = json_decode($result, true);

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!empty($ja['Time Series (Daily)'])) {
    foreach ($ja['Time Series (Daily)'] as $date => $results) {
        $cd = $date;
        $open = "";
        $close = "";
        $dividend = "";
        $high = "";
        $low = "";
        foreach ($results as $key => $value) {
            if (strpos($key, 'open') !== false) {
                $open = $value;
            } elseif (strpos($key, '. high') !== false) {
                $high = $value;
            } elseif (strpos($key, '. low') !== false) {
                $low = $value;
            } elseif (strpos($key, '. close') !== false) {
                $close = $value;
            } elseif (strpos($key, '. dividend') !== false) {
                $dividend = $value;
            }
        }
        $query = "insert ignore into stock_price 
        (stock_id, stock_date, open_price, close_price, day_high, day_low, dividends) 
        values (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $GLOBALS['mysqli']->prepare($query);
        $stmt->bind_param("sssssss", $sid, $cd, $open, $close, $high, $low, $dividend);
        $stmt->execute();
    }
}
