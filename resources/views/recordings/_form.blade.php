{{--
    Form bersama untuk create & edit.
    Variabel: $recording, $habitats, $callTypes, $speciesSuggestions, $action, $method, $submitLabel

    Anotasi dikirim sebagai array bertingkat: annotations[0][species_name], annotations[1][...], dst.
    Bila validasi gagal, old('annotations') berisi baris yang tadi dikirim sehingga isian tidak hilang.
--}}
@php
    $point = $recording->point;

    // Setelah validasi gagal pakai baris yang tadi dikirim (termasuk bila semua baris dihapus);
    // kalau tidak, pakai anotasi dari database.
    $rows = session()->hasOldInput()
        ? old('annotations', [])
        : ($recording->exists ? $recording->annotations->toArray() : []);
    $rows = is_array($rows) ? $rows : [];
    $nextIndex = count($rows) ? max(array_keys($rows)) + 1 : 0;
@endphp

<form method="POST" action="{{ $action }}" class="form" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="alert" role="alert">
            Data belum bisa disimpan. Periksa kembali isian yang ditandai merah ({{ $errors->count() }} kesalahan).
        </div>
    @endif

    <div class="form-layout">
        <div class="stack">
            {{-- ============ 1. Data rekaman ============ --}}
            <section class="card rise" style="--d: 1">
                <div class="card-head"><h2 class="card-title"><span class="step-num">1</span>Data rekaman</h2></div>
                <div class="grid grid-3">
                    <div class="field">
                        <label for="code">Kode rekaman <span class="req">*</span></label>
                        <input type="text" id="code" name="code" maxlength="30" value="{{ old('code', $recording->code) }}"
                               placeholder="HP-GP-001" class="{{ $errors->has('code') ? 'is-invalid' : '' }}">
                        @error('code') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field span-2">
                        <label for="name">Nama rekaman <span class="req">*</span></label>
                        <input type="text" id="name" name="name" maxlength="150" value="{{ old('name', $recording->name) }}"
                               placeholder="Fajar di jalur Cibodas" class="{{ $errors->has('name') ? 'is-invalid' : '' }}">
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label for="habitat">Habitat <span class="req">*</span></label>
                        <select id="habitat" name="habitat" class="{{ $errors->has('habitat') ? 'is-invalid' : '' }}">
                            <option value="">— Pilih habitat —</option>
                            @foreach ($habitats as $key => $label)
                                <option value="{{ $key }}" @selected(old('habitat', $recording->habitat) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('habitat') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label for="recorded_at">Tanggal rekam</label>
                        <input type="date" id="recorded_at" name="recorded_at"
                               value="{{ old('recorded_at', $recording->recorded_at?->format('Y-m-d')) }}"
                               class="{{ $errors->has('recorded_at') ? 'is-invalid' : '' }}">
                        @error('recorded_at') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field span-3">
                        <label for="notes">Catatan</label>
                        <textarea id="notes" name="notes" rows="2" maxlength="2000"
                                  class="{{ $errors->has('notes') ? 'is-invalid' : '' }}">{{ old('notes', $recording->notes) }}</textarea>
                        @error('notes') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- ============ 2. Titik perekaman (one-to-one) ============ --}}
            <section class="card rise" style="--d: 2">
                <div class="card-head">
                    <div>
                        <h2 class="card-title"><span class="step-num">2</span>Titik perekaman</h2>
                        <p>Satu rekaman hanya punya satu titik (relasi one-to-one).</p>
                    </div>
                </div>
                <div class="grid grid-4">
                    <div class="field span-2">
                        <label for="point_location_name">Nama lokasi <span class="req">*</span></label>
                        <input type="text" id="point_location_name" name="point[location_name]" maxlength="150"
                               value="{{ old('point.location_name', $point?->location_name) }}"
                               class="{{ $errors->has('point.location_name') ? 'is-invalid' : '' }}">
                        @error('point.location_name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label for="point_latitude">Lintang <span class="req">*</span></label>
                        <input type="number" step="any" id="point_latitude" name="point[latitude]" placeholder="-6.7456"
                               value="{{ old('point.latitude', $point?->latitude) }}"
                               class="{{ $errors->has('point.latitude') ? 'is-invalid' : '' }}">
                        @error('point.latitude') <p class="error">{{ $message }}</p> @else <p class="hint">-90 sampai 90</p> @enderror
                    </div>
                    <div class="field">
                        <label for="point_longitude">Bujur <span class="req">*</span></label>
                        <input type="number" step="any" id="point_longitude" name="point[longitude]" placeholder="107.0054"
                               value="{{ old('point.longitude', $point?->longitude) }}"
                               class="{{ $errors->has('point.longitude') ? 'is-invalid' : '' }}">
                        @error('point.longitude') <p class="error">{{ $message }}</p> @else <p class="hint">-180 sampai 180</p> @enderror
                    </div>
                    <div class="field">
                        <label for="point_altitude">Ketinggian (mdpl)</label>
                        <input type="number" step="1" id="point_altitude" name="point[altitude]"
                               value="{{ old('point.altitude', $point?->altitude) }}"
                               class="{{ $errors->has('point.altitude') ? 'is-invalid' : '' }}">
                        @error('point.altitude') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label for="point_weather">Cuaca</label>
                        <input type="text" id="point_weather" name="point[weather]" maxlength="50" placeholder="Cerah"
                               value="{{ old('point.weather', $point?->weather) }}"
                               class="{{ $errors->has('point.weather') ? 'is-invalid' : '' }}">
                        @error('point.weather') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
                @error('point') <p class="error">{{ $message }}</p> @enderror
            </section>

            {{-- ============ 3. Anotasi suara (one-to-many) ============ --}}
            <section class="card rise" style="--d: 3">
                <div class="card-head">
                    <div>
                        <h2 class="card-title"><span class="step-num">3</span>Anotasi suara <span class="count" id="annotation-count">{{ count($rows) }}</span></h2>
                        <p>Opsional. Satu rekaman boleh punya banyak anotasi (relasi one-to-many).</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="add-annotation"><svg class="ico"><use href="#i-plus"/></svg>Tambah anotasi</button>
                </div>

                @error('annotations') <p class="error">{{ $message }}</p> @enderror

                <p class="empty-inline" id="annotation-empty" @if (count($rows)) hidden @endif>Belum ada anotasi. Klik “Tambah anotasi”.</p>

                <div id="annotation-list" data-next="{{ $nextIndex }}">
                    @foreach ($rows as $i => $row)
                        @include('recordings._annotation_row', ['i' => $i, 'row' => is_array($row) ? $row : [], 'callTypes' => $callTypes])
                    @endforeach
                </div>

                {{-- Cetakan baris baru; __INDEX__ diganti angka oleh JavaScript --}}
                <template id="annotation-template">
                    @include('recordings._annotation_row', ['i' => '__INDEX__', 'row' => [], 'callTypes' => $callTypes])
                </template>

                <datalist id="species-list">
                    @foreach ($speciesSuggestions as $suggestion)
                        <option value="{{ $suggestion }}"></option>
                    @endforeach
                </datalist>
            </section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><svg class="ico"><use href="#i-check"/></svg>{{ $submitLabel }}</button>
                <a href="{{ $recording->exists ? route('recordings.show', $recording) : route('recordings.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </div>

        <aside class="tips">
            <div class="card rise" style="--d: 2">
                <h3>Tips pengisian</h3>
                <ol>
                    <li>Kode rekaman harus unik. Huruf kecil otomatis menjadi huruf besar.</li>
                    <li>Titik perekaman wajib diisi: nama lokasi, lintang, dan bujur.</li>
                    <li>Waktu anotasi dalam detik dari awal rekaman. Waktu selesai harus lebih besar dari waktu mulai.</li>
                    <li>Batas frekuensi diisi berpasangan (bawah dan atas), atau dikosongkan keduanya.</li>
                    <li>Nama spesies menyarankan nama yang pernah dipakai, supaya penulisan seragam.</li>
                </ol>
            </div>
        </aside>
    </div>
</form>

@push('scripts')
    <script>
        (function () {
            var list = document.getElementById('annotation-list');
            var template = document.getElementById('annotation-template');
            var emptyNote = document.getElementById('annotation-empty');
            var counter = document.getElementById('annotation-count');
            var addButton = document.getElementById('add-annotation');
            var nextIndex = parseInt(list.dataset.next, 10) || 0;

            function refresh() {
                var rows = list.querySelectorAll('[data-row]');
                Array.prototype.forEach.call(rows, function (row, position) {
                    row.querySelector('.row-number').textContent = position + 1;
                });
                counter.textContent = rows.length;
                emptyNote.hidden = rows.length > 0;
            }

            addButton.addEventListener('click', function () {
                list.insertAdjacentHTML('beforeend', template.innerHTML.split('__INDEX__').join(nextIndex++));
                refresh();
                var firstField = list.lastElementChild.querySelector('input[type="text"]');
                if (firstField) { firstField.focus(); }
            });

            list.addEventListener('click', function (event) {
                var button = event.target.closest('[data-remove-row]');
                if (!button) { return; }
                button.closest('[data-row]').remove();
                refresh();
            });

            refresh();
        })();
    </script>
@endpush
