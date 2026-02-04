<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class AccessManagementController extends Controller
{
    /**
     * Define available modules in the system.
     */
    private function getModules()
    {
        return [
            [
                'id' => 'design-toolkit',
                'name' => 'Design Toolkit',
                'description' => 'Akses ke Design Factor dan Governance System',
                'icon' => 'puzzle',
            ],
            [
                'id' => 'assessment',
                'name' => 'Assessment',
                'description' => 'Modul untuk melakukan assessment COBIT',
                'icon' => 'clipboard',
            ],
            [
                'id' => 'evaluation',
                'name' => 'Evaluation',
                'description' => 'Evaluasi dan monitoring hasil assessment',
                'icon' => 'chart',
            ],
            [
                'id' => 'reports',
                'name' => 'Reports',
                'description' => 'Generate dan lihat laporan',
                'icon' => 'document',
            ],
            [
                'id' => 'admin',
                'name' => 'Admin Panel',
                'description' => 'Akses ke panel administrasi',
                'icon' => 'cog',
            ],
        ];
    }

    /**
     * Get user permissions (dummy implementation).
     */
    private function getUserPermissions($userId)
    {
        // Dummy permissions - in real implementation, this would come from database
        $dummyPermissions = [
            1 => ['design-toolkit', 'assessment', 'evaluation', 'reports', 'admin'],
            2 => ['design-toolkit', 'assessment', 'evaluation'],
            3 => ['assessment', 'reports'],
        ];

        return $dummyPermissions[$userId] ?? ['design-toolkit', 'assessment'];
    }

    /**
     * Display access management page.
     */
    public function index()
    {
        $users = User::where('isActivated', true)
            ->where('approval_status', 'approved')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'jabatan' => $user->jabatan,
                    'organisasi' => $user->organisasi,
                    'permissions' => $this->getUserPermissions($user->id),
                ];
            });

        return Inertia::render('Admin/Access/Index', [
            'users' => $users,
            'modules' => $this->getModules(),
        ]);
    }

    /**
     * Show permission editor for a specific user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('Admin/Access/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'jabatan' => $user->jabatan,
                'organisasi' => $user->organisasi,
                'permissions' => $this->getUserPermissions($user->id),
            ],
            'modules' => $this->getModules(),
        ]);
    }

    /**
     * Update user permissions (dummy - just redirect with success).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        // Dummy - in real implementation, save to database
        // UserPermission::where('user_id', $id)->delete();
        // foreach ($request->permissions as $moduleId) {
        //     UserPermission::create(['user_id' => $id, 'module_id' => $moduleId]);
        // }

        return redirect()->route('admin.access.index')
            ->with('success', 'Permissions berhasil diperbarui. (Demo Mode)');
    }

    /**
     * Bulk update permissions for multiple users.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        // Dummy - in real implementation, save to database
        $count = count($request->user_ids);

        return back()->with('success', "Permissions untuk $count user berhasil diperbarui. (Demo Mode)");
    }
}
