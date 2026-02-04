<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    /**
     * Dummy organizations data.
     */
    private function getDummyOrganizations()
    {
        return [
            [
                'id' => 1,
                'name' => 'PT Telkom Indonesia',
                'code' => 'TLKM',
                'address' => 'Jl. Japati No.1, Bandung',
                'phone' => '021-1500007',
                'email' => 'info@telkom.co.id',
                'status' => 'active',
                'users_count' => 25,
                'created_at' => '2024-01-15',
            ],
            [
                'id' => 2,
                'name' => 'Bank Central Asia',
                'code' => 'BCA',
                'address' => 'Menara BCA, Jakarta',
                'phone' => '021-1500888',
                'email' => 'info@bca.co.id',
                'status' => 'active',
                'users_count' => 18,
                'created_at' => '2024-02-10',
            ],
            [
                'id' => 3,
                'name' => 'Gojek Indonesia',
                'code' => 'GOTO',
                'address' => 'Pasaraya Blok M, Jakarta',
                'phone' => '021-50251110',
                'email' => 'info@gojek.com',
                'status' => 'active',
                'users_count' => 32,
                'created_at' => '2024-03-05',
            ],
            [
                'id' => 4,
                'name' => 'PT Pertamina',
                'code' => 'PTMN',
                'address' => 'Jl. Medan Merdeka Timur, Jakarta',
                'phone' => '021-1500000',
                'email' => 'info@pertamina.com',
                'status' => 'inactive',
                'users_count' => 12,
                'created_at' => '2024-01-20',
            ],
            [
                'id' => 5,
                'name' => 'Universitas Indonesia',
                'code' => 'UI',
                'address' => 'Depok, Jawa Barat',
                'phone' => '021-7867222',
                'email' => 'info@ui.ac.id',
                'status' => 'active',
                'users_count' => 45,
                'created_at' => '2023-12-01',
            ],
        ];
    }

    /**
     * Display a listing of organizations.
     */
    public function index()
    {
        return Inertia::render('Admin/Organizations/Index', [
            'organizations' => $this->getDummyOrganizations(),
        ]);
    }

    /**
     * Show the form for creating a new organization.
     */
    public function create()
    {
        return Inertia::render('Admin/Organizations/Create');
    }

    /**
     * Store a newly created organization (dummy - just redirect with success).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        // Dummy - just redirect with success message
        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organisasi berhasil dibuat. (Demo Mode)');
    }

    /**
     * Show the form for editing an organization.
     */
    public function edit($id)
    {
        $organizations = collect($this->getDummyOrganizations());
        $organization = $organizations->firstWhere('id', (int)$id);

        if (!$organization) {
            return redirect()->route('admin.organizations.index')
                ->with('error', 'Organisasi tidak ditemukan.');
        }

        return Inertia::render('Admin/Organizations/Edit', [
            'organization' => $organization,
        ]);
    }

    /**
     * Update the organization (dummy - just redirect with success).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        // Dummy - just redirect with success message
        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organisasi berhasil diperbarui. (Demo Mode)');
    }

    /**
     * Remove the organization (dummy - just redirect with success).
     */
    public function destroy($id)
    {
        // Dummy - just redirect with success message
        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organisasi berhasil dihapus. (Demo Mode)');
    }
}
