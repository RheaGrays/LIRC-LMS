<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AdminSignupController;
use Illuminate\Support\Facades\Route;

// ── Root ─────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('kiosk.index'));

// ── Kiosk (public) ────────────────────────────────────────────
Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');

// Kiosk AJAX endpoints (public — kiosk runs without login)
Route::prefix('kiosk')->name('kiosk.')->middleware('throttle:30,1')->group(function () {
    Route::post('/lookup',   [AttendanceController::class, 'lookup'])->name('lookup');
    Route::post('/log',      [AttendanceController::class, 'log'])->name('log');
    Route::post('/last',     [AttendanceController::class, 'lastAction'])->name('last');
    Route::get('/occupancy', [AttendanceController::class, 'occupancy'])->name('occupancy');
});

// ── Student Registration (public) ─────────────────────────────
Route::get('/register', [App\Http\Controllers\StudentRegistrationController::class, 'index'])->name('register.index');
Route::post('/register', [App\Http\Controllers\StudentRegistrationController::class, 'store'])->name('register.store');

// ── Admin Auth ────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout',[AdminAuthController::class, 'logout'])->name('logout');

    // Signup (unauthenticated — submits a pending approval request)
    Route::post('/signup', [AdminSignupController::class, 'store'])->name('signup');
});

// ── Admin Panel (auth:admin guard required) ───────────────────
Route::prefix('admin')->name('admin.')->middleware('auth.admin')->group(function () {

    // Dashboard (all roles)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students — view, add, edit (all roles)
    Route::get('/students',           [StudentController::class, 'index'])->name('students.index');
    Route::post('/students',          [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{id}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{id}',      [StudentController::class, 'update'])->name('students.update');
    Route::get('/students/export',    [StudentController::class, 'export'])->name('students.export');

    // Students — delete, import (Super Admin only)
    Route::middleware('admin.role:Super Admin')->group(function () {
        Route::delete('/students/{id}',   [StudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/students/import',   [StudentController::class, 'import'])->name('students.import');
    });

    // Violations (all roles)
    Route::get('/students/{id}/violations',        [ViolationController::class, 'index'])->name('violations.index');
    Route::post('/students/{id}/violations',       [ViolationController::class, 'store'])->name('violations.store');
    Route::put('/violations/{vid}',                [ViolationController::class, 'update'])->name('violations.update');
    Route::delete('/violations/{vid}',             [ViolationController::class, 'destroy'])->name('violations.destroy');

    // Analytics (all roles)
    Route::get('/analytics',      [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data', [AnalyticsController::class, 'data'])->name('analytics.data');

    // Audit log (all roles)
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');

    // Sections / Section Counter (all roles)
    Route::get('/sections',        [SectionController::class, 'index'])->name('sections.index');
    Route::get('/sections/latest', [SectionController::class, 'latest'])->name('sections.latest');
    Route::post('/sections/upsert',[SectionController::class, 'upsert'])->name('sections.upsert');

    // Settings (Super Admin only)
    Route::middleware('admin.role:Super Admin')->group(function () {
        Route::get('/settings',  [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    // Approvals (Super Admin only)
    Route::middleware('admin.role:Super Admin')->group(function () {
        Route::get('/approvals',                [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{id}/approve',  [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{id}/reject',   [ApprovalController::class, 'reject'])->name('approvals.reject');
    });
});
