<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\AdminWelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.settings.users', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('guard_name', 'web')->get();
        return view('admin.settings.create-user', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|exists:roles,name',
        ]);

        $tempPassword = Str::random(10);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $user->assignRole($request->role);

        // Send email with credentials
        try {
            Mail::to($request->email)->send(new AdminWelcomeMail(
                $request->name,
                $request->email,
                $tempPassword,
                $request->role
            ));
        } catch (\Exception $e) {
            // Log but don't fail
        }

        return redirect()->route('admin.settings.users')
            ->with('success', "User '{$request->name}' created. Password sent via email.");
    }

    public function toggleStatus(User $user)
    {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        return back()->with('success', "User '{$user->name}' status updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    public function resetPassword(User $user)
    {
        $newPassword = Str::random(10);
        $user->update(['password' => Hash::make($newPassword)]);

        try {
            Mail::to($user->email)->send(new AdminWelcomeMail(
                $user->name,
                $user->email,
                $newPassword,
                $user->roles->first()->name ?? 'Admin'
            ));
        } catch (\Exception $e) {}

        return back()->with('success', "Password reset for '{$user->name}'. New password sent via email.");
    }
}
