<?php

namespace App\Domain;

use App;

class User
{
    public static function get($id)
    {
        return App::$db->get("user", "*", ["id" => $id]);
    }
}
