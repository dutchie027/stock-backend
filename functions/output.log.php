<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logfn = MONOLOG_LOC . "/". LOG_EXT . ".log";
$g_log = new Logger(LOG_EXT);
if (MONOLOG_LEVEL == "debug") {
    $g_log->pushHandler(new StreamHandler($logfn, Logger::DEBUG));
} elseif (MONOLOG_LEVEL == "info") {
    $g_log->pushHandler(new StreamHandler($logfn, Logger::INFO));
} elseif (MONOLOG_LEVEL == "notice") {
    $g_log->pushHandler(new StreamHandler($logfn, Logger::NOTICE));
} elseif (MONOLOG_LEVEL == "warning") {
    $g_log->pushHandler(new StreamHandler($logfn, Logger::WARNING));
} elseif (MONOLOG_LEVEL == "error") {
    $g_log->pushHandler(new StreamHandler($logfn, Logger::ERROR));
} elseif (MONOLOG_LEVEL == "critical") {
    $g_log->pushHandler(new StreamHandler($logfn, Logger::CRITICAL));
} else {
    $g_log->pushHandler(new StreamHandler($logfn, Logger::WARNING));
}
