<?php

namespace App\Http\Requests\Admin;

use App\Support\CurrentSite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /** Access is checked by the site.manage middleware and the policy in the controller. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $siteId = app(CurrentSite::class)->require()->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('site_id', $siteId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'unit' => ['nullable', 'string', 'max:20'],
            'is_visible' => ['boolean'],
            'is_available' => ['boolean'],
            'is_featured' => ['boolean'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=8000'],
            'remove_photo' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'category_id' => 'categoría',
            'description' => 'descripción',
            'price' => 'precio',
            'unit' => 'unidad',
            'photo' => 'foto',
        ];
    }
}
