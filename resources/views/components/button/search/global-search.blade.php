{{-- MOBILE: search icon aja --}}
<button
    class="navbar-search-icon-btn"
    onclick="document.getElementById('mobileSearchOverlay').classList.add('is-open');
             setTimeout(() => document.getElementById('mobileSearchInput').focus(), 50);"
    aria-label="Open search"
>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         width="18" height="18">
        <circle cx="11" cy="11" r="8" />
        <line x1="21" y1="21" x2="16.65" y2="16.65" />
    </svg>
</button>

{{-- DESKTOP: full search --}}
<div
    class="global-search-wrap navbar-search-desktop"
    x-data="{
        isOpen: false,
        query: '',
        results: [],
        loading: false,
        selected: -1,

        async search() {
            if (this.query.trim().length < 2) {
                this.results = [];
                return;
            }

            this.loading = true;

            try {
                const res = await fetch('/search?q=' + encodeURIComponent(this.query), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                this.results = await res.json();
            } catch (e) {
                this.results = [];
            }

            this.loading = false;
        },

        close() {
            this.isOpen = false;
            this.results = [];
            this.query = '';
            this.selected = -1;
        },

        navigate(dir) {
            if (!this.results.length) return;
            this.selected = (this.selected + dir + this.results.length) % this.results.length;
        },

        go() {
            if (this.selected >= 0 && this.results[this.selected]) {
                window.location.href = this.results[this.selected].url;
            }
        }
    }"
    @keydown.escape.window="close()"
    @click.outside="close()"
>
    <div class="search-box global-search-box" :class="{ 'search-focused': isOpen }">
        <svg class="search-icon-left" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>

        <input
            type="text"
            class="search-input"
            placeholder="Search..."
            x-model="query"
            @focus="isOpen = true"
            @input.debounce.300ms="search()"
            @keydown.arrow-down.prevent="navigate(1)"
            @keydown.arrow-up.prevent="navigate(-1)"
            @keydown.enter.prevent="go()"
            aria-label="Global search"
            autocomplete="off"
        />

        <svg
            x-show="loading"
            class="search-spinner"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        >
            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
        </svg>

        <kbd class="search-kbd" x-show="!isOpen && !loading">/</kbd>
    </div>

    <div
        class="search-results-wrap"
        x-show="isOpen && query.trim().length >= 2"
        x-transition:enter="dropdown-enter"
        x-transition:enter-start="dropdown-enter-start"
        x-transition:enter-end="dropdown-enter-end"
        x-cloak
    >
        <template x-if="results.length > 0">
            <ul class="search-results-list" role="listbox">
                <template x-for="(item, index) in results" :key="index">
                    <li>
                        <a
                            :href="item.url"
                            class="search-result-item"
                            :class="{ 'search-result-selected': selected === index }"
                            role="option"
                        >
                            <span class="result-category-badge" x-text="item.category"></span>
                            <span class="result-label" x-text="item.label"></span>
                            <span class="result-meta" x-text="item.meta ?? ''"></span>
                        </a>
                    </li>
                </template>
            </ul>
        </template>

        <template x-if="results.length === 0 && !loading">
            <div class="search-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.5" width="28" height="28">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>

                <p>Tidak ada hasil untuk "<span x-text="query"></span>"</p>
            </div>
        </template>
    </div>
</div>

{{-- MOBILE: search overlay --}}
<div
    id="mobileSearchOverlay"
    class="mobile-search-overlay"
    onclick="if(event.target === this) this.classList.remove('is-open')"
>
    <div
        class="mobile-search-inner"
        x-data="{
            query: '',
            results: [],
            loading: false,
            selected: -1,

            async search() {
                if (this.query.trim().length < 2) {
                    this.results = [];
                    return;
                }

                this.loading = true;

                try {
                    const res = await fetch('/search?q=' + encodeURIComponent(this.query), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    this.results = await res.json();
                } catch (e) {
                    this.results = [];
                }

                this.loading = false;
            },

            navigate(dir) {
                if (!this.results.length) return;
                this.selected = (this.selected + dir + this.results.length) % this.results.length;
            },

            go() {
                if (this.selected >= 0 && this.results[this.selected]) {
                    window.location.href = this.results[this.selected].url;
                }
            }
        }"
    >
        <div class="mobile-search-header">
            <div class="search-box mobile-search-box-inner">
                <svg class="search-icon-left" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>

                <input
                    id="mobileSearchInput"
                    type="text"
                    class="search-input"
                    placeholder="Search..."
                    x-model="query"
                    @input.debounce.300ms="search()"
                    @keydown.arrow-down.prevent="navigate(1)"
                    @keydown.arrow-up.prevent="navigate(-1)"
                    @keydown.enter.prevent="go()"
                    @keydown.escape.window="document.getElementById('mobileSearchOverlay').classList.remove('is-open'); query=''; results=[];"
                    autocomplete="off"
                />

                <svg
                    x-show="loading"
                    class="search-spinner"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
            </div>

            <button
                class="mobile-search-close"
                onclick="document.getElementById('mobileSearchOverlay').classList.remove('is-open')"
                aria-label="Close search"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <template x-if="results.length > 0">
            <ul class="search-results-list mobile-search-results" role="listbox">
                <template x-for="(item, index) in results" :key="index">
                    <li>
                        <a
                            :href="item.url"
                            class="search-result-item"
                            :class="{ 'search-result-selected': selected === index }"
                            role="option"
                        >
                            <span class="result-category-badge" x-text="item.category"></span>
                            <span class="result-label" x-text="item.label"></span>
                            <span class="result-meta" x-text="item.meta ?? ''"></span>
                        </a>
                    </li>
                </template>
            </ul>
        </template>

        <template x-if="results.length === 0 && !loading && query.trim().length >= 2">
            <div class="search-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.5" width="28" height="28">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>

                <p>No results for "<span x-text="query"></span>"</p>
            </div>
        </template>
    </div>
</div>