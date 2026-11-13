<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function create()
    {
        return Auth::guard('admin')->check()
            ? redirect()->route('admin.index')
            : Inertia::render('Admin/Login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ]);
        $key = 'admin-login:'.hash('sha256', mb_strtolower($credentials['login']).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['login' => 'Слишком много попыток. Повторите через минуту.']);
        }
        if (! Auth::guard('admin')->attempt($credentials)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['login' => 'Неверный логин или пароль.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        return redirect()->route('admin.index');
    }

    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        // Keep the operator's login in this browser intact.
        $request->session()->regenerate();
        return redirect()->route('admin.login');
    }
}
