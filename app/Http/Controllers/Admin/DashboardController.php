<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats
        $stats = [
            'total_users' => User::count(),
            'pending_approval' => User::where('approval_status', 'pending')->count(),
            'total_roles' => DB::table('roles')->count(),
            // 'active_sessions' => 0 // Placeholder
        ];

        // All Users (Paginated) for Management Section
        $users = User::latest()->paginate(10);

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $stats,
            'users' => $users,
        ]);
    }
}
