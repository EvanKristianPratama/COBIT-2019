<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'jabatan' => ['required', 'string', 'max:255'],
            'organisasi' => ['required', 'string', 'max:255'],
        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'jabatan' => $data['jabatan'],
            'organisasi' => $data['organisasi'],
            'approval_status' => 'pending', // Explicit pending
        ]);

        // $user->assignRole('user'); // Optional default role
        
        return $user;
    }
    
    /**
     * The user has been registered.
     */
    protected function registered(\Illuminate\Http\Request $request, $user)
    {
        // Redirect to approval notice instead of home
        return redirect()->route('approval.notice');
    }
}
