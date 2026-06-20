<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-init="if (localStorage.getItem('theme') === 'dark') {
    document.documentElement.classList.add('dark');
}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — Inventory Laboratorium ICT</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', 'Figtree', ui-sans-serif, system-ui, sans-serif;
            background: #f9fafb;
            color: #111827;
        }

        .dark body,
        body.dark {
            background: #13151f;
            color: #e2e8f0;
        }

        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
        }

        .admin-content {
            flex: 1;
            padding: 16px 12px 12px 12px;
            overflow-y: auto;
        }

        [x-cloak] {
            display: none !important;
        }

        :root {
            --bg-card: #ffffff;
            --bg-modal: #ffffff;
            --bg-table-header: #f3f4f6;
            --bg-input: #ffffff;
            --bg-notes: #f9fafb;
            --border-color: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #374151;
            --text-muted: #6b7280;
        }

        .dark {
            --bg-card: #1e2130;
            --bg-modal: #1e2130;
            --bg-table-header: #2a2d3e;
            --bg-input: #2a2d3e;
            --bg-notes: #252840;
            --border-color: #2d3148;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
        }

        @media (max-width: 768px) {
            .admin-content {
                padding: 16px 12px;
            }
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            .admin-content {
                padding: 20px 20px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="admin-shell">

        {{-- sidebar --}}
        <div class="sidebar-wrapper">
            @include('panel.sidebar')
        </div>

        {{-- main --}}
        <div class="admin-main">

            {{-- navbar --}}
            @include('panel.navbar', [
                'title' => trim($__env->yieldContent('title', 'Dashboard')),
            ])

            {{-- content area --}}
            <main class="admin-content" id="main-content">
                @yield('content')
                    <x-feedback.toast />
                    <x-feedback.loading />
                    <x-feedback.confirm />
            </main>

        </div>
    </div>

    @stack('scripts')
</body>

</html>
