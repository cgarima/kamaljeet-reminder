<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\BaseApiController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\ReminderController;


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

Route::post('/v1/register', [UserController::class, 'registerUser']);
Route::post('/v1/login', [UserController::class, 'authenticateUser']);
Route::post('/v1/passwordResetLink', [UserController::class, 'sendEmail']);
Route::post('/v1/resetPassword', [UserController::class, 'passwordReset']);



Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('/v1/createNewReminder', [ReminderController::class, 'storeReminder']);
    Route::put('/v1/updateReminder/{id}', [ReminderController::class, 'updateReminder']);
    Route::put('/v1/updateReminderStatus/{id}', [ReminderController::class, 'updateReminderStatus']);
    Route::get('/v1/indexReminder', [ReminderController::class, 'indexReminder']);
    Route::get('/v1/getUpComingReminders', [ReminderController::class, 'getUpComingReminders']);
    Route::get('/v1/getReminder/{id}', [ReminderController::class, 'getReminder']);
    Route::get('/v1/getReminderForDate', [ReminderController::class, 'getReminderForDate']);
    Route::get('/v1/getCompleteReminders', [ReminderController::class, 'getCompleteReminders']);
    Route::get('/v1/getOpenReminders', [ReminderController::class, 'getOpenReminders']);
    Route::delete('/v1/deleteReminderById/{id}', [ReminderController::class, 'deleteReminderById']);
    Route::delete('/v1/deleteReminderForDate', [ReminderController::class, 'deleteReminderForDate']);
    Route::delete('/v1/deleteCompleteReminders', [ReminderController::class, 'deleteCompleteReminders']);   


});


