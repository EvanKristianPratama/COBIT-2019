<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Services\EvaluationService;
use App\Models\MstObjective;
use App\Models\TrsScoping;
use App\Models\TrsEvalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AssessmentScopeController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function update(Request $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($evaluation->status === 'finished') {
                return response()->json(['success' => false, 'message' => 'Assessment is finished'], 403);
            }

            $selectedScopes = $request->input('scopes', []); 
            $isNewScope = $request->input('is_new', false);
            $scopeName = $request->input('nama_scope');

            DB::transaction(function () use ($evalId, $selectedScopes, $isNewScope, $scopeName, $request) {
                if ($isNewScope && $scopeName) {
                    $scoping = TrsScoping::create([
                        'eval_id' => $evalId,
                        'nama_scope' => $scopeName
                    ]);

                    $inserts = [];
                    foreach ($selectedScopes as $scope) {
                        $scope = trim((string)$scope);
                        if ($scope !== '') {
                            $inserts[] = [
                                'eval_id' => $evalId,
                                'scoping_id' => $scoping->id,
                                'domain_id' => $scope,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                    }

                    if (!empty($inserts)) TrsEvalDetail::insert($inserts);

                } else {
                    $scopeId = $request->input('scope_id');
                    $scopeToUpdate = $scopeId 
                        ? TrsScoping::where('id', $scopeId)->where('eval_id', $evalId)->first()
                        : TrsScoping::where('eval_id', $evalId)->first();
                    
                    if ($scopeToUpdate) {
                        if ($scopeName) $scopeToUpdate->update(['nama_scope' => $scopeName]);
                        
                        TrsEvalDetail::where('scoping_id', $scopeToUpdate->id)->delete();

                        $inserts = [];
                        foreach ($selectedScopes as $scope) {
                            $scope = trim((string)$scope);
                            if ($scope !== '') {
                                $inserts[] = [
                                    'eval_id' => $evalId,
                                    'scoping_id' => $scopeToUpdate->id,
                                    'domain_id' => $scope,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                        }

                        if (!empty($inserts)) TrsEvalDetail::insert($inserts);
                    }
                }
            });
            
            return response()->json(['success' => true, 'message' => 'Scope updated successfully']);

        } catch (\Exception $e) {
            Log::error("Failed to update scope", ['eval_id' => $evalId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update scope: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $scope = TrsScoping::find($request->input('scope_id'));

            if ($scope) {
                $scope->delete(); 
                return response()->json(['success' => true]);
            }
            
            return response()->json(['success' => false, 'message' => 'Scope not found']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getObjectives() {
        return response()->json(MstObjective::select('objective_id', 'objective')->get());
    }
}
