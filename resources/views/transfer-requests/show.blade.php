{{-- resources/views/transfer-requests/show.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('transfer-requests.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 font-mono">{{ $transferRequest->request_code }}</h2>
                    <div class="flex items-center gap-2 mt-1 text-sm">
                        <span class="text-xs bg-gray-100 text-gray-700 border border-gray-200 px-2 py-0.5 rounded-full">
                            {{ $transferRequest->fromLab->lab_name }}
                        </span>
                        <span class="text-gray-300">→</span>
                        <span class="text-xs bg-gray-100 text-gray-700 border border-gray-200 px-2 py-0.5 rounded-full">
                            {{ $transferRequest->toLab->lab_name }}
                        </span>
                    </div>
                </div>
            </div>
            @php [$label, $color] = $transferRequest->getStatusBadge(); @endphp
            <span class="inline-flex px-3 py-1.5 text-sm font-semibold rounded-full {{ $color }}">
                {{ $label }}
            </span>
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

        @if($transferRequest->status === 'rejected')
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-red-700 font-semibold text-sm mb-1">Transfer Ditolak</p>
            <p class="text-red-600 text-sm">{{ $transferRequest->rejection_reason }}</p>
            <p class="text-red-400 text-xs mt-2">
                Ditolak oleh {{ $transferRequest->approvedBy?->name }} •
                {{ $transferRequest->approved_at?->format('d M Y H:i') }}
            </p>
        </div>
        @endif

        @if($transferRequest->isCompleted())
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-700 font-semibold text-sm mb-1">Transfer Selesai</p>
            <p class="text-green-600 text-sm">
                Barang berhasil dipindahkan dari <strong>{{ $transferRequest->fromLab->lab_name }}</strong>
                ke <strong>{{ $transferRequest->toLab->lab_name }}</strong>. Stok kedua lab sudah diperbarui.
            </p>
            <p class="text-green-400 text-xs mt-2">
                Disetujui oleh {{ $transferRequest->approvedBy?->name }} •
                {{ $transferRequest->approved_at?->format('d M Y H:i') }}
            </p>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Kolom Kiri ────────────────────────────────────────────── --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide mb-3">
                        Informasi Transfer
                    </h3>
                    <dl class="space-y-2.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Lab Asal</dt>
                            <dd class="font-medium text-gray-800">{{ $transferRequest->fromLab->lab_name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Lab Tujuan</dt>
                            <dd class="font-medium text-gray-800">{{ $transferRequest->toLab->lab_name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Diajukan oleh</dt>
                            <dd class="text-gray-700">{{ $transferRequest->requestedBy->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Tanggal Ajuan</dt>
                            <dd class="text-gray-700">{{ $transferRequest->created_at->format('d M Y H:i') }}</dd>
                        </div>
                        @if($transferRequest->approved_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Diproses oleh</dt>
                            <dd class="text-gray-700">{{ $transferRequest->approvedBy?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Tanggal Proses</dt>
                            <dd class="text-gray-700">{{ $transferRequest->approved_at->format('d M Y H:i') }}</dd>
                        </div>
                        @endif
                        @if($transferRequest->notes)
                        <div class="pt-2 border-t border-gray-100">
                            <dt class="text-gray-500 mb-1">Catatan</dt>
                            <dd class="text-gray-700">{{ $transferRequest->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- ── Kolom Kanan ───────────────────────────────────────────── --}}
            <div class="lg:col-span-2">

                @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800 font-semibold text-sm mb-1">Menunggu Persetujuan Anda</p>
                    <p class="text-yellow-700 text-xs">
                        Pastikan stok di <strong>{{ $transferRequest->fromLab->lab_name }}</strong> mencukupi.
                        Isi 0 pada qty untuk menolak item tertentu.
                    </p>
                </div>

                <form action="{{ route('transfer-requests.approve', $transferRequest) }}" method="POST" id="approveForm">
                    @csrf
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">Daftar Barang</h3>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                            {{ $transferRequest->items->count() }} item
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Barang</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Qty Diajukan</th>
                                    @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Qty Disetujui</th>
                                    @elseif($transferRequest->approved_at)
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Qty Disetujui</th>
                                    @endif
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($transferRequest->items as $item)
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

                                    @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
                                    <td class="px-4 py-3 text-center">
                                        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                        <input type="number"
                                               name="items[{{ $loop->index }}][quantity_approved]"
                                               class="w-20 text-sm text-center border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 mx-auto"
                                               value="{{ $item->quantity_requested }}"
                                               min="0" max="{{ $item->quantity_requested }}" required>
                                    </td>
                                    @elseif($transferRequest->approved_at)
                                    <td class="px-4 py-3 text-center">
                                        @if($item->quantity_approved !== null)
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $item->quantity_approved > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $item->quantity_approved }}
                                        </span>
                                        @if($item->quantity_approved < $item->quantity_requested)
                                        <span class="block text-xs text-gray-400 mt-0.5">(partial)</span>
                                        @endif
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    @endif

                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item->notes ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
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
                                Setujui Transfer
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Modal Tolak ─────────────────────────────────────────────────────── --}}
    @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">
            <form action="{{ route('transfer-requests.reject', $transferRequest) }}" method="POST">
                @csrf
                <h3 class="text-lg font-semibold text-red-600 mb-2">Tolak Transfer Request</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Transfer <strong>{{ $transferRequest->request_code }}</strong>
                    ({{ $transferRequest->fromLab->lab_name }} → {{ $transferRequest->toLab->lab_name }})
                    akan ditolak. Tidak ada perubahan stok.
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
                    <button type="submit" class="text-sm bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-app-layout>
