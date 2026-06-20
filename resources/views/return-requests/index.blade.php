{{-- resources/views/return-requests/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Retur ke Gudang</h2>
                <p class="text-sm text-gray-500 mt-0.5">Permintaan pengembalian barang dari lab ke gudang utama</p>
            </div>
            {{-- Hanya staff (non-SPV) yang mengajukan retur --}}
            @unless(Auth::user()->role === 'spv inventory')
            <a href="{{ route('return-requests.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Return Request
            </a>
            @endunless
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">

        {{-- ── Flash Message ─────────────────────────────────────────────── --}}
        @if(session('success'))
        <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4a1 1 0 102 0V9a1 1 0 10-2 0zm1-4a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
        @endif

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

                @if(Auth::user()->role === 'spv inventory')
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Laboratorium</label>
                    <select name="lab_id" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1.5 pl-2 pr-8">
                        <option value="">Semua Lab</option>
                        @foreach($labs as $lab)
                        <option value="{{ $lab->id }}" @selected(request('lab_id') == $lab->id)>
                            {{ $lab->lab_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <button type="submit"
                        class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-1.5 rounded-lg transition">
                    Filter
                </button>

                @if(request()->hasAny(['status', 'lab_id']))
                <a href="{{ route('return-requests.index') }}"
                   class="text-sm text-gray-400 hover:text-gray-600 py-1.5">
                    Reset
                </a>
                @endif
            </form>
        </div>

        {{-- ── Tabel ──────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lab</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Diajukan oleh</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($returnRequests as $req)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm font-semibold text-blue-600">
                                {{ $req->request_code }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $req->laboratory?->lab_name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $req->requestedBy?->name ?? '-' }}
                        </td>
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
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $req->created_at->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('return-requests.show', $req) }}"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Detail →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">
                            Belum ada return request
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($returnRequests->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $returnRequests->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
