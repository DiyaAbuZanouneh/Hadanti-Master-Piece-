<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AgeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100'],
            'min_months' => ['required', 'integer', 'min:0', 'max:240'],
            'max_months' => ['required', 'integer', 'gt:min_months', 'max:240'],
            'is_active'  => ['required', 'boolean'],
        ];
    }
}