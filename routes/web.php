<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
Auth::routes();

Route::get('/settings', 'SettingsController@index')->name('settings')->middleware('auth');
Route::get('/vacations', 'VacationController@index')->name('vacations')->middleware('auth');
Route::get('/holidayYears', 'HolidayYearController@index')->middleware('auth');
Route::post('/holidayYears/sync', 'HolidayYearController@sync')->middleware('auth');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', 'UserController@getAllUsers');
Route::get('/activeUsers', 'UserController@getActiveUsers');

Route::post('/addPresence', 'PresenceController@addPresence');
Route::post('/addPresences', 'PresenceController@addPresences');
Route::post('/removePresence', 'PresenceController@removePresence');
Route::post('/removePresences', 'PresenceController@removePresences');
Route::post('/getPresences', 'PresenceController@getPresences');

Route::post('/changeStatus', 'UserController@changeStatus')->middleware('auth');
Route::post('/deleteUser', 'UserController@deleteUser')->middleware('auth');
Route::post('/editUser', 'UserController@editUser')->middleware('auth');
Route::post('/updateOrder', 'UserController@updateOrder')->middleware('auth');

Route::get('/getUserVacations', 'VacationController@getUserVacations')->middleware('auth');
Route::post('/addVacation', 'VacationController@addVacation')->middleware('auth');
Route::post('/getAllUsersVacations', 'VacationController@getAllUsersVacations');
Route::get('/getVacationCard/{id}', 'VacationController@getVacationCard')->middleware('auth');
Route::post('/deleteVacation', 'VacationController@deleteVacation')->middleware('auth');


