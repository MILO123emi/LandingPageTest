<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class EmailConfirmRequest extends FormRequest
{
    /** @return array<string, array<string>> */
    public function rules(): array
    {
        return ['code' => ['required', 'digits:6']];
    }
}
