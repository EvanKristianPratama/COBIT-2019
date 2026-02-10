<?php

namespace App\Http\Controllers;

use App\Data\Cobit\Df1Data;
use App\Data\Cobit\Df2Data;
use App\Data\Cobit\Df3Data;
use App\Data\Cobit\Df4Data;
use App\Data\Cobit\Df5Data;
use App\Data\Cobit\Df6Data;
use App\Data\Cobit\Df7Data;
use App\Data\Cobit\Df8Data;
use App\Data\Cobit\Df9Data;
use App\Data\Cobit\Df10Data;
use App\Services\Cobit\Df1Service;
use App\Services\Cobit\Df2Service;
use App\Services\Cobit\Df3Service;
use App\Services\Cobit\Df4Service;
use App\Services\Cobit\Df5Service;
use App\Services\Cobit\Df6Service;
use App\Services\Cobit\Df7Service;
use App\Services\Cobit\Df8Service;
use App\Services\Cobit\Df9Service;
use App\Services\Cobit\Df10Service;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

/**
 * Controller for Design Toolkit (Vue-based Design Factor pages)
 * 
 * Provides Inertia-based rendering for DF1-DF10 assessments.
 */
class DesignToolkitController extends Controller
{
    /**
     * Display the Design Toolkit index (list of all DFs)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGuest = $this->isGuestUser($user);

        $assessmentsSame = collect();
        $assessmentsOther = collect();

        if (Auth::check() && !$isGuest) {
            $sort = $request->input('sort', 'terbaru');
            $orderDir = $sort === 'terlama' ? 'asc' : 'desc';

            $assessmentsSame = $this->getUserAssessments($user, $request, $orderDir);
        }

        $dfRoutes = [];
        for ($i = 1; $i <= 10; $i++) {
            $dfRoutes[$i] = route('design-toolkit.show', ['number' => $i]);
        }

        $assessmentsSame = $assessmentsSame->map(function (Assessment $assessment) use ($user) {
            $data = $assessment->toArray();
            $data['can_delete'] = $this->canDeleteAssessment($user, $assessment);
            $data['scope_type'] = 'internal';
            return $data;
        });

        $assessmentsOther = collect();

        return Inertia::render('DesignToolkit/Index', [
            'auth' => [
                'user' => $user,
            ],
            'isGuest' => $isGuest,
            'assessmentsSame' => $assessmentsSame,
            'assessmentsOther' => $assessmentsOther,
            'assessmentId' => session('assessment_id'),
            'routes' => [
                'dashboard' => route('dashboard'),
                'target_capability' => '/cobit2019/target-capability',
                'target_maturity' => '/assessment-eval/target-maturity',
                'join' => route('design-toolkit.join'),
                'show' => $dfRoutes,
                'summaryStep2' => route('design-toolkit.step2.index'),
                'summaryStep3' => route('design-toolkit.step3.index'),
                'summaryStep4' => route('design-toolkit.step4.index'),
                'roadmap' => route('roadmap.index'),
                'destroy' => route('design-toolkit.destroy', ['assessment' => 0]),
            ],
        ]);
    }

    /**
     * Join/Open an assessment and redirect to Design Factors
     */
    public function join(Request $request)
    {
        $request->validate([
            'kode_assessment' => 'required|string',
        ]);

        $user = Auth::user();
        $kode = $request->input('kode_assessment');

        // Check if "new"
        if (in_array(strtolower($kode), ['new', 'create'])) {
            return $this->createNewAssessment($user, $request);
        }

        $assessment = Assessment::where('kode_assessment', $kode)->first();

        if (!$assessment) {
            return back()->with('error', 'Assessment code not found');
        }

        // Attach to user if no owner
        if ($assessment->user_id === null && !$this->isGuestUser($user)) {
            $assessment->user_id = $user->id;
            $assessment->save();
        }

        session()->put([
            'assessment_id' => $assessment->assessment_id,
            'instansi' => $assessment->instansi,
            'tahun' => $assessment->tahun,
            'is_guest' => false,
        ]);

        return redirect()->route('design-toolkit.show', ['number' => 1])
            ->with('success', "Opened assessment: {$assessment->kode_assessment}");
    }

    /**
     * Internal helper to create new assessment
     */
    private function createNewAssessment($user, Request $request)
    {
        $newCode = 'DT-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $tahun = $request->input('tahun', date('Y'));

        $assessment = Assessment::create([
            'kode_assessment' => $newCode,
            'instansi' => $user->organisasi ?? $user->name,
            'user_id' => $user->id,
            'tahun' => $tahun,
        ]);

        session()->put([
            'assessment_id' => $assessment->assessment_id,
            'instansi' => $assessment->instansi,
            'tahun' => $assessment->tahun,
            'is_guest' => false,
        ]);

        return redirect()->route('design-toolkit.show', ['number' => 1])
            ->with('success', "New assessment created: {$newCode}");
    }

    /**
     * Delete an assessment (owner or admin only)
     */
    public function destroy(Request $request, Assessment $assessment)
    {
        $user = Auth::user();
        if (!$this->canDeleteAssessment($user, $assessment)) {
            abort(403);
        }

        if ((int) session('assessment_id') === (int) $assessment->assessment_id) {
            session()->forget(['assessment_id', 'instansi', 'tahun', 'is_guest']);
        }

        $assessment->delete();

        return Redirect::back()->with('success', 'Assessment deleted.');
    }
    private function isGuestUser($user): bool
    {
        if (!$user) return false;
        return in_array(strtolower($user->role ?? ''), ['guest']) ||
               in_array(strtolower($user->jabatan ?? ''), ['guest']);
    }

    private function isAdmin($user): bool
    {
        return !empty($user->role) && strtolower($user->role) === 'admin';
    }

    private function canDeleteAssessment($user, Assessment $assessment): bool
    {
        if (!$user) return false;
        if ($this->isAdmin($user)) return true;
        return (int) $assessment->user_id === (int) $user->id;
    }

    private function getAdminAssessments($user, Request $request, string $orderDir): array
    {
        if (empty($user->organisasi)) {
            $queryAll = Assessment::query();
            return [
                'assessments_same' => collect(),
                'assessments_other' => $queryAll->orderBy('created_at', $orderDir)->get(),
            ];
        }

        $querySame = Assessment::where('instansi', 'like', '%' . $user->organisasi . '%');
        $queryOther = Assessment::where(function ($q) use ($user) {
            $q->where('instansi', 'not like', '%' . $user->organisasi . '%')
              ->orWhereNull('instansi')
              ->orWhere('instansi', '');
        });

        return [
            'assessments_same' => $querySame->orderBy('created_at', $orderDir)->get(),
            'assessments_other' => $queryOther->orderBy('created_at', $orderDir)->get(),
        ];
    }

    private function getUserAssessments($user, Request $request, string $orderDir)
    {
        $query = Assessment::query();

        if (!empty($user->role) && strtolower($user->role) === 'pic') {
            // PIC sees all
        } elseif (!empty($user->organisasi)) {
            $query->where('instansi', 'like', '%' . $user->organisasi . '%');
        } else {
            $query->where('user_id', $user->id);
        }

        return $query->orderBy('created_at', $orderDir)->get();
    }

    /**
     * Display a specific Design Factor assessment page
     */
    public function show(int $number)
    {
        if ($number < 1 || $number > 10) {
            abort(404, 'Design Factor not found');
        }

        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return redirect()->route('design-toolkit.index')->with('error', 'Silakan pilih atau buat assessment terlebih dahulu.');
        }

        $service = $this->getService($number);
        $history = $service->loadHistory((int) $assessmentId, $number);

        $dataClass = $this->getDataClass($number);
        $config = $this->getConfig($number, $dataClass);
        
        // Pass history to the Vue component
        $config['history'] = $history;
        $config['historyInputs'] = $this->mapServiceHistoryToVueProps($number, $history);
        
        return Inertia::render("DesignToolkit/DF{$number}/Index", $config);
    }

    /**
     * Map Service history data (flat) to Vue prop format (sequential array or structured objects)
     */
    private function mapServiceHistoryToVueProps(int $number, array $history): ?array
    {
        $inputs = $history['inputs'];
        if (!$inputs) return null;

        switch ($number) {
            case 3:
                $mapped = [];
                for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
                    $mapped[] = [
                        'impact' => $inputs["impact{$i}"] ?? 3,
                        'likelihood' => $inputs["likelihood{$i}"] ?? 3,
                        'rating' => $inputs["input{$i}df3"] ?? 9,
                    ];
                }
                return $mapped;

            default:
                // Handle sequential array
                if (is_array($inputs) && (count($inputs) === 0 || array_keys($inputs) === range(0, count($inputs) - 1))) {
                    return $inputs;
                }
                
                // Handle associative array input1dfX...
                $mapped = [];
                $i = 1;
                while (isset($inputs["input{$i}df{$number}"])) {
                    $mapped[] = $inputs["input{$i}df{$number}"];
                    $i++;
                }
                return !empty($mapped) ? $mapped : $inputs;
        }
    }

    /**
     * Store Design Factor assessment data
     */
    public function store(Request $request, int $number)
    {
        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return back()->with('error', 'Assessment ID tidak ditemukan.');
        }

        try {
            $service = $this->getService($number);
            $inputs = $this->mapVueRequestToServiceData($number, $request);
            
            $service->store($number, (int) $assessmentId, $inputs);

            return redirect()->back()->with('success', "DF{$number} saved successfully");
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Map Vue Inertia request data to the flat format expected by Cobit Services
     */
    private function mapVueRequestToServiceData(int $number, Request $request): array
    {
        $data = $request->all();
        $vueInputs = $request->input('inputs', []);
        $mapped = [];

        switch ($number) {
            case 3:
                // DF3 has Impact and Likelihood
                foreach ($vueInputs as $index => $item) {
                    $i = $index + 1;
                    $impact = (float) ($item['impact'] ?? 3);
                    $likelihood = (float) ($item['likelihood'] ?? 3);
                    $mapped["input{$i}df3"] = (int) ($impact * $likelihood);
                    $mapped["impact{$i}"] = $impact;
                    $mapped["likelihood{$i}"] = $likelihood;
                }
                break;

            default:
                // Standard mapping: input1dfX, input2dfX, etc.
                foreach ($vueInputs as $index => $value) {
                    $i = $index + 1;
                    $mapped["input{$i}df{$number}"] = $value;
                }
                // Special for DF1
                if ($number === 1) {
                    $mapped['strategy_archetype'] = $vueInputs[0] ?? 0;
                    $mapped['current_performance'] = $vueInputs[1] ?? 0;
                    $mapped['future_goals'] = $vueInputs[2] ?? 0;
                    $mapped['alignment_with_it'] = $vueInputs[3] ?? 0;
                    $mapped['df_id'] = 1;
                }
                break;
        }

        return array_merge($data, $mapped);
    }

    private function getService(int $number)
    {
        $services = [
            1 => Df1Service::class,
            2 => Df2Service::class,
            3 => Df3Service::class,
            4 => Df4Service::class,
            5 => Df5Service::class,
            6 => Df6Service::class,
            7 => Df7Service::class,
            8 => Df8Service::class,
            9 => Df9Service::class,
            10 => Df10Service::class,
        ];

        return app($services[$number]);
    }

    /**
     * Get the data class for a specific Design Factor
     */
    private function getDataClass(int $number): string
    {
        $classes = [
            1 => Df1Data::class,
            2 => Df2Data::class,
            3 => Df3Data::class,
            4 => Df4Data::class,
            5 => Df5Data::class,
            6 => Df6Data::class,
            7 => Df7Data::class,
            8 => Df8Data::class,
            9 => Df9Data::class,
            10 => Df10Data::class,
        ];

        return $classes[$number];
    }

    /**
     * Get configuration data for a specific Design Factor
     */
    private function getConfig(int $number, string $dataClass): array
    {
        $dfRoutes = [];
        for ($i = 1; $i <= 10; $i++) {
            $dfRoutes[$i] = route('design-toolkit.show', ['number' => $i]);
        }

        $baseConfig = [
            'dfNumber' => $number,
            'assessmentId' => session('assessment_id'),
            'objectiveLabels' => $this->getObjectiveLabels(),
            'routes' => [
                'dashboard' => route('dashboard'),
                'index' => route('design-toolkit.index'),
                'store' => route('design-toolkit.store', ['number' => $number]),
                'show' => $dfRoutes,
                'summaryStep2' => route('design-toolkit.step2.index'),
                'summaryStep3' => route('design-toolkit.step3.index'),
                'summaryStep4' => route('design-toolkit.step4.index'),
            ],
        ];

        // Add DF-specific data
        switch ($number) {
            case 1:
                return array_merge($baseConfig, [
                    'inputCount' => $dataClass::INPUT_COUNT,
                    'map' => $dataClass::MAP,
                    'baselineInputs' => $dataClass::BASELINE_INPUTS,
                    'baselineScores' => $dataClass::BASELINE_SCORES,
                    'fields' => $this->getDf1Fields(),
                ]);

            case 2:
                return array_merge($baseConfig, [
                    'inputCount' => $dataClass::INPUT_COUNT,
                    'map1' => $dataClass::MAP_1,
                    'map2' => $dataClass::MAP_2,
                    'baselineInputs' => $dataClass::BASELINE_INPUTS,
                    'baselineScores' => $dataClass::BASELINE_SCORES,
                    'fields' => $this->getDf2Fields(),
                ]);

            case 3:
                return array_merge($baseConfig, [
                    'inputCount' => $dataClass::INPUT_COUNT,
                    'map' => $dataClass::MAP,
                    'baselineScores' => $dataClass::BASELINE_SCORES,
                    'fields' => $this->getDf3Fields(),
                ]);

            case 4:
                return array_merge($baseConfig, [
                    'inputCount' => $dataClass::INPUT_COUNT,
                    'map' => $dataClass::MAP,
                    'baselineInputValue' => $dataClass::BASELINE_INPUT_VALUE,
                    'baselineScores' => $dataClass::BASELINE_SCORES,
                    'fields' => $this->getDf4Fields(),
                ]);

            case 5:
            case 6:
            case 8:
            case 9:
            case 10:
                return array_merge($baseConfig, [
                    'inputCount' => $dataClass::INPUT_COUNT,
                    'map' => $dataClass::MAP,
                    'baselineInputs' => $dataClass::BASELINE_INPUTS,
                    'baselineScores' => $dataClass::BASELINE_SCORES,
                    'labels' => $this->getPercentageLabels($number),
                ]);

            case 7:
                return array_merge($baseConfig, [
                    'inputCount' => $dataClass::INPUT_COUNT,
                    'map' => $dataClass::MAP,
                    'baselineInputs' => $dataClass::BASELINE_INPUTS,
                    'baselineScores' => $dataClass::BASELINE_SCORES,
                    'useAverageCalculation' => $dataClass::USE_AVERAGE_CALCULATION,
                    'fields' => $this->getDf7Fields(),
                ]);

            default:
                return $baseConfig;
        }
    }

    /**
     * Get the 40 governance objective labels
     */
    private function getObjectiveLabels(): array
    {
        return [
            'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
            'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
            'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
            'APO11', 'APO12', 'APO13', 'APO14',
            'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05',
            'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
            'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
            'MEA01', 'MEA02', 'MEA03', 'MEA04',
        ];
    }

    /**
     * Get DF1 field definitions
     */
    private function getDf1Fields(): array
    {
        return [
            ['name' => 'growth', 'label' => 'Growth/Acquisition', 'description' => 'Growing, diversifying, creating new products/markets, acquiring new customers/markets'],
            ['name' => 'innovation', 'label' => 'Innovation/Differentiation', 'description' => 'Developing new and unique products/services'],
            ['name' => 'cost', 'label' => 'Cost Leadership', 'description' => 'Minimizing the cost of service delivery to customers'],
            ['name' => 'service', 'label' => 'Client Service/Stability', 'description' => 'Service orientation, stability/continuity'],
        ];
    }

    /**
     * Get DF2 field definitions
     */
    private function getDf2Fields(): array
    {
        return [
            ['name' => 'eg01', 'label' => 'EG01', 'description' => 'Portfolio of competitive products and services'],
            ['name' => 'eg02', 'label' => 'EG02', 'description' => 'Managed business risk'],
            ['name' => 'eg03', 'label' => 'EG03', 'description' => 'Compliance with external laws and regulations'],
            ['name' => 'eg04', 'label' => 'EG04', 'description' => 'Quality of financial information'],
            ['name' => 'eg05', 'label' => 'EG05', 'description' => 'Customer-oriented service culture'],
            ['name' => 'eg06', 'label' => 'EG06', 'description' => 'Business-service continuity and availability'],
            ['name' => 'eg07', 'label' => 'EG07', 'description' => 'Quality of management information'],
            ['name' => 'eg08', 'label' => 'EG08', 'description' => 'Optimization of internal business process functionality'],
            ['name' => 'eg09', 'label' => 'EG09', 'description' => 'Optimization of business process costs'],
            ['name' => 'eg10', 'label' => 'EG10', 'description' => 'Staff skills, motivation and productivity'],
            ['name' => 'eg11', 'label' => 'EG11', 'description' => 'Compliance with internal policies'],
            ['name' => 'eg12', 'label' => 'EG12', 'description' => 'Managed digital transformation programs'],
            ['name' => 'eg13', 'label' => 'EG13', 'description' => 'Product and business innovation'],
        ];
    }

    /**
     * Get DF3 field definitions
     */
    private function getDf3Fields(): array
    {
        return [
            ['name' => 'risk1', 'description' => 'IT investment decision making, portfolio definition & maintenance'],
            ['name' => 'risk2', 'description' => 'Program & projects life cycle management'],
            ['name' => 'risk3', 'description' => 'IT cost & oversight'],
            ['name' => 'risk4', 'description' => 'IT expertise, skills & behavior'],
            ['name' => 'risk5', 'description' => 'Enterprise/IT architecture'],
            ['name' => 'risk6', 'description' => 'IT operational infrastructure incidents'],
            ['name' => 'risk7', 'description' => 'Unauthorized actions'],
            ['name' => 'risk8', 'description' => 'Software adoption/usage problems'],
            ['name' => 'risk9', 'description' => 'Hardware incidents'],
            ['name' => 'risk10', 'description' => 'Software failures'],
            ['name' => 'risk11', 'description' => 'Logical attacks (hacking, malware, etc.)'],
            ['name' => 'risk12', 'description' => 'Third-party/supplier incidents'],
            ['name' => 'risk13', 'description' => 'Noncompliance'],
            ['name' => 'risk14', 'description' => 'Geopolitical Issues'],
            ['name' => 'risk15', 'description' => 'Industrial action'],
            ['name' => 'risk16', 'description' => 'Acts of nature'],
            ['name' => 'risk17', 'description' => 'Technology-based innovation'],
            ['name' => 'risk18', 'description' => 'Environmental'],
            ['name' => 'risk19', 'description' => 'Data & information management'],
        ];
    }

    /**
     * Get DF4 field definitions
     */
    private function getDf4Fields(): array
    {
        return [
            ['name' => 'issue1', 'description' => 'Frustration between different IT entities across the organization because of a perception of low contribution to business value'],
            ['name' => 'issue2', 'description' => 'Frustration between business departments and the IT department because of failed initiatives or a perception of low contribution to business value'],
            ['name' => 'issue3', 'description' => 'Significant IT-related incidents, such as data loss, security breaches, project failure and application errors'],
            ['name' => 'issue4', 'description' => 'Service delivery problems by the IT outsourcer(s)'],
            ['name' => 'issue5', 'description' => 'Failures to meet IT-related regulatory or contractual requirements'],
            ['name' => 'issue6', 'description' => 'Regular audit findings about poor IT performance or reported IT quality or service problems'],
            ['name' => 'issue7', 'description' => 'Substantial hidden and rogue IT spending outside the control of normal IT investment mechanisms'],
            ['name' => 'issue8', 'description' => 'Duplications or overlaps between various initiatives, or other forms of wasted resources'],
            ['name' => 'issue9', 'description' => 'Insufficient IT resources, staff with inadequate skills or staff burnout/dissatisfaction'],
            ['name' => 'issue10', 'description' => 'IT-enabled changes or projects frequently failing to meet business needs and delivered late or over budget'],
            ['name' => 'issue11', 'description' => 'Reluctance by executives to engage with IT, or a lack of committed business sponsorship for IT'],
            ['name' => 'issue12', 'description' => 'Complex IT operating model and/or unclear decision mechanisms for IT-related decisions'],
            ['name' => 'issue13', 'description' => 'Excessively high cost of IT'],
            ['name' => 'issue14', 'description' => 'Obstructed or failed implementation of new initiatives caused by the current IT architecture'],
            ['name' => 'issue15', 'description' => 'Gap between business and technical knowledge, leading to different languages being spoken'],
            ['name' => 'issue16', 'description' => 'Regular issues with data quality and integration of data across various sources'],
            ['name' => 'issue17', 'description' => 'High level of end-user computing creating lack of oversight and quality control'],
            ['name' => 'issue18', 'description' => 'Business departments implementing their own information solutions with little IT involvement'],
            ['name' => 'issue19', 'description' => 'Ignorance of and/or noncompliance with privacy regulations'],
            ['name' => 'issue20', 'description' => 'Inability to exploit new technologies or innovate using I&T'],
        ];
    }

    /**
     * Get DF7 field definitions
     */
    private function getDf7Fields(): array
    {
        return [
            ['name' => 'support', 'label' => 'Support', 'description' => 'IT provides support services'],
            ['name' => 'factory', 'label' => 'Factory', 'description' => 'IT provides factory services'],
            ['name' => 'turnaround', 'label' => 'Turnaround', 'description' => 'IT provides turnaround services'],
            ['name' => 'strategic', 'label' => 'Strategic', 'description' => 'IT provides strategic services'],
        ];
    }

    /**
     * Get percentage labels for DF5, DF6, DF8, DF9, DF10
     */
    private function getPercentageLabels(int $number): array
    {
        $labels = [
            5 => ['High', 'Normal'],
            6 => ['High', 'Normal', 'Low'],
            8 => ['Outsourcing', 'Cloud', 'Insourced'],
            9 => ['Agile', 'DevOps', 'Traditional'],
            10 => ['First Mover', 'Follower', 'Slow Adopter'],
        ];

        return $labels[$number] ?? [];
    }
}
