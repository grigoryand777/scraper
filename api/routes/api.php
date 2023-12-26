<?php

use App\Http\Controllers\JobsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/wasa', [JobsController::class, 'hamster']);

Route::prefix('jobs')->group(function () {
    Route::post('/', [JobsController::class, 'create']);
    Route::delete('/{id}',[JobsController::class, 'delete']);
    Route::get('/{id}', [JobsController::class, 'get']);
});
