<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Anybus API',
    description: 'API documentation for Anybus platform',
    contact: new OA\Contact(email: 'support@anybus.com')
)]
#[OA\Server(
    url: '/api',
    description: 'Main API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
abstract class Controller
{
    //
}
