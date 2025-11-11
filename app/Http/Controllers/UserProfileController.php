<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            try {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                // Ensure avatars directory exists
                if (!Storage::disk('public')->exists('avatars')) {
                    Storage::disk('public')->makeDirectory('avatars');
                }
                
                // Store new avatar with timestamp
                $extension = $request->file('avatar')->getClientOriginalExtension();
                $filename = 'avatar_' . $user->id . '_' . time() . '.' . $extension;
                $avatarPath = $request->file('avatar')->storeAs('avatars', $filename, 'public');
                
                if ($avatarPath) {
                    $updateData['avatar'] = $avatarPath;
                    \Log::info('Avatar uploaded successfully', ['user_id' => $user->id, 'path' => $avatarPath]);
                }
            } catch (\Exception $e) {
                \Log::error('Avatar upload failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                return redirect()->back()->with('error', 'Failed to upload avatar: ' . $e->getMessage());
            }
        }

        // Handle password update
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}