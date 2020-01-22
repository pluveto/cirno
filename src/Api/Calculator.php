<?php
namespace App\Api;
class Calculator
{
     /**
     * @api {get} /calc/sqrt Calculate sqrt(num)
     * @apiName sqrt
     * @apiGroup Calculator
     * @apiVersion 0.2
     * @apiPermission none
     * @apiParam {integer{0-200}} num The number to be calculated.
     * @apiSuccess {integer} result The result of calculation.
     * @apiSuccessExample
     *  {
     *  "nonce": "c65a7eeb9f7ea4db282c2eba674e3705ff8747ac"
     *  }
     */
    public function mySqrt()
    {
        $num = \App::$api->request()->query->num;
        \App::$api->json([
            "result" => $num * $num
        ]);
    }
}