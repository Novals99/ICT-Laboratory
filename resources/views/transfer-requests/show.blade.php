@extends('panel.content')

@section('title', $transferRequest->request_code . ' — Detail Transfer Request')

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
    @if($transferRequest->status === 'rejected')
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
            <p class="text-red-700 font-semibold text-sm mb-1">Transfer Ditolak</p>
            <p class="text-red-600 text-sm">{{ $transferRequest->rejection_reason }}</p>
            <p class="text-red-400 text-xs mt-2">
                Ditolak oleh {{ $transferRequest->approvedBy?->name ?? '-' }} •
                {{ $transferRequest->approved_at?->format('d M Y H:i') }}
            </p>
        </div>
    @endif
    @if($transferRequest->isCompleted())
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4">
            <p class="text-green-700 font-semibold text-sm mb-1">Transfer Selesai</p>
            <p class="text-green-600 text-sm">
                Barang berhasil dipindahkan dari <strong>{{ $transferRequest->fromLab->lab_name }}</strong>
                ke <strong>{{ $transferRequest->toLab->lab_name }}</strong>. Stok kedua lab sudah diperbarui.
            </p>
            <p class="text-green-400 text-xs mt-2">
                Disetujui oleh {{ $transferRequest->approvedBy?->name ?? '-' }} •
                {{ $transferRequest->approved_at?->format('d M Y H:i') }}
            </p>
        </div>
    @endif
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-xl p-5">
                <h3 class="font-semibold text-sm uppercase tracking-wide mb-3" style="color:var(--text-secondary);">
                    Informasi Transfer
                </h3>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <dt style="color:var(--text-muted);">Lab Asal</dt>
                        <dd class="font-medium" style="color:var(--text-primary);">{{ $transferRequest->fromLab->lab_name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt style="color:var(--text-muted);">Lab Tujuan</dt>
                        <dd class="font-medium" style="color:var(--text-primary);">{{ $transferRequest->toLab->lab_name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt style="color:var(--text-muted);">Diajukan oleh</dt>
                        <dd style="color:var(--text-secondary);">{{ $transferRequest->requestedBy?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt style="color:var(--text-muted);">Tanggal Ajuan</dt>
                        <dd style="color:var(--text-secondary);">{{ $transferRequest->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    @if($transferRequest->approved_at)
                        <div class="flex justify-between">
                            <dt style="color:var(--text-muted);">Diproses oleh</dt>
                            <dd style="color:var(--text-secondary);">{{ $transferRequest->approvedBy?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt style="color:var(--text-muted);">Tanggal Proses</dt>
                            <dd style="color:var(--text-secondary);">{{ $transferRequest->approved_at->format('d M Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($transferRequest->notes)
                        <div class="pt-2 border-t" style="border-color:var(--border-color);">
                            <dt style="color:var(--text-muted);" class="mb-1">Catatan</dt>
                            <dd style="color:var(--text-secondary);">{{ $transferRequest->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
        <div class="lg:col-span-2">
            @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
                <div class="mb-4 rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                    <p class="text-yellow-800 font-semibold text-sm mb-1">Menunggu Persetujuan Anda</p>
                    <p class="text-yellow-700 text-xs">
                        Pastikan stok di <strong>{{ $transferRequest->fromLab->lab_name }}</strong> mencukupi.
                        Isi 0 pada qty untuk menolak item tertentu.
                    </p>
                </div>
                <form action="{{ route('transfer-requests.approve', $transferRequest) }}" method="POST">
                    @csrf
            @endif
            <div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--border-color);">
                    <h3 class="font-semibold text-sm uppercase tracking-wide" style="color:var(--text-secondary);">
                        Daftar Barang
                    </h3>
                    <span class="text-xs px-2 py-0.5 rounded-full" style="background:var(--bg-table-header); color:var(--text-secondary);">
                        {{ $transferRequest->items->count() }} item
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead style="background:var(--bg-table-header);">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium" style="color:var(--text-secondary);">Barang</th>
                                <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Qty Diajukan</th>
                                @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
                                    <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Qty Disetujui</th>
                                @elseif($transferRequest->approved_at)
                                    <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Qty Disetujui</th>
                                @endif
                                <th class="px-4 py-3 text-left font-medium" style="color:var(--text-secondary);">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transferRequest->items as $item)
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
                                    @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
                                        <td class="px-4 py-3 text-center">
                                            <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                            <input type="number"
                                                   name="items[{{ $loop->index }}][quantity_approved]"
                                                   style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                                   class="w-20 text-sm text-center rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400 mx-auto"
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
                                                <span style="color:var(--text-muted);">-</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 text-sm" style="color:var(--text-muted);">{{ $item->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
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
@if(Auth::user()->role === 'spv inventory' && $transferRequest->isPending())
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div style="background:var(--bg-modal); border:1px solid var(--border-color);" class="rounded-xl shadow-lg max-w-md w-full p-6">
            <form action="{{ route('transfer-requests.reject', $transferRequest) }}" method="POST">
                @csrf
                <h3 class="text-lg font-semibold mb-2" style="color:#dc2626;">Tolak Transfer Request</h3>
                <p class="text-sm mb-4" style="color:var(--text-muted);">
                    Transfer <strong>{{ $transferRequest->request_code }}</strong>
                    ({{ $transferRequest->fromLab->lab_name }} → {{ $transferRequest->toLab->lab_name }})
                    akan ditolak. Tidak ada perubahan stok.
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
