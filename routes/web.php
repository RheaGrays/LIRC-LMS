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
use App\Http\Controllers\LibraryCollectionController;
use App\Http\Controllers\AcademicController;
use Illuminate\Support\Facades\Route;

// ── Root ─────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('kiosk.index'));
Route::get('/csrf-token', fn() => response()->json(['token' => csrf_token()]))->name('csrf.token');

// ── Kiosk (public) ────────────────────────────────────────────
Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');

// Kiosk AJAX endpoints (protected via KioskTokenAuth middleware — permits local kiosk app, secures remote requests)
Route::prefix('kiosk')->name('kiosk.')->middleware(\App\Http\Middleware\KioskTokenAuth::class)->group(function () {
    Route::post('/lookup',   [AttendanceController::class, 'lookup'])->name('lookup')->middleware('throttle:10,1');
    Route::post('/process',  [AttendanceController::class, 'process'])->name('process')->middleware('throttle:30,1');
    Route::get('/search',    [AttendanceController::class, 'search'])->name('search')->middleware('throttle:60,1');
    Route::post('/log',      [AttendanceController::class, 'log'])->name('log')->middleware('throttle:30,1');
    Route::post('/last',     [AttendanceController::class, 'lastAction'])->name('last')->middleware('throttle:30,1');
    Route::get('/occupancy', [AttendanceController::class, 'occupancy'])->name('occupancy')->middleware('throttle:30,1');
    Route::get('/latest-scan',[AttendanceController::class, 'latestScan'])->name('latest-scan');
});

// ── Student Registration (public, rate-limited) ──────────────
// Critical Fix #2: Rate limited to 10 page views/min and 5 submissions/min
// to prevent DB flooding with fake student records.
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/register', [App\Http\Controllers\StudentRegistrationController::class, 'index'])->name('register.index');
});
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [App\Http\Controllers\StudentRegistrationController::class, 'store'])->name('register.store');
});

// Public API for Academic Departments (Needed for Student Registration dropdowns)
Route::get('/api/academics', [AcademicController::class, 'apiData'])->name('api.academics');

// Public API for Patron Categories (needed for Registration form)
Route::get('/api/patron-categories', [SettingsController::class, 'patronCategories'])->name('api.patron-categories');

// Mobile Camera Photo Sync for Patron Registration
Route::get('/register/mobile-camera', [\App\Http\Controllers\MobilePhotoSyncController::class, 'showMobileCamera'])->name('register.mobile-camera');
Route::post('/api/register/photo-session/create', [\App\Http\Controllers\MobilePhotoSyncController::class, 'createSession'])->name('api.register.photo-session.create');
Route::post('/api/register/photo-session/upload', [\App\Http\Controllers\MobilePhotoSyncController::class, 'uploadPhoto'])->name('api.register.photo-session.upload');
Route::get('/api/register/photo-session/check/{sessionId}', [\App\Http\Controllers\MobilePhotoSyncController::class, 'checkSession'])->name('api.register.photo-session.check');

// Public API for Mobile App (Standalone Expo App)
Route::post('/api/kiosk/process', [AttendanceController::class, 'process'])->name('api.kiosk.process')->middleware('throttle:120,1');

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

    // Archiving Students (Super Admin only, or all roles. Let's make it all roles, or Super Admin? The user didn't specify, I'll restrict to Super Admin since it's destructive)
    Route::middleware('admin.role:Super Admin')->group(function () {
        Route::get('/students/archive', [App\Http\Controllers\StudentArchiveController::class, 'index'])->name('students.archive');
        Route::post('/students/archive/deactivate', [App\Http\Controllers\StudentArchiveController::class, 'bulkDeactivate'])->name('students.archive.deactivate');
    });

    // Dashboard (all roles)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students — view, add, edit (all roles)
    Route::get('/students',           [StudentController::class, 'index'])->name('students.index');
    Route::post('/students',          [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/export',    [StudentController::class, 'export'])->name('students.export');
    Route::get('/students/{id}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{id}',      [StudentController::class, 'update'])->name('students.update');

    // Students — delete, import (Super Admin only)
    Route::middleware('admin.role:Super Admin')->group(function () {
        Route::delete('/students/{id}',   [StudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/students/import',   [StudentController::class, 'import'])->name('students.import');
    });

    // Violations (all roles, except destroy)
    Route::get('/students/{id}/violations',        [ViolationController::class, 'index'])->name('violations.index');
    Route::post('/students/{id}/violations',       [ViolationController::class, 'store'])->name('violations.store');
    Route::put('/violations/{vid}',                [ViolationController::class, 'update'])->name('violations.update');

    // Analytics (all roles)
    Route::get('/analytics',      [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data', [AnalyticsController::class, 'data'])->name('analytics.data');

    // Audit log (all roles)
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');

    // Sections / Section Counter (all roles)
    Route::get('/sections',        [SectionController::class, 'index'])->name('sections.index');
    Route::get('/statistics',      [SectionController::class, 'statistics'])->name('statistics.index');
    Route::get('/sections/latest', [SectionController::class, 'latest'])->name('sections.latest');
    Route::post('/sections/upsert',[SectionController::class, 'upsert'])->name('sections.upsert');
    Route::post('/sections/upload-image', [SectionController::class, 'uploadImage'])->name('sections.upload-image');

    // Library Collections (kiosk slideshow) — all admin roles
    Route::get('/library-collections',                   [LibraryCollectionController::class, 'index'])->name('library-collections.index');
    Route::post('/library-collections',                  [LibraryCollectionController::class, 'store'])->name('library-collections.store');
    Route::put('/library-collections/{libraryCollection}',   [LibraryCollectionController::class, 'update'])->name('library-collections.update');
    Route::delete('/library-collections/{libraryCollection}',[LibraryCollectionController::class, 'destroy'])->name('library-collections.destroy');

    // Settings (Super Admin only)
    Route::middleware('admin.role:Super Admin')->group(function () {
        Route::get('/settings',  [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        
        // Academic Terms (managed in Settings)
        Route::post('/settings/terms', [SettingsController::class, 'storeTerm'])->name('settings.terms.store');
        Route::put('/settings/terms/{id}', [SettingsController::class, 'updateTerm'])->name('settings.terms.update');
        Route::delete('/settings/terms/{id}', [SettingsController::class, 'destroyTerm'])->name('settings.terms.destroy');
    });

    // Academic Setup (all roles)
    Route::get('/academics', [AcademicController::class, 'index'])->name('academics.index');

    // Route::get('/api/academics', ...) has been moved to public routes for Registration page
    Route::post('/academics/departments', [AcademicController::class, 'storeDepartment'])->name('academics.departments.store');
    Route::put('/academics/departments/{id}', [AcademicController::class, 'updateDepartment'])->name('academics.departments.update');
    Route::delete('/academics/departments/{id}', [AcademicController::class, 'destroyDepartment'])->name('academics.departments.destroy');
    
    Route::post('/academics/programs', [AcademicController::class, 'storeProgram'])->name('academics.programs.store');
    Route::put('/academics/programs/{id}', [AcademicController::class, 'updateProgram'])->name('academics.programs.update');
    Route::delete('/academics/programs/{id}', [AcademicController::class, 'destroyProgram'])->name('academics.programs.destroy');


    // Approvals & Violations Destruction (Super Admin only)
    Route::middleware('admin.role:Super Admin')->group(function () {
        Route::get('/approvals',                [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{id}/approve',  [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{id}/reject',   [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::delete('/violations/{vid}',      [ViolationController::class, 'destroy'])->name('violations.destroy');
    });
});
