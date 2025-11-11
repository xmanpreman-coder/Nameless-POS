<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules()
    {
        $userId = $this->route('user')->id;
        
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8|max:255|confirmed',
            'role' => 'required|string|exists:roles,name',
            'is_active' => 'required|in:1,2',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already taken.',
            'password.confirmed' => 'Password confirmation does not match.',
            'role.required' => 'Role is required.',
            'role.exists' => 'Selected role does not exist.',
            'is_active.required' => 'Status is required.'
        ];
    }
}