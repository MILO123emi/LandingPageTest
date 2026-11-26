<?php

namespace App\Http\Requests\Auth;

use App\Concerns\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class RecoverPasswordRequest extends FormRequest
{
    use PasswordValidationRules;

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return ['code' => ['required', 'digits:6'], 'password' => $this->passwordRules()];
    }
}
