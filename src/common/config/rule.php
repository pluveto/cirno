<?php
/*
    API Parameters rules
    Auto generated at 2020-01-22 11:10:55
*/
return [
    '/auth/login/email' => [
        'param' => [
            'email' => [
                'type' => 'string',
                'required' => true,
            ],
            'nonce' => [
                'type' => 'string',
                'required' => true,
            ],
            'sign' => [
                'type' => 'string',
                'required' => true,
            ],
        ],
    ],
    '/auth/register/email' => [
        'param' => [
            'username' => [
                'type' => 'string',
                'required' => true,
            ],
            'email' => [
                'type' => 'string',
                'required' => true,
            ],
            'captch' => [
                'type' => 'string',
                'required' => true,
            ],
            'password' => [
                'type' => 'string',
                'required' => true,
            ],
        ],
    ],
    '/calc/sqrt' => [
        'param' => [
            'num' => [
                'type' => 'integer',
                'min' => 0,
                'max' => 200,
                'required' => true,
            ],
        ],
    ],
];