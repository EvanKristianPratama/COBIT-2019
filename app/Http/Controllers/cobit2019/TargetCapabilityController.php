<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\TargetCapability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class TargetCapabilityController extends Controller
{
    private const FIELDS = [
        'EDM01','EDM02','EDM03','EDM04','EDM05',
        'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
        'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
        'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
        'MEA01','MEA02','MEA03','MEA04',
    ];

    // maksimum per field (urutan mengikuti FIELDS)
    private const DATA_MAXIMUM = [
        4, 5, 4, 4, 4, 5, 4, 5, 4, 5, 5, 4, 5, 4, 5, 5, 5, 5, 5, 5,
        4, 4, 5, 5, 4, 5, 5, 5, 5, 4, 5, 5, 5, 5, 4, 5, 5, 5, 5, 4
    ];

    public function edit(Request $request, $id = null)
    {
        $target = $id
            ? TargetCapability::find($id)
            : TargetCapability::where('user_id', Auth::id())->latest('updated_at')->first();

        // buat map code => max untuk view
        $maxMap = array_combine(self::FIELDS, self::DATA_MAXIMUM);
        $totalFields = count(self::FIELDS);

        // ambil seluruh target user untuk ditampilkan berdampingan per tahun
        $allTargets = TargetCapability::where('user_id', Auth::id())
            ->orderBy('tahun', 'desc')
            ->get();

        return view('cobit2019.targetCapability', compact('target', 'maxMap', 'totalFields', 'allTargets'));
    }

    public function save(Request $request)
    {
        // gunakan validasi yang sudah memeriksa max per field
        $data = $request->validate(array_merge($this->baseRules(), $this->capabilityRules()));

        $payload = $this->extractPayload($data);

        if (!empty($data['target_id'])) {
            TargetCapability::where('target_id', $data['target_id'])->update($payload);
            $id = $data['target_id'];
        } else {
            $model = TargetCapability::create($payload);
            $id = $model->target_id;
        }

        return Redirect::back()->with('success', 'Target capability saved.')->with('target_id', $id);
    }

    /**
     * Buat tahun baru (salin organisasi & user) -> redirect ke edit record baru
     */
    public function addYear(Request $request)
    {
        $userId = Auth::id();

        // cari tahun terbaru milik user
        $latest = TargetCapability::where('user_id', $userId)->orderBy('tahun', 'desc')->first();
        $nextYear = $request->input('tahun') ? (int)$request->input('tahun') : ($latest ? ((int)$latest->tahun + 1) : (int)now()->year);

        $payload = [
            'user_id' => $userId,
            'organisasi' => $latest->organisasi ?? Auth::user()->organisasi ?? null,
            'tahun' => $nextYear,
        ];

        // set semua capability null
        foreach (self::FIELDS as $code) {
            $payload[$code] = null;
        }

        $model = TargetCapability::create($payload);

        return Redirect::route('target-capability.edit', ['id' => $model->target_id])
            ->with('success', 'Tahun baru dibuat.')
            ->with('target_id', $model->target_id);
    }

    private function baseRules(): array
    {
        return [
            'target_id'  => 'nullable|integer',
            'user_id'    => 'required|integer|exists:users,id',
            'organisasi' => 'nullable|string|max:255',
            'tahun'      => 'required|integer|min:2000|max:2099',
        ];
    }

    private function capabilityRules(): array
    {
        // buat map code => max sesuai DATA_MAXIMUM
        $map = array_combine(self::FIELDS, self::DATA_MAXIMUM);
        $rules = [];
        foreach (self::FIELDS as $code) {
            $max = $map[$code] ?? 5; // fallback 5 jika sesuatu aneh
            $rules[$code] = "nullable|integer|min:0|max:{$max}";
        }
        // tambahkan total_target jika perlu (frontend mengirimkan hidden field)
        $rules['total_target'] = 'nullable|numeric';
        return $rules;
    }

    private function extractPayload(array $data): array
    {
        $payload = [
            'user_id'    => $data['user_id'],
            'organisasi' => $data['organisasi'] ?? null,
            'tahun'      => $data['tahun'],
        ];

        foreach (self::FIELDS as $code) {
            $payload[$code] = $data[$code] ?? null;
        }

        return $payload;
    }
}
