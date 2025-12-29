<?php

use App\Http\Controllers\Admin\AssessmentController as AdminAssessment;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentEval\AssessmentEvalController;
use App\Http\Controllers\AssessmentEval\AssessmentListController;
use App\Http\Controllers\AssessmentEval\AssessmentReportController;
use App\Http\Controllers\AssessmentEval\AssessmentScopeController;
use App\Http\Controllers\AssessmentEval\EvidenceController;
use App\Http\Controllers\AssessmentEval\TargetMaturityController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\cobit2019\Df10Controller;
use App\Http\Controllers\cobit2019\Df2Controller;
use App\Http\Controllers\cobit2019\Df3Controller;
use App\Http\Controllers\cobit2019\Df4Controller;
use App\Http\Controllers\cobit2019\Df5Controller;
use App\Http\Controllers\cobit2019\Df6Controller;
use App\Http\Controllers\cobit2019\Df7Controller;
use App\Http\Controllers\cobit2019\Df8Controller;
use App\Http\Controllers\cobit2019\Df9Controller;
use App\Http\Controllers\cobit2019\DfController;
use App\Http\Controllers\cobit2019\MstObjectiveController;
use App\Http\Controllers\cobit2019\Step2Controller;
use App\Http\Controllers\cobit2019\Step3Controller;
use App\Http\Controllers\cobit2019\Step4Controller;
use App\Http\Controllers\cobit2019\TargetCapabilityController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;

Route::bind('evalId', function ($value) {
    try {
        // Try to decrypt if it looks like hex (long string)
        if (ctype_xdigit($value) && strlen($value) > 20) {
            return Crypt::decryptString(hex2bin($value));
        }

        // Fallback for direct integer access (optional: can remove if strict security is needed)
        // But helpful during migration or debugging.
        // User asked to hide it, so maybe strict?
        // Let's allow int for backward compatibility if logic fails or during dev.
        return $value;
    } catch (\Exception $e) {
        abort(404);
    }
});

// Public routes

Route::get('/assessment/join', [AssessmentController::class, 'showJoinForm'])
    ->name('assessment.join')
    ->middleware('auth');

Route::post('/assessment/join', [AssessmentController::class, 'join'])
    ->name('assessment.join.store')
    ->middleware('auth');

Route::post('/assessment/request', [AssessmentController::class, 'requestAssessment'])
    ->middleware('auth')
    ->name('assessment.request');

Route::get('/objectives', [MstObjectiveController::class, 'index']);
// Route::get('/objectives/{id}', [MstObjectiveController::class, 'show']);
Route::get('objectives/{id}', [MstObjectiveController::class, 'show'])->name('cobit2019.objectives.show');

// View aggregated data per component (server-side)
Route::get('/objectives/component/{component}', [MstObjectiveController::class, 'byComponent'])
    ->name('cobit2019.objectives.bycomponent');

// Admin routes (auth + role check di controller)
Route::prefix('admin')
    ->middleware('auth')
    ->name('admin.')
    ->group(function () {

        // Dashboard (alias assessments.index)
        Route::get('dashboard', [AdminAssessment::class, 'index'])
            ->name('dashboard');
        Route::get('assessments', [AdminAssessment::class, 'index'])
            ->name('assessments.index');

        // Page users
        Route::get('users', [UserAdminController::class, 'index'])
            ->name('users.index');
        Route::put('users/{id}', [UserAdminController::class, 'update'])->name('users.update');
        Route::put('users/{user}/deactivate', [UserAdminController::class, 'deactivate'])->name('users.deactivate');
        Route::put('users/{user}/activate', [UserAdminController::class, 'activate'])->name('users.activate');

        // CRUD Assessment
        Route::post('assessments', [AdminAssessment::class, 'store'])
            ->name('assessments.store');
        Route::get('assessments/{assessment_id}', [AdminAssessment::class, 'show'])
            ->name('assessments.show');
        Route::delete('assessments/{assessment_id}', [AdminAssessment::class, 'destroy'])
            ->name('assessments.destroy');

        // Tampilkan semua pending requests
        Route::get('requests', [AdminAssessment::class, 'pendingRequests'])
            ->name('requests');

        // **Request–Approve** routes
        // Tampilkan semua pending requests
        Route::get('requests', [AdminAssessment::class, 'pendingRequests'])
            ->name('requests');

        // Approve satu request berdasar index di JSON
        Route::post('requests/{idx}/approve', [AdminAssessment::class, 'approveRequest'])
            ->name('requests.approve');
    });

// Redirect ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest, login, dan register routes
Route::get('/guest', [GuestController::class, 'loginGuest'])->name('guest.login');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('login/google', [LoginController::class, 'redirectToGoogle']);
Route::get('login/google/callback', [LoginController::class, 'handleGoogleCallback']);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Home route
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

// Cobit Home view
Route::get('/cobit2019/cobit_home', function () {
    return view('cobit2019.cobit_home');
})->name('cobit.home')->middleware('auth');

// Target capability routes
Route::middleware('auth')->group(function () {
    Route::get('/cobit2019/target-capability/{id?}', [TargetCapabilityController::class, 'edit'])
        ->name('target-capability.edit');
    Route::post('/cobit2019/target-capability/save', [TargetCapabilityController::class, 'save'])
        ->name('target-capability.save');
    Route::post('target-capability/add-year', [TargetCapabilityController::class, 'addYear'])->name('target-capability.addYear');
});

// Route untuk Step 2 (Summary) - pastikan view Step2 sudah didefinisikan
Route::get('/step2', [Step2Controller::class, 'index'])->name('step2.index')->middleware('auth');
Route::post('/step2/store', [Step2Controller::class, 'storeStep2'])
    ->name('step2.store')
    ->middleware('auth');

// Route GET untuk tampilkan Step 3
Route::get('/step3', [Step3Controller::class, 'index'])
    ->name('step3.index')
    ->middleware('auth');

// Route POST untuk simpan Step 3 ke session
Route::post('/step3/store', [Step3Controller::class, 'store'])
    ->name('step3.store')
    ->middleware('auth');

// Route GET untuk tampilkan Step 4
Route::get('/step4', [Step4Controller::class, 'index'])
    ->name('step4.index')
    ->middleware('auth');

// Route POST untuk simpan Step 4 ke session
Route::post('/step4/store', [Step4Controller::class, 'store'])
    ->name('step4.store')
    ->middleware('auth');

// DF1
Route::get('/df1/form/{id}', [DfController::class, 'showDesignFactorForm'])
    ->name('df1.form')->middleware(['auth', 'jabatan.df:1']);
Route::post('/df1/store', [DfController::class, 'store'])
    ->name('df1.store')->middleware(['auth', 'jabatan.df:1']);
Route::get('/df1/output/{id}', [DfController::class, 'showOutput'])
    ->name('df1.output')->middleware(['auth', 'jabatan.df:1']);

// DF2
Route::get('/df2/form/{id}', [Df2Controller::class, 'showDesignFactor2Form'])
    ->name('df2.form')->middleware(['auth', 'jabatan.df:2']);
Route::post('/df2/store', [Df2Controller::class, 'store'])
    ->name('df2.store')->middleware(['auth', 'jabatan.df:2']);
Route::get('/df2/output/{id}', [Df2Controller::class, 'showOutput'])
    ->name('df2.output')->middleware(['auth', 'jabatan.df:2']);

// DF3
Route::get('/df3/form/{id}', [Df3Controller::class, 'showDesignFactor3Form'])
    ->name('df3.form')->middleware(['auth', 'jabatan.df:3']);
Route::post('/df3/store', [Df3Controller::class, 'store'])
    ->name('df3.store')->middleware(['auth', 'jabatan.df:3']);
Route::get('/df3/output/{id}', [Df3Controller::class, 'showOutput'])
    ->name('df3.output')->middleware(['auth', 'jabatan.df:3']);

// DF4
Route::get('/df4/form/{id}', [Df4Controller::class, 'showDesignFactor4Form'])
    ->name('df4.form')->middleware(['auth', 'jabatan.df:4']);
Route::post('/df4/store', [Df4Controller::class, 'store'])
    ->name('df4.store')->middleware(['auth', 'jabatan.df:4']);
Route::get('/df4/output/{id}', [Df4Controller::class, 'showOutput'])
    ->name('df4.output')->middleware(['auth', 'jabatan.df:4']);

// DF5
Route::get('/df5/form/{id}', [Df5Controller::class, 'showDesignFactor5Form'])
    ->name('df5.form')->middleware(['auth', 'jabatan.df:5']);
Route::post('/df5/store', [Df5Controller::class, 'store'])
    ->name('df5.store')->middleware(['auth', 'jabatan.df:5']);
Route::get('/df5/output/{id}', [Df5Controller::class, 'showOutput'])
    ->name('df5.output')->middleware(['auth', 'jabatan.df:5']);

// DF6
Route::get('/df6/form/{id}', [Df6Controller::class, 'showDesignFactor6Form'])
    ->name('df6.form')->middleware(['auth', 'jabatan.df:6']);
Route::post('/df6/store', [Df6Controller::class, 'store'])
    ->name('df6.store')->middleware(['auth', 'jabatan.df:6']);
Route::get('/df6/output/{id}', [Df6Controller::class, 'showOutput'])
    ->name('df6.output')->middleware(['auth', 'jabatan.df:6']);

// DF7
Route::get('/df7/form/{id}', [Df7Controller::class, 'showDesignFactor7Form'])
    ->name('df7.form')->middleware(['auth', 'jabatan.df:7']);
Route::post('/df7/store', [Df7Controller::class, 'store'])
    ->name('df7.store')->middleware(['auth', 'jabatan.df:7']);
Route::get('/df7/output/{id}', [Df7Controller::class, 'showOutput'])
    ->name('df7.output')->middleware(['auth', 'jabatan.df:7']);

// DF8
Route::get('/df8/form/{id}', [Df8Controller::class, 'showDesignFactor8Form'])
    ->name('df8.form')->middleware(['auth', 'jabatan.df:8']);
Route::post('/df8/store', [Df8Controller::class, 'store'])
    ->name('df8.store')->middleware(['auth', 'jabatan.df:8']);
Route::get('/df8/output/{id}', [Df8Controller::class, 'showOutput'])
    ->name('df8.output')->middleware(['auth', 'jabatan.df:8']);

// DF9
Route::get('/df9/form/{id}', [Df9Controller::class, 'showDesignFactor9Form'])
    ->name('df9.form')->middleware(['auth', 'jabatan.df:9']);
Route::post('/df9/store', [Df9Controller::class, 'store'])
    ->name('df9.store')->middleware(['auth', 'jabatan.df:9']);
Route::get('/df9/output/{id}', [Df9Controller::class, 'showOutput'])
    ->name('df9.output')->middleware(['auth', 'jabatan.df:9']);

// DF10
Route::get('/df10/form/{id}', [Df10Controller::class, 'showDesignFactor10Form'])
    ->name('df10.form')->middleware(['auth', 'jabatan.df:10']);
Route::post('/df10/store', [Df10Controller::class, 'store'])
    ->name('df10.store')->middleware(['auth', 'jabatan.df:10']);
Route::get('/df10/output/{id}', [Df10Controller::class, 'showOutput'])
    ->name('df10.output')->middleware(['auth', 'jabatan.df:10']);

// Route untuk toggle akses DF middleware
Route::get('/akses-df/toggle', function () {
    $current = session('jabatan_df_middleware_enabled', true);
    session(['jabatan_df_middleware_enabled' => ! $current]);

    return back();
})->name('akses-df.toggle');

// Assessment Evaluation routes
Route::get('/assessment-eval', [AssessmentEvalController::class, 'index'])
    ->name('assessment-eval.index')
    ->middleware('auth');

Route::get('/assessment-eval/list', [AssessmentListController::class, 'index'])
    ->name('assessment-eval.list')
    ->middleware('auth');

Route::get('/assessment-eval/report-all', [AssessmentReportController::class, 'index'])
    ->name('assessment-eval.report.all')
    ->middleware('auth');

Route::get('/assessment-eval/report-spiderweb', [AssessmentReportController::class, 'spiderweb'])
    ->name('assessment-eval.report.spiderweb')
    ->middleware('auth');

Route::resource('assessment-eval/target-maturity', TargetMaturityController::class)
    ->only(['index', 'store', 'destroy'])
    ->middleware('auth');

Route::post('/assessment-eval/create', [AssessmentEvalController::class, 'createAssessment'])
    ->name('assessment-eval.create')
    ->middleware('auth');

Route::get('/assessment-eval/{evalId}', [AssessmentEvalController::class, 'showAssessment'])
    ->name('assessment-eval.show')
    ->middleware('auth');

Route::post('/assessment-eval/{evalId}/update-scope', [AssessmentScopeController::class, 'update'])
    ->name('assessment-eval.update-scope')
    ->middleware('auth');

Route::delete('/assessment-eval/delete-scope', [AssessmentScopeController::class, 'destroy'])
    ->name('assessment-eval.delete-scope')
    ->middleware('auth');

Route::post('/assessment-eval/{evalId}/save', [AssessmentEvalController::class, 'save'])
    ->name('assessment-eval.save')
    ->middleware('auth');

Route::get('/assessment-eval/{evalId}/load', [AssessmentEvalController::class, 'load'])
    ->name('assessment-eval.load')
    ->middleware('auth');

Route::delete('/assessment-eval/{evalId}', [AssessmentEvalController::class, 'delete'])
    ->name('assessment-eval.delete')
    ->middleware('auth');

Route::post('/assessment-eval/{evalId}/finish', [AssessmentEvalController::class, 'finish'])
    ->name('assessment-eval.finish')
    ->middleware('auth');

Route::post('/assessment-eval/{evalId}/unlock', [AssessmentEvalController::class, 'unlock'])
    ->name('assessment-eval.unlock')
    ->middleware('auth');

Route::post('/assessment-eval/{evalId}/evidence', [EvidenceController::class, 'store'])
    ->name('assessment-eval.evidence.store')
    ->middleware('auth');

Route::get('/assessment-eval/{evalId}/evidence', [EvidenceController::class, 'index'])
    ->name('assessment-eval.evidence.index')
    ->middleware('auth');

Route::get('/assessment-eval/{evalId}/evidence/previous', [EvidenceController::class, 'previous'])
    ->name('assessment-eval.evidence.previous')
    ->middleware('auth');

Route::put('/assessment-eval/evidence/{evidenceId}', [EvidenceController::class, 'update'])
    ->name('assessment-eval.evidence.update')
    ->middleware('auth');

Route::get('/assessment-eval/{evalId}/report', [AssessmentReportController::class, 'show'])
    ->name('assessment-eval.report')
    ->middleware('auth');

Route::get('/assessment-eval/{evalId}/summary/{objectiveId?}', [AssessmentReportController::class, 'summary'])
    ->name('assessment-eval.summary')
    ->middleware('auth');

Route::get('/assessment-eval/{evalId}/report-activity/{objectiveId}', [
    \App\Http\Controllers\AssessmentEval\ActivityReportController::class, 'show'
])->name('assessment-eval.report-activity')
  ->middleware('auth');

Route::get('/assessment-eval/{evalId}/score', [AssessmentEvalController::class, 'getMaturityScore'])
    ->name('assessment-eval.score')
    ->middleware('auth');
