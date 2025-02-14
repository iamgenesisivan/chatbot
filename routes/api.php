<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

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


Route::middleware(['web'])->group(function () {
    Route::post('/chat-stream', [ChatController::class, 'streamResponse']);
    Route::get('/chat-history', [ChatController::class, 'getChatHistory']);
    Route::post('/clear-chat', [ChatController::class, 'clearChatHistory']);
});
