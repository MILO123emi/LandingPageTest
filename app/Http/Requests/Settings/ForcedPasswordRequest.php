<?php

namespace App\Http\Requests\Settings;

use App\Concerns\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class ForcedPasswordRequest extends FormRequest
{
    use PasswordValidationRules;

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return ['password' => $this->passwordRules()];
    }
}
