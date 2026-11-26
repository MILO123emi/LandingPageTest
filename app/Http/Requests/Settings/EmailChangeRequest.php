<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class EmailChangeRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    /** @return array<string, array<string>> */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ];
    }
}
