<?php

namespace App\Type\Exception;

use App;
use Exception;

class BadRequestException extends Exception
{
    /**
     * @var int
     */
    public $statusCode;
    /**
     * @var string
     */
    public $message;

    public function __construct($message, $statusCode = 400)
    {
        $this->statusCode = $statusCode;
        $this->message = $message;
        parent::__construct($message);
    }
    public function toArray()
    {
        return [
            "message" => $this->message
        ];
    }
}
