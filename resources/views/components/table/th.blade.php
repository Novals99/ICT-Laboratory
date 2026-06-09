@props([
    'align' => 'left',
])

@php
    $alignClass = match ($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
@endphp

<th {{ $attributes->merge(['class' => "panel-table-th {$alignClass}"]) }}>
    {{ $slot }}
</th>

{{-- th = table head --}}