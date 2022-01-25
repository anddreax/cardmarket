<?php

use App\Http\Controllers\ActionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['api-auth', 'check-admin'])->group(function(){
    Route::put('/newCard', [ActionsController::class, 'newCard']);
    Route::put('/newCollection', [ActionsController::class, 'newCollection']);
});

Route::get('/listCards', [ActionsController::class, 'listCards'])->middleware(['api-auth', 'check-other']);

Route::get('/listSales', [ActionsController::class, 'listSales'])->middleware('api-auth');

Route::put('/newSale', [ActionsController::class, 'newSale'])->middleware(['api-auth', 'check-other']);

Route::post('/login', [ActionsController::class, 'login']);

Route::put('/newUser', [ActionsController::class, 'newUser']);

Route::get('/recoverypass', [ActionsController::class, 'recoverypass']);

