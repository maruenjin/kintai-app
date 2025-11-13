<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendance;
use App\Http\Controllers\Admin\ApplicationController as AdminApp;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\User\AttendanceController       as UserAttendance;
use App\Http\Controllers\User\AttendanceListController   as UserAttendanceList;

use App\Http\Controllers\User\ApplicationController      as UserApplication;

/*
|--------------------------------------------------------------------------
| Public / Guest
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('welcome'))->name('welcome');


Route::get('/admin/login', fn () => view('auth.admin-login'))
    ->name('admin.login')
    ->middleware('guest');
Route::post('/admin/login', function (AdminLoginRequest $request) {
    $credentials = $request->only('email', 'password');

    if (! Auth::attempt($credentials)) {
        return back()->withErrors([
            'email' => __('auth.failed'), 
        ])->onlyInput('email');
    }

   
    if (auth()->user()->role != 1) {
        Auth::logout();

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

  
    return redirect()->route('admin.home');
})->middleware('guest')->name('admin.login.post');



Route::middleware('auth')->get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');


Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', fn () => redirect()->route('admin.attendance.list'))->name('home');

   
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AdminAttendance::class, 'index'])->name('list'); 
        Route::get('{attendance}', [AdminAttendance::class, 'show'])
            ->whereNumber('attendance')->name('show');                    
        Route::put('{attendance}', [AdminAttendance::class, 'update'])
        ->whereNumber('attendance')->name('update');   
    });

   
    Route::get('/applications',                [AdminApp::class, 'index'])->name('apps.index');
    Route::get('/applications/{app}',          [AdminApp::class, 'show'])
        ->whereNumber('app')->name('apps.show');
    Route::post('/applications/{app}/approve', [AdminApp::class, 'approve'])
        ->whereNumber('app')->name('apps.approve');
    Route::post('/applications/{app}/reject',  [AdminApp::class, 'reject'])
        ->whereNumber('app')->name('apps.reject');

   
    Route::get('/staffs', [AdminStaffController::class, 'index'])->name('staffs.index');
    Route::get('/staffs/{user}/monthly', [AdminStaffController::class, 'monthly'])
        ->whereNumber('user')->name('staffs.monthly');
    Route::get('/staffs/{user}/monthly/csv', [AdminStaffController::class, 'csv'])
        ->whereNumber('user')->name('staffs.monthly.csv');

    
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staffs.index.alias');
   
    Route::get('/attendance/staff/{user}', [AdminStaffController::class, 'monthly'])
        ->whereNumber('user')->name('staffs.monthly.alias');
});




Route::middleware(['auth', 'verified'])->prefix('attendance')->name('user.attendance.')->group(function () {
    Route::get('/',            [UserAttendance::class,     'index'])->name('index'); 
    Route::get('list',         [UserAttendanceList::class, 'index'])->name('list');  
     Route::get('detail/{attendance}', [UserAttendanceList::class, 'show'])
            ->whereNumber('attendance')
            ->name('show');
                                
    Route::post('clock-in',  [UserAttendance::class, 'clockIn'])->name('clockin');
    Route::post('break-in',  [UserAttendance::class, 'breakIn'])->name('breakin');
    Route::post('break-out', [UserAttendance::class, 'breakOut'])->name('breakout');
    Route::post('clock-out', [UserAttendance::class, 'clockOut'])->name('clockout');
});


Route::middleware(['auth','verified'])
    ->get('/stamp_correction_request/list', [UserApplication::class, 'index'])
    ->name('user.apps.index.alias');

Route::middleware(['auth','verified'])
    ->get('/stamp_correction_request/create/{attendance}', [UserApplication::class, 'create'])
    ->whereNumber('attendance')->name('user.apps.create.alias');


Route::middleware(['auth', 'verified'])->prefix('applications')->name('user.apps.')->group(function () {
    Route::get('/',                   [UserApplication::class, 'index'])->name('index'); 
    Route::get('create/{attendance}', [UserApplication::class, 'create'])
        ->whereNumber('attendance')->name('create');
    Route::post('{attendance}',       [UserApplication::class, 'store'])
        ->whereNumber('attendance')->name('store');
});



