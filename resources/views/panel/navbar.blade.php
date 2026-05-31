{{--
    cara pakeny: @include('components.layout.navbar', ['title' => 'Admin Dashboard'])
--}}

<div class="navbar-wrapper">
<header class="navbar-bar">

    {{-- hamburger: muncul di mobile doanh --}}
    <button class="navbar-hamburger" onclick="toggleSidebar()" aria-label="Toggle menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             width="18" height="18">
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- judul halaman --}}
    <h1 class="navbar-title">{{ $title ?? 'Dashboard' }}</h1>

    {{-- kanan: search global + profil --}}
    <div class="navbar-right">

        {{-- ── MOBILE: icon search global ── --}}
        <button class="navbar-search-icon-btn"
                onclick="document.getElementById('mobileSearchOverlay').classList.add('is-open');
                         setTimeout(() => document.getElementById('mobileSearchInput').focus(), 50);"
                aria-label="Open search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 width="18" height="18">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </button>

        {{-- ── DEKSTOP: muncul senama-namanya ── --}}
        <div class="navbar-search-wrap navbar-search-desktop"
             x-data="{
                isOpen: false,
                query: '',
                results: [],
                loading: false,
                selected: -1,

                async search() {
                    if (this.query.trim().length < 2) { this.results = []; return; }
                    this.loading = true;
                    try {
                        const res = await fetch('/search?q=' + encodeURIComponent(this.query), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        this.results = await res.json();
                    } catch(e) { this.results = []; }
                    this.loading = false;
                },

                close() { this.isOpen = false; this.results = []; this.query = ''; this.selected = -1; },

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
             @click.outside="close()">

            {{-- input box --}}
            <div class="navbar-search-box" :class="{ 'search-focused': isOpen }">
                <svg class="search-icon-left" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>

                <input
                    type="text"
                    class="navbar-search-input"
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

                {{-- spinner saat loading --}}
                <svg x-show="loading" class="search-spinner" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>

                {{-- hint shortcut --}}
                <kbd class="search-kbd" x-show="!isOpen && !loading">/</kbd>
            </div>

            {{-- dropdown hasil --}}
            <div class="search-results-wrap"
                 x-show="isOpen && query.trim().length >= 2"
                 x-transition:enter="dropdown-enter"
                 x-transition:enter-start="dropdown-enter-start"
                 x-transition:enter-end="dropdown-enter-end"
                 x-cloak>

                <template x-if="results.length > 0">
                    <ul class="search-results-list" role="listbox">
                        <template x-for="(item, index) in results" :key="index">
                            <li>
                                <a :href="item.url"
                                   class="search-result-item"
                                   :class="{ 'search-result-selected': selected === index }"
                                   role="option">
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
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <p>Tidak ada hasil untuk "<span x-text="query"></span>"</p>
                    </div>
                </template>

            </div>
        </div>

        {{-- ── DESKTOP: dropdown profil (dark/light mode + logout) ── --}}
        <div class="navbar-profile navbar-profile-desktop"
             x-data="{ open: false }"
             @click.outside="open = false">

            <button @click="open = !open" class="navbar-profile-btn" aria-label="Profile menu">
                <span class="navbar-username">{{ auth()->user()->name ?? 'Guest' }}</span>
                <div class="navbar-avatar" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                </div>
                <svg class="navbar-chevron" :class="{ 'rotate-180': open }"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>

            <div x-show="open"
                 x-transition:enter="dropdown-enter"
                 x-transition:enter-start="dropdown-enter-start"
                 x-transition:enter-end="dropdown-enter-end"
                 x-transition:leave="dropdown-leave"
                 x-transition:leave-start="dropdown-enter-end"
                 x-transition:leave-end="dropdown-enter-start"
                 class="navbar-dropdown"
                 x-cloak>

                {{-- nama + email user --}}
                <div class="dropdown-header">
                    <p class="dropdown-name">{{ auth()->user()->name ?? 'Guest' }}</p>
                    <p class="dropdown-email">{{ auth()->user()->email ?? '' }}</p>
                </div>

                <div class="dropdown-divider"></div>

                {{-- btn tema --}}
                <div class="dropdown-section-label">Tema</div>
                <div class="dropdown-theme-row"
                     x-data="{ theme: localStorage.getItem('theme') || 'light' }"
                     x-init="$watch('theme', val => {
                         localStorage.setItem('theme', val);
                         document.documentElement.classList.toggle('dark', val === 'dark');
                     })">
                    <button @click="theme = 'light'"
                            :class="theme === 'light' ? 'theme-btn-active' : 'theme-btn'">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                             width="15" height="15">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        Light
                    </button>
                    <button @click="theme = 'dark'"
                            :class="theme === 'dark' ? 'theme-btn-active' : 'theme-btn'">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                             width="15" height="15">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z"/>
                        </svg>
                        Dark
                    </button>
                </div>

                <div class="dropdown-divider"></div>

                {{-- logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                             width="15" height="15">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </button>
                </form>

            </div>
        </div>

    </div>
</header>

{{-- ── MOBILE: Search Overlay ── --}}
<div id="mobileSearchOverlay" class="mobile-search-overlay"
     onclick="if(event.target===this) this.classList.remove('is-open')">
    <div class="mobile-search-inner"
         x-data="{
            query: '',
            results: [],
            loading: false,
            selected: -1,
            async search() {
                if (this.query.trim().length < 2) { this.results = []; return; }
                this.loading = true;
                try {
                    const res = await fetch('/search?q=' + encodeURIComponent(this.query), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.results = await res.json();
                } catch(e) { this.results = []; }
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
         }">

        <div class="mobile-search-header">
            <div class="navbar-search-box mobile-search-box-inner">
                <svg class="search-icon-left" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    id="mobileSearchInput"
                    type="text"
                    class="navbar-search-input"
                    placeholder="Search..."
                    x-model="query"
                    @input.debounce.300ms="search()"
                    @keydown.arrow-down.prevent="navigate(1)"
                    @keydown.arrow-up.prevent="navigate(-1)"
                    @keydown.enter.prevent="go()"
                    @keydown.escape.window="document.getElementById('mobileSearchOverlay').classList.remove('is-open'); query=''; results=[];"
                    autocomplete="off"
                />
                <svg x-show="loading" class="search-spinner" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>
            </div>
            <button class="mobile-search-close"
                    onclick="document.getElementById('mobileSearchOverlay').classList.remove('is-open')"
                    aria-label="Close search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- hasil --}}
        <template x-if="results.length > 0">
            <ul class="search-results-list mobile-search-results" role="listbox">
                <template x-for="(item, index) in results" :key="index">
                    <li>
                        <a :href="item.url"
                           class="search-result-item"
                           :class="{ 'search-result-selected': selected === index }"
                           role="option">
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
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <p>Tidak ada hasil untuk "<span x-text="query"></span>"</p>
            </div>
        </template>

    </div>
</div>

</div>

<script>
function toggleSidebar() {
    const wrapper = document.querySelector('.sidebar-wrapper');
    if (!wrapper) return;
    wrapper.classList.toggle('sidebar-open');
}

// klik diluar sidebar = ketutup
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.querySelector('.sidebar-wrapper');
    if (!wrapper) return;
    wrapper.addEventListener('click', function (e) {
        // klo klik di wrapper, bukan di .sidebar itu sendiri
        if (e.target === wrapper) {
            wrapper.classList.remove('sidebar-open');
        }
    });
});
</script>