<?php

namespace App\Http\Controllers\Spreadsheet;

use App\Http\Controllers\Controller;
use App\Models\Spreadsheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpreadsheetController extends Controller
{
    public function index(Request $request)
    {
        $spreadsheets = Spreadsheet::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('spreadsheet.index', compact('spreadsheets'));
    }

    public function create()
    {
        return view('spreadsheet.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $spreadsheet = Spreadsheet::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'data' => null // Initialize as null, will be populated when user saves
        ]);

        return redirect()->route('spreadsheet.show', $spreadsheet->id);
    }

    public function show($id)
    {
        $spreadsheet = Spreadsheet::where('user_id', Auth::id())->findOrFail($id);
        
        return view('spreadsheet.show', compact('spreadsheet'));
    }

    public function saveData(Request $request, $id)
    {
        try {
            $spreadsheet = Spreadsheet::where('user_id', Auth::id())->findOrFail($id);
            
            // Get the data from request
            $data = $request->input('data');
            
            // Store the data directly - Laravel will handle JSON encoding via cast
            $spreadsheet->data = $data;
            $spreadsheet->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Spreadsheet save error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $spreadsheet = Spreadsheet::where('user_id', Auth::id())->findOrFail($id);
        $spreadsheet->update([
            'title' => $request->title,
            'description' => $request->description
        ]);

        return redirect()->route('spreadsheet.index')->with('success', 'Spreadsheet updated successfully.');
    }

    public function destroy($id)
    {
        $spreadsheet = Spreadsheet::where('user_id', Auth::id())->findOrFail($id);
        $spreadsheet->delete();

        return redirect()->route('spreadsheet.index')->with('success', 'Spreadsheet deleted successfully.');
    }
}
