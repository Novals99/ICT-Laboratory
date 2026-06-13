@props([
    'href' => null,
    'type' => 'button',
])

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge([
            'class' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-[#111B4C] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a2a6b] active:scale-95'
        ]) }}
    >
        <svg
            class="h-5 w-5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>

        <span>{{ $slot->isEmpty() ? 'Add' : $slot }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-[#111B4C] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1a2a6b] active:scale-95'
        ]) }}
    >
        <svg
            class="h-5 w-5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>

        <span>{{ $slot->isEmpty() ? 'Add' : $slot }}</span>
    </button>
@endif