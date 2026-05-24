<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|in:admin,manager,worker,veterinarian',
            'phone' => 'nullable|string|max:20',
            'farm_name' => 'nullable|string|max:255'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'farm_name' => $request->farm_name
        ]);
        
        // Assign role
        $role = $request->role ?? 'worker';
        $user->assignRole($role);
        
        // Create token
        $token = $user->createToken('auth_token', $this->getAbilitiesForRole($role))->plainTextToken;
        
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }
    
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        
        $user = User::where('email', $request->email)->first();
        
        // Revoke existing tokens if needed
        if ($request->revoke_existing) {
            $user->tokens()->delete();
        }
        
        $deviceName = $request->device_name ?? request()->userAgent();
        $token = $user->createToken($deviceName, $this->getAbilitiesForRole($user->roles->first()->name))->plainTextToken;
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'role' => $user->roles->first()->name,
                'permissions' => $user->getAllPermissions()->pluck('name')
            ]
        ]);
    }
    
    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
    
    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
                'role' => $request->user()->roles->first()->name,
                'permissions' => $request->user()->getAllPermissions()->pluck('name')
            ]
        ]);
    }
    
    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        
        $token = $user->createToken('auth_token', $this->getAbilitiesForRole($user->roles->first()->name))->plainTextToken;
        
        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }
    
    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = $request->user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }
        
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
    
    /**
     * Get abilities based on user role
     */
    private function getAbilitiesForRole($role)
    {
        $abilities = [
            'admin' => [
                'dashboard:view',
                'flocks:view', 'flocks:create', 'flocks:edit', 'flocks:delete',
                'daily-logs:view', 'daily-logs:create', 'daily-logs:edit', 'daily-logs:delete',
                'feed:view', 'feed:create', 'feed:edit', 'feed:delete',
                'health:view', 'health:create', 'health:edit', 'health:delete',
                'reports:view', 'reports:export',
                'users:view', 'users:create', 'users:edit', 'users:delete',
                'settings:manage'
            ],
            'manager' => [
                'dashboard:view',
                'flocks:view', 'flocks:create', 'flocks:edit',
                'daily-logs:view', 'daily-logs:create', 'daily-logs:edit',
                'feed:view', 'feed:create', 'feed:edit',
                'health:view', 'health:create', 'health:edit',
                'reports:view', 'reports:export',
                'users:view'
            ],
            'worker' => [
                'dashboard:view',
                'flocks:view',
                'daily-logs:view', 'daily-logs:create', 'daily-logs:edit',
                'feed:view',
                'health:view'
            ],
            'veterinarian' => [
                'dashboard:view',
                'flocks:view',
                'daily-logs:view',
                'health:view', 'health:create', 'health:edit',
                'treatments:view', 'treatments:create', 'treatments:edit',
                'reports:view'
            ]
        ];
        
        return $abilities[$role] ?? $abilities['worker'];
    }
}