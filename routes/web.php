<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PublicController;
use App\Http\Controllers\BookingController;

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/check', [PublicController::class, 'check'])->name('check');
Route::get('/track/{code}', [PublicController::class, 'track'])->name('booking.track');
Route::get('/track/{code}/pdf', [PublicController::class, 'downloadPdf'])->name('booking.track.pdf');
Route::get('/tos', function () {
    return view('tos');
})->name('tos');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store')->middleware('throttle:5,1');
Route::get('/booking-list', [PublicController::class, 'bookingList'])->name('booking.list');
Route::post('/booking-change-request', [PublicController::class, 'storeChangeRequest'])->name('booking.change-request')->middleware('throttle:5,1');

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\LaboratoryController;
use App\Http\Controllers\Admin\BusinessUnitController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatisticController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/force-change-password', [ProfileController::class, 'forceChangePassword'])->name('profile.force-change-password');
    
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/statistics', [StatisticController::class, 'index'])->name('statistics.index');
        Route::get('/reports/download-pdf', [\App\Http\Controllers\Admin\ReportController::class, 'downloadPdf'])->name('reports.pdf');
        Route::get('/reports/download-csv', [\App\Http\Controllers\Admin\ReportController::class, 'downloadCsv'])->name('reports.csv');
        Route::get('/reports/download-excel', [\App\Http\Controllers\Admin\ReportController::class, 'downloadExcel'])->name('reports.excel');
        Route::get('laboratories/search', [LaboratoryController::class, 'search'])->name('laboratories.search');
        Route::resource('laboratories', LaboratoryController::class);

        Route::get('lab-mappings', [App\Http\Controllers\Admin\LabMappingController::class, 'index'])->name('lab-mappings.index');
        Route::get('lab-mappings/{laboratory}', [App\Http\Controllers\Admin\LabMappingController::class, 'show'])->name('lab-mappings.show');
        Route::post('lab-mappings/{laboratory}/config', [App\Http\Controllers\Admin\LabMappingController::class, 'updateConfig'])->name('lab-mappings.config');
        Route::post('lab-mappings/{laboratory}/pc', [App\Http\Controllers\Admin\LabMappingController::class, 'savePc'])->name('lab-mappings.save-pc');
        Route::delete('lab-mappings/pc/{labPc}', [App\Http\Controllers\Admin\LabMappingController::class, 'deletePc'])->name('lab-mappings.delete-pc');

        Route::get('pc-damages', [App\Http\Controllers\Admin\PcDamageController::class, 'index'])->name('pc-damages.index');
        Route::post('pc-damages/report/{labPc}', [App\Http\Controllers\Admin\PcDamageController::class, 'report'])->name('pc-damages.report');
        Route::put('pc-damages/{pcDamage}/status', [App\Http\Controllers\Admin\PcDamageController::class, 'updateStatus'])->name('pc-damages.update-status');
        Route::resource('business-units', BusinessUnitController::class);
        Route::post('business-units/{businessUnit}/sub-units', [BusinessUnitController::class, 'storeSubUnit'])->name('business-units.sub-units.store');
        Route::put('sub-units/{subUnit}', [BusinessUnitController::class, 'updateSubUnit'])->name('sub-units.update');
        Route::delete('sub-units/{subUnit}', [BusinessUnitController::class, 'destroySubUnit'])->name('sub-units.destroy');
        
        Route::get('bookings/check-new', [AdminBookingController::class, 'checkNew'])->name('bookings.check-new');
        Route::resource('bookings', AdminBookingController::class);
        
        Route::resource('users', UserController::class);
        Route::get('activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
        
        Route::get('change-requests', [\App\Http\Controllers\Admin\ChangeRequestController::class, 'index'])->name('change-requests.index');
        Route::post('change-requests/{id}/process', [\App\Http\Controllers\Admin\ChangeRequestController::class, 'process'])->name('change-requests.process');
        Route::delete('change-requests/{id}', [\App\Http\Controllers\Admin\ChangeRequestController::class, 'destroy'])->name('change-requests.destroy');
        
        Route::get('settings/smtp', [SettingController::class, 'smtp'])->name('settings.smtp');
        Route::post('settings/smtp', [SettingController::class, 'updateSmtp'])->name('settings.smtp.update');
        
        Route::resource('settings/restricted-emails', \App\Http\Controllers\Admin\RestrictedEmailController::class)
            ->names('restricted-emails')
            ->only(['index', 'store', 'update', 'destroy']);
        
        Route::get('settings/whatsapp', [SettingController::class, 'whatsapp'])->name('settings.whatsapp');
        Route::post('settings/whatsapp', [SettingController::class, 'updateWhatsapp'])->name('settings.whatsapp.update');
        Route::get('settings/domain', [SettingController::class, 'domain'])->name('settings.domain');
        Route::post('settings/domain', [SettingController::class, 'updateDomain'])->name('settings.domain.update');
        Route::get('settings/theme', [SettingController::class, 'theme'])->name('settings.theme');
        Route::post('settings/theme', [SettingController::class, 'updateTheme'])->name('settings.theme.update');
    });
});

require __DIR__.'/auth.php';
