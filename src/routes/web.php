<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommonController;


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

Route::get('/login', [AuthController::class, 'user'])->name('login');
Route::post('/login', [AuthController::class, 'loginUser']);
Route::get('/admin/login', [AuthController::class, 'admin'])->name('admin.login');;
Route::post('/admin/login', [AuthController::class, 'loginAdmin']);

Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

Route::middleware('auth', 'verified')->group(function ()
{
    Route::get('/attendance', [UserController::class, 'index']);
    Route::post('/attendance', [UserController::class, 'stamping']);
    Route::get('/attendance/list', [UserController::class, 'attendanceList']);
    Route::get('/stamp_correction_request/list', [UserController::class, 'requestList']);

    Route::get('/attendance/{id}', [CommonController::class, 'detail']);
    Route::patch('/attendance/{id}', [CommonController::class, 'update']);

    Route::get('/admin/attendance/list', [AdminController::class, 'index']);
    Route::get('/admin/staff/list', [AdminController::class, 'staff']);
    Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'private']);
});