<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\User\Rules\MatchCurrentPassword;

class ProfileController extends Controller
{

    public function edit() {
        return view('user::profile');
    }


    public function update(Request $request) {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'image' => 'nullable|image|mimes:png,jpeg,jpg|max:2048'
        ]);

        $user = auth()->user();

        $user->update([
            'name'  => $request->name,
            'email' => $request->email
        ]);

        // Handle image upload - store to media library
        if ($request->hasFile('image')) {
            // Delete existing avatar if any
            if ($user->getFirstMedia('avatars')) {
                $user->getFirstMedia('avatars')->delete();
            }

            // Add new media file to avatars collection
            $user->addMediaFromRequest('image')
                ->toMediaCollection('avatars');

            \Log::info('Profile Image Updated', [
                'user_id' => $user->id,
                'media_count' => $user->getMedia('avatars')->count()
            ]);
        }

        toast('Profile Updated!', 'success');

        return back();
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'current_password'  => ['required', 'max:255', new MatchCurrentPassword()],
            'password' => 'required|min:8|max:255|confirmed'
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password)
        ]);

        toast('Password Updated!', 'success');

        return back();
    }
}
