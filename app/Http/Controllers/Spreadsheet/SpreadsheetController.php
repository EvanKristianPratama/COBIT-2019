<?php

namespace App\Http\Controllers\Spreadsheet;

use App\Http\Controllers\Controller;
use App\Models\Spreadsheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SpreadsheetController extends Controller
{
    private const VALIDATION_RULES = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string'
    ];

    public function index(Request $request): View
    {
        $spreadsheets = Spreadsheet::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('spreadsheet.index', compact('spreadsheets'));
    }

    public function create(): View
    {
        return view('spreadsheet.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(self::VALIDATION_RULES);

        $spreadsheet = Spreadsheet::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'data' => null
        ]);

        return redirect()->route('spreadsheet.show', $spreadsheet->id);
    }

    public function show($id): View
    {
        $spreadsheet = $this->findUserSpreadsheet($id);

        return view('spreadsheet.show', compact('spreadsheet'));
    }

    public function saveData(Request $request, $id): JsonResponse
    {
        try {
            $spreadsheet = $this->findUserSpreadsheet($id);

            $data = $request->input('data');

            $spreadsheet->data = $data;
            $spreadsheet->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Spreadsheet save error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate(self::VALIDATION_RULES);

        $spreadsheet = $this->findUserSpreadsheet($id);
        $spreadsheet->update([
            'title' => $request->title,
            'description' => $request->description
        ]);

        return redirect()
            ->route('spreadsheet.index')
            ->with('success', 'Spreadsheet updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $spreadsheet = $this->findUserSpreadsheet($id);
        $spreadsheet->delete();

        return redirect()
            ->route('spreadsheet.index')
            ->with('success', 'Spreadsheet deleted successfully.');
    }

    private function findUserSpreadsheet($id): Spreadsheet
    {
        return Spreadsheet::where('user_id', Auth::id())->findOrFail($id);
    }
}

