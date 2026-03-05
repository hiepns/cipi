<?php

use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\AliasController;
use App\Http\Controllers\Api\SslController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // Apps
    Route::get('/apps', [AppController::class, 'list'])->middleware('ability:apps-view');
    Route::get('/apps/{name}', [AppController::class, 'show'])->middleware('ability:apps-view');
    Route::post('/apps', [AppController::class, 'create'])->middleware('ability:apps-create');
    Route::put('/apps/{name}', [AppController::class, 'edit'])->middleware('ability:apps-edit');
    Route::delete('/apps/{name}', [AppController::class, 'delete'])->middleware('ability:apps-delete');

    // Aliases
    Route::get('/apps/{name}/aliases', [AliasController::class, 'list'])->middleware('ability:aliases-view');
    Route::post('/apps/{name}/aliases/{alias}', [AliasController::class, 'create'])->middleware('ability:aliases-create');
    Route::delete('/apps/{name}/aliases/{alias}', [AliasController::class, 'delete'])->middleware('ability:aliases-delete');

    // SSL
    Route::post('/ssl/{name}', [SslController::class, 'install'])->middleware('ability:ssl-manage');
});
