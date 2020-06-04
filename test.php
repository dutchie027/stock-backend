#!/usr/bin/php
<?php

require_once __DIR__ . "/functions/common.php";

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
    $template = file_get_contents(__DIR__ . '/templates/daily.email.tpl', true);

    while ($row = $stocks->fetch_assoc()) {
        if ($row['daily_email'] == 1 && (filter_var($row['signin_email'], FILTER_VALIDATE_EMAIL))) {
            $vars = array(
                '{{LDATE}}'=> date("M d, Y"),
                '{{FNAME}}'=> $row['fname'],
                '{{TICKER}}'=> $row['ticker'],
                '{{STOCK}}'=> $row['stock_name'],
                '{{PORT_NAME}}'=> $row['portfolio_name'],
                '{{SHARES}}'=> $row['shares'],
                '{{PURCH_DATE}}'=> $row['purchase_date'] . "(" . $row['days_owned'] . " days ago)",
                '{{PURCH_PRICE}}'=> $row['purchase_price'],
                '{{OPEN_PRICE}}'=> $row['open_price'],
                '{{CLOSE_PRICE}}'=> $row['close_price'],
                '{{DAY_CHANGE}}'=> $row['close_price'] - $row['open_price'],
                '{{ORIG_WORTH}}'=> $row['original_worth'],
                '{{CURR_WORTH}}' => $row['current_worth'],
                '{{GAIN_LOSS}}' => $row['gain_loss'],
                '{{PERC_CHG}}' => number_format($row['current_worth'] / $row['original_worth'] * 100 - 100, 2)
            );
            
            $content = str_replace(array_keys($vars), $vars, $template);

            $to = $row['signin_email'];
            $from = "stock@squarebaboon.com";
            $subject = "Daily Stock Email: " . $row['ticker'];
            SendSMTPMail($to, $from, $subject, $content);
        }
    }
}
