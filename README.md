<img src="https://i.ibb.co/mD1SYh0/200px.png" align="left"></img>


# Cirno



Cirno-php is a light-weight php api framework for rapid development, based on Flight and Medoo, with apiDoc support.

## Features

Cirno-php helps create api with many **AUTOMATICAL** operations, which saves your massive time. 

1. Auto generate **http methods, routes** into files.
2. Auto **parameters** basic filte.
3. Auto **user permissions**  filte

Let's take an example:

Create a file `src/Api/Calculator.php`

```php
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
```

And execute:

```shell
$ php generator.php run
```

You can see some files generated:

```php
//file: src/common/route.php
<?php
$apiWelcome = new \App\Api\Welcome();
Flight::route('GET /', array($apiWelcome, 'index'));
```

```php
//file: src/common/rule.php
return [    
    '/calc/sqrt' => [
        'param' => [
            'num' => [
                'type' => 'integereger',
                'min' => 0,
                'max' => 200,
                'required' => true,
            ],
        ],
    ],
];
```

```php
//file: src/common/permission.php
<?php
return[
    '/calc/sqrt'=>'none',
];
```

And then we go to `http://127.0.0.1:8080/calc/sqrt?num=10`

We got:

```json
{
  "result": 100
}
```

What if `http://127.0.0.1:8080/calc/sqrt?num=1000`?

```json
{
  "message": "Parameter `num` is expected to be less than 200. "
}
```

And what if `http://127.0.0.1:8080/calc/sqrt?ss=1000`?

```json
{
  "message": "Mising required parameter."
}
```

And `http://127.0.0.1:8080/calc/sqrt?num=`?

```json
{
  "message": "Parameter `num` is given but it has empty value."
}
```



As you can see, what you need to do is just write API code. API comments will be parsed.

