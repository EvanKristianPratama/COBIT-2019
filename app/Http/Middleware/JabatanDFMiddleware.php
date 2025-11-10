<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JabatanDFMiddleware
{
    
    protected $jabatanDF = [
        'Board' => [1,2,3,4,5,6],
        'Executive Management' => [1,2,3,4,5,6],
        'Business Managers' => [1,2,3,4,5,6],
        'IT Managers' => [1,2,3,4,5,6,7,8,9,10],
        'Assurance Providers' => [1,2,3,4,5,6],
        'Risk Management' => [1,2,3,4,5,6],
        'Staff' => [4],
        'Guest' => [1,2,3,4,5,6,7,8,9,10],
        'Administrator' => [1,2,3,4,5,6,7,8,9,10]
    ];

    public function handle(Request $request, Closure $next, $df = null): Response
    {
        
        $enabled = session('jabatan_df_middleware_enabled', true);

        if ($enabled && $df !== null) {
            $user = auth()->user();
            $jabatan = $user?->jabatan;
            $allowedDF = $this->jabatanDF[$jabatan] ?? [];

            if (!in_array($df, $allowedDF)) {
                // Redirect ke DF pertama yang boleh diakses
                $firstDF = $allowedDF[0] ?? 1;
                return redirect()->route('df' . $firstDF . '.form', ['id' => $firstDF])
                    ->with('jabatan_warning', 'Jabatan anda tidak memiliki akses ke halaman ini!');
            }
        }
        session(['show_alert' => true]);

        return $next($request);
    }
}