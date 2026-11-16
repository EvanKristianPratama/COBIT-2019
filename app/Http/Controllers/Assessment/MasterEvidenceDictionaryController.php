<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Services\Cobit\GamoOverviewService;
use Illuminate\Http\Request;

class MasterEvidenceDictionaryController extends Controller
{
    public function __construct(private readonly GamoOverviewService $overview)
    {
    }

    public function index(Request $request)
    {
        $request->validate([
            'focus_area' => ['nullable', 'integer'],
            'objective_id' => ['nullable', 'string', 'max:255'],
            'organization_id' => ['nullable', 'integer'],
        ]);

        $dictionaryOrganizationId = $request->integer('organization_id') ?: null;
        if ($dictionaryOrganizationId !== null) {
            abort_unless($request->user()->assignedOrganizations()->contains('organization_id', $dictionaryOrganizationId), 403);
        }

        return view('assessment.master-evidence.dictionary.index', $this->overview->data(
            (int) $request->query('focus_area', 1),
            $request->query('objective_id')
        ) + ['dictionaryOrganizationId' => $dictionaryOrganizationId]);
    }
}
