<?php

namespace App\Http\Controllers\Spreadsheet;

use App\Http\Controllers\Controller;
use App\Models\Spreadsheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet as PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            $spreadsheet = Spreadsheet::where('user_id', Auth::id())->findOrFail($id);
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'title' => 'nullable|string|max:255'
        ]);

        try {
            $file = $request->file('file');
            $reader = IOFactory::createReaderForFile($file->getPathname());
            $reader->setReadDataOnly(true);
            $excelSpreadsheet = $reader->load($file->getPathname());
            
            $sheetsData = [];
            $sheetCount = $excelSpreadsheet->getSheetCount();
            
            for ($i = 0; $i < $sheetCount; $i++) {
                $worksheet = $excelSpreadsheet->getSheet($i);
                $sheetName = $worksheet->getTitle();
                $rows = $worksheet->toArray(null, true, true, false);
                
                $rows = $this->normalizeGridData($rows, 30, 20);
                
                $sheetsData[] = [
                    'name' => $sheetName,
                    'cells' => $rows,
                    'style' => [],
                    'mergeCells' => [],
                    'colWidths' => null
                ];
            }
            
            $title = $request->title ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            
            $spreadsheet = Spreadsheet::create([
                'user_id' => Auth::id(),
                'title' => $title,
                'description' => 'Imported from: ' . $file->getClientOriginalName(),
                'data' => [
                    'sheets' => $sheetsData,
                    'activeSheet' => 0
                ]
            ]);

            return redirect()
                ->route('spreadsheet.show', $spreadsheet->id)
                ->with('success', 'File imported successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Spreadsheet import error: ' . $e->getMessage());
            return back()->with('error', 'Failed to import file: ' . $e->getMessage());
        }
    }

    public function export($id)
    {
        $spreadsheet = Spreadsheet::where('user_id', Auth::id())->findOrFail($id);
        
        try {
            $excelSpreadsheet = new PhpSpreadsheet();
            $excelSpreadsheet->removeSheetByIndex(0);
            
            $data = $spreadsheet->data;
            
            if (isset($data['sheets']) && is_array($data['sheets'])) {
                foreach ($data['sheets'] as $index => $sheetData) {
                    $worksheet = $excelSpreadsheet->createSheet($index);
                    $worksheet->setTitle($sheetData['name'] ?? 'Sheet ' . ($index + 1));
                    
                    $cells = $sheetData['cells'] ?? [];
                    $this->populateWorksheet($worksheet, $cells);
                }
            } else {
                $worksheet = $excelSpreadsheet->createSheet(0);
                $worksheet->setTitle('Sheet 1');
                
                $cells = $data['cells'] ?? (is_array($data) ? $data : []);
                $this->populateWorksheet($worksheet, $cells);
            }
            
            $excelSpreadsheet->setActiveSheetIndex(0);
            
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $spreadsheet->title) . '.xlsx';
            
            $writer = new Xlsx($excelSpreadsheet);
            
            $tempFile = tempnam(sys_get_temp_dir(), 'spreadsheet_');
            $writer->save($tempFile);
            
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Spreadsheet export error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export file: ' . $e->getMessage());
        }
    }

    private function normalizeGridData(array $rows, int $minRows, int $minCols): array
    {
        while (count($rows) < $minRows) {
            $rows[] = array_fill(0, $minCols, '');
        }
        
        foreach ($rows as &$row) {
            if (!is_array($row)) {
                $row = [$row];
            }
            while (count($row) < $minCols) {
                $row[] = '';
            }
        }
        
        return $rows;
    }

    private function populateWorksheet($worksheet, array $cells): void
    {
        foreach ($cells as $rowIndex => $row) {
            if (!is_array($row)) continue;
            
            foreach ($row as $colIndex => $value) {
                $worksheet->setCellValue([$colIndex + 1, $rowIndex + 1], $value ?? '');
            }
        }
    }
}