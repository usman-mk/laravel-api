<?php

namespace App\Http\Requests;

use App\Http\Controllers\API\BaseController;
use App\Rules\PasswordRules as Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'string',
                Password::min(8) // Minimum length
                    // ->letters()
                    ->mixedCase() // 'regex:/[A-Z]/', At least one uppercase letter and 'regex:/[a-z]/', At least one lowercase letter
                    ->numbers() //  'regex:/[0-9]/', At least one digit
                    ->symbols() //  'regex:/[@$!%*#?&]/', At least one special character
            ],
            'password_confirmation' => 'required|same:password',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            BaseController::validationError($validator)
        );
    }
}
