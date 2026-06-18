{{-- resources/views/return-requests/show.blade.php --}}
{{-- Detail Return Request + Form Approval untuk SPV --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('return-requests.index') }}"
                   class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 font-mono">{{ $returnRequest->request_code }}</h2>
                    <p class="text-sm text-gray-500">{{ $returnRequest->laboratory->lab_name }}</p>
                </div>
            </div>
            @php [$label, $color] = $returnRequest->getStatusBadge(); @endphp
            <span class="inline-flex px-3 py-1.5 text-sm font-semibold rounded-full {{ $color }}">
                {{ $label }}
            </span>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">

        {{-- ── Flash Messages ────────────────────────────────────────────── --}}
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

        {{-- ── Jika ditolak ──────────────────────────────────────────────── --}}
        @if($returnRequest->isRejected())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-red-700 font-semibold text-sm mb-1">Request Ditolak</p>
            <p class="text-red-600 text-sm">{{ $returnRequest->rejection_reason }}</p>
            <p class="text-red-400 text-xs mt-2">
                Ditolak oleh {{ $returnRequest->approvedBy?->name }} •
                {{ $returnRequest->approved_at?->format('d M Y H:i') }}
            </p>
        </div>
        @endif

        {{-- ── Jika selesai ──────────────────────────────────────────────── --}}
        @if($returnRequest->isCompleted())
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-700 font-semibold text-sm mb-1">Retur Selesai</p>
            <p class="text-green-600 text-sm">
                Barang telah diproses dan stok telah diperbarui di gudang maupun lab.
            </p>
            <p class="text-green-400 text-xs mt-2">
                Disetujui oleh {{ $returnRequest->approvedBy?->name }} •
                {{ $returnRequest->approved_at?->format('d M Y H:i') }}
            </p>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Kolom Kiri: Info ──────────────────────────────────────── --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide mb-3">
                        Informasi Request
                    </h3>
                    <dl class="space-y-2.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Laboratorium</dt>
                            <dd class="font-medium text-gray-800">{{ $returnRequest->laboratory->lab_name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Diajukan oleh</dt>
                            <dd class="text-gray-700">{{ $returnRequest->requestedBy->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Tanggal Ajuan</dt>
                            <dd class="text-gray-700">{{ $returnRequest->created_at->format('d M Y H:i') }}</dd>
                        </div>
                        @if($returnRequest->approved_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Diproses oleh</dt>
                            <dd class="text-gray-700">{{ $returnRequest->approvedBy?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Tanggal Proses</dt>
                            <dd class="text-gray-700">{{ $returnRequest->approved_at->format('d M Y H:i') }}</dd>
                        </div>
                        @endif
                        @if($returnRequest->notes)
                        <div class="pt-2 border-t border-gray-100">
                            <dt class="text-gray-500 mb-1">Catatan</dt>
                            <dd class="text-gray-700">{{ $returnRequest->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- ── Kolom Kanan: Items + Approval ─────────────────────────── --}}
            <div class="lg:col-span-2">

                {{-- Banner approval --}}
                @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800 font-semibold text-sm mb-1">
                        Menunggu Persetujuan Anda
                    </p>
                    <p class="text-yellow-700 text-xs">
                        Anda bisa ubah jumlah yang disetujui per item. Isi 0 untuk menolak item tertentu
                        tanpa menolak seluruh request.
                    </p>
                </div>

                <form action="{{ route('return-requests.approve', $returnRequest) }}" method="POST" id="approveForm">
                    @csrf
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">
                            Daftar Barang
                        </h3>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                            {{ $returnRequest->items->count() }} item
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Barang</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Qty Diajukan</th>
                                    @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Qty Disetujui</th>
                                    @elseif($returnRequest->approved_at)
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Qty Disetujui</th>
                                    @endif
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Kondisi</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Alasan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($returnRequest->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800 text-sm">{{ $item->asset->asset_name }}</div>
                                        <div class="text-xs text-gray-400">{{ $item->asset->asset_category }}</div>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <span class="text-xs bg-gray-100 text-gray-600 font-medium px-2 py-0.5 rounded-full">
                                            {{ $item->quantity_requested }}
                                        </span>
                                    </td>

                                    {{-- Input qty_approved untuk SPV --}}
                                    @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
                                    <td class="px-4 py-3 text-center">
                                        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                        <input type="number"
                                               name="items[{{ $loop->index }}][quantity_approved]"
                                               class="w-20 text-sm text-center border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 mx-auto"
                                               value="{{ $item->quantity_requested }}"
                                               min="0" max="{{ $item->quantity_requested }}" required>
                                    </td>
                                    @elseif($returnRequest->approved_at)
                                    <td class="px-4 py-3 text-center">
                                        @if($item->quantity_approved !== null)
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $item->quantity_approved > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $item->quantity_approved }}
                                        </span>
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    @endif

                                    {{-- Kondisi --}}
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $item->getConditionColor() }}">
                                            {{ $item->getConditionLabel() }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item->reason ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer Approval --}}
                    @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
                    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-xs text-gray-400">Isi 0 untuk menolak item tertentu.</p>
                        <div class="flex gap-2">
                            <button type="button"
                                    onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                                    class="text-sm border border-red-200 text-red-600 hover:bg-red-50 font-medium px-4 py-2 rounded-lg transition">
                                Tolak
                            </button>
                            <button type="submit"
                                    class="text-sm bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-2 rounded-lg transition">
                                Setujui Request
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
                </form>
                @endif

            </div>
        </div>
    </div>

    {{-- ── Modal Tolak (vanilla JS toggle, tanpa Alpine agar simpel) ────────── --}}
    @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">
            <form action="{{ route('return-requests.reject', $returnRequest) }}" method="POST">
                @csrf
                <h3 class="text-lg font-semibold text-red-600 mb-2">Tolak Return Request</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Request <strong>{{ $returnRequest->request_code }}</strong> akan ditolak.
                    Tidak ada perubahan stok yang terjadi.
                </p>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea name="rejection_reason" rows="3" required minlength="10"
                          class="w-full text-sm border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                          placeholder="Tuliskan alasan (min. 10 karakter)..."></textarea>

                <div class="flex justify-end gap-2 mt-5">
                    <button type="button"
                            onclick="document.getElementById('rejectModal').classList.add('hidden')"
                            class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="text-sm bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-app-layout>
