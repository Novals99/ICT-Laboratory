@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'default',
    'title' => null,
])

@php
    $variantClass = match ($variant) {
        'view' => 'panel-action-view',
        'edit' => 'panel-action-edit',
        'restore' => 'panel-action-restore',
        'delete' => 'panel-action-delete',
        default => 'panel-action-default',
    };
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        title="{{ $title }}"
        {{ $attributes->merge(['class' => "panel-table-action {$variantClass}"]) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        title="{{ $title }}"
        {{ $attributes->merge(['class' => "panel-table-action {$variantClass}"]) }}
    >
        {{ $slot }}
    </button>
@endif