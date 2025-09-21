<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $credentials = [
            'email' => $validated['email'],
            // Penting: key 'password' berisi plain text; Laravel akan validasi terhadap getAuthPassword()
            'password' => $validated['password'],
        ];

        $remember = (bool) ($validated['remember'] ?? false);

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Simpan informasi login terakhir
            $user = Auth::user();
            if ($user) {
                $user->last_login_at = now();
                $user->last_login_ip = $request->ip();
                $user->last_login_user_agent = (string) $request->userAgent();
                $user->save();
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}