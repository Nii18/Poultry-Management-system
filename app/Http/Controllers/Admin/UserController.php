<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
            'role'      => ['required', 'in:admin,manager,worker,veterinarian,accountant'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'farm_name' => ['nullable', 'string', 'max:255'],
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'phone'     => $request->phone,
            'farm_name' => $request->farm_name,
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role'      => ['required', 'in:admin,manager,worker,veterinarian,accountant'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'farm_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user->name      = $request->name;
        $user->email     = $request->email;
        $user->role      = $request->role;
        $user->phone     = $request->phone;
        $user->farm_name = $request->farm_name;
        $user->is_active = $request->boolean('is_active');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->back()
                ->with('error', 'Cannot delete the last admin account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            // Return JSON error for AJAX, redirect for non-AJAX
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account.',
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        // AJAX request — return JSON so JS can update the DOM without a page reload
        if (request()->ajax()) {
            return response()->json([
                'success'   => true,
                'is_active' => $user->is_active,
                'message'   => "{$user->name} {$status} successfully.",
            ]);
        }

        // Fallback for non-AJAX (e.g. direct form submit)
        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} {$status} successfully.");
    }
}