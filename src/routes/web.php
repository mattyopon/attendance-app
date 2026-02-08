<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RestController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminStampCorrectionRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

// 管理者ログイン（認証不要）
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// 一般ユーザー（認証 + メール認証済み + 一般ユーザーのみ）
Route::middleware(['auth', 'verified', 'user'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/rest-start', [RestController::class, 'start'])->name('rest.start');
    Route::post('/attendance/rest-end', [RestController::class, 'end'])->name('rest.end');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/{id}/correction', [StampCorrectionRequestController::class, 'store'])->name('stamp_correction.store');
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])->name('stamp_correction.list');
});

// 管理者（認証 + 管理者権限）
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('admin.attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
    Route::get('/staff/list', [AdminStaffController::class, 'list'])->name('admin.staff.list');
    Route::get('/staff/{id}/attendance', [AdminStaffController::class, 'attendanceList'])->name('admin.staff.attendance');
    Route::get('/staff/{id}/attendance/export', [AdminStaffController::class, 'exportCsv'])->name('admin.staff.attendance.export');
    Route::get('/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'list'])->name('admin.stamp_correction.list');
    Route::get('/stamp_correction_request/{id}/approve', [AdminStampCorrectionRequestController::class, 'show'])->name('admin.stamp_correction.show');
    Route::put('/stamp_correction_request/{id}/approve', [AdminStampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction.approve');
});

// 管理者ログアウト
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout')->middleware('auth');
