<?php

namespace Modules\User\Http\Controllers;

use Modules\User\DataTables\UsersDataTable;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function index(UsersDataTable $dataTable) {
        abort_if(Gate::denies('access_user_management'), 403);

        return $dataTable->render('user::users.index');
    }


    public function create() {
        abort_if(Gate::denies('access_user_management'), 403);

        return view('user::users.create');
    }


    public function store(StoreUserRequest $request) {
        abort_if(Gate::denies('access_user_management'), 403);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->is_active
        ]);

        $user->assignRole($request->role);

        // Handle avatar upload - Direct to avatars folder
        if ($request->hasFile('avatar')) {
            try {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                // Store new avatar directly to avatars folder
                $extension = $request->file('avatar')->getClientOriginalExtension();
                $filename = $user->id . '_avatar_' . time() . '.' . $extension;
                $avatarPath = $request->file('avatar')->storeAs('avatars', $filename, 'public');
                
                // Update user avatar field
                $user->update(['avatar' => $avatarPath]);
                
                Log::info('User Store: Avatar uploaded successfully', [
                    'user_id' => $user->id,
                    'avatar_path' => $avatarPath,
                    'full_path' => storage_path('app/public/' . $avatarPath)
                ]);
                
            } catch (\Exception $e) {
                Log::error('User Store: Avatar upload failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        toast("User Created & Assigned '$request->role' Role!", 'success');

        return redirect()->route('users.index');
    }


    public function edit(User $user) {
        abort_if(Gate::denies('access_user_management'), 403);

        return view('user::users.edit', compact('user'));
    }


    public function update(UpdateUserRequest $request, User $user) {
        abort_if(Gate::denies('access_user_management'), 403);

        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'is_active' => $request->is_active
        ]);

        $user->syncRoles($request->role);

        // Handle avatar upload for update - Direct to avatars folder
        if ($request->hasFile('avatar')) {
            try {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                    Log::info('User Update: Old avatar deleted', ['user_id' => $user->id, 'old_path' => $user->avatar]);
                }
                
                // Store new avatar directly to avatars folder
                $extension = $request->file('avatar')->getClientOriginalExtension();
                $filename = $user->id . '_avatar_' . time() . '.' . $extension;
                $avatarPath = $request->file('avatar')->storeAs('avatars', $filename, 'public');
                
                // Update user avatar field
                $user->update(['avatar' => $avatarPath]);
                
                Log::info('User Update: Avatar updated successfully', [
                    'user_id' => $user->id,
                    'avatar_path' => $avatarPath,
                    'full_path' => storage_path('app/public/' . $avatarPath)
                ]);
                
            } catch (\Exception $e) {
                Log::error('User Update: Avatar upload failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        toast("User Updated & Assigned '$request->role' Role!", 'info');

        return redirect()->route('users.index');
    }


    public function destroy(User $user) {
        abort_if(Gate::denies('access_user_management'), 403);

        $user->delete();

        toast('User Deleted!', 'warning');

        return redirect()->route('users.index');
    }
}