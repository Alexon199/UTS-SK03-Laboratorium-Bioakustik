{{-- Komponen Blade: kartu dengan judul, subjudul, dan slot aksi opsional. --}}
@props(['title', 'subtitle' => null, 'span' => 'col-6', 'd' => 1])

<section {{ $attributes->merge(['class' => "card {$span} rise", 'style' => "--d: {$d}"]) }}>
    <div class="card-head">
        <div>
            <h2>{{ $title }}</h2>
            @if ($subtitle)<p>{{ $subtitle }}</p>@endif
        </div>
        @isset($actions){{ $actions }}@endisset
    </div>
    {{ $slot }}
</section>
