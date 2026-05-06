<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrenatalController;
use App\Http\Controllers\PrenatalRecordController;
use App\Http\Controllers\ChildRecordController;
use App\Http\Controllers\ImmunizationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VaccineController;
use App\Http\Controllers\Midwife\CloudBackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PrenatalCheckupController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SystemAnalysisController;
use Illuminate\Support\Facades\Artisan;


// Redirect root to login
Route::get('/', fn () => redirect()->route('login'));

// Guest routes (rate limiting disabled for demo)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', fn () => view('login'))->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
});

// Google OAuth routes (rate limiting disabled for demo)
Route::middleware([])->group(function () {
    Route::get('/google/auth', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.auth');
    Route::get('/google/callback', [GoogleAuthController::class, 'handleCallback'])->name('google.callback');
});
Route::post('/google/disconnect', [GoogleAuthController::class, 'disconnect'])->name('google.disconnect')->middleware(['auth']);

// Authenticated routes (rate limiting disabled for demo)
Route::middleware(['auth'])->group(function () {

    // Dashboard routes by role
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return match ($user->role) {
            'midwife' => redirect()->route('midwife.dashboard'),
            'bhw'     => redirect()->route('bhw.dashboard'),
            default   => abort(403, 'Unauthorized role'),
        };
    })->name('dashboard');

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
        Route::get('/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
        Route::get('/new', [NotificationController::class, 'getNewNotifications'])->name('notifications.new');
        Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
        Route::delete('/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
        Route::post('/send-test', [NotificationController::class, 'sendTest'])->name('notifications.send-test');
        Route::post('/trigger-checks', [NotificationController::class, 'triggerChecks'])->name('notifications.trigger-checks');
    });

    /* ----------------------------------------------------------
       MIDWIFE AREA — prefixed & named
       ---------------------------------------------------------- */
    Route::prefix('midwife')
         ->middleware(['auth', 'role:midwife'])  // 🔒 SECURITY FIX: Only midwives can access
         ->name('midwife.')
         ->group(function () {

            // Dashboard route
            Route::get('/dashboard', [DashboardController::class, 'index'])
                 ->name('dashboard');

            // Patient Prenatal Record Routes
            Route::resource('patients', PatientController::class);
            Route::get('patients-search', [PatientController::class, 'search'])->name('patients.search');
            Route::get('patients-search-minimal', [PatientController::class, 'searchMinimal'])->name('patients.search.minimal');
            Route::get('patients/{id}/details', [PatientController::class, 'getPatientDetails'])->name('patients.details');
            Route::get('patients/{id}/profile', [PatientController::class, 'profile'])->name('patients.profile');
            Route::get('patients/{id}/print', [PatientController::class, 'printProfile'])->name('patients.print');

            /* --- NEW: complete resource for PrenatalRecord --- */
            Route::resource('prenatalrecord', PrenatalRecordController::class);
            Route::post('prenatalrecord/{id}/complete', [PrenatalRecordController::class, 'completePregnancy'])->name('prenatalrecord.complete');

            //Prenatal Checkup Routes
            Route::resource('prenatalcheckup', PrenatalCheckupController::class);
            Route::get('prenatalcheckup/{id}/data', [PrenatalCheckupController::class, 'getData'])->name('prenatalcheckup.data');
            Route::post('prenatalcheckup/{id}/complete', [PrenatalCheckupController::class, 'markCompleted'])->name('prenatalcheckup.complete');
            Route::put('prenatalcheckup/{id}/schedule', [PrenatalCheckupController::class, 'updateSchedule'])->name('prenatalcheckup.schedule');
            Route::post('prenatalcheckup/{id}/mark-missed', [PrenatalCheckupController::class, 'markAsMissed'])->name('prenatalcheckup.mark-missed');
            Route::post('prenatalcheckup/{id}/reschedule', [PrenatalCheckupController::class, 'rescheduleMissed'])->name('prenatalcheckup.reschedule');
            Route::get('prenatalcheckup-patients/search', [PrenatalCheckupController::class, 'getPatientsWithActivePrenatalRecords'])->name('prenatalcheckup.patients.search');

            //Appointment Routes
            Route::resource('appointments', AppointmentController::class);
            Route::post('appointments/{id}/complete', [AppointmentController::class, 'markCompleted'])->name('appointments.complete');
            Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
            Route::post('appointments/{id}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
            Route::get('appointments-data/upcoming', [AppointmentController::class, 'getUpcoming'])->name('appointments.upcoming');
            Route::get('appointments-data/today', [AppointmentController::class, 'getToday'])->name('appointments.today');

            //Child Record Routes
            Route::resource('childrecord', ChildRecordController::class);
            Route::get('childrecord-search', [ChildRecordController::class, 'search'])->name('childrecord.search');
            
            //Child Immunization Routes
            Route::post('childrecord/{childRecord}/immunizations', [App\Http\Controllers\ChildImmunizationController::class, 'store'])->name('childrecord.immunizations.store');
            Route::put('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'update'])->name('childrecord.immunizations.update');
            Route::delete('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'destroy'])->name('childrecord.immunizations.destroy');



            Route::post('vaccines/stock-transaction', [VaccineController::class, 'stockTransaction'])
                 ->name('vaccines.stock-transaction');

            //Immunization Routes - Specific routes must come BEFORE resource routes
            Route::post('immunization/auto-generate/{childId}', [ImmunizationController::class, 'autoGenerateSchedule'])->name('immunization.auto-generate');
            Route::get('immunization/next-vaccine/{childId}', [ImmunizationController::class, 'getNextRecommendedVaccine'])->name('immunization.next-vaccine');
            Route::get('immunization/children-data', [ImmunizationController::class, 'getChildrenForImmunization'])->name('immunization.children-data');
            Route::get('immunization/child/{childId}/vaccines', [ImmunizationController::class, 'getAvailableVaccinesForChild'])->name('immunization.child-vaccines');
            Route::get('immunization/child/{childId}/vaccines/{vaccineId}/doses', [ImmunizationController::class, 'getAvailableDosesForChild'])->name('immunization.child-doses');
            Route::post('immunization/{id}/mark-missed', [ImmunizationController::class, 'markAsMissed'])->name('immunization.mark-missed');
            Route::post('immunization/{id}/reschedule', [ImmunizationController::class, 'reschedule'])->name('immunization.reschedule');
            Route::post('immunization/{id}/complete', [ImmunizationController::class, 'completeImmunization'])->name('immunization.complete');
            Route::resource('immunization', ImmunizationController::class);

            //Vaccine Routes
            Route::resource('vaccines', VaccineController::class);

            //Report
            Route::get('/reports', [ReportController::class, 'midwifeIndex'])->name('report');
            Route::get('/reports/print', [ReportController::class, 'printView'])->name('report.print');
            Route::get('/reports/bhw-accomplishment', [ReportController::class, 'bhwAccomplishmentPrint'])->name('report.bhw.accomplishment');
            Route::post('/reports/generate', [ReportController::class, 'generateReport'])->name('report.generate');
            Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('report.export.pdf');
            Route::post('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('report.export.excel');

            // System Analysis Report
            Route::get('/system-analysis-report', [SystemAnalysisController::class, 'generateAnalysisReport'])->name('system.analysis.report');

            // SMS Logs Routes
            Route::get('/sms-logs', [\App\Http\Controllers\SmsLogController::class, 'index'])->name('sms-logs.index');
            Route::get('/sms-logs/{id}', [\App\Http\Controllers\SmsLogController::class, 'show'])->name('sms-logs.show');

            // User Management Routes (moved from Admin)
            Route::prefix('user')->name('user.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::get('/{user}', [UserController::class, 'show'])->name('show');
                Route::put('/{user}', [UserController::class, 'update'])->name('update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                Route::patch('/{user}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
                Route::patch('/{user}/activate', [UserController::class, 'activate'])->name('activate');
            });

            // Cloud Backup Management Routes (moved from Admin)
            Route::prefix('cloudbackup')->name('cloudbackup.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'index'])->name('index');
                Route::get('/data', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'getData'])->name('data');
                Route::post('/create', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'store'])->name('store');
                Route::get('/progress/{id}', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'progress'])->name('progress');
                Route::get('/download/{id}', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'download'])->name('download');
                Route::post('/restore', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'restore'])->name('restore');
                Route::get('/restore-progress/{id}', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'restoreProgress'])->name('restore-progress');
                Route::delete('/{id}', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'destroy'])->name('destroy');
                Route::post('/estimate-size', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'estimateSize'])->name('estimate-size');
                Route::post('/sync', [\App\Http\Controllers\Midwife\CloudBackupController::class, 'syncGoogleDrive'])->name('sync');
            });

        });

    // BHW routes (role middleware, no throttling for demo)
    Route::prefix('bhw')
            ->middleware(['auth', 'role:bhw'])
            ->name('bhw.')
            ->group(function () {

            // Dashboard route
            Route::get('/dashboard', [DashboardController::class, 'bhwIndex'])->name('dashboard');
            
            //Patient Routes for BHW
            // Specific routes MUST come before resource routes
            Route::get('patients/search', [PatientController::class, 'search'])->name('patients.search');
            Route::get('patients/search-minimal', [PatientController::class, 'searchMinimal'])->name('patients.search.minimal');
            Route::get('patients/{id}/details', [PatientController::class, 'getPatientDetails'])->name('patients.details');
            Route::get('patients/{id}/profile', [PatientController::class, 'profile'])->name('patients.profile');
            Route::get('patients/{id}/print', [PatientController::class, 'printProfile'])->name('patients.print');
            Route::resource('patients', PatientController::class);

            //Prenatal Record Routes for BHW
            Route::post('prenatalrecord/{id}/complete', [PrenatalRecordController::class, 'completePregnancy'])->name('prenatalrecord.complete');
            Route::resource('prenatalrecord', PrenatalRecordController::class);

            //Prenatal Checkup Routes for BHW
            Route::resource('prenatalcheckup', PrenatalCheckupController::class);
            Route::get('prenatalcheckup/{id}/data', [PrenatalCheckupController::class, 'getData'])->name('prenatalcheckup.data');
            Route::post('prenatalcheckup/{id}/complete', [PrenatalCheckupController::class, 'markCompleted'])->name('prenatalcheckup.complete');
            Route::put('prenatalcheckup/{id}/schedule', [PrenatalCheckupController::class, 'updateSchedule'])->name('prenatalcheckup.schedule');
            Route::post('prenatalcheckup/{id}/mark-missed', [PrenatalCheckupController::class, 'markAsMissed'])->name('prenatalcheckup.mark-missed');
            Route::post('prenatalcheckup/{id}/reschedule', [PrenatalCheckupController::class, 'rescheduleMissed'])->name('prenatalcheckup.reschedule');
            

            //Child Record Routes for BHW
            Route::resource('childrecord', ChildRecordController::class);
            Route::get('childrecord-search', [ChildRecordController::class, 'search'])->name('childrecord.search');

            //Immunization Routes for BHW - Specific routes must come BEFORE resource routes
            Route::post('immunizations/auto-generate/{childId}', [ImmunizationController::class, 'autoGenerateSchedule'])->name('immunizations.auto-generate');
            Route::get('immunizations/next-vaccine/{childId}', [ImmunizationController::class, 'getNextRecommendedVaccine'])->name('immunizations.next-vaccine');
            Route::get('immunizations/children-data', [ImmunizationController::class, 'getChildrenForImmunization'])->name('immunizations.children-data');
            Route::get('immunizations/child/{childId}/vaccines', [ImmunizationController::class, 'getAvailableVaccinesForChild'])->name('immunizations.child-vaccines');
            Route::get('immunizations/child/{childId}/vaccines/{vaccineId}/doses', [ImmunizationController::class, 'getAvailableDosesForChild'])->name('immunizations.child-doses');
            Route::post('immunizations/{id}/mark-missed', [ImmunizationController::class, 'markAsMissed'])->name('immunizations.mark-missed');
            Route::post('immunizations/{id}/reschedule', [ImmunizationController::class, 'reschedule'])->name('immunizations.reschedule');
            Route::resource('immunizations', ImmunizationController::class)->names('immunization');

            //Appointment Routes for BHW
            Route::resource('appointments', AppointmentController::class);
            Route::post('appointments/{id}/complete', [AppointmentController::class, 'markCompleted'])->name('appointments.complete');
            Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
            Route::post('appointments/{id}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
            Route::get('appointments-data/upcoming', [AppointmentController::class, 'getUpcoming'])->name('appointments.upcoming');
            Route::get('appointments-data/today', [AppointmentController::class, 'getToday'])->name('appointments.today');
            
            //Child Immunization Routes for BHW
            Route::post('childrecord/{childRecord}/immunizations', [App\Http\Controllers\ChildImmunizationController::class, 'store'])->name('childrecord.immunizations.store');
            Route::put('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'update'])->name('childrecord.immunizations.update');
            Route::delete('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'destroy'])->name('childrecord.immunizations.destroy');
            
            Route::get('/report', [ReportController::class, 'bhwIndex'])->name('report');
            Route::get('/report/print', [ReportController::class, 'printView'])->name('report.print');
            Route::get('/report/accomplishment', [ReportController::class, 'bhwAccomplishmentPrint'])->name('report.accomplishment');
            Route::post('/report/generate', [ReportController::class, 'generateReport'])->name('report.generate');
            Route::post('/report/export-pdf', [ReportController::class, 'exportPdf'])->name('report.export.pdf');
            Route::post('/report/export-excel', [ReportController::class, 'exportExcel'])->name('report.export.excel');

            // SMS Logs Routes for BHW
            Route::get('/sms-logs', [\App\Http\Controllers\SmsLogController::class, 'index'])->name('sms-logs.index');
            Route::get('/sms-logs/{id}', [\App\Http\Controllers\SmsLogController::class, 'show'])->name('sms-logs.show');
         });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
// TEMPORARY SEED ROUTE — remove after use
Route::get('/seed-users', function() {
    Artisan::call('db:seed', [
        '--class' => 'UserSeeder',
        '--force' => true
    ]);
    return 'User accounts have been seeded!';
});

// TEMPORARY VACCINE SEEDER ROUTE — REMOVE AFTER USE!
Route::get('/seed-vaccines', function() {
    $existingCount = \App\Models\Vaccine::count();
    
    if ($existingCount > 0) {
        return response()->json([
            'status' => 'skipped',
            'message' => 'Vaccines already exist',
            'count' => $existingCount,
            'vaccines' => \App\Models\Vaccine::pluck('name')
        ]);
    }
    
    Artisan::call('db:seed', [
        '--class' => 'VaccineSeeder',
        '--force' => true
    ]);
    
    return response()->json([
        'status' => 'success',
        'message' => 'Vaccines seeded successfully!',
        'count' => \App\Models\Vaccine::count(),
        'vaccines' => \App\Models\Vaccine::pluck('name')
    ]);
});

// TEMPORARY HISTORICAL SEEDER ROUTE — REMOVE AFTER USE!
Route::get('/seed-historical', function() {
    Artisan::call('db:seed', [
        '--class' => 'HistoricalDataSeeder',
        '--force' => true
    ]);
    
    return response()->json([
        'status' => 'success',
        'message' => 'Historical data (2023-2026) seeded successfully!',
        'patients_count' => \App\Models\Patient::count(),
        'prenatal_records_count' => \App\Models\PrenatalRecord::count(),
        'child_records_count' => \App\Models\ChildRecord::count(),
        'immunizations_count' => \App\Models\Immunization::where('status', 'Done')->count(),
    ]);
});