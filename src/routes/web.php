<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AttendanceController as AdminAttendance;
use App\Http\Controllers\Admin\ApplicationController as AdminApp;
use App\Http\Controllers\Admin\StaffController as AdminStaff;

use App\Http\Controllers\User\AttendanceController as UserAttendance;
use App\Http\Controllers\User\AttendanceListController as UserAttendanceList;
use App\Http\Controllers\User\ApplicationController as UserApplication;

// ============================================================
// Public / Guest
// ============================================================
Route::get('/', fn () => view('welcome'));

// 管理者ログイン画面（未ログインのみ）
Route::get('/admin/login', fn () => view('auth.admin-login'))
    ->name('admin.login')
    ->middleware('guest');

// Fortify メール認証 画面（ログイン済みだが未認証ユーザー）
Route::middleware('auth')->get('/email/verify', fn () => view('auth.verify-email'))
    ->name('verification.notice');

// ============================================================
// Admin Area  (auth + admin)
// URL: /admin/...
// Route names: admin.*
// ============================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // 勤怠（管理）
    Route::get('/attendance/list', [AdminAttendance::class, 'index'])->name('attendance.list');
    Route::get('/attendance/{attendance}', [AdminAttendance::class, 'show'])->name('attendance.show');
    Route::put('/attendance/{attendance}', [AdminAttendance::class, 'update'])->name('attendance.update');

    // 申請（管理）
    Route::get('/applications', [AdminApp::class, 'index'])->name('apps.index');
    Route::get('/applications/{app}', [AdminApp::class, 'show'])->name('apps.show');
    Route::put('/applications/{app}/approve', [AdminApp::class, 'approve'])->name('apps.approve');
    Route::put('/applications/{app}/reject',  [AdminApp::class, 'reject'])->name('apps.reject');

    // スタッフ
    Route::get('/staffs', [AdminStaff::class, 'index'])->name('staff.index');
    Route::get('/staffs/{user}/monthly', [AdminStaff::class, 'monthly'])->name('staff.monthly');
    Route::get('/staffs/{user}/monthly/csv', [AdminStaff::class, 'csv'])->name('staff.csv');
});

// ============================================================
// User Area  (auth + verified)
// attendance: /attendance/...
// applications: /applications/...
// ============================================================

// 打刻 + 月次一覧 + 詳細
Route::middleware(['auth', 'verified'])->prefix('attendance')->name('user.attendance.')->group(function () {
    Route::get('/', [UserAttendance::class, 'index'])->name('index');
    Route::get('list', [UserAttendanceList::class, 'index'])->name('list');               // 先に宣言
    Route::get('{attendance}', [UserAttendanceList::class, 'show'])                      // 詳細
        ->whereNumber('attendance')->name('show');

    // 打刻アクション
    Route::post('clock-in',  [UserAttendance::class, 'clockIn'])->name('clockin');
    Route::post('break-in',  [UserAttendance::class, 'breakIn'])->name('breakin');
    Route::post('break-out', [UserAttendance::class, 'breakOut'])->name('breakout');
    Route::post('clock-out', [UserAttendance::class, 'clockOut'])->name('clockout');
}); // ← ここで確実に閉じる！

// 申請（ユーザー）※ attendance とは別プレフィックス
Route::middleware(['auth', 'verified'])->prefix('applications')->name('user.apps.')->group(function () {
    Route::get('/', [UserApplication::class, 'index'])->name('index');
    Route::get('create/{attendance}', [UserApplication::class, 'create'])
        ->whereNumber('attendance')->name('create');
    // 申請対象の勤怠IDをURLで受ける（createの送信先）
    Route::post('{attendance}', [UserApplication::class, 'store'])
        ->whereNumber('attendance')->name('store');
});

// ============================================================
// Optional: 404 fallback
// ============================================================
// Route::fallback(fn () => abort(404));
