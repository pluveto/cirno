<?php
/*
    API Parameters rules
    Auto generated at 2020-01-22 10:23:23
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
    '/auth/logout' => [
        'param' => [
            'password' => [
                'type' => 'string',
                'min' => 3,
                'max' => 19,
                'required' => true,
            ],
        ],
    ],
];