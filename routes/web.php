<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;

// ── Public Website ────────────────────────────────────────────────
Route::get('/',            [App\Http\Controllers\Website\WebsiteController::class, 'home'])->name('home');
Route::get('/home',        [App\Http\Controllers\Website\WebsiteController::class, 'home'])->name('website.home');
Route::get('/admissions',  [App\Http\Controllers\Website\WebsiteController::class, 'admissions'])->name('website.admissions');
Route::get('/faq',         [App\Http\Controllers\Website\WebsiteController::class, 'faq'])->name('website.faq');
Route::post('/chat',       [App\Http\Controllers\Website\ChatController::class, 'respond'])->name('website.chat')->middleware('throttle:30,1440');
Route::post('/admissions', [App\Http\Controllers\Website\WebsiteController::class, 'submitAdmission'])->name('website.admissions.submit');

// ── Website CMS (admin only) ──────────────────────────────────────
Route::prefix('admin/website')->name('admin.website.')->middleware(['auth','role:super_admin,admin'])->group(function () {
    Route::get('/',              [App\Http\Controllers\Admin\WebsiteCmsController::class, 'index'])->name('index');
    Route::post('/save',         [App\Http\Controllers\Admin\WebsiteCmsController::class, 'save'])->name('save');
    Route::post('/items',        [App\Http\Controllers\Admin\WebsiteCmsController::class, 'storeItem'])->name('items.store');
    Route::post('/items/{item}', [App\Http\Controllers\Admin\WebsiteCmsController::class, 'updateItem'])->name('items.update');
    Route::delete('/items/{item}',[App\Http\Controllers\Admin\WebsiteCmsController::class, 'destroyItem'])->name('items.destroy');
});


use App\Http\Controllers\Admin\SettingsController;

// Redirect root to login
// Root handled by WebsiteController

// Auth routes
Route::get('/login', [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware(['guest','throttle:5,1']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Password Reset ──────────────────────────────────────────────
Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->middleware('guest')->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware(['guest','throttle:5,1'])->name('password.email');
Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])->middleware(['guest','throttle:5,1'])->name('password.update');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,admin,teacher,accountant,librarian,receptionist'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

// Profile routes (all authenticated users)
Route::prefix('admin/profile')->name('admin.profile.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('index');
    Route::post('/update', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('update');
    Route::post('/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('password');
});

// Academic Structure
Route::prefix('admin')->name('admin.')->middleware(['auth','role:super_admin,admin'])->group(function () {
    Route::get('/academic-years', [App\Http\Controllers\Admin\AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::post('/academic-years', [App\Http\Controllers\Admin\AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::post('/academic-years/{year}/set-current', [App\Http\Controllers\Admin\AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
    Route::delete('/academic-years/{year}', [App\Http\Controllers\Admin\AcademicYearController::class, 'destroy'])->name('academic-years.destroy');

    Route::get('/classes', [App\Http\Controllers\Admin\ClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [App\Http\Controllers\Admin\ClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}', [App\Http\Controllers\Admin\ClassController::class, 'show'])->name('classes.show');
    Route::put('/classes/{class}', [App\Http\Controllers\Admin\ClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{class}', [App\Http\Controllers\Admin\ClassController::class, 'destroy'])->name('classes.destroy');
    Route::post('/classes/{class}/sections', [App\Http\Controllers\Admin\ClassController::class, 'storeSection'])->name('classes.sections.store');
    Route::delete('/classes/{class}/sections/{section}', [App\Http\Controllers\Admin\ClassController::class, 'destroySection'])->name('classes.sections.destroy');
    Route::post('/classes/{class}/subjects', [App\Http\Controllers\Admin\ClassController::class, 'assignSubject'])->name('classes.subjects.assign');
    Route::delete('/classes/{class}/subjects/{subject}', [App\Http\Controllers\Admin\ClassController::class, 'removeSubject'])->name('classes.subjects.remove');

    Route::get('/subjects', [App\Http\Controllers\Admin\SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [App\Http\Controllers\Admin\SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{subject}', [App\Http\Controllers\Admin\SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [App\Http\Controllers\Admin\SubjectController::class, 'destroy'])->name('subjects.destroy');
});

// Students
Route::prefix('admin')->name('admin.')->middleware(['auth','role:super_admin,admin,receptionist,accountant'])->group(function () {
    Route::get('/students', [App\Http\Controllers\Admin\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [App\Http\Controllers\Admin\StudentController::class, 'create'])->name('students.create');
    Route::get('/students/import', [App\Http\Controllers\Admin\StudentController::class, 'importForm'])->name('students.import.form');
    Route::get('/students/import/template', [App\Http\Controllers\Admin\StudentController::class, 'downloadTemplate'])->name('students.import.template');
    Route::post('/students/import', [App\Http\Controllers\Admin\StudentController::class, 'import'])->name('students.import');
    Route::post('/students', [App\Http\Controllers\Admin\StudentController::class, 'store'])->name('students.store');
    // Student PINs — must be before {student} wildcard
    Route::get('/students/pins',                 [App\Http\Controllers\Admin\StudentPinController::class, 'index'])->name('students.pins.index');
    Route::post('/students/pins/reset-all',      [App\Http\Controllers\Admin\StudentPinController::class, 'resetAll'])->name('students.pins.reset-all');
    Route::post('/students/pins/{student}/reset',[App\Http\Controllers\Admin\StudentPinController::class, 'reset'])->name('students.pins.reset');

    Route::get('/students/{student}', [App\Http\Controllers\Admin\StudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [App\Http\Controllers\Admin\StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [App\Http\Controllers\Admin\StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [App\Http\Controllers\Admin\StudentController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/{student}/withdraw', [App\Http\Controllers\Admin\StudentController::class, 'withdraw'])->name('students.withdraw');
    Route::post('/students/{student}/reinstate', [App\Http\Controllers\Admin\StudentController::class, 'reinstate'])->name('students.reinstate');
    Route::get('/admissions', [App\Http\Controllers\Admin\AdmissionEnquiryController::class, 'index'])->name('admissions.index');
    Route::post('/admissions/{id}/status', [App\Http\Controllers\Admin\AdmissionEnquiryController::class, 'updateStatus'])->name('admissions.status');
    Route::delete('/admissions/{id}', [App\Http\Controllers\Admin\AdmissionEnquiryController::class, 'destroy'])->name('admissions.destroy');
    Route::post('/students/{student}/documents', [App\Http\Controllers\Admin\StudentController::class, 'uploadDocument'])->name('students.documents.upload');
    Route::delete('/students/{student}/documents/{document}', [App\Http\Controllers\Admin\StudentController::class, 'deleteDocument'])->name('students.documents.delete');
    Route::get('/api/classes/{class}/sections', [App\Http\Controllers\Admin\StudentController::class, 'getSections'])->name('api.sections');
});

// Staff
Route::prefix('admin')->name('admin.')->middleware(['auth','role:super_admin,admin'])->group(function () {
    Route::get('/staff', [App\Http\Controllers\Admin\StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [App\Http\Controllers\Admin\StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [App\Http\Controllers\Admin\StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staff}', [App\Http\Controllers\Admin\StaffController::class, 'show'])->name('staff.show');
    Route::get('/staff/{staff}/edit', [App\Http\Controllers\Admin\StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff}', [App\Http\Controllers\Admin\StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [App\Http\Controllers\Admin\StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/{staff}/documents', [App\Http\Controllers\Admin\StaffController::class, 'uploadDocument'])->name('staff.documents.upload');
    Route::delete('/staff/{staff}/documents/{document}', [App\Http\Controllers\Admin\StaffController::class, 'deleteDocument'])->name('staff.documents.delete');
});

// Fees
Route::prefix('admin/fees')->name('admin.fees.')->middleware(['auth','role:super_admin,admin,accountant'])->group(function () {
    Route::get('/categories', [App\Http\Controllers\Admin\FeeController::class, 'categories'])->name('categories');
    Route::post('/categories', [App\Http\Controllers\Admin\FeeController::class, 'storeCategory'])->name('categories.store');
    Route::delete('/categories/{category}', [App\Http\Controllers\Admin\FeeController::class, 'destroyCategory'])->name('categories.destroy');

    Route::get('/structures', [App\Http\Controllers\Admin\FeeController::class, 'structures'])->name('structures');
    Route::post('/structures', [App\Http\Controllers\Admin\FeeController::class, 'storeStructure'])->name('structures.store');
    Route::delete('/structures/{structure}', [App\Http\Controllers\Admin\FeeController::class, 'destroyStructure'])->name('structures.destroy');

    Route::get('/collect', [App\Http\Controllers\Admin\FeeController::class, 'collect'])->name('collect');
    Route::post('/collect', [App\Http\Controllers\Admin\FeeController::class, 'processPayment'])->name('collect.process');

    Route::get('/receipt/{payment}', [App\Http\Controllers\Admin\FeeController::class, 'receipt'])->name('receipt');
    Route::get('/receipt/{payment}/pdf', [App\Http\Controllers\Admin\FeeController::class, 'receiptPdf'])->name('receipt.pdf');

    Route::get('/report', [App\Http\Controllers\Admin\FeeController::class, 'report'])->name('report');
    Route::get('/balance', [App\Http\Controllers\Admin\FeeController::class, 'balanceReport'])->name('balance');
    Route::post('/payments/{payment}/void', [App\Http\Controllers\Admin\FeeController::class, 'voidPayment'])->name('void');
    Route::get('/discounts', [App\Http\Controllers\Admin\DiscountController::class, 'index'])->name('discounts');
    Route::post('/discounts', [App\Http\Controllers\Admin\DiscountController::class, 'store'])->name('discounts.store');
    Route::post('/discounts/assign', [App\Http\Controllers\Admin\DiscountController::class, 'assign'])->name('discounts.assign');
    Route::delete('/discounts/assign/{id}', [App\Http\Controllers\Admin\DiscountController::class, 'unassign'])->name('discounts.unassign');
    Route::delete('/discounts/{id}', [App\Http\Controllers\Admin\DiscountController::class, 'destroy'])->name('discounts.destroy');
});

// Finance (accounts, expenses, cash book)
Route::prefix('admin/finance')->name('admin.finance.')->middleware(['auth','role:super_admin,admin,accountant'])->group(function () {
    $c = App\Http\Controllers\Admin\FinanceController::class;
    Route::get('/', [$c, 'index'])->name('index');
    Route::get('/accounts', [$c, 'accounts'])->name('accounts');
    Route::post('/accounts', [$c, 'storeAccount'])->name('accounts.store');
    Route::post('/accounts/{account}/toggle', [$c, 'toggleAccount'])->name('accounts.toggle');
    Route::post('/accounts/{account}', [$c, 'updateAccount'])->name('accounts.update');
    Route::post('/transfers', [$c, 'storeTransfer'])->name('transfers.store');
    Route::post('/transfers/{transfer}/void', [$c, 'voidTransfer'])->name('transfers.void');
    Route::get('/categories', [$c, 'categories'])->name('categories');
    Route::post('/categories', [$c, 'storeCategory'])->name('categories.store');
    Route::post('/categories/{category}/toggle', [$c, 'toggleCategory'])->name('categories.toggle');
    Route::get('/transactions/{type}', [$c, 'transactions'])->where('type', 'expense|income')->name('transactions');
    Route::post('/transactions', [$c, 'storeTransaction'])->name('transactions.store');
    Route::post('/transactions/{transaction}/void', [$c, 'voidTransaction'])->name('transactions.void');
    Route::get('/cashbook', [$c, 'cashbook'])->name('cashbook');
    Route::get('/statement', [$c, 'statement'])->name('statement');
    Route::get('/budget', [$c, 'budget'])->name('budget');
    Route::post('/budget', [$c, 'saveBudget'])->name('budget.save');
    Route::get('/transactions/{transaction}/voucher', [$c, 'voucher'])->name('transactions.voucher');
    Route::post('/transactions/{transaction}/approve', [$c, 'approve'])->name('transactions.approve');
    Route::post('/transactions/{transaction}/reject', [$c, 'reject'])->name('transactions.reject');
    Route::post('/approval', [$c, 'setThreshold'])->name('approval');
    Route::post('/lock', [$c, 'setLock'])->name('lock');
});

// Attendance
Route::prefix('admin/attendance')->name('admin.attendance.')->middleware(['auth','role:super_admin,admin,teacher'])->group(function () {
    Route::get('/mark', [App\Http\Controllers\Admin\AttendanceController::class, 'mark'])->name('mark');
    Route::post('/save', [App\Http\Controllers\Admin\AttendanceController::class, 'save'])->name('save');
    Route::get('/report', [App\Http\Controllers\Admin\AttendanceController::class, 'report'])->name('report');
    Route::get('/student/{student}', [App\Http\Controllers\Admin\AttendanceController::class, 'studentSummary'])->name('student');
});

// Examinations
Route::prefix('admin/exams')->name('admin.exams.')->middleware(['auth','role:super_admin,admin,teacher'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\ExamController::class, 'index'])->name('index');
    Route::post('/types', [App\Http\Controllers\Admin\ExamController::class, 'storeType'])->name('types.store');
    Route::post('/types/{exam}/publish', [App\Http\Controllers\Admin\ExamController::class, 'togglePublish'])->name('types.publish');
    Route::delete('/types/{exam}', [App\Http\Controllers\Admin\ExamController::class, 'destroyType'])->name('types.destroy');

    Route::get('/types/{exam}/schedule', [App\Http\Controllers\Admin\ExamController::class, 'schedule'])->name('schedule');
    Route::post('/types/{exam}/schedule', [App\Http\Controllers\Admin\ExamController::class, 'storeSchedule'])->name('schedule.store');
    Route::delete('/types/{exam}/schedule/{schedule}', [App\Http\Controllers\Admin\ExamController::class, 'destroySchedule'])->name('schedule.destroy');

    Route::get('/types/{exam}/marks', [App\Http\Controllers\Admin\ExamController::class, 'marks'])->name('marks');
    Route::post('/types/{exam}/marks', [App\Http\Controllers\Admin\ExamController::class, 'saveMarks'])->name('marks.save');

    Route::get('/types/{exam}/results', [App\Http\Controllers\Admin\ExamController::class, 'results'])->name('results');
    Route::get('/types/{exam}/report-card/{student}', [App\Http\Controllers\Admin\ExamController::class, 'reportCard'])->name('report-card');
    Route::post('/types/{exam}/report-card/{student}/email', [App\Http\Controllers\Admin\ExamController::class, 'emailReportCard'])->name('report-card.email');

    Route::get('/grades', [App\Http\Controllers\Admin\ExamController::class, 'gradeScales'])->name('grades');
    Route::post('/grades', [App\Http\Controllers\Admin\ExamController::class, 'storeGrade'])->name('grades.store');
    Route::delete('/grades/{scale}', [App\Http\Controllers\Admin\ExamController::class, 'destroyGrade'])->name('grades.destroy');

    Route::post('/types/{exam}/term-report/{student}', [App\Http\Controllers\Admin\ExamController::class, 'saveTermReport'])->name('term-report.save');
});

// ── Teacher Portal ───────────────────────────────────────────────
Route::prefix('teacher')->name('teacher.')->middleware(['auth','role:teacher'])->group(function () {
    Route::get('/dashboard',    [App\Http\Controllers\Teacher\TeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/my-classes',   [App\Http\Controllers\Teacher\TeacherController::class, 'myClasses'])->name('my-classes');
    Route::get('/my-students',  [App\Http\Controllers\Teacher\TeacherController::class, 'myStudents'])->name('my-students');

    // Lesson Plans
    Route::get('/lesson-plans',           [App\Http\Controllers\Teacher\TeacherController::class, 'lessonPlans'])->name('lesson-plans.index');
    Route::get('/lesson-plans/create',    [App\Http\Controllers\Teacher\TeacherController::class, 'createLessonPlan'])->name('lesson-plans.create');
    Route::post('/lesson-plans',          [App\Http\Controllers\Teacher\TeacherController::class, 'storeLessonPlan'])->name('lesson-plans.store');
    Route::get('/lesson-plans/{plan}/edit',   [App\Http\Controllers\Teacher\TeacherController::class, 'editLessonPlan'])->name('lesson-plans.edit');
    Route::put('/lesson-plans/{plan}',        [App\Http\Controllers\Teacher\TeacherController::class, 'updateLessonPlan'])->name('lesson-plans.update');
    Route::delete('/lesson-plans/{plan}',     [App\Http\Controllers\Teacher\TeacherController::class, 'destroyLessonPlan'])->name('lesson-plans.destroy');
});

// ── Admin: Lesson Plan Review ────────────────────────────────────
Route::prefix('admin/lesson-plans')->name('admin.lesson-plans.')->middleware(['auth','role:super_admin,admin'])->group(function () {
    Route::get('/',             [App\Http\Controllers\Admin\ExamController::class, 'lessonPlansAdmin'])->name('index');
    Route::post('/{plan}/review', [App\Http\Controllers\Admin\ExamController::class, 'reviewLessonPlan'])->name('review');
});

// ── Timetable ─────────────────────────────────────────────────────
Route::prefix('admin/timetable')->name('admin.timetable.')->middleware(['auth','role:super_admin,admin,teacher'])->group(function () {
    Route::get('/',      [App\Http\Controllers\Admin\TimetableController::class, 'index'])->name('index');
    Route::post('/save', [App\Http\Controllers\Admin\TimetableController::class, 'save'])->name('save');
    Route::get('/view',  [App\Http\Controllers\Admin\TimetableController::class, 'view'])->name('view');
});

// ── Student Portal ────────────────────────────────────────────────
Route::prefix('student')->name('student.')->group(function () {
    Route::get('/login',  [App\Http\Controllers\Student\StudentController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\Student\StudentController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
    Route::post('/logout',[App\Http\Controllers\Student\StudentController::class, 'logout'])->name('logout');

    Route::middleware('student.auth')->group(function () {
        Route::get('/dashboard',   [App\Http\Controllers\Student\StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/fees',        [App\Http\Controllers\Student\StudentController::class, 'fees'])->name('fees');
        Route::get('/attendance',  [App\Http\Controllers\Student\StudentController::class, 'attendance'])->name('attendance');
        Route::get('/results',     [App\Http\Controllers\Student\StudentController::class, 'results'])->name('results');
        Route::get('/homework',    [App\Http\Controllers\Student\StudentController::class, 'homework'])->name('homework');
        Route::get('/notices',     [App\Http\Controllers\Student\StudentController::class, 'notices'])->name('notices');
        Route::get('/timetable',   [App\Http\Controllers\Student\StudentController::class, 'timetable'])->name('timetable');
        Route::get('/exam-timetable', [App\Http\Controllers\Student\StudentController::class, 'examTimetable'])->name('exam-timetable');
        Route::get('/exams-practice', [App\Http\Controllers\Student\StudentController::class, 'examsPracticeLaunch'])->name('exams-practice');
        Route::get('/change-pin',  [App\Http\Controllers\Student\StudentController::class, 'showChangePin'])->name('change-pin');
        Route::get('/profile', [App\Http\Controllers\Student\StudentController::class, 'profile'])->name('profile');
        Route::get('/report-card/{exam}', [App\Http\Controllers\Student\StudentController::class, 'reportCard'])->name('report-card');
        Route::post('/change-pin', [App\Http\Controllers\Student\StudentController::class, 'changePin'])->name('change-pin.post');
    });
});

// ── Homework (Admin + Teacher) ────────────────────────────────────
Route::prefix('admin/homework')->name('admin.homework.')->middleware(['auth','role:super_admin,admin,teacher'])->group(function () {
    Route::get('/',              [App\Http\Controllers\Admin\HomeworkController::class, 'index'])->name('index');
    Route::get('/create',        [App\Http\Controllers\Admin\HomeworkController::class, 'create'])->name('create');
    Route::post('/',             [App\Http\Controllers\Admin\HomeworkController::class, 'store'])->name('store');
    Route::delete('/{homework}', [App\Http\Controllers\Admin\HomeworkController::class, 'destroy'])->name('destroy');
});

// Teacher homework aliases
Route::prefix('teacher/homework')->name('teacher.homework.')->middleware(['auth','role:teacher'])->group(function () {
    Route::get('/',          [App\Http\Controllers\Admin\HomeworkController::class, 'index'])->name('index');
    Route::get('/create',    [App\Http\Controllers\Admin\HomeworkController::class, 'create'])->name('create');
    Route::post('/',         [App\Http\Controllers\Admin\HomeworkController::class, 'store'])->name('store');
    Route::delete('/{homework}', [App\Http\Controllers\Admin\HomeworkController::class, 'destroy'])->name('destroy');
});

// ── Notices ──────────────────────────────────────────────────────
Route::prefix('admin/notices')->name('admin.notices.')->middleware(['auth','role:super_admin,admin'])->group(function () {
    Route::get('/',             [App\Http\Controllers\Admin\NoticeController::class, 'index'])->name('index');
    Route::post('/',            [App\Http\Controllers\Admin\NoticeController::class, 'store'])->name('store');
    Route::delete('/{notice}',  [App\Http\Controllers\Admin\NoticeController::class, 'destroy'])->name('destroy');
});

// ── Homework Submissions ─────────────────────────────────────────
// Student submits
Route::post('/student/homework/{homework}/submit',
    [App\Http\Controllers\Student\StudentController::class, 'submitHomework'])
    ->name('student.homework.submit')
    ->middleware('student.auth');

// Teacher/Admin grades
Route::prefix('admin/homework')->name('admin.homework.')->middleware(['auth','role:super_admin,admin,teacher'])->group(function () {
    Route::get('/{homework}/submissions',   [App\Http\Controllers\Admin\HomeworkController::class, 'submissions'])->name('submissions');
    Route::post('/{homework}/submissions/{submission}/grade', [App\Http\Controllers\Admin\HomeworkController::class, 'grade'])->name('grade');
});
Route::prefix('teacher/homework')->name('teacher.homework.')->middleware(['auth','role:teacher'])->group(function () {
    Route::get('/{homework}/submissions',   [App\Http\Controllers\Admin\HomeworkController::class, 'submissions'])->name('submissions');
    Route::post('/{homework}/submissions/{submission}/grade', [App\Http\Controllers\Admin\HomeworkController::class, 'grade'])->name('grade');
});


// ── Website CMS: Hero Media ───────────────────────────────────────
Route::prefix('admin/website')->name('admin.website.')->middleware(['auth','role:super_admin,admin'])->group(function () {
    Route::post('/hero-media',        [App\Http\Controllers\Admin\WebsiteCmsController::class, 'storeHeroMedia'])->name('hero-media.store');
    Route::delete('/hero-media/{media}', [App\Http\Controllers\Admin\WebsiteCmsController::class, 'destroyHeroMedia'])->name('hero-media.destroy');
    Route::post('/news',              [App\Http\Controllers\Admin\WebsiteCmsController::class, 'storeNews'])->name('news.store');
    Route::post('/news/{news}',       [App\Http\Controllers\Admin\WebsiteCmsController::class, 'updateNews'])->name('news.update');
    Route::delete('/news/{news}',     [App\Http\Controllers\Admin\WebsiteCmsController::class, 'destroyNews'])->name('news.destroy');
});

// ── Public News Article ───────────────────────────────────────────
Route::get('/news/{slug}', [App\Http\Controllers\Website\WebsiteController::class, 'newsShow'])->name('website.news.show');

// Gallery bulk categorize
Route::post('/admin/gallery-bulk-categorize',
    [App\Http\Controllers\Admin\WebsiteCmsController::class, 'bulkCategorize'])
    ->name('admin.website.gallery.bulk-categorize')
    ->middleware(['auth','role:super_admin,admin']);

// ── Student Promotion ─────────────────────────────────────────────
Route::prefix('admin/promotion')->name('admin.promotion.')->middleware(['auth','role:super_admin,admin,teacher'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\PromotionController::class, 'index'])->name('index');
    Route::post('/roster', [App\Http\Controllers\Admin\PromotionController::class, 'roster'])->name('roster');
    Route::post('/process', [App\Http\Controllers\Admin\PromotionController::class, 'process'])->name('process');
    Route::get('/recommendations', [App\Http\Controllers\Admin\PromotionController::class, 'recommendations'])->name('recommendations');
});
Route::get('/admin/students/{student}/history', [App\Http\Controllers\Admin\PromotionController::class, 'history'])
    ->name('admin.students.history')->middleware(['auth','role:super_admin,admin']);

// ── Teacher: Promotion Recommendations ──────────────────────────
Route::prefix('teacher/promotion')->name('teacher.promotion.')->middleware(['auth','role:teacher'])->group(function () {
    Route::get('/', [App\Http\Controllers\Teacher\TeacherController::class, 'promotionIndex'])->name('index');
    Route::post('/roster', [App\Http\Controllers\Teacher\TeacherController::class, 'promotionRoster'])->name('roster');
    Route::post('/store', [App\Http\Controllers\Teacher\TeacherController::class, 'promotionStore'])->name('store');
});

// ── Teacher Profile (shares Admin\ProfileController — role-aware view) ──
Route::prefix('teacher/profile')->name('teacher.profile.')->middleware(['auth','role:teacher'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('index');
    Route::post('/update', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('update');
    Route::post('/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('password');
});

// ── Activity Logs (Super Admin only) ────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth','role:super_admin'])->group(function () {
    Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::delete('/activity-logs/{log}', [App\Http\Controllers\Admin\ActivityLogController::class, 'destroy'])->name('activity-logs.destroy');
    Route::post('/activity-logs/purge', [App\Http\Controllers\Admin\ActivityLogController::class, 'purge'])->name('activity-logs.purge');
});
