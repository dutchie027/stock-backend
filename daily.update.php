<?php

include_once "/opt/configs/config.php";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$api = "https://www.alphavantage.co/query";

$query = "SELECT * FROM stock";

if ($stocks = $GLOBALS['mysqli']->query($query)) {
    while ($row = $stocks->fetch_assoc()) {
        $symbol = $row['ticker'];
        $sid = $row['stock_id'];

        $url = $api . "?function=TIME_SERIES_DAILY_ADJUSTED&symbol=".$symbol."&apikey=".AV_API;

        $result = file_get_contents($url);
        $ja = json_decode($result, true);

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
    }
}
