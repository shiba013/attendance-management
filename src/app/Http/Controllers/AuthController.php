<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function register()
    {
        return view('auth.register');
    }

    public function createUser(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->sendEmailVerificationNotification();
        Auth::login($user);
        return redirect('/email/verify');
    }

    public function email(Request $request)
    {
        $user = Auth::user();
        return view('auth.verify-email', compact('user'));
    }

    public function verification(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect('/login');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/login');
        }
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    }

    public function user()
    {
        session(['login_type' => 'user']);
        return view('auth.login_user');
    }

    public function loginUser(LoginRequest $request)
    {
        $user = $request->only([
            'email',
            'password',
        ]);
        if (Auth::attempt($user)) {
            $request->session()->regenerate();
            return redirect('/attendance');
        }
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput();
    }

    public function admin()
    {
        session(['login_type' => 'admin']);
        return view('auth.login_admin');
    }

    public function loginAdmin(LoginRequest $request)
    {
        $user = $request->only([
            'email',
            'password',
        ]);
        if(Auth::attempt($user)) {
            $request->session()->regenerate();
            $user = Auth::user();
            if ($user->role === 1) {
                return redirect('/admin/attendance/list');
            } else {
                Auth::logout();
                return back()->withErrors([
                    'email' => '管理者ユーザとしての権限が必要です',
                ])->withInput();
            }
        }
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput();
    }

    public function destroy(Request $request)
    {
        $role = Auth::user()->role;
        Auth::guard('web')->logout();

        $loginType = session('login_type');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($loginType === 'admin') {
            return redirect('/admin/login');
        } elseif($loginType === 'user') {
            return redirect('/login');
        }
    }
}
