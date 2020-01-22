<?php

namespace App\Api;

class Welcome
{
    /**
     * @api {get} / 欢迎界面
     * @apiName Welcome
     * @apiGroup Welcome
     *
     * @apiSuccess {String} firstname Firstname of the User.
     * @apiSuccess {String} lastname  Lastname of the User.
     */
    public function index()
    {
        $ret = [
            "name" => "Memori Api",
            "version" => "0.2"
        ];
        \Flight::json($ret);
        return;
    }
}
