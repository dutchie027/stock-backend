#!/usr/bin/php
<?php

require_once __DIR__ . "/functions/common.php";

$api = "https://www.alphavantage.co/query";

$query = "SELECT * FROM stock";

if ($stocks = $GLOBALS['mysqli']->query($query)) {
    while ($row = $stocks->fetch_assoc()) {
        $symbol = $row['ticker'];
        $sid = $row['stock_id'];

        $url = $api . "?function=TIME_SERIES_DAILY_ADJUSTED&symbol=".$symbol."&apikey=".AV_API;

        $result = file_get_contents($url);
        $ja = json_decode($result, true);

        $day_count = 0;
        if (!empty($ja['Time Series (Daily)'])) {
            foreach ($ja['Time Series (Daily)'] as $date => $results) {
                # we really only want to do this twice because we've already seeded the stock
                $day_count++;
                if ($day_count > 3) {
                    break;
                }
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
        sleep(15);
    }
}

# This bit gets done every day #
$query = "SELECT *, shares * close_price AS current_worth,
shares * purchase_price AS original_worth,
(shares * close_price) - (shares * purchase_price) AS gain_loss,
DATEDIFF(now(),purchase_date) AS days_owned,
((shares * close_price) - (shares * purchase_price)) / DATEDIFF(now(),purchase_date) AS daily_change
FROM signin_stock ss
LEFT JOIN portfolio p ON ss.portfolio_id=p.portfolio_id
INNER JOIN stock_price sp ON ss.stock_id=sp.stock_id
INNER JOIN stock st ON st.stock_id=ss.stock_id
INNER JOIN signin s ON s.signin_id=ss.signin_id
WHERE stock_price_id=(SELECT stock_price_id FROM stock_price 
WHERE stock_id=ss.stock_id ORDER BY stock_date DESC LIMIT 1)
AND (ss.daily_email=1 OR ss.daily_push=1)";

if ($stocks = $GLOBALS['mysqli']->query($query)) {
    while ($row = $stocks->fetch_assoc()) {
        if ($row['daily_push'] == 1 && (strlen($row['pushover_token']) > 10)) {
            $msg = $row['stock_name'] . "\n";
            $msg .= "Opening Price: " . $row['open_price'] . "\n";
            $msg .= "Closing Price: " . $row['close_price']. "\n";
            $msg .= "Current Worth: " . $row['current_worth']. " (".$row['gain_loss'].")\n";
            SendPushover($msg, $row['pushover_token']);
        }
        if ($row['daily_email'] == 1 && (filter_var($row['signin_email'], FILTER_VALIDATE_EMAIL))) {
            $msg = $row['stock_name'] . "\n";
            $msg .= "Opening Price: " . $row['open_price'] . "\n";
            $msg .= "Closing Price: " . $row['close_price']. "\n";
            $msg .= "Current Worth: " . $row['current_worth']. " (".$row['gain_loss'].")\n";
            $to = $row['signin_email'];
            $from = "stock@squarebaboon.com";
            $subject = "Daily Stock Email: " . $row['ticker'];
            SendSMTPMail($to, $from, $subject, $msg);
        }
    }
}

# now if it's a friday we do the weekly bit
if (date("l") == "Friday") {
    # This query will get you all of the prices from the month
    # to get the first one simply order by date and limit 1
    # to get the last one order by date desc and limit 1
    // SELECT *
    // FROM stock_price
    // WHERE stock_id=1
    // AND YEAR(stock_date) = YEAR(CURRENT_DATE()) AND
    //       WEEK(stock_date) = WEEK(CURRENT_DATE());
}

# now if it's the last friday of the month we do the monthly bit
$lastFriday = date('Y-m-d', strtotime('last fri of this month'));
$today = date("Y-m-d");
if ($lastFriday == $today) {
    # This query will get you all of the prices from the month
    # to get the first one simply order by date and limit 1
    # to get the last one order by date desc and limit 1
    // SELECT *
    // FROM stock_price
    // WHERE stock_id=1
    // AND YEAR(stock_date) = YEAR(CURRENT_DATE()) AND
    //       MONTH(stock_date) = MONTH(CURRENT_DATE());
    
    # execute the last friday of the month script
}
