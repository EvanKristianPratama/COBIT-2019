<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\cobit2019\Concerns\ResolvesObjectiveFocusArea;
use App\Http\Controllers\Controller;
use App\Models\MstGuidance;
use App\Models\MstKeyCulture;
use App\Models\MstPolicy;
use App\Models\MstSIA;
use App\Models\MstSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjectiveContentController extends Controller
{
    use ResolvesObjectiveFocusArea;

    public function createPolicy(Request $request)
    {
        $data = $request->validate([
            'objective_id' => 'required|string|exists:mst_objective,objective_id',
            'policy' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $focusAreaId = $this->focusAreaIdForObjective($data['objective_id']);

        $policy = DB::transaction(function () use ($data, $focusAreaId) {
            return MstPolicy::create([
                'policy_id' => $this->nextPolicyId(),
                'objective_id' => $data['objective_id'],
                'focus_area_id' => $focusAreaId,
                'policy' => $data['policy'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        });

        return response()->json($policy->fresh(), 201);
    }

    public function updatePolicy(Request $request, $policyId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'policy' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $policy = MstPolicy::findOrFail($policyId);
        if (!empty($data['focus_area_id']) && (int) $policy->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $policy->update([
            'policy' => $data['policy'] ?? $policy->policy,
            'description' => $data['description'] ?? $policy->description,
        ]);

        return response()->json($policy->fresh());
    }

    public function destroyPolicy(Request $request, $policyId)
    {
        $policy = MstPolicy::findOrFail($policyId);
        DB::transaction(function () use ($policy) {
            // Delete pivot guidances relations
            DB::table('trs_policyguidance')->where('policy_id', $policy->policy_id)->delete();
            $policy->delete();
        });

        return response()->json(['message' => 'Policy deleted successfully.']);
    }

    public function createPolicyGuidance(Request $request, $policyId)
    {
        $policy = MstPolicy::findOrFail($policyId);

        $data = $request->validate([
            'guidance' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $guidance = DB::transaction(function () use ($policy, $data) {
            $guidance = MstGuidance::create([
                'guidance_id' => $this->nextGuidanceId(),
                'guidance' => $data['guidance'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);

            $policy->guidances()->syncWithoutDetaching([$guidance->guidance_id]);

            return $guidance;
        });

        return response()->json($guidance->fresh(), 201);
    }

    public function createSkill(Request $request)
    {
        $data = $request->validate([
            'objective_id' => 'required|string|exists:mst_objective,objective_id',
            'skill' => 'nullable|string|max:255',
        ]);

        $data['focus_area_id'] = $this->focusAreaIdForObjective($data['objective_id']);
        $skill = MstSkill::create($data);

        return response()->json($skill->fresh(), 201);
    }

    public function updateSkill(Request $request, $skillId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'skill' => 'nullable|string|max:255',
        ]);

        $skill = MstSkill::findOrFail($skillId);
        if (!empty($data['focus_area_id']) && (int) $skill->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $skill->update([
            'skill' => $data['skill'] ?? $skill->skill,
        ]);

        return response()->json($skill->fresh());
    }

    public function destroySkill(Request $request, $skillId)
    {
        $skill = MstSkill::findOrFail($skillId);
        DB::transaction(function () use ($skill) {
            // Delete pivot relations
            DB::table('trs_skillguidance')->where('skill_id', $skill->skill_id)->delete();
            $skill->delete();
        });

        return response()->json(['message' => 'Skill deleted successfully.']);
    }

    public function createSkillGuidance(Request $request, $skillId)
    {
        $skill = MstSkill::findOrFail($skillId);

        $data = $request->validate([
            'guidance' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $guidance = DB::transaction(function () use ($skill, $data) {
            $guidance = MstGuidance::create([
                'guidance_id' => $this->nextGuidanceId(),
                'guidance' => $data['guidance'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);

            $skill->guidances()->syncWithoutDetaching([$guidance->guidance_id]);

            return $guidance;
        });

        return response()->json($guidance->fresh(), 201);
    }

    public function createKeyCulture(Request $request)
    {
        $data = $request->validate([
            'objective_id' => 'required|string|exists:mst_objective,objective_id',
            'element' => 'nullable|string',
        ]);

        $focusAreaId = $this->focusAreaIdForObjective($data['objective_id']);

        $keyCulture = DB::transaction(function () use ($data, $focusAreaId) {
            return MstKeyCulture::create([
                'keyculture_id' => $this->nextKeyCultureId(),
                'objective_id' => $data['objective_id'],
                'focus_area_id' => $focusAreaId,
                'element' => $data['element'] ?? null,
            ]);
        });

        return response()->json($keyCulture->fresh(), 201);
    }

    public function updateKeyCulture(Request $request, $keyCultureId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'element' => 'nullable|string',
        ]);

        $keyCulture = MstKeyCulture::findOrFail($keyCultureId);
        if (!empty($data['focus_area_id']) && (int) $keyCulture->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $keyCulture->update([
            'element' => $data['element'] ?? $keyCulture->element,
        ]);

        return response()->json($keyCulture->fresh());
    }

    public function createKeyCultureGuidance(Request $request, $keyCultureId)
    {
        $keyCulture = MstKeyCulture::findOrFail($keyCultureId);

        $data = $request->validate([
            'guidance' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $guidance = DB::transaction(function () use ($keyCulture, $data) {
            $guidance = MstGuidance::create([
                'guidance_id' => $this->nextGuidanceId(),
                'guidance' => $data['guidance'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);

            $keyCulture->guidances()->syncWithoutDetaching([$guidance->guidance_id]);

            return $guidance;
        });

        return response()->json($guidance->fresh(), 201);
    }

    public function createSia(Request $request)
    {
        $data = $request->validate([
            'objective_id' => 'required|string|exists:mst_objective,objective_id',
            'description' => 'nullable|string',
        ]);

        $focusAreaId = $this->focusAreaIdForObjective($data['objective_id']);

        $sia = DB::transaction(function () use ($data, $focusAreaId) {
            return MstSIA::create([
                'sia_id' => $this->nextSiaId(),
                'objective_id' => $data['objective_id'],
                'focus_area_id' => $focusAreaId,
                'description' => $data['description'] ?? null,
            ]);
        });

        return response()->json($sia->fresh(), 201);
    }

    public function updateSia(Request $request, $siaId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'description' => 'nullable|string',
        ]);

        $sia = MstSIA::findOrFail($siaId);
        if (!empty($data['focus_area_id']) && (int) $sia->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $sia->update([
            'description' => $data['description'] ?? $sia->description,
        ]);

        return response()->json($sia->fresh());
    }

    public function updateGuidance(Request $request, $guidanceId)
    {
        $guidance = MstGuidance::findOrFail($guidanceId);

        $data = $request->validate([
            'guidance' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $guidance->update($data);

        return response()->json($guidance->fresh());
    }

    public function destroyGuidance($guidanceId)
    {
        $guidance = MstGuidance::findOrFail($guidanceId);
        DB::transaction(function () use ($guidance) {
            DB::table('trs_policyguidance')->where('guidance_id', $guidance->guidance_id)->delete();
            DB::table('trs_skillguidance')->where('guidance_id', $guidance->guidance_id)->delete();
            DB::table('trs_keycultureguidance')->where('guidance_id', $guidance->guidance_id)->delete();
            $guidance->delete();
        });

        return response()->json(['message' => 'Guidance deleted successfully.']);
    }

    protected function nextPolicyId(): int
    {
        $latest = DB::table('mst_policy')
            ->select('policy_id')
            ->orderByDesc('policy_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->policy_id ?? 0)) + 1;
    }

    protected function nextKeyCultureId(): int
    {
        $latest = DB::table('mst_keyculture')
            ->select('keyculture_id')
            ->orderByDesc('keyculture_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->keyculture_id ?? 0)) + 1;
    }

    protected function nextSiaId(): int
    {
        $latest = DB::table('mst_SIA')
            ->select('sia_id')
            ->orderByDesc('sia_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->sia_id ?? 0)) + 1;
    }

    protected function nextGuidanceId(): int
    {
        $latest = DB::table('mst_guidance')
            ->select('guidance_id')
            ->orderByDesc('guidance_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->guidance_id ?? 0)) + 1;
    }
}
