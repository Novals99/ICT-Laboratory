<!DOCTYPE html>
<html lang="id">
{{--
    LAYOUT MASTER (SEMENTARA)
    
    File ini: resources/views/layouts/app.blade.php
    
    Layout ini meniru desain sidebar di Figma:
    - Sidebar kiri navy dengan logo "Inventory Laboratorium ICT"
    - Menu: Dashboard, User, Laboratory, Lab Request, Inventory & Stock, Activity Log
    - Header kanan: nama user + icon profile
    - Content area di tengah
    
    CARA GANTI ke layout master tim nanti:
    Cukup edit file activity-log/index.blade.php:
    Dari: @extends('layouts.app')
    Ke:   @extends('layouts.layoutTim')
    
    Konsep @yield dan @section:
    - @yield('title') di layout = placeholder yang akan diisi child view
    - @section('title', 'Activity Log') di child = isi placeholder tersebut
    - @yield('content') = tempat content utama child view ditampilkan
--}}
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- CSRF token untuk keamanan form (proteksi dari serangan CSRF) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Title dinamis dari child view, dengan default "Inventory Lab" --}}
    <title>@yield('title', 'Inventory Lab') - Inventory Laboratorium ICT</title>
    
    {{-- Tailwind via CDN (sementara, untuk produksi sebaiknya install via npm) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Font dari Google Fonts: Plus Jakarta Sans (modern, professional, sesuai feel Figma) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- Lucide Icons (icon library yang clean & modern) --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background-color: #F8FAFC;
        }
        
        /* Warna utama dari Figma — navy biru tua */
        :root {
            --navy-primary: #1E2A5E;
            --navy-light: #2D3A6F;
        }
        
        /* Custom scrollbar yang lebih halus */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Active menu indicator */
        .menu-active {
            background-color: rgba(30, 42, 94, 0.08);
            color: #1E2A5E;
            font-weight: 600;
            border-left: 3px solid #1E2A5E;
        }
    </style>
    
    {{-- @stack memungkinkan child view inject CSS/JS tambahan --}}
    @stack('styles')
</head>
<body class="min-h-screen">
    
    <div class="flex min-h-screen">
        
        {{-- ========================================== --}}
        {{-- SIDEBAR KIRI --}}
        {{-- ========================================== --}}
        <aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0 hidden lg:flex flex-col">
            
            {{-- Logo & Brand --}}
            <div class="px-6 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#1E2A5E] to-[#2D3A6F] flex items-center justify-center">
                        <i data-lucide="layers" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-[#1E2A5E] leading-tight">Inventory</h1>
                        <p class="text-xs text-slate-500 leading-tight">Laboratorium ICT</p>
                    </div>
                </div>
            </div>
            
            {{-- Main Menu --}}
            <nav class="flex-1 px-3 py-4 overflow-y-auto">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider px-3 mb-3">Main Menu</p>
                
                {{-- 
                    Tiap menu item: kondisi 'active' dicek pakai request()->routeIs('nama-route.*')
                    Ini fitur Laravel: routeIs() mengembalikan true kalau route saat ini cocok dengan pattern.
                --}}
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition mb-1">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition mb-1">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>User</span>
                </a>
                
                <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition mb-1">
                    <span class="flex items-center gap-3">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                        <span>Laboratory</span>
                    </span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
                
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition mb-1">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    <span>Lab Request</span>
                </a>
                
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition mb-1">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>Inventory & Stock</span>
                </a>
                
                {{-- Activity Log: active state pakai class menu-active --}}
                <a href="{{ route('activity-log.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition mb-1 {{ request()->routeIs('activity-log.*') ? 'menu-active' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>Activity Log</span>
                </a>
            </nav>
            
            {{-- Footer Sidebar: tombol logout --}}
            <div class="px-3 py-4 border-t border-slate-100">
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-red-600 hover:bg-red-50 transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        
        {{-- ========================================== --}}
        {{-- AREA KANAN: HEADER + MAIN CONTENT --}}
        {{-- ========================================== --}}
        <div class="flex-1 flex flex-col min-w-0">
            
            {{-- Header --}}
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                <div>
                    <h1 class="text-xl font-bold text-slate-800">@yield('header', 'Admin Dashboard')</h1>
                </div>
                
                {{-- User Profile (nanti dynamic dari Auth::user()) --}}
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-slate-700">
                            {{ Auth::user()->nama ?? 'Ali rajin mengaji' }}
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-slate-600"></i>
                    </div>
                </div>
            </header>
            
            {{-- Main Content: di sini @yield('content') akan diisi child view --}}
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>
    
    {{-- Render Lucide icons setelah DOM siap --}}
    <script>
        lucide.createIcons();
    </script>
    
    @stack('scripts')
</body>
</html>
