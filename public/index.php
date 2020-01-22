<?php
// 引入composer
require_once __DIR__ . '/../vendor/autoload.php';
define('API_SRC', realpath(__DIR__ . '/../src'));
require_once API_SRC . "/common/functions.php";
\App::init();
\App::start();
