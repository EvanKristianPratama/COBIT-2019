<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use Inertia\Inertia;


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
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $activatedUsers = User::where('isActivated', true)
            ->where('approval_status', 'approved')
            ->get();
        
        $deactivatedUsers = User::where('isActivated', false)
            ->where('approval_status', '!=', 'pending')
            ->get();
        
        $pendingUsers = User::where('approval_status', 'pending')->get();

        return Inertia::render('Admin/Users/Index', [
            'activatedUsers' => $activatedUsers,
            'deactivatedUsers' => $deactivatedUsers,
            'pendingUsers' => $pendingUsers,
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->only('name', 'email', 'role', 'jabatan', 'organisasi'));

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function deactivate(User $user)
    {
        $user->isActivated = false;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dinonaktifkan.');
    }


    public function activate(User $user)
    {
        $user->isActivated = true;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diaktifkan kembali.');
    }

    public function approve(User $user)
    {
        $user->approval_status = 'approved';
        $user->isActivated = true;
        $user->save();
        
        // Optionally assign default role if not set
        // if (!$user->hasRole('user')) {
        //     $user->assignRole('user');
        // }

        return back()->with('success', 'User approved successfully.');
    }

    public function reject(User $user)
    {
        $user->approval_status = 'rejected';
        $user->isActivated = false;
        $user->save();

        return back()->with('success', 'User rejected.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $count = User::whereIn('id', $request->user_ids)
            ->where('approval_status', 'pending')
            ->update([
                'approval_status' => 'approved',
                'isActivated' => true
            ]);

        return back()->with('success', "$count user berhasil disetujui.");
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $count = User::whereIn('id', $request->user_ids)
            ->where('approval_status', 'pending')
            ->update([
                'approval_status' => 'rejected',
                'isActivated' => false
            ]);

        return back()->with('success', "$count user berhasil ditolak.");
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,user',
            'jabatan' => 'nullable|string|max:255',
            'organisasi' => 'nullable|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'jabatan' => $request->jabatan,
            'organisasi' => $request->organisasi,
            'isActivated' => true,
            'approval_status' => 'approved',
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dibuat.');
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}