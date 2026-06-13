@props([
    'action' => url()->current(),
    'method' => 'GET',
    'name' => 'search',
    'value' => '',
    'placeholder' => 'Search...',
])

<form
    method="{{ $method }}"
    action="{{ $action }}"
    {{ $attributes->merge(['class' => 'module-search-form']) }}
>
    <div class="search-box module-search-box">
        <svg
            class="search-icon-left"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>

        <input
            type="text"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            class="search-input"
            autocomplete="off"
        />

        @if ($value)
            <a
                href="{{ $action }}"
                class="module-search-clear"
                aria-label="Clear search"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </a>
        @else
            <kbd class="search-kbd">/</kbd>
        @endif
    </div>
</form>