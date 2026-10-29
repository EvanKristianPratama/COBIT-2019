<?php

namespace App\Http\Middleware;

use App\Models\Assessment;
use App\Services\Cobit\CobitAssessmentAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCobitAssessmentAccess
{
    public function __construct(
        private readonly CobitAssessmentAccessService $cobitAssessmentAccessService
    ) {}

    public function handle(Request $request, Closure $next, string $mode = 'view'): Response
    {
        $assessmentId = $request->route('assessment_id')
            ?? $request->input('assessment_id') ?? session('assessment_id');

        if (! is_numeric($assessmentId)) {
            return $next($request);
        }

        $assessment = Assessment::where('assessment_id', (int) $assessmentId)->first();
        if (! $assessment) {
            abort(403, 'Assessment tidak ditemukan atau akses ditolak.');
        }

        $user = $request->user();
        $allowed = $mode === 'input'
            ? $this->cobitAssessmentAccessService->canInput($user, $assessment)
            : $this->cobitAssessmentAccessService->canView($user, $assessment);

        if (! $allowed) {
            abort(403, 'Anda tidak memiliki akses ke assessment ini.');
        }

        if (! $this->cobitAssessmentAccessService->isInActiveOrganizations($user, $assessment)) {
            if ($request->isMethod('GET') && ! $request->expectsJson()) {
                session()->forget(['assessment_id', 'instansi', 'kode_assessment', 'step2', 'step3', 'step4', 'respondent_ids']);

                return redirect()->route('cobit.design-factors.index')
                    ->with('warning', 'Pilih Design Factor milik perusahaan yang sedang dipilih.');
            }
            abort(403, 'Design Factor ini bukan milik perusahaan yang sedang dipilih.');
        }

        session([
            'assessment_id' => $assessment->assessment_id,
            'instansi' => $assessment->instansi,
            'kode_assessment' => $assessment->kode_assessment,
        ]);

        return $next($request);
    }
}
