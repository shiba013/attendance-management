<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/register', [AuthController::class, 'register']);
Route::post('/register' , [AuthController::class, 'createUser']);

Route::get('/email/verify', [AuthController::class ,'email'])
->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verification'])
->middleware('auth', 'signed')->name('verification.verify');
Route::post('/email/verify/resend', [AuthController::class ,'resend'])
->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'loginUser']);

Route::middleware('auth', 'verified')->group(function ()
{
    Route::get('/attendance', [UserController::class, 'index']);
    Route::post('/attendance', [UserController::class, 'stamping']);
    Route::get('/attendance/list', [UserController::class, 'list']);
    Route::get('/attendance/{id}', [UserController::class, 'detail']);
    Route::get('/stamp_correction_request/list', [UserController::class, 'request']);
});