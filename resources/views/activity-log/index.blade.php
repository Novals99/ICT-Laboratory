{{-- 
    VIEW: Activity Log Index
    File: resources/views/activity-log/index.blade.php
    
    Konsep penting:
    - @extends('layouts.app') = pakai layout master tadi
    - @section('content') = isi area content di layout
    - Variable $logs, $search, dll dikirim dari controller
    
    Style yang dipakai:
    - Card putih dengan rounded-2xl untuk container utama
    - Navy color (#1E2A5E) sebagai accent (sesuai Figma)
    - Spacing yang generous (lebih ke arah refined minimalism)
    - Hover states yang halus
--}}
@extends('layouts.app')

@section('title', 'Activity Log')
@section('header', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    
    {{-- ========================================== --}}
    {{-- CARD UTAMA: Activity Log --}}
    {{-- ========================================== --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        
        {{-- Header Card: Title + Search + Filter + Export --}}
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                
                {{-- Title --}}
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Activity Log</h2>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Total {{ $logs->total() }} aktivitas tercatat
                    </p>
                </div>
                
                {{-- 
                    Toolbar: Search + Filter + Export
                    Pakai form GET supaya filter ke-passing di URL,
                    bisa di-share & di-bookmark
                --}}
                <form method="GET" action="{{ route('activity-log.index') }}" 
                      id="filterForm"
                      class="flex flex-col sm:flex-row gap-2">
                    
                    {{-- Search Box --}}
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search }}"
                            placeholder="Search..." 
                            class="w-full sm:w-64 pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1E2A5E]/20 focus:border-[#1E2A5E] transition"
                        >
                    </div>
                    
                    {{-- Filter Button (toggle filter panel) --}}
                    <button type="button" 
                            onclick="document.getElementById('filterPanel').classList.toggle('hidden')"
                            class="flex items-center justify-center gap-2 px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition text-slate-700">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span>Filter</span>
                    </button>
                    
                    {{-- Export Button --}}
                    {{-- 
                        Trik: kita pakai JS untuk ambil semua nilai form,
                        lalu redirect ke route export dengan query string yang sama.
                        Atau cara simple: bikin link langsung dengan query saat ini.
                    --}}
                    <a href="{{ route('activity-log.export', request()->query()) }}" 
                       class="flex items-center justify-center gap-2 px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition text-slate-700">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Export</span>
                    </a>
                </form>
            </div>
            
            {{-- 
                FILTER PANEL — hidden by default, muncul kalau klik tombol Filter
                Ini bonus dari saya. Activity Diagram halaman 23 menyebut "Pilih Filter, 
                input Filter, kirim filter" — jadi kita kasih panel yang proper.
            --}}
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
                            <option value="">Semua Role</option>
                            <option value="Admin" {{ $role === 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Assistant" {{ $role === 'Assistant' ? 'selected' : '' }}>Assistant</option>
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
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-16">
                            No
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Tanggal & Waktu
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Keterangan
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    
                    {{-- 
                        @forelse: loop yang ada fallback kalau data kosong.
                        Sama seperti @foreach + cek kosong, tapi lebih ringkas.
                    --}}
                    @forelse ($logs as $index => $log)
                        <tr class="hover:bg-slate-50/50 transition">
                            
                            {{-- 
                                Nomor urut yang benar saat paginasi:
                                Halaman 2 dengan 10 item/halaman -> item pertama nomor 11
                                Rumus: (halaman_skrg - 1) * per_page + index + 1
                                Atau pakai: $logs->firstItem() + $index
                            --}}
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ $logs->firstItem() + $index }}
                            </td>
                            
                            {{-- Tanggal dengan format yang readable --}}
                            <td class="px-6 py-4 text-sm">
                                <div class="text-slate-700 font-medium">
                                    {{ $log->created_at->format('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $log->created_at->format('H:i') }} WIB
                                </div>
                            </td>
                            
                            {{-- 
                                Nama User dengan avatar
                                Pakai null-safe (?->) kalau-kalau user sudah dihapus
                            --}}
                            <td class="px-6 py-4 text-sm">
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
                            </td>
                            
                            {{-- Badge Role dengan warna sesuai role --}}
                            <td class="px-6 py-4 text-sm">
                                @php
                                    // Tentukan warna badge sesuai role
                                    $roleClasses = match($log->user?->role) {
                                        'Admin' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'Assistant' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'SPV' => 'bg-purple-50 text-purple-700 border-purple-100',
                                        default => 'bg-slate-50 text-slate-600 border-slate-100',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md border {{ $roleClasses }}">
                                    {{ $log->user?->role ?? '-' }}
                                </span>
                            </td>
                            
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
        </div>
        
        {{-- ========================================== --}}
        {{-- PAGINATION --}}
        {{-- ========================================== --}}
        @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{-- 
                    $logs->links() = render pagination default Laravel
                    Tapi defaultnya pakai Bootstrap. Kita pakai custom Tailwind
                    dengan parameter view. Atau gunakan pagination yang sudah kita custom di bawah.
                --}}
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
    </div>
</div>

{{-- 
    @push('scripts') = tambah script ke @stack('scripts') di layout.
    Berguna kalau view ini butuh JS tambahan yang gak ada di view lain.
--}}
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
