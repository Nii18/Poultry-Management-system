<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $role = $request->get('role');
        
        $query = User::query();
        
        if ($role) {
            $query->where('role', $role);
        }
        
        $users = $query->orderBy('name')->paginate(20);
        
        // Define available roles for the filter dropdown
        $roles = [
            'admin' => 'Admin',
            'manager' => 'Farm Manager',
            'worker' => 'Farm Worker',
            'veterinarian' => 'Veterinarian',
            'accountant' => 'Accountant',
        ];
        
        return view('users.index', compact('users', 'roles', 'role'));
    }
    
    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = [
            'admin' => 'Admin',
            'manager' => 'Farm Manager',
            'worker' => 'Farm Worker',
            'veterinarian' => 'Veterinarian',
            'accountant' => 'Accountant',
        ];
        
        return view('users.create', compact('roles'));
    }
    
    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'farm_name' => 'nullable|string|max:255',
            'role' => 'required|in:admin,manager,worker,veterinarian,accountant'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'farm_name' => $request->farm_name,
                'role' => $request->role,
                'is_active' => true
            ]);
            
            return redirect()->route('users.show', $user->id)
                ->with('success', 'User created successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        // Role display mapping
        $roleDisplay = [
            'admin' => 'Admin',
            'manager' => 'Farm Manager',
            'worker' => 'Farm Worker',
            'veterinarian' => 'Veterinarian',
            'accountant' => 'Accountant',
        ];
        
        return view('users.show', compact('user', 'roleDisplay'));
    }
    
    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        
        $roles = [
            'admin' => 'Admin',
            'manager' => 'Farm Manager',
            'worker' => 'Farm Worker',
            'veterinarian' => 'Veterinarian',
            'accountant' => 'Accountant',
        ];
        
        return view('users.edit', compact('user', 'roles'));
    }
    
    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'farm_name' => 'nullable|string|max:255',
            'role' => 'required|in:admin,manager,worker,veterinarian,accountant'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = User::findOrFail($id);
        
        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'farm_name' => $request->farm_name,
                'role' => $request->role
            ]);
            
            return redirect()->route('users.show', $user->id)
                ->with('success', 'User updated successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }
    
    /**
     * Update user password
     */
    public function updatePassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = User::findOrFail($id);
        
        try {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            
            return redirect()->route('users.show', $user->id)
                ->with('success', 'Password updated successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update password: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account');
        }
        
        // Prevent deleting the last admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot delete the last admin user');
            }
        }
        
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }
    
    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deactivating own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account');
        }
        
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('users.show', $user->id)
            ->with('success', "User {$status} successfully");
    }

    /**
     * Show a list of users that can be impersonated (Role-based)
     */
    public function switchUser()
    {
        /** @var \App\Models\User $currentUser */
    
        // Get the currently authenticated user
        $currentUser = Auth::user();
        
        // Check if user has permission to switch (Admin or Manager)
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to switch users.');
        }
        
        // Get switchable users based on current user's role
        $users = $currentUser->getSwitchableUsers();
        
        // Return the switch view
        return view('users.switch', compact('users'));
    }

    /**
     * Impersonate another user (Role-based check)
     */
    public function switchToUser(User $user)
    {
        /** @var \App\Models\User $currentUser */
  
        // Get the currently authenticated user
        $currentUser = Auth::user();
        
        // Check permission using the canSwitchTo method
        if (!$currentUser->canSwitchTo($user)) {
            return redirect()->route('user.switch')
                ->with('error', 'You do not have permission to switch to this user.');
        }
        
        // Store original user ID if not already stored
        if (!Session::has('impersonator_id')) {
            Session::put('impersonator_id', Auth::id());
        }

        // Login as the target user
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Now impersonating ' . $user->name);
    }

    /**
     * Return back to the original user
     */
    public function switchBack()
    {
        $impersonatorId = Session::pull('impersonator_id');

        if ($impersonatorId) {
            $impersonator = User::find($impersonatorId);
            if ($impersonator) {
                Auth::login($impersonator);
                return redirect()->route('dashboard')->with('success', 'Returned to your account.');
            }
        }

        return redirect()->route('dashboard')->with('error', 'No impersonation session found.');
    }
}