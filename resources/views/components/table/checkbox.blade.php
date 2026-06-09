@props([
    'name' => null,
    'value' => null,
])

<input
    type="checkbox"
    @if ($name) name="{{ $name }}" @endif
    @if (! is_null($value)) value="{{ $value }}" @endif
    {{ $attributes->merge(['class' => 'panel-table-checkbox']) }}
>