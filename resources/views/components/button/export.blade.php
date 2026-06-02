@props([
    'href' => null,
    'type' => 'button',
])

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge([
            'class' => 'panel-btn-secondary'
        ]) }}
    >
        <span>{{ $slot->isEmpty() ? 'Export' : $slot }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'panel-btn-secondary'
        ]) }}
    >
        <span>{{ $slot->isEmpty() ? 'Export' : $slot }}</span>
    </button>
@endif