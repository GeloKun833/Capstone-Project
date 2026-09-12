<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\Setting;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AccountsController;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SemesterController;

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

/** for side bar menu active */
if (!function_exists('set_active')) {
    function set_active( $route ) {
        if( is_array( $route ) ){
            return in_array(Request::path(), $route) ? 'active' : '';
        }
        return Request::path() == $route ? 'active' : '';
    }
}

Route::get('/', function () {
    return view('auth.login');
});

Route::group(['middleware'=>'auth'],function()
{
    Route::get('home',function()
    {
        return view('home');
    });
});


Route::group(['namespace' => 'App\Http\Controllers\Auth'],function()
{
    // ----------------------------login ------------------------------//
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'authenticate');
        Route::get('/logout', 'logout')->name('logout');
        Route::post('change/password', 'changePassword')->name('change/password');
    });

    // ----------------------------password reset ------------------------------//
    Route::controller(\App\Http\Controllers\Auth\PasswordResetLinkController::class)->group(function () {
        Route::get('password/reset', 'create')->name('password.request');
        Route::post('password/email', 'store')->name('password.email');
    });

    Route::controller(\App\Http\Controllers\Auth\NewPasswordController::class)->group(function () {
        Route::get('password/reset/{token}', 'create')->name('password.reset');
        Route::post('password/reset', 'store')->name('password.update');
    });

});

Route::group(['namespace' => 'App\Http\Controllers'],function()
{
    // -------------------------- main dashboard ----------------------//
    Route::controller(HomeController::class)->group(function () {
            Route::get('/home', 'index')->middleware('auth')->name('home');
    Route::get('/dashboard', 'dashboard')->middleware('auth')->name('dashboard');
    Route::get('user/profile/page', 'userProfile')->middleware('auth')->name('user/profile/page');
    Route::get('teacher/classes', 'teacherClasses')->middleware(['auth', 'role:Teacher'])->name('teacher.classes');
    Route::get('teacher/subjects', 'teacherSubjects')->middleware(['auth', 'role:Teacher'])->name('teacher.subjects');
    });



    // ----------------------------- user controller ---------------------//
    Route::controller(UserManagementController::class)->group(function () {
        Route::get('list/users', 'index')->middleware(['auth', 'role:Admin'])->name('list/users');
        Route::get('list/parents', 'parentList')->middleware(['auth', 'role:Admin'])->name('list/parents');
        Route::post('change/password', 'changePassword')->name('change/password');
        Route::get('view/user/edit/{id}', 'userView')->middleware(['auth', 'role:Admin']);
        Route::post('user/update', 'userUpdate')->middleware(['auth', 'role:Admin'])->name('user/update');
        Route::post('user/delete', 'userDelete')->middleware(['auth', 'role:Admin'])->name('user/delete');
        Route::get('get-users-data', 'getUsersData')->middleware(['auth', 'role:Admin'])->name('get-users-data'); /** get all data users */
    });

    // User profile routes (HomeController)
    Route::controller(HomeController::class)->group(function () {
        Route::get('user/profile/edit', 'editProfile')->middleware('auth')->name('user/profile/edit');
        Route::post('user/profile/update', 'updateProfile')->middleware('auth')->name('user/profile/update');
        Route::post('user/password/update', 'updatePassword')->middleware('auth')->name('user/password/update');
        Route::get('activity-log', 'activityLog')->middleware('auth')->name('activity.log');
        Route::get('admin/activity-log', 'adminActivityLog')->middleware(['auth', 'role:Admin'])->name('admin.activity.log');
    });

    // ------------------------ setting -------------------------------//
    Route::controller(Setting::class)->group(function () {
        Route::get('setting/page', 'index')->middleware(['auth', 'role:Admin'])->name('setting/page');
        Route::post('setting/update', 'updateSettings')->middleware(['auth', 'role:Admin'])->name('setting/update');
        Route::post('setting/delete-file', 'deleteFile')->middleware(['auth', 'role:Admin'])->name('setting/delete-file');
        Route::get('setting/access-limits', 'accessLimits')->middleware(['auth', 'role:Admin'])->name('setting.access-limits');
        Route::post('setting/access-limits', 'updateAccessLimits')->middleware(['auth', 'role:Admin'])->name('setting.access-limits.update');
    });

    // ------------------------ backup (admin) -------------------------------//
    Route::group(['prefix' => 'admin/backup', 'middleware' => ['auth', 'role:Admin']], function () {
        Route::get('/', [App\Http\Controllers\BackupController::class, 'index'])->name('admin.backup.index');
        Route::post('/create', [App\Http\Controllers\BackupController::class, 'create'])->name('admin.backup.create');
        Route::get('/download/{filename}', [App\Http\Controllers\BackupController::class, 'download'])->name('admin.backup.download');
        Route::delete('/delete/{filename}', [App\Http\Controllers\BackupController::class, 'destroy'])->name('admin.backup.destroy');
    });

    // ------------------------ student -------------------------------//
    Route::controller(StudentController::class)->group(function () {
        Route::get('student/list', 'student')->middleware(['auth', 'role:Admin'])->name('student/list'); // list student
        Route::get('student/grid', 'studentGrid')->middleware(['auth', 'role:Admin'])->name('student/grid'); // grid student
        Route::get('student/add/page', 'studentAdd')->middleware(['auth', 'role:Admin'])->name('student/add/page'); // page student
        Route::post('student/add/save', 'studentSave')->middleware(['auth', 'role:Admin'])->name('student/add/save'); // save record student
        Route::get('student/edit/{id}', 'studentEdit')->middleware(['auth', 'role:Admin']); // view for edit
        Route::post('student/update', 'studentUpdate')->middleware(['auth', 'role:Admin'])->name('student/update'); // update record student
        Route::post('student/delete', 'studentDelete')->middleware(['auth', 'role:Admin'])->name('student/delete'); // delete record student
        Route::get('student/profile/{id}', 'studentProfile')->middleware('auth'); // profile student
    });

    // Restore archived student
    Route::post('student/restore/{id}', [App\Http\Controllers\StudentController::class, 'restore'])->name('student.restore');

    // ------------------------ student promotions -------------------------------//
    Route::controller(App\Http\Controllers\PromotionController::class)->group(function () {
        Route::get('promotions', 'index')->middleware(['auth', 'role:Admin'])->name('promotions.index');
        Route::get('promotions/create', 'create')->middleware(['auth', 'role:Admin'])->name('promotions.create');
        Route::post('promotions/store', 'store')->middleware(['auth', 'role:Admin'])->name('promotions.store');
        Route::get('promotions/history', 'history')->middleware(['auth', 'role:Admin'])->name('promotions.history');
        Route::get('promotions/student/{studentId}', 'studentHistory')->middleware(['auth', 'role:Admin'])->name('promotions.student-history');
        Route::delete('promotions/{id}', 'destroy')->middleware(['auth', 'role:Admin'])->name('promotions.destroy');
    });

    // ------------------------ teacher -------------------------------//
    Route::controller(TeacherController::class)->group(function () {
        Route::get('teacher/add/page', 'teacherAdd')->middleware(['auth', 'role:Admin'])->name('teacher/add/page'); // page teacher
        Route::get('teacher/list/page', 'teacherList')->middleware(['auth', 'role:Admin'])->name('teacher/list/page'); // page teacher
        Route::get('teacher/grid/page', 'teacherGrid')->middleware(['auth', 'role:Admin'])->name('teacher/grid/page'); // page grid teacher
        Route::post('teacher/save', 'saveRecord')->middleware(['auth', 'role:Admin'])->name('teacher/save'); // save record
        Route::get('teacher/edit/{user_id}', 'editRecord')->middleware(['auth', 'role:Admin']); // view teacher record
        Route::post('teacher/update', 'updateRecordTeacher')->middleware(['auth', 'role:Admin'])->name('teacher/update'); // update record
        Route::post('teacher/delete', 'teacherDelete')->middleware(['auth', 'role:Admin'])->name('teacher/delete'); // delete record teacher
        Route::post('teacher/sync-users', 'syncTeacherUsers')->middleware(['auth', 'role:Admin'])->name('teacher/sync-users'); // sync teacher users
        Route::get('teacher/sis/{user_id}', 'viewTIS')->middleware(['auth', 'role:Admin|Registrar'])->name('teacher.sis'); // Teacher Information System
    });

    // ----------------------- department -----------------------------//
    Route::controller(DepartmentController::class)->group(function () {
        Route::get('department/list/page', 'departmentList')->middleware(['auth', 'role:Admin'])->name('department/list/page'); // department/list/page
        Route::get('department/add/page', 'indexDepartment')->middleware(['auth', 'role:Admin'])->name('department/add/page'); // page add department
        Route::get('department/edit/{department_id}', 'editDepartment')->middleware(['auth', 'role:Admin']); // page add department
        Route::post('department/save', 'saveRecord')->middleware(['auth', 'role:Admin'])->name('department/save'); // department/save
        Route::post('department/update', 'updateRecord')->middleware(['auth', 'role:Admin'])->name('department/update'); // department/update
        Route::post('department/delete', 'deleteRecord')->middleware(['auth', 'role:Admin'])->name('department/delete'); // department/delete
        Route::get('get-data-list', 'getDataList')->middleware(['auth', 'role:Admin'])->name('get-data-list'); // get data list

    });

    // ----------------------- subject -----------------------------//
    Route::controller(SubjectController::class)->group(function () {
        Route::get('subject/list/page', 'subjectList')->middleware(['auth', 'role:Admin|Registrar'])->name('subject/list/page');
        Route::get('subject/add/page', 'subjectAdd')->middleware(['auth', 'role:Admin|Registrar'])->name('subject/add/page');
        Route::post('subject/save', 'saveRecord')->middleware(['auth', 'role:Admin|Registrar'])->name('subject/save');
        Route::post('subject/update', 'updateRecord')->middleware(['auth', 'role:Admin|Registrar'])->name('subject/update');
        Route::post('subject/delete', 'deleteRecord')->middleware(['auth', 'role:Admin|Registrar'])->name('subject/delete');
        Route::get('subject/edit/{subject_id}', 'subjectEdit')->middleware(['auth', 'role:Admin|Registrar']);
    });

    // ----------------------- invoice -----------------------------//
    Route::controller(InvoiceController::class)->group(function () {
        Route::get('invoice/list/page', 'invoiceList')->middleware('auth')->name('invoice/list/page'); // subjeinvoicect/list/page
        Route::get('invoice/paid/page', 'invoicePaid')->middleware('auth')->name('invoice/paid/page'); // invoice/paid/page
        Route::get('invoice/overdue/page', 'invoiceOverdue')->middleware('auth')->name('invoice/overdue/page'); // invoice/overdue/page
        Route::get('invoice/draft/page', 'invoiceDraft')->middleware('auth')->name('invoice/draft/page'); // invoice/draft/page
        Route::get('invoice/recurring/page', 'invoiceRecurring')->middleware('auth')->name('invoice/recurring/page'); // invoice/recurring/page
        Route::get('invoice/cancelled/page', 'invoiceCancelled')->middleware('auth')->name('invoice/cancelled/page'); // invoice/cancelled/page
        Route::get('invoice/grid/page', 'invoiceGrid')->middleware('auth')->name('invoice/grid/page'); // invoice/grid/page
        Route::get('invoice/add/page', 'invoiceAdd')->middleware('auth')->name('invoice/add/page'); // invoice/add/page
        Route::post('invoice/add/save', 'saveRecord')->name('invoice/add/save'); // invoice/add/save
        Route::post('invoice/update/save', 'updateRecord')->name('invoice/update/save'); // invoice/update/save
        Route::post('invoice/delete', 'deleteRecord')->name('invoice/delete'); // invoice/delete
        Route::get('invoice/edit/{invoice_id}', 'invoiceEdit')->middleware('auth')->name('invoice/edit/page'); // invoice/edit/page
        Route::get('invoice/view/{invoice_id}', 'invoiceView')->middleware('auth')->name('invoice/view/page'); // invoice/view/page
        Route::get('invoice/settings/page', 'invoiceSettings')->middleware('auth')->name('invoice/settings/page'); // invoice/settings/page
        Route::get('invoice/settings/tax/page', 'invoiceSettingsTax')->middleware('auth')->name('invoice/settings/tax/page'); // invoice/settings/tax/page
        Route::get('invoice/settings/bank/page', 'invoiceSettingsBank')->middleware('auth')->name('invoice/settings/bank/page'); // invoice/settings/bank/page
    });

    // ----------------------- accounts ----------------------------//
    Route::controller(AccountsController::class)->group(function () {
        Route::get('account/fees/collections/page', 'index')->middleware('auth')->name('account/fees/collections/page'); // account/fees/collections/page
        Route::get('add/fees/collection/page', 'addFeesCollection')->middleware('auth')->name('add/fees/collection/page'); // add/fees/collection
        Route::post('fees/collection/save', 'saveRecord')->middleware('auth')->name('fees/collection/save'); // fees/collection/save
    });
});

// Add resource routes for academic years, semesters, and enrollments
Route::resource('academic_years', AcademicYearController::class)->middleware('auth');
Route::resource('semesters', SemesterController::class)->middleware('auth');
Route::resource('sections', SectionController::class)->middleware('auth');

// Enrollment routes (Admin + Registrar for subject catalog / teacher assignment)
Route::group(['middleware' => ['auth', 'role:Admin']], function () {
    Route::resource('enrollments', EnrollmentController::class);
});

Route::group(['middleware' => ['auth', 'role:Admin|Registrar']], function () {
    Route::get('class-subject/unified-management', [App\Http\Controllers\ClassSubjectController::class, 'unifiedManagementForm'])->name('class-subject.unified-management');
    Route::post('class-subject/unified-management', [App\Http\Controllers\ClassSubjectController::class, 'handleAssignment'])->name('class-subject.handle-assignment');
    Route::post('class-subject/import-defaults', [App\Http\Controllers\ClassSubjectController::class, 'importDefaultSubjects'])->name('class-subject.import-defaults');
    Route::post('class-subject/quick-add-subject', [App\Http\Controllers\ClassSubjectController::class, 'quickAddSubject'])->name('class-subject.quick-add-subject');
    Route::post('class-subject/quick-add-section', [App\Http\Controllers\ClassSubjectController::class, 'quickAddSection'])->name('class-subject.quick-add-section');
});
// Attendance routes (available to teachers and admins)
Route::get('teacher/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
Route::resource('teacher/attendance', AttendanceController::class)->except(['index']);
Route::get('attendance/export', [App\Http\Controllers\AttendanceController::class, 'export'])->name('attendance.export');
Route::get('attendance/student', [App\Http\Controllers\AttendanceController::class, 'studentView'])->name('attendance.student');
Route::get('attendance/parent', [App\Http\Controllers\AttendanceController::class, 'parentView'])->name('attendance.parent');

// Reports Routes (Admin and Teacher)
Route::group(['prefix' => 'reports', 'as' => 'reports.', 'middleware' => ['auth', 'role:Admin|Teacher']], function () {
    Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
    Route::get('/transcript/{studentId}', [App\Http\Controllers\ReportController::class, 'generateTranscript'])->name('transcript');
    Route::get('/class-list/{sectionId}', [App\Http\Controllers\ReportController::class, 'generateClassList'])->name('class-list');
    Route::get('/grade-slip/{studentId}', [App\Http\Controllers\ReportController::class, 'generateGradeSlip'])->name('grade-slip');
    Route::get('/progress-summary/{studentId}', [App\Http\Controllers\ReportController::class, 'generateProgressSummary'])->name('progress-summary');
    Route::get('/bulk/{type}', [App\Http\Controllers\ReportController::class, 'generateBulk'])->name('bulk');
});

// Admin-only routes
Route::group(['middleware' => ['role:Admin']], function () {
    
    // ----------------------- Class Schedule Management (Admin Only) -----------------------------//
    Route::group(['prefix' => 'admin/schedules', 'as' => 'admin.schedules.'], function () {
        Route::get('/', [App\Http\Controllers\ClassScheduleController::class, 'adminIndex'])->name('index');
        Route::get('create', [App\Http\Controllers\ClassScheduleController::class, 'create'])->name('create');
        Route::post('store', [App\Http\Controllers\ClassScheduleController::class, 'store'])->name('store');
        Route::get('teacher/{teacher}/assignments', [App\Http\Controllers\ClassScheduleController::class, 'teacherAssignments'])->name('teacher-assignments');
        Route::get('{schedule}/edit', [App\Http\Controllers\ClassScheduleController::class, 'edit'])->name('edit');
        Route::put('{schedule}', [App\Http\Controllers\ClassScheduleController::class, 'update'])->name('update');
        Route::delete('{schedule}', [App\Http\Controllers\ClassScheduleController::class, 'destroy'])->name('destroy');
        Route::get('{schedule}', [App\Http\Controllers\ClassScheduleController::class, 'show'])->name('show');
    });
    
    // Curriculum Management (Admin Only) — connected to subject catalog by grade
    Route::post('curriculum/sync-all', [App\Http\Controllers\CurriculumController::class, 'syncAllFromCatalog'])->name('curriculum.syncAll');
    Route::resource('curriculum', App\Http\Controllers\CurriculumController::class);
    Route::get('curriculum/{curriculum}/assign-subjects', [App\Http\Controllers\CurriculumController::class, 'assignSubjectsForm'])->name('curriculum.assignSubjectsForm');
    Route::post('curriculum/{curriculum}/assign-subjects', [App\Http\Controllers\CurriculumController::class, 'assignSubjects'])->name('curriculum.assignSubjects');
    Route::post('curriculum/{curriculum}/sync-from-catalog', [App\Http\Controllers\CurriculumController::class, 'syncFromCatalog'])->name('curriculum.syncFromCatalog');
   
    // ----------------------- Grading Module Routes (Admin Access) -----------------------------//
    Route::group(['prefix' => 'admin/grading'], function () {
        // GPA and Ranking (Admin can view all)
        Route::get('gpa-ranking', [App\Http\Controllers\GradingController::class, 'gpaRanking'])->name('admin.grading.gpa-ranking');
        
        // Performance Analytics (Admin can view all)
        Route::get('performance-analytics', [App\Http\Controllers\GradingController::class, 'performanceAnalytics'])->name('admin.grading.performance-analytics');
        
        // Grade Alerts (Admin can manage all)
        Route::get('grade-alerts', [App\Http\Controllers\GradingController::class, 'gradeAlerts'])->name('admin.grading.grade-alerts');
        Route::post('resolve-alert/{alert}', [App\Http\Controllers\GradingController::class, 'resolveAlert'])->name('admin.grading.resolve-alert');
        
        // Export Routes (Admin can export all)
        Route::get('export-grades', [App\Http\Controllers\GradingController::class, 'exportGrades'])->name('admin.grading.export-grades');
        Route::get('export-gpa', [App\Http\Controllers\GradingController::class, 'exportGpa'])->name('admin.grading.export-gpa');
    });
});

// Teacher-only routes
Route::group(['middleware' => ['role:Teacher']], function () {
    // Teacher Schedule
    Route::get('teacher/my-schedule', [App\Http\Controllers\ClassScheduleController::class, 'teacherSchedule'])->name('teacher.my-schedule');
    
    // ----------------------- Grading Module Routes (Teacher Only) -----------------------------//
    Route::group(['prefix' => 'grading'], function () {
        // Grade Entry
        Route::get('grade-entry', [App\Http\Controllers\GradingController::class, 'gradeEntryForm'])->name('teacher.grading.grade-entry');
        Route::post('load-students', [App\Http\Controllers\GradingController::class, 'loadStudents'])->name('teacher.grading.load-students');
        Route::post('store-grades', [App\Http\Controllers\GradingController::class, 'storeGrades'])->name('teacher.grading.store-grades');
        Route::post('store-quarterly-grades', [App\Http\Controllers\GradingController::class, 'storeQuarterlyGrades'])->name('teacher.grading.store-quarterly-grades');
        
        // GPA and Ranking
        Route::get('gpa-ranking', [App\Http\Controllers\GradingController::class, 'gpaRanking'])->name('teacher.grading.gpa-ranking');
        
        // Performance Analytics
        Route::get('performance-analytics', [App\Http\Controllers\GradingController::class, 'performanceAnalytics'])->name('teacher.grading.performance-analytics');
        
        // Weight Settings
        Route::get('weight-settings', [App\Http\Controllers\GradingController::class, 'weightSettings'])->name('teacher.grading.weight-settings');
        Route::post('store-weight-settings', [App\Http\Controllers\GradingController::class, 'storeWeightSettings'])->name('teacher.grading.store-weight-settings');
        
        // Grade Alerts
        Route::get('grade-alerts', [App\Http\Controllers\GradingController::class, 'gradeAlerts'])->name('teacher.grading.grade-alerts');
        Route::post('resolve-alert/{alert}', [App\Http\Controllers\GradingController::class, 'resolveAlert'])->name('teacher.grading.resolve-alert');
        
        // Export Routes
        Route::get('export-grades', [App\Http\Controllers\GradingController::class, 'exportGrades'])->name('teacher.grading.export-grades');
        Route::get('export-gpa', [App\Http\Controllers\GradingController::class, 'exportGpa'])->name('teacher.grading.export-gpa');
    });

    // ----------------------- Lesson Planner Module Routes (Teacher Only) -----------------------------//
    Route::group(['prefix' => 'lessons'], function () {
        // Lesson Management
        Route::get('/', [App\Http\Controllers\LessonController::class, 'index'])->name('lessons.index');
        Route::get('/create', [App\Http\Controllers\LessonController::class, 'create'])->name('lessons.create');
        Route::post('/', [App\Http\Controllers\LessonController::class, 'store'])->name('lessons.store');
        Route::get('/{lesson}', [App\Http\Controllers\LessonController::class, 'show'])->name('lessons.show');
        Route::get('/{lesson}/edit', [App\Http\Controllers\LessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/{lesson}', [App\Http\Controllers\LessonController::class, 'update'])->name('lessons.update');
        Route::delete('/{lesson}', [App\Http\Controllers\LessonController::class, 'destroy'])->name('lessons.destroy');
        
        // Lesson Status Management
        Route::post('/{lesson}/publish', [App\Http\Controllers\LessonController::class, 'publish'])->name('lessons.publish');
        Route::post('/{lesson}/complete', [App\Http\Controllers\LessonController::class, 'complete'])->name('lessons.complete');
        
        // Activity Management
        Route::get('/{lesson}/activities', [App\Http\Controllers\ActivityController::class, 'index'])->name('lessons.activities.index');
        Route::get('/{lesson}/activities/create', [App\Http\Controllers\ActivityController::class, 'create'])->name('lessons.activities.create');
        Route::post('/{lesson}/activities', [App\Http\Controllers\ActivityController::class, 'store'])->name('lessons.activities.store');
        Route::get('/{lesson}/activities/{activity}', [App\Http\Controllers\ActivityController::class, 'show'])->name('lessons.activities.show');
        Route::get('/{lesson}/activities/{activity}/edit', [App\Http\Controllers\ActivityController::class, 'edit'])->name('lessons.activities.edit');
        Route::put('/{lesson}/activities/{activity}', [App\Http\Controllers\ActivityController::class, 'update'])->name('lessons.activities.update');
        Route::delete('/{lesson}/activities/{activity}', [App\Http\Controllers\ActivityController::class, 'destroy'])->name('lessons.activities.destroy');
        
                        // Activity Rubric Management
                Route::get('/{lesson}/activities/{activity}/rubric', [App\Http\Controllers\ActivityController::class, 'rubric'])->name('lessons.activities.rubric');
                Route::post('/{lesson}/activities/{activity}/rubric', [App\Http\Controllers\ActivityController::class, 'storeRubric'])->name('lessons.activities.store-rubric');
                Route::get('/{lesson}/activities/{activity}/rubric/{rubric}/edit', [App\Http\Controllers\ActivityController::class, 'editRubric'])->name('lessons.activities.edit-rubric');
                Route::put('/{lesson}/activities/{activity}/rubric/{rubric}', [App\Http\Controllers\ActivityController::class, 'updateRubric'])->name('lessons.activities.update-rubric');
                Route::delete('/{lesson}/activities/{activity}/rubric/{rubric}', [App\Http\Controllers\ActivityController::class, 'destroyRubric'])->name('lessons.activities.destroy-rubric');
                
                // Activity Submissions Management
                Route::get('/{lesson}/activities/{activity}/submissions', [App\Http\Controllers\SubmissionController::class, 'index'])->name('lessons.activities.submissions');
                Route::post('/{lesson}/activities/{activity}/submissions', [App\Http\Controllers\SubmissionController::class, 'store'])->name('lessons.activities.store-submission');
                Route::get('/{lesson}/activities/{activity}/submissions/{submission}', [App\Http\Controllers\SubmissionController::class, 'show'])->name('lessons.activities.show-submission');
                Route::delete('/{lesson}/activities/{activity}/submissions/{submission}', [App\Http\Controllers\SubmissionController::class, 'destroy'])->name('lessons.activities.destroy-submission');
                
                // Grading Management
                Route::get('/{lesson}/activities/{activity}/submissions/{submission}/grade', [App\Http\Controllers\SubmissionController::class, 'gradeSubmission'])->name('lessons.activities.grade-submission');
                Route::post('/{lesson}/activities/{activity}/submissions/{submission}/grade', [App\Http\Controllers\SubmissionController::class, 'storeGrade'])->name('lessons.activities.store-grade');
                Route::get('/{lesson}/activities/{activity}/submissions/{submission}/grade/view', [App\Http\Controllers\SubmissionController::class, 'viewGrade'])->name('lessons.activities.view-grade');
                Route::get('/{lesson}/activities/{activity}/submissions/{submission}/grade/edit', [App\Http\Controllers\SubmissionController::class, 'editGrade'])->name('lessons.activities.edit-grade');
                Route::put('/{lesson}/activities/{activity}/submissions/{submission}/grade', [App\Http\Controllers\SubmissionController::class, 'updateGrade'])->name('lessons.activities.update-grade');
                
                // Export
                Route::get('/{lesson}/activities/{activity}/submissions/export', [App\Http\Controllers\SubmissionController::class, 'exportSubmissions'])->name('lessons.activities.export-submissions');
                
                // Lesson Recommendations
                Route::get('/recommendations/student-analysis', [App\Http\Controllers\LessonRecommendationController::class, 'studentAnalysis'])->name('lessons.recommendations.student-analysis');
                Route::get('/recommendations/class-analysis', [App\Http\Controllers\LessonRecommendationController::class, 'classAnalysis'])->name('lessons.recommendations.class-analysis');
                Route::get('/recommendations/export', [App\Http\Controllers\LessonRecommendationController::class, 'exportAnalysis'])->name('lessons.recommendations.export');
    });
});

// Notification routes
Route::group(['middleware' => ['auth']], function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});

// Student-only routes
Route::group(['middleware' => ['role:Student']], function () {
    // My Classes route
    Route::get('/my-classes', [App\Http\Controllers\StudentController::class, 'myClasses'])->name('student.my-classes');
    // Class detail route
    Route::get('/class/{enrollmentId}', [App\Http\Controllers\StudentController::class, 'classDetail'])->name('student.class.detail');
    Route::get('/class/{enrollmentId}/lessons/{lesson}', [App\Http\Controllers\StudentController::class, 'lessonShow'])->name('student.lessons.show');
    // Student grades route
    Route::get('/grades', [App\Http\Controllers\StudentController::class, 'grades'])->name('student.grades');
    // Student attendance route
    Route::get('/attendance', [App\Http\Controllers\StudentController::class, 'attendance'])->name('student.attendance');
    // Lesson activity submission routes (student-prefixed to avoid clashing with teacher lesson routes)
    Route::get('/student/lessons/{lesson}/activities/{activity}', [App\Http\Controllers\ActivityController::class, 'studentShow'])->name('student.activities.show');
    Route::post('/student/lessons/{lesson}/activities/{activity}/submit', [App\Http\Controllers\SubmissionController::class, 'store'])->name('student.activities.submit');
    Route::get('/student/lessons/{lesson}/activities/{activity}/submissions/{submission}', [App\Http\Controllers\SubmissionController::class, 'show'])->name('student.activities.view-submission');
    Route::get('/student/lessons/{lesson}/activities/{activity}/submissions/{submission}/grade', [App\Http\Controllers\SubmissionController::class, 'viewGrade'])->name('student.activities.view-grade');
    // Study recommendations
    Route::get('/recommendations', [App\Http\Controllers\LessonRecommendationController::class, 'myRecommendations'])->name('student.recommendations');
});

// Student Information System (SIS) route for admin and teachers
Route::get('/student/sis/{user_id}', [App\Http\Controllers\StudentController::class, 'viewSIS'])->middleware(['auth', 'role:Admin|Teacher|Registrar'])->name('student.sis');

// Parent-only routes
Route::group(['middleware' => ['role:Parent']], function () {
    // Place parent-only routes here
});

Route::get('subjects/{id}/assign-teachers', [SubjectController::class, 'assignTeachersForm'])->name('subjects.assignTeachersForm');
Route::post('subjects/{id}/assign-teachers', [SubjectController::class, 'assignTeachers'])->name('subjects.assignTeachers');
Route::get('sections/{id}/assign-students', [SectionController::class, 'assignStudentsForm'])->name('sections.assignStudentsForm');
Route::post('sections/{id}/assign-students', [SectionController::class, 'assignStudents'])->name('sections.assignStudents');

// ----------------------- Enrollment Portal Routes -----------------------------//
// Public enrollment portal routes (no CSRF protection)
Route::get('/enrollment-portal/', [App\Http\Controllers\EnrollmentPortalController::class, 'index'])->name('enrollment.portal.index');
Route::get('/enrollment-portal/apply', [App\Http\Controllers\EnrollmentPortalController::class, 'create'])->name('enrollment.portal.create');
Route::post('/enrollment-portal/apply', [App\Http\Controllers\EnrollmentPortalController::class, 'store'])->name('enrollment.portal.store');
Route::get('/enrollment-portal/status', [App\Http\Controllers\EnrollmentPortalController::class, 'status'])->name('enrollment.portal.status');
Route::get('/enrollment-portal/check-status', function() { return redirect()->route('enrollment.portal.status'); });
Route::post('/enrollment-portal/check-status', [App\Http\Controllers\EnrollmentPortalController::class, 'checkStatus'])->name('enrollment.portal.check-status');
Route::get('/enrollment-portal/application/{id}', [App\Http\Controllers\EnrollmentPortalController::class, 'show'])->name('enrollment.portal.show');
Route::get('/enrollment-portal/success/{id}', [App\Http\Controllers\EnrollmentPortalController::class, 'success'])->name('enrollment.portal.success');
Route::get('/enrollment-portal/document/{id}/download', [App\Http\Controllers\EnrollmentPortalController::class, 'downloadDocument'])->name('enrollment.portal.download-document');
Route::post('/enrollment-portal/verify-old-student', [App\Http\Controllers\EnrollmentPortalController::class, 'verifyOldStudent'])->name('enrollment.verify-old-student');

// Old Student Enrollment Routes
Route::get('/enrollment-portal/old-student/login', [App\Http\Controllers\EnrollmentPortalController::class, 'oldStudentLogin'])->name('enrollment.old-student.login');
Route::post('/enrollment-portal/old-student/authenticate', [App\Http\Controllers\EnrollmentPortalController::class, 'oldStudentAuthenticate'])->name('enrollment.old-student.authenticate');
Route::get('/enrollment-portal/old-student/dashboard', [App\Http\Controllers\EnrollmentPortalController::class, 'oldStudentDashboard'])->name('enrollment.old-student.dashboard');
Route::post('/enrollment-portal/old-student/select-section', [App\Http\Controllers\EnrollmentPortalController::class, 'oldStudentSelectSection'])->name('enrollment.old-student.select-section');
Route::get('/enrollment-portal/old-student/generate-form/{sectionId}', [App\Http\Controllers\EnrollmentPortalController::class, 'oldStudentGenerateForm'])->name('enrollment.old-student.generate-form');

// Get sections by grade level (for enrollment form)
Route::get('/enrollment-portal/get-sections/{gradeLevel}', [App\Http\Controllers\EnrollmentPortalController::class, 'getSectionsByGradeLevel'])->name('enrollment.get-sections');
Route::get('/enrollment-portal/get-subjects/{gradeLevel}', [App\Http\Controllers\EnrollmentPortalController::class, 'getSubjectsByGradeLevel'])->name('enrollment.get-subjects');

// ----------------------- Enrollment Registrar Routes (Admin & Registrar Only) -----------------------------//
Route::group(['prefix' => 'admin/enrollment', 'middleware' => ['auth', 'role:Admin|Registrar']], function () {
    Route::get('/', [App\Http\Controllers\EnrollmentRegistrarController::class, 'index'])->name('enrollment.registrar.index');
    Route::get('/statistics', [App\Http\Controllers\EnrollmentRegistrarController::class, 'statistics'])->name('enrollment.registrar.statistics');
    Route::get('/archive', [App\Http\Controllers\EnrollmentRegistrarController::class, 'archive'])->name('enrollment.registrar.archive');
    Route::get('/{id}', [App\Http\Controllers\EnrollmentRegistrarController::class, 'show'])->name('enrollment.registrar.show');
    Route::post('/{id}/mark-under-review', [App\Http\Controllers\EnrollmentRegistrarController::class, 'markUnderReview'])->name('enrollment.registrar.mark-under-review');
    Route::post('/{id}/approve', [App\Http\Controllers\EnrollmentRegistrarController::class, 'approve'])->name('enrollment.registrar.approve');
    Route::post('/{id}/reject', [App\Http\Controllers\EnrollmentRegistrarController::class, 'reject'])->name('enrollment.registrar.reject');
    Route::post('/{id}/needs-documents', [App\Http\Controllers\EnrollmentRegistrarController::class, 'needsDocuments'])->name('enrollment.registrar.needs-documents');
    Route::post('/{id}/process', [App\Http\Controllers\EnrollmentRegistrarController::class, 'processApplication'])->name('enrollment.registrar.process');
    Route::delete('/{id}', [App\Http\Controllers\EnrollmentRegistrarController::class, 'destroy'])->name('enrollment.registrar.destroy');
    Route::post('/archive/{id}/restore', [App\Http\Controllers\EnrollmentRegistrarController::class, 'restore'])->name('enrollment.registrar.restore');
    Route::delete('/archive/{id}/force-delete', [App\Http\Controllers\EnrollmentRegistrarController::class, 'forceDelete'])->name('enrollment.registrar.force-delete');
    Route::post('/document/{id}/verify', [App\Http\Controllers\EnrollmentRegistrarController::class, 'verifyDocument'])->name('enrollment.registrar.verify-document');
    Route::post('/document/{id}/reject', [App\Http\Controllers\EnrollmentRegistrarController::class, 'rejectDocument'])->name('enrollment.registrar.reject-document');
    Route::post('/{id}/create-manual-account', [App\Http\Controllers\EnrollmentRegistrarController::class, 'createManualAccount'])->name('enrollment.registrar.create-manual-account');
});
Route::get('teacher/{id}/assign-grade-levels', [App\Http\Controllers\TeacherController::class, 'assignGradeLevelsForm'])->name('teacher.assignGradeLevelsForm');
Route::post('teacher/{id}/assign-grade-levels', [App\Http\Controllers\TeacherController::class, 'assignGradeLevels'])->name('teacher.assignGradeLevels');
Route::get('api/sections/{section}/subjects', function(App\Models\Section $section) {
    return $section->students()
        ->with('subjects')
        ->get()
        ->pluck('subjects')
        ->flatten()
        ->unique('id')
        ->values();
})->name('api.section.subjects');

// Analytics Routes
Route::group(['prefix' => 'analytics', 'middleware' => ['auth']], function () {
    // Student Analytics
    Route::get('/student-dashboard', [App\Http\Controllers\AnalyticsController::class, 'studentDashboard'])->name('analytics.student-dashboard');
    
    // Teacher Analytics
    Route::get('/teacher-dashboard', [App\Http\Controllers\AnalyticsController::class, 'teacherDashboard'])->name('analytics.teacher-dashboard');
    
    // Admin Analytics
    Route::get('/admin-dashboard', [App\Http\Controllers\AnalyticsController::class, 'adminDashboard'])->name('analytics.admin-dashboard');
    
    // API endpoints for chart data
    Route::get('/chart-data', [App\Http\Controllers\AnalyticsController::class, 'getChartData'])->name('analytics.chart-data');
    
    // Export reports
    Route::get('/export-report', [App\Http\Controllers\AnalyticsController::class, 'exportReport'])->name('analytics.export-report');
    
    // Detail views (for teachers/admins viewing specific students/teachers)
    Route::get('/student/{studentId}', [App\Http\Controllers\AnalyticsController::class, 'getStudentAnalytics'])->name('analytics.student-detail');
    Route::get('/teacher/{teacherId}', [App\Http\Controllers\AnalyticsController::class, 'getTeacherAnalytics'])->name('analytics.teacher-detail');
});

// Calendar Routes
Route::group(['prefix' => 'calendar', 'middleware' => ['auth']], function () {
    // View-only access for Admin, Teacher, and Student
    Route::get('/', [App\Http\Controllers\CalendarEventController::class, 'index'])
        ->middleware('role:Admin,Teacher,Student,Parent')
        ->name('calendar.index');

    // Management APIs / CRUD — Admin & Teacher only
    Route::middleware('role:Admin,Teacher')->group(function () {
        Route::get('/events/list', [App\Http\Controllers\CalendarEventController::class, 'eventsList'])->name('calendar.events.list');
        Route::get('/create', [App\Http\Controllers\CalendarEventController::class, 'create'])->name('calendar.create');
        Route::post('/', [App\Http\Controllers\CalendarEventController::class, 'store'])->name('calendar.store');
        Route::get('/available-slots', [App\Http\Controllers\CalendarEventController::class, 'getAvailableSlots'])->name('calendar.available-slots');
        Route::get('/check-conflicts', [App\Http\Controllers\CalendarEventController::class, 'checkConflicts'])->name('calendar.check-conflicts');
        Route::get('/subject-preferences', [App\Http\Controllers\CalendarEventController::class, 'subjectPreferences'])->name('calendar.subject-preferences');
        Route::get('/workload', [App\Http\Controllers\CalendarEventController::class, 'workload'])->name('calendar.workload');
        Route::get('/{calendarEvent}/edit', [App\Http\Controllers\CalendarEventController::class, 'edit'])->name('calendar.edit');
        Route::put('/{calendarEvent}', [App\Http\Controllers\CalendarEventController::class, 'update'])->name('calendar.update');
        Route::delete('/{calendarEvent}', [App\Http\Controllers\CalendarEventController::class, 'destroy'])->name('calendar.destroy');
    });

    Route::get('/{calendarEvent}', [App\Http\Controllers\CalendarEventController::class, 'show'])
        ->middleware('role:Admin,Teacher,Student,Parent')
        ->name('calendar.show');
});

// Schedule Routes
Route::group(['prefix' => 'schedule', 'middleware' => ['role:Student,Admin,Teacher,Parent']], function () {
    Route::get('/', [App\Http\Controllers\ClassScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/dashboard-data', [App\Http\Controllers\ClassScheduleController::class, 'getDashboardSchedule'])->name('schedule.dashboard-data');
    Route::get('/test-my-schedule', function() {
        return view('test.my-schedule-test');
    })->name('schedule.test');
});

// Student-specific routes
Route::group(['middleware' => ['role:Student']], function () {
    Route::get('/my-schedule', [App\Http\Controllers\ClassScheduleController::class, 'mySchedule'])->name('student.my-schedule');
});

// Messaging and Notification Routes
Route::group(['prefix' => 'announcements', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/create', [App\Http\Controllers\AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/', [App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/dashboard/data', [App\Http\Controllers\AnnouncementController::class, 'getDashboardAnnouncements'])->name('announcements.dashboard-data');
    Route::get('/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'show'])->name('announcements.show');
    Route::get('/{announcement}/edit', [App\Http\Controllers\AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::patch('/{announcement}/toggle-pin', [App\Http\Controllers\AnnouncementController::class, 'togglePin'])->name('announcements.toggle-pin');
});

// Chat Routes (Messenger-style chat)
Route::group(['prefix' => 'chat', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/conversation/{userId}', [App\Http\Controllers\ChatController::class, 'getConversation'])->name('chat.conversation');
    Route::post('/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::delete('/message/{messageId}', [App\Http\Controllers\ChatController::class, 'deleteMessage'])->name('chat.delete-message');
    Route::get('/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::get('/search', [App\Http\Controllers\ChatController::class, 'searchContacts'])->name('chat.search');
});

// Notification Routes
Route::group(['prefix' => 'notifications', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/{id}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/mark-all-as-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});

// Assignment Routes
Route::group(['prefix' => 'assignments', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/create', [App\Http\Controllers\AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/', [App\Http\Controllers\AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/{assignment}', [App\Http\Controllers\AssignmentController::class, 'show'])->name('assignments.show');
    Route::get('/{assignment}/edit', [App\Http\Controllers\AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('/{assignment}', [App\Http\Controllers\AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/{assignment}', [App\Http\Controllers\AssignmentController::class, 'destroy'])->name('assignments.destroy');
    
    // Assignment management
    Route::post('/{assignment}/publish', [App\Http\Controllers\AssignmentController::class, 'publish'])->name('assignments.publish');
    Route::post('/{assignment}/close', [App\Http\Controllers\AssignmentController::class, 'close'])->name('assignments.close');
    Route::get('/{assignment}/submissions', [App\Http\Controllers\AssignmentController::class, 'submissions'])->name('assignments.submissions');
    
    // Grading
    Route::post('/submissions/{submission}/grade', [App\Http\Controllers\AssignmentController::class, 'gradeSubmission'])->name('assignments.grade-submission');
    
    // AJAX routes for submission viewing and grading
    Route::get('/submissions/{submission}/view', [App\Http\Controllers\AssignmentController::class, 'viewSubmission'])->name('assignments.view-submission');
    Route::get('/submissions/{submission}/details', [App\Http\Controllers\AssignmentController::class, 'getSubmissionDetails'])->name('assignments.submission-details');
    Route::get('/submissions/{submission}/grade-data', [App\Http\Controllers\AssignmentController::class, 'getGradeData'])->name('assignments.grade-data');
    
    // Export
    Route::get('/{assignment}/export-pdf', [App\Http\Controllers\AssignmentController::class, 'exportPdf'])->name('assignments.export-pdf');
    Route::get('/export-excel', [App\Http\Controllers\AssignmentController::class, 'exportExcel'])->name('assignments.export-excel');
});

// Class Post Routes
Route::group(['prefix' => 'class-posts', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\ClassPostController::class, 'index'])->name('class-posts.index');
    Route::get('/create', [App\Http\Controllers\ClassPostController::class, 'create'])->name('class-posts.create');
    Route::post('/', [App\Http\Controllers\ClassPostController::class, 'store'])->name('class-posts.store');
    Route::get('/{classPost}', [App\Http\Controllers\ClassPostController::class, 'show'])->name('class-posts.show');
    Route::get('/{classPost}/edit', [App\Http\Controllers\ClassPostController::class, 'edit'])->name('class-posts.edit');
    Route::put('/{classPost}', [App\Http\Controllers\ClassPostController::class, 'update'])->name('class-posts.update');
    Route::delete('/{classPost}', [App\Http\Controllers\ClassPostController::class, 'destroy'])->name('class-posts.destroy');
    
    // Post management
    Route::post('/{classPost}/toggle-pin', [App\Http\Controllers\ClassPostController::class, 'togglePin'])->name('class-posts.toggle-pin');
    Route::post('/{classPost}/publish', [App\Http\Controllers\ClassPostController::class, 'publish'])->name('class-posts.publish');
    Route::post('/{classPost}/unpublish', [App\Http\Controllers\ClassPostController::class, 'unpublish'])->name('class-posts.unpublish');
    
    // Comments
    Route::post('/{classPost}/comments', [App\Http\Controllers\ClassPostController::class, 'storeComment'])->name('class-posts.store-comment');
    Route::delete('/comments/{comment}', [App\Http\Controllers\ClassPostController::class, 'deleteComment'])->name('class-posts.delete-comment');
    Route::patch('/comments/{comment}/toggle-approval', [App\Http\Controllers\ClassPostController::class, 'toggleCommentApproval'])->name('class-posts.toggle-comment-approval');
});

// Student Assignment Routes
Route::group(['prefix' => 'student', 'middleware' => ['auth', 'role:Student']], function () {
    Route::get('/assignments', [App\Http\Controllers\StudentAssignmentController::class, 'index'])->name('student.assignments.index');
    Route::get('/assignments/{assignment}', [App\Http\Controllers\StudentAssignmentController::class, 'show'])->name('student.assignments.show');
    Route::post('/assignments/{assignment}/submit', [App\Http\Controllers\StudentAssignmentController::class, 'submit'])->name('student.assignments.submit');
    Route::get('/assignments/{assignment}/submission', [App\Http\Controllers\StudentAssignmentController::class, 'submission'])->name('student.assignments.submission');
});

// Parent Routes
Route::group(['prefix' => 'parent', 'middleware' => ['auth', 'role:Parent']], function () {
    Route::get('/', [App\Http\Controllers\ParentController::class, 'index'])->name('parent.index');
    Route::get('/child/{childId}', [App\Http\Controllers\ParentController::class, 'childHub'])->name('parent.child.hub');
    Route::get('/child/{childId}/profile', [App\Http\Controllers\ParentController::class, 'childProfile'])->name('parent.child.profile');
    Route::get('/child/{childId}/grades', [App\Http\Controllers\ParentController::class, 'childGrades'])->name('parent.child.grades');
    Route::get('/child/{childId}/attendance', [App\Http\Controllers\ParentController::class, 'childAttendance'])->name('parent.child.attendance');
    Route::get('/child/{childId}/activities', [App\Http\Controllers\ParentController::class, 'childActivities'])->name('parent.child.activities');
    Route::get('/schedule', [App\Http\Controllers\ClassScheduleController::class, 'index'])->name('parent.schedule');
});

