<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tampilkan daftar user untuk admin
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $activatedUsers = User::where('isActivated', true)->get();
        $deactivatedUsers = User::where('isActivated', false)->get();

        return view('admin.users.users', compact('activatedUsers', 'deactivatedUsers'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'jabatan' => $validated['jabatan'],
            'organisasi' => $validated['organisasi'],
        ]);

        $user->isActivated = $request->boolean('isActivated', true);
        $user->save();

        return redirect()
            ->back()
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);
        $user->update($request->only('name', 'email', 'role', 'jabatan', 'organisasi'));

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function deactivate(User $user)
    {
        $this->ensureAdmin();

        $user->isActivated = false;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dinonaktifkan.');
    }

    public function activate(User $user)
    {
        $this->ensureAdmin();

        $user->isActivated = true;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diaktifkan kembali.');
    }

    protected function ensureAdmin(): void
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }
    }
}
