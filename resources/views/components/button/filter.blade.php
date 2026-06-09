{{-- @props([
    'type' => 'button',
])

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'panel-btn-secondary'
    ]) }}
>
    <svg
        class="panel-btn-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
    </svg>

    <span>{{ $slot->isEmpty() ? 'Filter' : $slot }}</span>
</button> --}}


@props([
    'type' => 'button',
    'action' => request()->url(),
])

<div style="position: relative;" x-data="{ open: false }" @click.outside="open = false">
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => 'panel-btn-secondary']) }}
        @click="open = !open"
    >
        <svg
            class="panel-btn-icon"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
        </svg>
        <span>Filter</span>
    </button>

    <div x-show="open" x-transition class="filter-popup">
        <form method="GET" action="{{ $action }}">

            {{-- carry over params selain filter & search (biar pagination dll ga ilang) --}}
            @foreach(request()->except(['role', 'status', 'search', 'page']) as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach

            {{-- search tetap dibawa --}}
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            {{ $slot }}

            <div class="filter-actions">
                <button type="submit" class="filter-btn-apply">Apply</button>
                <a href="{{ $action }}{{ request('search') ? '?search='.request('search') : '' }}" class="filter-btn-reset">Reset</a>
            </div>

        </form>
    </div>
</div>