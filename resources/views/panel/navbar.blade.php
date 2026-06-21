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
        {{-- <x-button.search.global-search /> --}}

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
                <div class="dropdown-section-label">Theme</div>
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