<?php

namespace App\Http\Requests;

use App\Services\SpeciesService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpeciesFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(array_keys(SpeciesService::SORTS))],
        ];
    }
}
