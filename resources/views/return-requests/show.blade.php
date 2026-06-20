@extends('panel.content')

@section('title', $returnRequest->request_code . ' — Detail Return Request')

@section('content')
<div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-2xl p-6 shadow-sm">
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif
    @if($returnRequest->isRejected())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
            <p class="text-red-700 font-semibold text-sm mb-1">Request Ditolak</p>
            <p class="text-red-600 text-sm">{{ $returnRequest->rejection_reason }}</p>
            <p class="text-red-400 text-xs mt-2">
                Ditolak oleh {{ $returnRequest->approvedBy?->name ?? '-' }} •
                {{ $returnRequest->approved_at?->format('d M Y H:i') }}
            </p>
        </div>
    @endif
    @if($returnRequest->isCompleted())
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4">
            <p class="text-green-700 font-semibold text-sm mb-1">Retur Selesai</p>
            <p class="text-green-600 text-sm">
                Barang telah diproses dan stok telah diperbarui di gudang maupun lab.
            </p>
            <p class="text-green-400 text-xs mt-2">
                Disetujui oleh {{ $returnRequest->approvedBy?->name ?? '-' }} •
                {{ $returnRequest->approved_at?->format('d M Y H:i') }}
            </p>
        </div>
    @endif
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-xl p-5">
                <h3 class="font-semibold text-sm uppercase tracking-wide mb-3" style="color:var(--text-secondary);">
                    Informasi Request
                </h3>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <dt style="color:var(--text-muted);">Laboratorium</dt>
                        <dd class="font-medium" style="color:var(--text-primary);">{{ $returnRequest->laboratory->lab_name ?? '-' }}</dd>
                    </div>
                    @if($returnRequest->pc_id)
                        <div class="flex justify-between">
                            <dt style="color:var(--text-muted);">Retur PC</dt>
                            <dd class="font-medium" style="color:var(--text-primary);">
                                {{ $returnRequest->pc ? 'PC ' . $returnRequest->pc->type_pc : 'PC (dihapus)' }}
                            </dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt style="color:var(--text-muted);">Diajukan oleh</dt>
                        <dd style="color:var(--text-secondary);">{{ $returnRequest->requestedBy?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt style="color:var(--text-muted);">Tanggal Ajuan</dt>
                        <dd style="color:var(--text-secondary);">{{ $returnRequest->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    @if($returnRequest->approved_at)
                        <div class="flex justify-between">
                            <dt style="color:var(--text-muted);">Diproses oleh</dt>
                            <dd style="color:var(--text-secondary);">{{ $returnRequest->approvedBy?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt style="color:var(--text-muted);">Tanggal Proses</dt>
                            <dd style="color:var(--text-secondary);">{{ $returnRequest->approved_at->format('d M Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($returnRequest->notes)
                        <div class="pt-2 border-t" style="border-color:var(--border-color);">
                            <dt style="color:var(--text-muted);" class="mb-1">Catatan</dt>
                            <dd style="color:var(--text-secondary);">{{ $returnRequest->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
        <div class="lg:col-span-2">
            @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
                <div class="mb-4 rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                    <p class="text-yellow-800 font-semibold text-sm mb-1">
                        Menunggu Persetujuan Anda
                    </p>
                    <p class="text-yellow-700 text-xs">
                        Anda bisa ubah jumlah yang disetujui per item. Isi 0 untuk menolak item tertentu tanpa menolak seluruh request.
                    </p>
                </div>
                <form action="{{ route('return-requests.approve', $returnRequest) }}" method="POST" id="approveForm">
                    @csrf
            @endif
            <div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--border-color);">
                    <h3 class="font-semibold text-sm uppercase tracking-wide" style="color:var(--text-secondary);">
                        Daftar Barang
                    </h3>
                    <span class="text-xs px-2 py-0.5 rounded-full" style="background:var(--bg-table-header); color:var(--text-secondary);">
                        {{ $returnRequest->items->count() }} item
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead style="background:var(--bg-table-header);">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium" style="color:var(--text-secondary);">Barang</th>
                                <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Qty Diajukan</th>
                                @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
                                    <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Qty Disetujui</th>
                                @elseif($returnRequest->approved_at)
                                    <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Qty Disetujui</th>
                                @endif
                                <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Kondisi</th>
                                <th class="px-4 py-3 text-left font-medium" style="color:var(--text-secondary);">Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($returnRequest->items as $item)
                                <tr style="border-bottom:1px solid var(--border-color);">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-sm" style="color:var(--text-primary);">{{ $item->asset->asset_name }}</div>
                                        <div class="text-xs" style="color:var(--text-muted);">{{ $item->asset->asset_category }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:var(--bg-table-header); color:var(--text-secondary);">
                                            {{ $item->quantity_requested }}
                                        </span>
                                    </td>
                                    @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
                                        <td class="px-4 py-3 text-center">
                                            <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                            <input type="number"
                                                   name="items[{{ $loop->index }}][quantity_approved]"
                                                   style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                                   class="w-20 text-sm text-center rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400 mx-auto"
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
                                                <span style="color:var(--text-muted);">-</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $condColor = match($item->condition) {
                                                'good' => 'bg-green-100 text-green-700',
                                                'damaged' => 'bg-yellow-100 text-yellow-700',
                                                'lost' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $condColor }}">
                                            {{ ucfirst($item->condition) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm" style="color:var(--text-muted);">{{ $item->reason ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
                    <div class="px-5 py-4 border-t flex items-center justify-between" style="border-color:var(--border-color);">
                        <p class="text-xs" style="color:var(--text-muted);">Isi 0 untuk menolak item tertentu.</p>
                        <div class="flex gap-3">
                            <button type="button"
                                    onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                                    class="text-sm border px-4 py-2 rounded-lg transition hover:opacity-80"
                                    style="border-color:var(--border-color); color:#dc2626;">
                                Tolak
                            </button>
                            <button type="submit"
                                    class="text-sm text-white font-medium px-5 py-2 rounded-lg transition hover:opacity-80"
                                    style="background:#10b981;">
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
@if(Auth::user()->role === 'spv inventory' && $returnRequest->isPending())
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div style="background:var(--bg-modal); border:1px solid var(--border-color);" class="rounded-xl shadow-lg max-w-md w-full p-6">
            <form action="{{ route('return-requests.reject', $returnRequest) }}" method="POST">
                @csrf
                <h3 class="text-lg font-semibold mb-2" style="color:#dc2626;">Tolak Return Request</h3>
                <p class="text-sm mb-4" style="color:var(--text-muted);">
                    Request <strong>{{ $returnRequest->request_code }}</strong> akan ditolak.
                    Tidak ada perubahan stok yang terjadi.
                </p>
                <label class="block text-sm font-medium mb-1" style="color:var(--text-secondary);">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea name="rejection_reason" rows="3" required minlength="10"
                          style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                          class="w-full text-sm rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400"
                          placeholder="Tuliskan alasan (min. 10 karakter)..."></textarea>
                <div class="flex justify-end gap-3 mt-5">
                    <button type="button"
                            onclick="document.getElementById('rejectModal').classList.add('hidden')"
                            class="text-sm px-4 py-2 rounded-lg border transition hover:opacity-80"
                            style="border-color:var(--border-color); color:var(--text-secondary);">
                        Batal
                    </button>
                    <button type="submit"
                            class="text-sm text-white font-medium px-4 py-2 rounded-lg transition hover:opacity-80"
                            style="background:#dc2626;">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
