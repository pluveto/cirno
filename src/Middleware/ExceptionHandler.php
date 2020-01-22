<?php
namespace App\Middleware;
use App;
use App\Type\Exception\BadRequestException;

class ExceptionHandler
{
    public function init()
    {
        \App::$api->map('error', function (\Exception $ex) {
            if ($ex instanceof BadRequestException) {
                \App::$api->json($ex->toArray(), $ex->statusCode);
            }
            // Handle error
            else {
               var_dump($ex->getMessage());
               var_dump($ex->getTraceAsString());
            }
        });
        \Flight::map('notFound', function () {
            throw new BadRequestException("API not found or method not allowed.", 404);
        });
    }
}
