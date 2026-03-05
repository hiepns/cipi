<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Cipi API',
        'version' => '1.0.0',
        'docs' => '/docs',
        'mcp' => '/mcp',
    ]);
});

Route::get('/docs', function () {
    return response()->file(public_path('api-docs/index.html'));
});
