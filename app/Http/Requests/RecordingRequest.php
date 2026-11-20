<?php

namespace App\Http\Requests;

use App\Support\Catalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Satu Form Request dipakai untuk store DAN update.
 * Bedanya hanya pada aturan unique kode: saat update, rekaman itu sendiri diabaikan.
 */
class RecordingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // aplikasi ini tidak memakai login; tambahkan policy bila nanti diperlukan
    }

    /** Rapikan input sebelum divalidasi: kode selalu huruf besar tanpa spasi pinggir. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }
    }

    public function rules(): array
    {
        return [
            // --- Rekaman ---
            'code' => [
                'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('recordings', 'code')->ignore($this->route('recording')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'habitat' => ['required', Rule::in(array_keys(Catalog::HABITATS))],
            'recorded_at' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],

            // --- Titik perekaman (one-to-one, wajib ada) ---
            'point' => ['required', 'array'],
            'point.location_name' => ['required', 'string', 'max:150'],
            'point.latitude' => ['required', 'numeric', 'between:-90,90'],
            'point.longitude' => ['required', 'numeric', 'between:-180,180'],
            'point.altitude' => ['nullable', 'integer', 'between:-500,9000'],
            'point.weather' => ['nullable', 'string', 'max:50'],

            // --- Anotasi (one-to-many, boleh kosong) ---
            'annotations' => ['nullable', 'array', 'max:100'],
            'annotations.*.id' => ['nullable', 'integer'],
            'annotations.*.species_name' => ['required', 'string', 'max:150'],
            'annotations.*.call_type' => ['required', Rule::in(array_keys(Catalog::CALL_TYPES))],
            'annotations.*.start_time' => ['required', 'numeric', 'min:0', 'max:99999'],
            'annotations.*.end_time' => ['required', 'numeric', 'gt:annotations.*.start_time', 'max:99999'],
            'annotations.*.low_freq_hz' => ['nullable', 'integer', 'min:0', 'max:200000'],
            'annotations.*.high_freq_hz' => ['nullable', 'integer', 'min:0', 'max:200000'],
            'annotations.*.confidence' => ['nullable', 'integer', 'between:0,100'],
            'annotations.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'annotations.*.end_time.gt' => 'Waktu selesai harus lebih besar dari waktu mulai.',
        ];
    }

    /**
     * Validasi lintas-field yang sulit dinyatakan dengan satu aturan:
     * batas frekuensi harus diisi berpasangan dan batas atas > batas bawah.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $annotations = $this->input('annotations');

                if (! is_array($annotations)) {
                    return;
                }

                foreach ($annotations as $i => $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $low = $row['low_freq_hz'] ?? null;
                    $high = $row['high_freq_hz'] ?? null;

                    if (filled($low) && blank($high)) {
                        $validator->errors()->add("annotations.$i.high_freq_hz", 'Isi juga batas atas frekuensi, atau kosongkan keduanya.');
                    } elseif (blank($low) && filled($high)) {
                        $validator->errors()->add("annotations.$i.low_freq_hz", 'Isi juga batas bawah frekuensi, atau kosongkan keduanya.');
                    } elseif (is_numeric($low) && is_numeric($high) && (float) $high <= (float) $low) {
                        $validator->errors()->add("annotations.$i.high_freq_hz", 'Batas atas frekuensi harus lebih besar dari batas bawah.');
                    }
                }
            },
        ];
    }
}
