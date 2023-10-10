<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserUpdateRequest extends FormRequest
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
            'confirmpassword' => 'same:password',
            'roles' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Please enter name',
            'confirmpassword.same' => 'Two passwords do not match',
            'roles.required' => 'Please enter roles',
        ];
    }
}
