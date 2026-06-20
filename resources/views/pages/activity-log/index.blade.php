@extends('panel.content')

@section('title', auth()->user()->role === 'spv inventory' ? 'SPV Dashboard' : 'Staff Dashboard')

@section('content')
<div class="panel-page-card">

    {{-- ========================================== --}}
    {{-- CARD UTAMA: Activity Log --}}
    {{-- ========================================== --}}
    {{-- <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden"> --}}

        {{-- Header Card: Title + Search + Filter + Export --}}
        <div class="px-6 py-5 ">
           <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                {{-- Title --}}
                <div>
                    <h2 class="panel-page-title">Activity Log</h2>
                    {{-- <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Total {{ $logs->total() }} aktivitas tercatat
                    </p> --}}
                </div>

                {{--
                    Toolbar: Search + Filter + Export
                    Pakai form GET supaya filter ke-passing di URL
                --}}


                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                     {{-- search --}}
                     <x-button.search.modul-search :action="route('activity-log.index')" name="search"
                            :value="request('search')" placeholder="Search..." />

                    {{-- Filter Button (toggle filter panel) --}}
                    <x-button.filter :action="route('activity-log.index')">


                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        {{-- Role --}}
                        <div class="filter-section">
                            <div class="filter-section-title">Role</div>

                            @foreach (['Staff', 'SPV'] as $roleOption)
                                <label class="filter-checkbox-row">
                                    <input
                                        type="checkbox"
                                        name="role"
                                        value="{{ $roleOption }}"
                                        {{ request('role') == $roleOption ? 'checked' : '' }}
                                        style="accent-color: #111B4C;"
                                    >
                                    <span>{{ $roleOption }}</span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Date --}}
                        <div class="filter-section">
                            <div class="filter-section-title">Activity Date</div>

                            <div class="filter-date-group">
                                <div class="filter-date-item">
                                    <label class="filter-date-label">From date</label>
                                    <input
                                        type="date"
                                        name="start_date"
                                        value="{{ request('start_date') }}"
                                        class="filter-date-input"
                                    >
                                </div>

                                <div class="filter-date-item">
                                    <label class="filter-date-label">To date</label>
                                    <input
                                        type="date"
                                        name="end_date"
                                        value="{{ request('end_date') }}"
                                        class="filter-date-input"
                                    >
                                </div>
                            </div>
                        </div>

                    </x-button.filter>

                    {{-- Export Button --}}
                   <x-button.export
    href="{{ route('activity-log.export', request()->query()) }}">
                  Export
                    </x-button.export>
                </form>
            </div>


            <div id="filterPanel" class="{{ $startDate || $endDate || $role ? '' : 'hidden' }} mt-4 pt-4 border-t border-slate-100">
                <form method="GET" action="{{ route('activity-log.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">

                    {{-- Preserve search saat filter di-apply --}}
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div>
                        <label class="text-xs text-slate-500 font-medium mb-1 block">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E2A5E]/20 focus:border-[#1E2A5E]">
                    </div>

                    <div>
                        <label class="text-xs text-slate-500 font-medium mb-1 block">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E2A5E]/20 focus:border-[#1E2A5E]">
                    </div>

                    <div>
                        <label class="text-xs text-slate-500 font-medium mb-1 block">Role</label>
                        <select name="role"
                                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E2A5E]/20 focus:border-[#1E2A5E] bg-white">
                        <option value="Staff" {{ $role === 'Staff' ? 'selected' : '' }}>Staff</option>
                        <option value="SPV" {{ $role === 'SPV' ? 'selected' : '' }}>SPV Inventory</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="flex-1 px-4 py-2 text-sm bg-[#1E2A5E] text-white rounded-lg hover:bg-[#2D3A6F] transition font-medium">
                            Terapkan
                        </button>
                        <a href="{{ route('activity-log.index') }}"
                           class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition text-slate-600">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- TABEL LOG AKTIVITAS --}}
        {{-- ========================================== --}}
        <x-table.index>
                <thead>
                    <tr class="panel-table-row">
                        <x-table.th>No</x-table.th>
                        <x-table.th>Date & Time</x-table.th>
                        <x-table.th>User</x-table.th>
                        <x-table.th>Role</x-table.th>
                        <x-table.th>Description</x-table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">

                    @forelse ($logs as $index => $log)
                        <tr class="hover:bg-slate-50/50 transition">

                            <x-table.td>
                                {{ $logs->firstItem() + $index }}
                            </x-table.td>

                            {{-- Tanggal dengan format yang readable --}}
                            <x-table.td>
                                <div class="text-slate-700 font-medium">
                                    {{ $log->created_at->format('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $log->created_at->format('H:i') }} WIB
                                </div>
                            </x-table.td>

                            <x-table.td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-xs font-semibold text-slate-600">
                                        {{-- Initial dari nama: "Ali rajin" -> "AR" --}}
                                        {{ strtoupper(substr($log->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-slate-700 font-medium">
                                            {{ $log->user?->name ?? 'User dihapus' }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ $log->user?->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </x-table.td>

                            {{-- Badge Role dengan warna sesuai role --}}
                            <x-table.td>
                                @php
                                $roleClasses = match(strtolower($log->user?->role ?? '')) {
                                    'staff' => 'bg-emerald-100 text-emerald-700 border-emerald-300 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-900',
                                    'spv inventory' => 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-950/30 dark:text-blue-300 dark:border-blue-900',
                                    default => 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                };
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold {{ $roleClasses }}">
                                    {{ $log->user?->role ?? '-' }}
                                </span>
                            </x-table.td>

                            {{-- Keterangan --}}
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $log->activity }}
                            </td>
                        </tr>

                    @empty
                        {{-- Empty state: kalau gak ada log sama sekali --}}
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center">
                                        <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-600">Belum ada aktivitas tercatat</p>
                                        <p class="text-xs text-slate-400 mt-1">
                                            @if($search || $startDate || $endDate || $role)
                                                Tidak ada log yang cocok dengan filter yang dipilih.
                                                <a href="{{ route('activity-log.index') }}" class="text-[#1E2A5E] hover:underline">Reset filter</a>
                                            @else
                                                Log aktivitas akan muncul saat user melakukan aksi di sistem.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table.index>

        {{-- ========================================== --}}
        {{-- PAGINATION --}}
        {{-- ========================================== --}}
        @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
                    <p class="text-slate-500">
                        Menampilkan
                        <span class="font-medium text-slate-700">{{ $logs->firstItem() }}</span>
                        sampai
                        <span class="font-medium text-slate-700">{{ $logs->lastItem() }}</span>
                        dari
                        <span class="font-medium text-slate-700">{{ $logs->total() }}</span>
                        log
                    </p>

                    {{-- Custom pagination buttons --}}
                    <div class="flex items-center gap-1">
                        {{-- Previous --}}
                        @if ($logs->onFirstPage())
                            <span class="px-3 py-1.5 text-sm text-slate-300 cursor-not-allowed">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </span>
                        @else
                            <a href="{{ $logs->previousPageUrl() }}"
                               class="px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 rounded-md transition">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </a>
                        @endif

                        {{-- Page numbers --}}
                        @foreach ($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                            @if ($page == $logs->currentPage())
                                <span class="px-3 py-1.5 text-sm bg-[#1E2A5E] text-white rounded-md font-medium min-w-[36px] text-center">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 rounded-md transition min-w-[36px] text-center">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if ($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}"
                               class="px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 rounded-md transition">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        @else
                            <span class="px-3 py-1.5 text-sm text-slate-300 cursor-not-allowed">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    {{-- </div> --}}
</div>

@push('scripts')
<script>
    // Re-render icons setelah content berubah (misal setelah filter)
    lucide.createIcons();

    // Auto-submit form saat user mengetik di search (debounced)
    const searchInput = document.querySelector('input[name="search"]');
    let debounceTimer;
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            // Tunggu 500ms setelah user berhenti ngetik, baru submit
            // (supaya gak setiap huruf reload halaman)
            debounceTimer = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });
    }
</script>
@endpush
@endsection
