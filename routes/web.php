<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HolidayYearController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacationController;

Auth::routes();

Route::get('/', function () {
    return view('welcome');
});

Route::get('/settings', [SettingsController::class, 'index'])->name('settings')->middleware('auth');
Route::get('/vacations', [VacationController::class, 'index'])->name('vacations')->middleware('auth');
Route::get('/holidayYears', [HolidayYearController::class, 'index'])->middleware('auth');
Route::post('/holidayYears/sync', [HolidayYearController::class, 'sync'])->middleware('auth');

Route::get('/users', [UserController::class, 'getAllUsers']);
Route::get('/activeUsers', [UserController::class, 'getActiveUsers']);

Route::post('/addPresence', [PresenceController::class, 'addPresence']);
Route::post('/addPresences', [PresenceController::class, 'addPresences']);
Route::post('/removePresence', [PresenceController::class, 'removePresence']);
Route::post('/removePresences', [PresenceController::class, 'removePresences']);
Route::post('/getPresences', [PresenceController::class, 'getPresences']);

Route::post('/changeStatus', [UserController::class, 'changeStatus'])->middleware('auth');
Route::post('/deleteUser', [UserController::class, 'deleteUser'])->middleware('auth');
Route::post('/editUser', [UserController::class, 'editUser'])->middleware('auth');
Route::post('/updateOrder', [UserController::class, 'updateOrder'])->middleware('auth');
Route::post('/updateEmployment', [UserController::class, 'updateEmployment'])->middleware('auth');
Route::get('/employmentLogs/{userId}', [UserController::class, 'getEmploymentLogs'])->middleware('auth');

Route::get('/getUserVacations', [VacationController::class, 'getUserVacations'])->middleware('auth');
Route::get('/getAdminVacations', [VacationController::class, 'getAdminVacations'])->middleware('auth');
Route::post('/addVacation', [VacationController::class, 'addVacation'])->middleware('auth');
Route::post('/getAllUsersVacations', [VacationController::class, 'getAllUsersVacations']);
Route::get('/getVacationCard/{id}', [VacationController::class, 'getVacationCard'])->middleware('auth');
Route::post('/deleteVacation', [VacationController::class, 'deleteVacation'])->middleware('auth');
Route::post('/updateCarryover', [VacationController::class, 'updateCarryover'])->middleware('auth');
