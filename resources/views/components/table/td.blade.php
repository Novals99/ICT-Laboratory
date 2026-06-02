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

<td {{ $attributes->merge(['class' => "panel-table-td {$alignClass}"]) }}>
    {{ $slot }}
</td>

{{-- td = table data --}}