<?php
// app/Http/Controllers/AccountController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class AccountController extends Controller
{
    // Show account edit form
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();
        
        return view('account.edit', [
            'user' => $user
        ]);
    }

    // Update account information
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($validated);

        return redirect()->route('account.edit')->with('success', 'Account updated successfully!');
    }

    // Update account avatar
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
        ]);

        /** @var User $user */
        $user = Auth::user();
        
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = basename($avatarPath);
        $user->save();

        return redirect()->route('account.edit')->with('success', 'Profile picture updated successfully!');
    }

    // Delete account avatar
    public function deleteAvatar()
    {
        /** @var User $user */
        $user = Auth::user();
        
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return redirect()->route('account.edit')->with('success', 'Profile picture removed!');
    }

    // Show change password form
    public function editPassword()
    {
        return view('account.password');
    }

    // Update password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('account.password.edit')->with('success', 'Password changed successfully!');
    }
}