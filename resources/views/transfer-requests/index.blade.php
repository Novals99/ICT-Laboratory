{{-- resources/views/transfer-requests/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Mutasi Antar Lab</h2>
                <p class="text-sm text-gray-500 mt-0.5">Pemindahan barang langsung antar laboratorium</p>
            </div>
            @unless(Auth::user()->role === 'spv inventory')
            <a href="{{ route('transfer-requests.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Buat Transfer Request
            </a>
            @endunless
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
        @endif

        @unless(Auth::user()->role === 'spv inventory')
        <div class="mb-4 bg-blue-50 border border-blue-100 text-blue-700 text-xs px-4 py-2.5 rounded-lg">
            Menampilkan transfer yang melibatkan lab Anda, baik sebagai pengirim maupun penerima.
        </div>
        @endunless

        {{-- ── Filter ────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select name="status" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1.5 pl-2 pr-8">
                        <option value="">Semua</option>
                        <option value="pending"   @selected(request('status') === 'pending')>Menunggu</option>
                        <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                        <option value="rejected"  @selected(request('status') === 'rejected')>Ditolak</option>
                    </select>
                </div>
                <button type="submit"
                        class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-1.5 rounded-lg transition">
                    Filter
                </button>
                @if(request()->hasAny(['status']))
                <a href="{{ route('transfer-requests.index') }}" class="text-sm text-gray-400 hover:text-gray-600 py-1.5">
                    Reset
                </a>
                @endif
            </form>
        </div>

        {{-- ── Tabel ──────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Lab Asal</th>
                        <th class="px-4 py-3"></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Lab Tujuan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Diajukan oleh</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Item</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Tanggal</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transferRequests as $req)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-sm font-semibold text-blue-600">
                            {{ $req->request_code }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs bg-gray-100 text-gray-700 border border-gray-200 px-2 py-0.5 rounded-full">
                                {{ $req->fromLab?->lab_name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-2 py-3 text-gray-300">→</td>
                        <td class="px-4 py-3">
                            <span class="text-xs bg-gray-100 text-gray-700 border border-gray-200 px-2 py-0.5 rounded-full">
                                {{ $req->toLab?->lab_name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $req->requestedBy?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs bg-gray-100 text-gray-600 font-medium px-2 py-0.5 rounded-full">
                                {{ $req->items_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php [$label, $color] = $req->getStatusBadge(); @endphp
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $color }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $req->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('transfer-requests.show', $req) }}"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Detail →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400 text-sm">
                            Belum ada transfer request
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($transferRequests->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $transferRequests->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
