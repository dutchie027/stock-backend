<?php

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_errno) {
    print '<p>System is currently undergoing maintenance. Please come back soon.</p>';
    if (DEBUG_MODE) {
        print "<pre>" . $mysqli->connect_error . "</pre>";
    }
    die();
}
