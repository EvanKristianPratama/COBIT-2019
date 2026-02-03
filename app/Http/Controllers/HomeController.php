<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Mock apps for dashboard
        $apps = [
            ['key' => 'cobit', 'name' => 'COBIT 2019', 'description' => 'Governance Framework & Toolkits', 'sso_url' => '/cobit2019/cobit_home'],
            ['key' => 'finance', 'name' => 'Finance App', 'description' => 'Financial planning and reports', 'sso_url' => '#'],
            ['key' => 'hr', 'name' => 'HR Portal', 'description' => 'Employee management system', 'sso_url' => '#'],
            ['key' => 'pmo', 'name' => 'Project Management', 'description' => 'Track project progress', 'sso_url' => '#'],
        ];

        return \Inertia\Inertia::render('Dashboard/Home', [
            'user' => $user,
            'apps' => $apps
        ]);
    }
}
