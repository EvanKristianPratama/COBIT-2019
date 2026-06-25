<?php

use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\AssessmentController as DesignAssessmentAdmin;
use App\Http\Controllers\Admin\AccessAdminController;
use App\Http\Controllers\Admin\DesignFactorAdminController;
use App\Http\Controllers\Admin\EvaluationAdminController as AdminAssessment;
use App\Http\Controllers\Admin\OrganizationAdminController;
use App\Http\Controllers\Assessment\ActivityReportController;
use App\Http\Controllers\Assessment\AssessmentEvalController;
use App\Http\Controllers\Assessment\AssessmentListController;
use App\Http\Controllers\Assessment\AssessmentReportController;
use App\Http\Controllers\Assessment\AssessmentScopeController;
use App\Http\Controllers\Assessment\AssessmentSummaryController;
use App\Http\Controllers\Assessment\EvidenceController;
use App\Http\Controllers\Assessment\TargetMaturityController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\cobit2019\DesignToolkitController;
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
use App\Http\Controllers\cobit2019\FocusAreaController;
use App\Http\Controllers\cobit2019\MstObjectiveController;
use App\Http\Controllers\cobit2019\RoadmapController;
use App\Http\Controllers\cobit2019\Step2Controller;
use App\Http\Controllers\cobit2019\Step3Controller;
use App\Http\Controllers\cobit2019\Step4Controller;
use App\Http\Controllers\cobit2019\TargetCapabilityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Spreadsheet\SpreadsheetController;
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
    } catch (Exception $e) {
        abort(404);
    }
});

// Public routes
Route::get('/api/cobit/roles-matrix', [MstObjectiveController::class, 'getRolesMatrix'])->name('cobit.roles-matrix');
Route::get('/api/cobit/gamo-infoflow', [MstObjectiveController::class, 'getGamoInfoflow'])->name('cobit.gamo-infoflow');

// Public component API routes
Route::get('/api/cobit/components', [MstObjectiveController::class, 'getComponentsList'])->name('cobit.components.list');
Route::get('/api/cobit/components/{component}', [MstObjectiveController::class, 'getComponentApi'])->name('cobit.components.show');

// Component API route aliases
Route::get('/api/cobit/overview', [MstObjectiveController::class, 'getOverviewApi'])->name('cobit.api.overview');
Route::get('/api/cobit/goals', [MstObjectiveController::class, 'getGoalsApi'])->name('cobit.api.goals');
Route::get('/api/cobit/domains', [MstObjectiveController::class, 'getDomainsApi'])->name('cobit.api.domains');
Route::get('/api/cobit/practices', [MstObjectiveController::class, 'getPracticesApi'])->name('cobit.api.practices');
Route::get('/api/cobit/processes', [MstObjectiveController::class, 'getPracticesApi'])->name('cobit.api.processes');
Route::get('/api/cobit/organizational', [MstObjectiveController::class, 'getOrganizationalApi'])->name('cobit.api.organizational');
Route::get('/api/cobit/infoflows', [MstObjectiveController::class, 'getInfoflowsApi'])->name('cobit.api.infoflows');
Route::get('/api/cobit/information-flows', [MstObjectiveController::class, 'getInfoflowsApi'])->name('cobit.api.information-flows');
Route::get('/api/cobit/policies', [MstObjectiveController::class, 'getPoliciesApi'])->name('cobit.api.policies');
Route::get('/api/cobit/skills', [MstObjectiveController::class, 'getSkillsApi'])->name('cobit.api.skills');
Route::get('/api/cobit/culture', [MstObjectiveController::class, 'getCultureApi'])->name('cobit.api.culture');
Route::get('/api/cobit/services', [MstObjectiveController::class, 'getServicesApi'])->name('cobit.api.services');

// Public Focus Area API routes
Route::get('/api/cobit/focus-areas', [FocusAreaController::class, 'apiList'])->name('cobit.api.focus-areas');
Route::get('/api/cobit/focus-areas/{id}', [FocusAreaController::class, 'apiShow'])->name('cobit.api.focus-areas.show');

Route::get('/assessment/join', [DesignToolkitController::class, 'showJoinForm'])
    ->name('assessment.join')
    ->middleware(['auth', 'permission:cobit.view']);

Route::post('/assessment/join', [DesignToolkitController::class, 'join'])
    ->name('assessment.join.store')
    ->middleware(['auth', 'permission:cobit.view']);

Route::post('/assessment/request', [DesignToolkitController::class, 'requestAssessment'])
    ->middleware(['auth', 'permission:cobit.view'])
    ->name('assessment.request');

Route::middleware(['auth', 'permission:cobit.view'])->group(function () {
    Route::get('/objectives', [MstObjectiveController::class, 'index']);
    Route::get('objectives/{id}', [MstObjectiveController::class, 'show'])->name('cobit_component.show');

    // Visual flow analysis for a selected GAMO (Information Flow & RACI)
    Route::get('/objectives/analysis/gamo', [MstObjectiveController::class, 'gamoAnalysis'])
        ->name('cobit_component.gamoanalysis');

    // View aggregated data per component (server-side)
    Route::get('/objectives/component/{component}', [MstObjectiveController::class, 'byComponent'])
        ->name('cobit_component.bycomponent');

    // Get practices list for infoflow dropdown
    Route::get('/objectives/practices-list', [MstObjectiveController::class, 'getPracticesList'])
        ->name('cobit_component.practices-list');

    // Focus Area pages
    Route::get('/focus-areas', [FocusAreaController::class, 'index'])->name('focus-areas.index');
    Route::get('/focus-areas/{id}', [FocusAreaController::class, 'show'])->name('focus-areas.show');
});

Route::middleware(['auth', 'permission:design-factors.input'])->group(function () {
    // Focus Area CRUD
    Route::post('/focus-areas', [FocusAreaController::class, 'store'])->name('focus-areas.store');
    Route::put('/focus-areas/{id}', [FocusAreaController::class, 'update'])->name('focus-areas.update');
    Route::delete('/focus-areas/{id}', [FocusAreaController::class, 'destroy'])->name('focus-areas.destroy');
    // Store a new objective within a focus area
    Route::post('/focus-areas/{id}/objectives', [FocusAreaController::class, 'storeObjective'])->name('focus-areas.objectives.store');
    // Generate COBIT 5 objectives
    Route::post('/focus-areas/{id}/generate-cobit5', [FocusAreaController::class, 'generateCobit5'])->name('focus-areas.generate-cobit5');
    // Focus Area objective CRUD (update & destroy)
    Route::put('/focus-areas/{id}/objectives/{objectiveId}', [FocusAreaController::class, 'updateObjective'])
        ->name('focus-areas.objectives.update');
    Route::delete('/focus-areas/{id}/objectives/{objectiveId}', [FocusAreaController::class, 'destroyObjective'])
        ->name('focus-areas.objectives.destroy');
});

Route::middleware(['auth', 'permission:design-factors.input'])->group(function () {
    Route::post('/objectives/infoflow-input', [MstObjectiveController::class, 'createInfoflowInput'])
        ->name('cobit_component.infoflow-input.store');
    Route::put('/objectives/infoflow-input/{inputId}', [MstObjectiveController::class, 'updateInfoflowInput'])
        ->name('cobit_component.infoflow-input.update');
    Route::delete('/objectives/infoflow-input/{inputId}', [MstObjectiveController::class, 'deleteInfoflowInput'])
        ->name('cobit_component.infoflow-input.destroy');
    Route::post('/objectives/infoflow-output', [MstObjectiveController::class, 'createInfoflowOutput'])
        ->name('cobit_component.infoflow-output.store');
    Route::put('/objectives/infoflow-output/{outputId}', [MstObjectiveController::class, 'updateInfoflowOutput'])
        ->name('cobit_component.infoflow-output.update');
    Route::delete('/objectives/infoflow-output/{outputId}', [MstObjectiveController::class, 'deleteInfoflowOutput'])
        ->name('cobit_component.infoflow-output.destroy');
    Route::post('/objectives/policies', [MstObjectiveController::class, 'createPolicy'])
        ->name('cobit_component.policies.store');
    Route::put('/objectives/policies/{policyId}', [MstObjectiveController::class, 'updatePolicy'])
        ->name('cobit_component.policies.update');
    Route::put('/objectives/entergoals/{entergoalsId}', [MstObjectiveController::class, 'updateEnterGoal'])
        ->name('cobit_component.entergoals.update');
    Route::put('/objectives/entergoals-metrics/{metricId}', [MstObjectiveController::class, 'updateEnterGoalMetric'])
        ->name('cobit_component.entergoals.metrics.update');
    Route::put('/objectives/aligngoals/{aligngoalsId}', [MstObjectiveController::class, 'updateAlignGoal'])
        ->name('cobit_component.aligngoals.update');
    Route::put('/objectives/aligngoals-metrics/{metricId}', [MstObjectiveController::class, 'updateAlignGoalMetric'])
        ->name('cobit_component.aligngoals.metrics.update');
    Route::post('/objectives/practices', [MstObjectiveController::class, 'createPractice'])
        ->name('cobit_component.practices.store');
    Route::put('/objectives/practices/{practiceId}', [MstObjectiveController::class, 'updatePractice'])
        ->name('cobit_component.practices.update');
    Route::delete('/objectives/practices/{practiceId}', [MstObjectiveController::class, 'destroyPractice'])
        ->name('cobit_component.practices.destroy');
    Route::put('/objectives/practices/{practiceId}/roles/{roleId}', [MstObjectiveController::class, 'updatePracticeRole'])
        ->name('cobit_component.practices.roles.update');
    Route::delete('/objectives/{objectiveId}/roles/{roleId}', [MstObjectiveController::class, 'destroyObjectiveRole'])
        ->name('cobit_component.objectives.roles.destroy');
    Route::put('/objectives/roles/{roleId}', [MstObjectiveController::class, 'updateMasterRole'])
        ->name('cobit_component.master_roles.update');
    Route::post('/objectives/activities', [MstObjectiveController::class, 'createActivity'])
        ->name('cobit_component.activities.store');
    Route::put('/objectives/activities/{activityId}', [MstObjectiveController::class, 'updateActivity'])
        ->name('cobit_component.activities.update');
    Route::delete('/objectives/activities/{activityId}', [MstObjectiveController::class, 'destroyActivity'])
        ->name('cobit_component.activities.destroy');
    Route::post('/objectives/policies/{policyId}/guidance', [MstObjectiveController::class, 'createPolicyGuidance'])
        ->name('cobit_component.policies.guidance.store');
    Route::post('/objectives/skills', [MstObjectiveController::class, 'createSkill'])
        ->name('cobit_component.skills.store');
    Route::put('/objectives/skills/{skillId}', [MstObjectiveController::class, 'updateSkill'])
        ->name('cobit_component.skills.update');
    Route::post('/objectives/skills/{skillId}/guidance', [MstObjectiveController::class, 'createSkillGuidance'])
        ->name('cobit_component.skills.guidance.store');
    Route::post('/objectives/key-culture', [MstObjectiveController::class, 'createKeyCulture'])
        ->name('cobit_component.key-culture.store');
    Route::put('/objectives/key-culture/{keyCultureId}', [MstObjectiveController::class, 'updateKeyCulture'])
        ->name('cobit_component.key-culture.update');
    Route::post('/objectives/key-culture/{keyCultureId}/guidance', [MstObjectiveController::class, 'createKeyCultureGuidance'])
        ->name('cobit_component.key-culture.guidance.store');
    Route::post('/objectives/sia', [MstObjectiveController::class, 'createSia'])
        ->name('cobit_component.sia.store');
    Route::put('/objectives/sia/{siaId}', [MstObjectiveController::class, 'updateSia'])
        ->name('cobit_component.sia.update');
    Route::put('/objectives/guidance/{guidanceId}', [MstObjectiveController::class, 'updateGuidance'])
        ->name('cobit_component.guidance.update');
});

// Admin routes (auth + role check di controller)
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
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
        Route::post('users', [UserAdminController::class, 'store'])->name('users.store');
        Route::put('users/{id}', [UserAdminController::class, 'update'])->name('users.update');
        Route::put('users/{user}/deactivate', [UserAdminController::class, 'deactivate'])->name('users.deactivate');
        Route::put('users/{user}/activate', [UserAdminController::class, 'activate'])->name('users.activate');

        Route::get('organizations', [OrganizationAdminController::class, 'index'])
            ->name('organizations.index');
        Route::post('organizations', [OrganizationAdminController::class, 'store'])
            ->name('organizations.store');
        Route::put('organizations/{organization}', [OrganizationAdminController::class, 'update'])
            ->name('organizations.update');

        Route::get('access', [AccessAdminController::class, 'index'])
            ->name('access.index');
        Route::put('access/{accessProfile:access_profile_key}', [AccessAdminController::class, 'updateProfile'])
            ->name('access.update-profile');

        Route::get('design-factors', [DesignFactorAdminController::class, 'index'])
            ->name('design-factors.index');

        // CRUD Design Factor Assessment Codes
        Route::get('design-assessment-codes', [DesignAssessmentAdmin::class, 'index'])
            ->name('design-assessments.index');
        Route::post('design-assessment-codes', [DesignAssessmentAdmin::class, 'store'])
            ->name('design-assessments.store');
        Route::get('design-assessment-codes/{assessment_id}', [DesignAssessmentAdmin::class, 'show'])
            ->name('design-assessments.show');
        Route::post('design-assessment-codes/{assessment_id}/assign-user', [DesignAssessmentAdmin::class, 'assignUser'])
            ->name('design-assessments.assign-user');
        Route::delete('design-assessment-codes/{assessment_id}/assignments/{assignment_id}', [DesignAssessmentAdmin::class, 'revokeUser'])
            ->name('design-assessments.revoke-user');
        Route::delete('design-assessment-codes/{assessment_id}', [DesignAssessmentAdmin::class, 'destroy'])
            ->name('design-assessments.destroy');

        Route::get('design-assessment-requests', [DesignAssessmentAdmin::class, 'pendingRequests'])
            ->name('design-assessments.requests');
        Route::post('design-assessment-requests/{idx}/approve', [DesignAssessmentAdmin::class, 'approveRequest'])
            ->name('design-assessments.requests.approve');
    });

// Redirect ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('login/google', [LoginController::class, 'redirectToGoogle']);
Route::get('login/google/callback', [LoginController::class, 'handleGoogleCallback']);

Route::match(['get', 'post'], '/register', static function () {
    return redirect()->route('login');
})->name('register');

// Home route
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

// Cobit Home view
Route::get('/design_factor/cobit_home', [DesignToolkitController::class, 'showJoinForm'])
    ->name('cobit.home')
    ->middleware(['auth', 'permission:cobit.view']);

// Target capability routes
Route::middleware(['auth', 'permission:design-factors.view'])->group(function () {
    Route::get('/design_factor/target-capability/{id?}', [TargetCapabilityController::class, 'edit'])
        ->name('target-capability.edit');
});

Route::middleware(['auth', 'permission:design-factors.input'])->group(function () {
    Route::post('/design_factor/target-capability/save', [TargetCapabilityController::class, 'save'])
        ->name('target-capability.save');
    Route::post('target-capability/add-year', [TargetCapabilityController::class, 'addYear'])->name('target-capability.addYear');
});

// Route untuk Step 2 (Summary) - pastikan view Step2 sudah didefinisikan
Route::get('/step2', [Step2Controller::class, 'index'])
    ->name('step2.index')
    ->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view']);
Route::post('/step2/store', [Step2Controller::class, 'storeStep2'])
    ->name('step2.store')
    ->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input']);

// Route GET untuk tampilkan Step 3
Route::get('/step3', [Step3Controller::class, 'index'])
    ->name('step3.index')
    ->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view']);

// Route POST untuk simpan Step 3 ke session
Route::post('/step3/store', [Step3Controller::class, 'store'])
    ->name('step3.store')
    ->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input']);

// Route GET untuk tampilkan Step 4
Route::get('/step4', [Step4Controller::class, 'index'])
    ->name('step4.index')
    ->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view']);

// Route POST untuk simpan Step 4 ke session
Route::post('/step4/store', [Step4Controller::class, 'store'])
    ->name('step4.store')
    ->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input']);

// DF1
Route::get('/df1/form/{id}', [DfController::class, 'showDesignFactorForm'])
    ->name('df1.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:1']);
Route::post('/df1/store', [DfController::class, 'store'])
    ->name('df1.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:1']);
Route::get('/df1/output/{id}', [DfController::class, 'showOutput'])
    ->name('df1.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:1']);

// DF2
Route::get('/df2/form/{id}', [Df2Controller::class, 'showDesignFactor2Form'])
    ->name('df2.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:2']);
Route::post('/df2/store', [Df2Controller::class, 'store'])
    ->name('df2.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:2']);
Route::get('/df2/output/{id}', [Df2Controller::class, 'showOutput'])
    ->name('df2.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:2']);

// DF3
Route::get('/df3/form/{id}', [Df3Controller::class, 'showDesignFactor3Form'])
    ->name('df3.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:3']);
Route::post('/df3/store', [Df3Controller::class, 'store'])
    ->name('df3.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:3']);
Route::get('/df3/output/{id}', [Df3Controller::class, 'showOutput'])
    ->name('df3.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:3']);

// DF4
Route::get('/df4/form/{id}', [Df4Controller::class, 'showDesignFactor4Form'])
    ->name('df4.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:4']);
Route::post('/df4/store', [Df4Controller::class, 'store'])
    ->name('df4.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:4']);
Route::get('/df4/output/{id}', [Df4Controller::class, 'showOutput'])
    ->name('df4.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:4']);

// DF5
Route::get('/df5/form/{id}', [Df5Controller::class, 'showDesignFactor5Form'])
    ->name('df5.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:5']);
Route::post('/df5/store', [Df5Controller::class, 'store'])
    ->name('df5.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:5']);
Route::get('/df5/output/{id}', [Df5Controller::class, 'showOutput'])
    ->name('df5.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:5']);

// DF6
Route::get('/df6/form/{id}', [Df6Controller::class, 'showDesignFactor6Form'])
    ->name('df6.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:6']);
Route::post('/df6/store', [Df6Controller::class, 'store'])
    ->name('df6.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:6']);
Route::get('/df6/output/{id}', [Df6Controller::class, 'showOutput'])
    ->name('df6.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:6']);

// DF7
Route::get('/df7/form/{id}', [Df7Controller::class, 'showDesignFactor7Form'])
    ->name('df7.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:7']);
Route::post('/df7/store', [Df7Controller::class, 'store'])
    ->name('df7.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:7']);
Route::get('/df7/output/{id}', [Df7Controller::class, 'showOutput'])
    ->name('df7.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:7']);

// DF8
Route::get('/df8/form/{id}', [Df8Controller::class, 'showDesignFactor8Form'])
    ->name('df8.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:8']);
Route::post('/df8/store', [Df8Controller::class, 'store'])
    ->name('df8.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:8']);
Route::get('/df8/output/{id}', [Df8Controller::class, 'showOutput'])
    ->name('df8.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:8']);

// DF9
Route::get('/df9/form/{id}', [Df9Controller::class, 'showDesignFactor9Form'])
    ->name('df9.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:9']);
Route::post('/df9/store', [Df9Controller::class, 'store'])
    ->name('df9.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:9']);
Route::get('/df9/output/{id}', [Df9Controller::class, 'showOutput'])
    ->name('df9.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:9']);

// DF10
Route::get('/df10/form/{id}', [Df10Controller::class, 'showDesignFactor10Form'])
    ->name('df10.form')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:10']);
Route::post('/df10/store', [Df10Controller::class, 'store'])
    ->name('df10.store')->middleware(['auth', 'permission:design-factors.input', 'cobit.assessment.access:input', 'jabatan.df:10']);
Route::get('/df10/output/{id}', [Df10Controller::class, 'showOutput'])
    ->name('df10.output')->middleware(['auth', 'permission:design-factors.view', 'cobit.assessment.access:view', 'jabatan.df:10']);

// Route untuk toggle akses DF middleware
Route::get('/akses-df/toggle', function () {
    $current = session('jabatan_df_middleware_enabled', true);
    session(['jabatan_df_middleware_enabled' => ! $current]);

    return back();
})->name('akses-df.toggle');

// Roadmap Capability Routes
Route::prefix('design_factor/roadmap')
    ->middleware(['auth', 'approved.user'])
    ->name('roadmap.')
    ->group(function () {
        Route::get('/', [RoadmapController::class, 'index'])->name('index');
        Route::get('/report', [RoadmapController::class, 'report'])->name('report');
        Route::post('/store', [RoadmapController::class, 'store'])->name('store');
        Route::post('/add-year', [RoadmapController::class, 'addYear'])->name('add-year');
        Route::get('/step4-scope', [RoadmapController::class, 'step4Scope'])->name('step4-scope');
        Route::get('/scopes', [RoadmapController::class, 'scopingOptions'])->name('scopes');
        Route::post('/delete-year', [RoadmapController::class, 'deleteYear'])->name('delete-year');
    });

// Assessment routes
Route::middleware(['auth', 'permission:assessments.view'])->group(function () {
    Route::get('/assessment', [AssessmentListController::class, 'index'])
        ->name('assessment.index');

    Route::get('/assessment/list', [AssessmentListController::class, 'index'])
        ->name('assessment.list');

    Route::get('/assessment/report-all', [AssessmentReportController::class, 'index'])
        ->name('assessment.report.all');

    Route::post('/assessment/report-all/pdf', [AssessmentReportController::class, 'exportPdf'])
        ->name('assessment.report.all-pdf');

    Route::get('/assessment/report-spiderweb', [AssessmentReportController::class, 'spiderweb'])
        ->name('assessment.report.spiderweb');

    Route::resource('assessment/target-maturity', TargetMaturityController::class)
        ->only(['index']);

    Route::get('/assessment/{evalId}', [AssessmentEvalController::class, 'showAssessment'])
        ->name('assessment.show');

    Route::get('/assessment/{evalId}/load', [AssessmentEvalController::class, 'load'])
        ->name('assessment.load');

    Route::get('/assessment/{evalId}/evidence', [EvidenceController::class, 'index'])
        ->name('assessment.evidence.index');

    Route::get('/assessment/{evalId}/evidence/previous', [EvidenceController::class, 'previous'])
        ->name('assessment.evidence.previous');

    Route::get('/assessment/{evalId}/report', [AssessmentReportController::class, 'show'])
        ->name('assessment.report');

    Route::get('/assessment/{evalId}/summary', [AssessmentSummaryController::class, 'getNote'])
        ->name('assessment.note');

    Route::get('/assessment/{evalId}/summary/{objectiveId?}', [AssessmentSummaryController::class, 'summary'])
        ->name('assessment.summary');

    Route::get('/assessment/{evalId}/summary-pdf/{objectiveId?}', [AssessmentSummaryController::class, 'summaryPdf'])
        ->name('assessment.summary-pdf');

    Route::get('/assessment/{evalId}/summary-json/{objectiveId?}', [AssessmentSummaryController::class, 'summaryJson'])
        ->name('assessment.summary-json');

    Route::get('/assessment/{evalId}/summary-detail-pdf/{objectiveId?}', [AssessmentSummaryController::class, 'summaryDetailPdf'])
        ->name('assessment.summary-detail-pdf');

    Route::get('/assessment/{evalId}/report-activity/{objectiveId}', [ActivityReportController::class, 'show'])
        ->name('assessment.report-activity');

    Route::get('/assessment/{evalId}/report-activity-pdf/{objectiveId}', [ActivityReportController::class, 'downloadPdf'])
        ->name('assessment.report-activity-pdf');

    Route::get('/assessment/{evalId}/score', [AssessmentEvalController::class, 'getMaturityScore'])
        ->name('assessment.score');
});

Route::middleware(['auth', 'permission:assessments.input'])->group(function () {
    Route::resource('assessment/target-maturity', TargetMaturityController::class)
        ->only(['store', 'destroy']);

    Route::post('/assessment/create', [AssessmentEvalController::class, 'createAssessment'])
        ->name('assessment.create');

    Route::post('/assessment/{evalId}/update-scope', [AssessmentScopeController::class, 'update'])
        ->name('assessment.update-scope');

    Route::delete('/assessment/delete-scope', [AssessmentScopeController::class, 'destroy'])
        ->name('assessment.delete-scope');

    Route::post('/assessment/{evalId}/save', [AssessmentEvalController::class, 'save'])
        ->name('assessment.save');

    Route::delete('/assessment/{evalId}', [AssessmentEvalController::class, 'delete'])
        ->name('assessment.delete');

    Route::post('/assessment/{evalId}/finish', [AssessmentEvalController::class, 'finish'])
        ->name('assessment.finish');

    Route::post('/assessment/{evalId}/unlock', [AssessmentEvalController::class, 'unlock'])
        ->name('assessment.unlock');

    Route::post('/assessment/{evalId}/evidence', [EvidenceController::class, 'store'])
        ->name('assessment.evidence.store');

    Route::put('/assessment/evidence/{evidenceId}', [EvidenceController::class, 'update'])
        ->name('assessment.evidence.update');

    Route::post('/assessment/{evalId}/summary/save-note', [AssessmentSummaryController::class, 'saveNote'])
        ->name('assessment.summary.save-note');
});

// Spreadsheet Tools
Route::prefix('spreadsheet')
    ->middleware(['auth', 'approved.user'])
    ->name('spreadsheet.')
    ->group(function () {
        Route::get('/', [SpreadsheetController::class, 'index'])->name('index');
        Route::get('/create', [SpreadsheetController::class, 'create'])->name('create');
        Route::post('/', [SpreadsheetController::class, 'store'])->name('store');
        Route::post('/import', [SpreadsheetController::class, 'import'])->name('import');
        Route::get('/{id}', [SpreadsheetController::class, 'show'])->name('show');
        Route::get('/{id}/export', [SpreadsheetController::class, 'export'])->name('export');
        Route::post('/{id}/save', [SpreadsheetController::class, 'saveData'])->name('save');
        Route::put('/{id}', [SpreadsheetController::class, 'update'])->name('update');
        Route::delete('/{id}', [SpreadsheetController::class, 'destroy'])->name('destroy');
    });

Route::get('/clear-semua', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');

    return 'Cache Berhasil Dibersihkan!';
});
