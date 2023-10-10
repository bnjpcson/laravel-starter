<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('user-create');
        
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'confirmpassword' => 'required|same:password',
            'roles' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter name',
            'email.required' => 'Please enter email',
            'email.unique' => 'This email already exists.',
            'email.email' => 'Please provide a valid email address.',
            'password.required' => 'Please enter password',
            'password.min' => 'Password must be 6 or more characters long.',
            'confirmpassword.required' => 'Please enter confirm password',
            'confirmpassword.same' => 'Two passwords do not match',
            'roles.required' => 'Please enter roles',
        ];
    }
}
