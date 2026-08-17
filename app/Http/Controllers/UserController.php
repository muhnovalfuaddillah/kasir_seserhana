<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role', 'asc')->orderBy('name', 'asc')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,admin,manager,kasir',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        ActivityLog::log('USER_CREATE', "Membuat akun pengguna baru: {$user->name} ({$user->email}) role {$user->role}");

        return redirect()->route('users.index')->with('success', "Pengguna '{$user->name}' berhasil ditambahkan!");
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:super_admin,admin,manager,kasir',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        ActivityLog::log('USER_UPDATE', "Mengubah data pengguna: {$user->name} ({$user->email})");

        return redirect()->route('users.index')->with('success', "Data pengguna '{$user->name}' berhasil diperbarui!");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::log('USER_DELETE', "Menghapus akun pengguna: {$name}");

        return redirect()->route('users.index')->with('success', "Pengguna '{$name}' berhasil dihapus.");
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user')->orderBy('created_at', 'desc')->paginate(25);
        return view('users.activity_logs', compact('logs'));
    }
}
