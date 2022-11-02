<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('v1/register', [UserController::class, 'register']);
Route::post('v1/login', [UserController::class, 'login']);

Route::get('v1/jumlahuser', [UserController::class, 'jmluser']);
Route::get('v1/all', [UserController::class, 'all']);

Route::middleware('auth:sanctum')->group(function (){
    Route::post('v1/user', [UserController::class, 'updateprofile']);
    Route::post('v1/logout', [UserController::class, 'logout']);
});
