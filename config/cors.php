<?php

return [

    'paths' => ['api/', 'sanctum/csrf-cookie'],

    'allowed_methods' => [''],

    'allowed_origins' => ['ticketopia-frontend.vercel.app/', 'http://localhost:3000/'], // Specify your frontend origin

    'allowed_origins_patterns' => [],

    'allowed_headers' => [''],

    'exposed_headers' => ['Set-Cookie'], 

    'max_age' => 0,

    'supports_credentials' => true, // Set this to true to allow cookies
];