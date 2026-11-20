<?php

namespace App\Http\Requests;

use App\Services\RecordingService;
use App\Support\Catalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi parameter pencarian/filter/urutan pada halaman daftar dan ekspor (GET). */
class RecordingFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $to = ['nullable', 'date'];

        // after_or_equal hanya relevan bila tanggal awal diisi
        if ($this->filled('from')) {
            $to[] = 'after_or_equal:from';
        }

        return [
            'q' => ['nullable', 'string', 'max:100'],
            'habitat' => ['nullable', Rule::in(array_keys(Catalog::HABITATS))],
            'call_type' => ['nullable', Rule::in(array_keys(Catalog::CALL_TYPES))],
            'from' => ['nullable', 'date'],
            'to' => $to,
            'sort' => ['nullable', Rule::in(array_keys(RecordingService::SORTS))],
            'per_page' => ['nullable', Rule::in(RecordingService::PER_PAGE)],
        ];
    }
}
