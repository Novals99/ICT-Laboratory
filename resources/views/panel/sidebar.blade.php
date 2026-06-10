<aside class="sidebar">

    {{-- logo ict --}}
    <div class="sidebar-brand">
        <div class="sidebar-logo">
            <img src="{{ asset('images/logo-ict.png') }}" alt="Logo" width="40" height="40"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
            {{-- fallback SVG klo logo gada --}}
            <div style="display:none; width:40px; height:40px; background:#111B4C; border-radius:50%; align-items:center; justify-content:center;">
                <svg viewBox="0 0 36 36" width="28" height="28">
                    <rect x="4" y="5" width="12" height="10" rx="2" fill="#fff" opacity="0.9"/>
                    <rect x="20" y="5" width="12" height="10" rx="2" fill="#98083D" opacity="0.95"/>
                    <rect x="4" y="19" width="12" height="10" rx="2" fill="#98083D" opacity="0.95"/>
                    <rect x="20" y="19" width="12" height="10" rx="2" fill="#fff" opacity="0.9"/>
                </svg>
            </div>
        </div>
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">Inventory</span>
            <span class="sidebar-brand-sub">Laboratorium ICT</span>
        </div>
    </div>

    {{-- nav --}}
    <nav class="sidebar-nav" aria-label="Main menu">

        <p class="sidebar-section-label">Main Menu</p>

        {{-- dashboard --}}
        <a href="#"
           class="sidebar-item {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard
        </a>

        {{-- user --}}
        <a href="#"
           class="sidebar-item {{ request()->routeIs('users.*') ? 'sidebar-item-active' : '' }}">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            User
        </a>

        {{-- Laboratory — sub-menu dinamis dari db --}}
        <div x-data="{ open: {{ request()->routeIs('laboratory.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-item sidebar-item-toggle
                           {{ request()->routeIs('laboratory.*') ? 'sidebar-item-active' : '' }}">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Laboratory
                <svg class="sidebar-chevron" :class="{ 'rotate-90': open }"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="sidebar-submenu">
                @forelse ($laboratories ?? [] as $lab)
                    <a href="#"
                       class="sidebar-subitem
                              {{ request()->routeIs('laboratory.show') && request()->route('laboratory') == $lab->id
                                 ? 'sidebar-subitem-active' : '' }}">
                        <span class="subitem-dot" aria-hidden="true"></span>
                        {{ $lab->lab_name }}
                    </a>
                @empty
                    <p class="sidebar-subitem-empty">Belum ada laboratorium</p>
                @endforelse
            </div>
        </div>

        {{-- lab request --}}
        {{-- <a href="{{ route('requestlab.index') }}"
           class="sidebar-item {{ request()->routeIs('requestlab.*') ? 'sidebar-item-active' : '' }}">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="12" y1="18" x2="12" y2="12"/>
                <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
            Lab Request
        </a> --}}

        {{-- inventory & stock --}}
        {{-- <a href="{{ route('asset.index') }}"
           class="sidebar-item {{ request()->routeIs('asset.*') ? 'sidebar-item-active' : '' }}">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            Inventory & Stock
        </a> --}}

        {{-- activity log --}}
        {{-- <a href="{{ route('activity-log.index') }}"
           class="sidebar-item {{ request()->routeIs('activity-log.*') ? 'sidebar-item-active' : '' }}">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Activity Log
        </a> --}}

    </nav>

    {{-- ── MOBILE: profil di paling bwh sidebar ── --}}
    <div class="sidebar-profile-mobile"
         x-data="{ open: false }"
         @click.outside="open = false">

        <div class="sidebar-profile-divider"></div>

        <button @click="open = !open" class="sidebar-profile-btn" aria-label="Profile menu">
            <div class="sidebar-profile-avatar" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
            </div>
            <div class="sidebar-profile-info">
                <span class="sidebar-profile-name">{{ auth()->user()->name ?? 'Guest' }}</span>
                <span class="sidebar-profile-email">{{ auth()->user()->email ?? '' }}</span>
            </div>
            <svg class="sidebar-profile-chevron" :class="{ 'rotate-180': open }"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>

        {{-- dropdown buka ke atas --}}
        <div x-show="open"
             x-transition:enter="dropdown-enter"
             x-transition:enter-start="dropdown-enter-start"
             x-transition:enter-end="dropdown-enter-end"
             class="sidebar-profile-dropdown"
             x-cloak>

            {{-- info user --}}
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

            {{-- Logout --}}
            <form method="POST" action="#">
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

</aside>