<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('user')) {
            return redirect('/dashboard');
        }
        return view('Login');
    }

    public function doLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->username;
        $password = $request->password;

        $rows = DB::select("
            SELECT u.kd_user, u.kd_group, u.username, u.password, u.keterangan, u.status,
                   g.nama AS group_nama, g.group_level
            FROM m_user u
            LEFT JOIN m_group g ON u.kd_group = g.kd_group
            WHERE u.username = ? AND u.status = 1
        ", [$username]);

        if (empty($rows) || !Hash::check($password, $rows[0]->password)) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['login' => 'Username atau password salah.']);
        }

        $user = $rows[0];
        $roleMap = [
            'US0000' => 'superadmin',
            'US0001' => 'admin',
            'US0002' => 'kasir',
        ];

        session([
            'user' => [
                'kd_user'     => $user->kd_user,
                'kd_group'    => $user->kd_group,
                'group_nama'  => $user->group_nama,
                'group_level' => (int) ($user->group_level ?? 99),
                'username'    => $user->username,
                'keterangan'  => $user->keterangan,
                'role'        => $roleMap[$user->kd_group] ?? 'user',
            ],
        ]);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('status', 'Anda telah logout.');
    }

    public function showForgotPassword()
    {
        if (session()->has('user')) {
            return redirect('/dashboard');
        }
        return view('ForgotPassword');
    }

    public function doForgotPassword(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        $username = $request->username;

        $rows = DB::select("SELECT kd_user, username, status FROM m_user WHERE username = ?", [$username]);

        if (empty($rows)) {
            return back()
                ->withInput()
                ->withErrors(['username' => 'Username tidak ditemukan.']);
        }

        if ((int) $rows[0]->status !== 1) {
            return back()
                ->withInput()
                ->withErrors(['username' => 'Akun tidak aktif. Silakan hubungi administrator.']);
        }

        // Generate password baru random 8 karakter (alphanumeric)
        $newPassword = Str::random(8);
        $hash        = Hash::make($newPassword);

        DB::update("UPDATE m_user SET password = ? WHERE kd_user = ?",
                   [$hash, $rows[0]->kd_user]);

        return view('ForgotPassword', [
            'reset_success'  => true,
            'reset_username' => $rows[0]->username,
            'new_password'   => $newPassword,
        ]);
    }
}
