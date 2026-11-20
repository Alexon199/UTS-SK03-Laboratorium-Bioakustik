{{--
    Satu baris anotasi di form.
    $i = indeks baris (angka, atau placeholder __INDEX__ untuk cetakan JS)
    $row = nilai awal (array), $callTypes = daftar jenis suara
--}}
@php
    $err = fn (string $field) => $errors->first("annotations.{$i}.{$field}");
@endphp

<div class="ann-row" data-row>
    <div class="ann-row-head">
        <strong>Anotasi <span class="row-number">#</span></strong>
        <button type="button" class="btn btn-danger btn-sm" data-remove-row><svg class="ico"><use href="#i-trash"/></svg>Hapus baris</button>
    </div>

    {{-- id lama dibawa supaya baris yang sama di-update, bukan dibuat ulang --}}
    <input type="hidden" name="annotations[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">

    <div class="grid grid-3">
        <div class="field span-2">
            <label for="a{{ $i }}_species">Nama spesies <span class="req">*</span></label>
            <input type="text" id="a{{ $i }}_species" name="annotations[{{ $i }}][species_name]" maxlength="150" list="species-list"
                   value="{{ $row['species_name'] ?? '' }}" placeholder="Owa Jawa (Hylobates moloch)"
                   class="{{ $err('species_name') ? 'is-invalid' : '' }}">
            @if ($msg = $err('species_name')) <p class="error">{{ $msg }}</p> @endif
        </div>
        <div class="field">
            <label for="a{{ $i }}_type">Jenis suara <span class="req">*</span></label>
            <select id="a{{ $i }}_type" name="annotations[{{ $i }}][call_type]" class="{{ $err('call_type') ? 'is-invalid' : '' }}">
                <option value="">— Pilih —</option>
                @foreach ($callTypes as $key => $label)
                    <option value="{{ $key }}" @selected(($row['call_type'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @if ($msg = $err('call_type')) <p class="error">{{ $msg }}</p> @endif
        </div>
    </div>

    <div class="grid grid-5">
        <div class="field">
            <label for="a{{ $i }}_start">Mulai (detik) <span class="req">*</span></label>
            <input type="number" step="0.01" min="0" id="a{{ $i }}_start" name="annotations[{{ $i }}][start_time]"
                   value="{{ $row['start_time'] ?? '' }}" class="{{ $err('start_time') ? 'is-invalid' : '' }}">
            @if ($msg = $err('start_time')) <p class="error">{{ $msg }}</p> @endif
        </div>
        <div class="field">
            <label for="a{{ $i }}_end">Selesai (detik) <span class="req">*</span></label>
            <input type="number" step="0.01" min="0" id="a{{ $i }}_end" name="annotations[{{ $i }}][end_time]"
                   value="{{ $row['end_time'] ?? '' }}" class="{{ $err('end_time') ? 'is-invalid' : '' }}">
            @if ($msg = $err('end_time')) <p class="error">{{ $msg }}</p> @endif
        </div>
        <div class="field">
            <label for="a{{ $i }}_low">Frek. bawah (Hz)</label>
            <input type="number" step="1" min="0" id="a{{ $i }}_low" name="annotations[{{ $i }}][low_freq_hz]"
                   value="{{ $row['low_freq_hz'] ?? '' }}" class="{{ $err('low_freq_hz') ? 'is-invalid' : '' }}">
            @if ($msg = $err('low_freq_hz')) <p class="error">{{ $msg }}</p> @endif
        </div>
        <div class="field">
            <label for="a{{ $i }}_high">Frek. atas (Hz)</label>
            <input type="number" step="1" min="0" id="a{{ $i }}_high" name="annotations[{{ $i }}][high_freq_hz]"
                   value="{{ $row['high_freq_hz'] ?? '' }}" class="{{ $err('high_freq_hz') ? 'is-invalid' : '' }}">
            @if ($msg = $err('high_freq_hz')) <p class="error">{{ $msg }}</p> @endif
        </div>
        <div class="field">
            <label for="a{{ $i }}_conf">Keyakinan (%)</label>
            <input type="number" step="1" min="0" max="100" id="a{{ $i }}_conf" name="annotations[{{ $i }}][confidence]"
                   value="{{ $row['confidence'] ?? '' }}" class="{{ $err('confidence') ? 'is-invalid' : '' }}">
            @if ($msg = $err('confidence')) <p class="error">{{ $msg }}</p> @endif
        </div>
    </div>

    <div class="field">
        <label for="a{{ $i }}_notes">Catatan</label>
        <input type="text" id="a{{ $i }}_notes" name="annotations[{{ $i }}][notes]" maxlength="1000"
               value="{{ $row['notes'] ?? '' }}" class="{{ $err('notes') ? 'is-invalid' : '' }}">
        @if ($msg = $err('notes')) <p class="error">{{ $msg }}</p> @endif
    </div>
</div>
