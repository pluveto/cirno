<?php

/**
 * App 静态类
 * 
 * @author ZhangZijing <i@pluvet.com>
 * 
 */
class App
{
    /**
     * @var \Medoo\Medoo
     */
    public static $db;
    /**
     * @var \Flight
     */
    public static $api;
    /**
     * Config
     *
     * @var \App\Helper\FileConfig
     */
    public static $config;

    /**
     * Permission Inspector Midddleware
     *
     * @var \App\Middleware\IMiddleware[]
     */
    public static $middlewares;

    public static function init()
    {
        date_default_timezone_set('Asia/Shanghai');
        \App::$middlewares[] = new \App\MiddleWare\PermissionFilter();
        \App::$middlewares[] = new \App\MiddleWare\ExceptionHandler();
        \App::$api = \Flight::app();
        \App::$config = new \App\Helper\FileConfig(API_SRC . "/common/config");
        \App::$db = new Medoo\Medoo(@include(API_SRC . "/common/config/db.php"));
        require_once API_SRC . "/common/route.php";
    }

    public static function start()
    {
        foreach (self::$middlewares as $middleware) {
            $middleware->init();
        }
        self::$api->start();
    }
}
