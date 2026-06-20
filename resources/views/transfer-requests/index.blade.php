{{-- resources/views/transfer-requests/index.blade.php --}}

@extends('panel.content')

@section('title', 'Mutasi Antar Lab')

@php
    $role = auth()->user()->role;
    $isSpv = $role === 'spv inventory';
    $canCreateRequest = $role !== 'spv inventory';
@endphp

@section('content')
<div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-2xl p-6 shadow-sm">

    {{-- ── Header ───────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-3xl font-semibold" style="color:var(--text-primary);">Mutasi Antar Lab</h2>
            <p class="mt-1 text-sm" style="color:var(--text-muted);">
                Pemindahan barang langsung antar laboratorium
            </p>
        </div>

        @if($canCreateRequest)
            <x-button.add type="button" onclick="openPanelModal('createTransferModal')">
                Buat Transfer Request
            </x-button.add>
        @endif
    </div>

    {{-- ── Flash Message ────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @unless($isSpv)
        <div style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-secondary);"
             class="mb-4 rounded-lg px-4 py-2.5 text-xs">
            Menampilkan transfer yang melibatkan lab Anda, baik sebagai pengirim maupun penerima.
        </div>
    @endunless

    {{-- ── Filter ───────────────────────────────────────────────────────── --}}
    <div style="background:var(--bg-input); border:1px solid var(--border-color);" class="mb-4 rounded-xl p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium" style="color:var(--text-muted);">Status</label>
                <select name="status"
                        style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                        class="rounded-lg py-1.5 pl-2 pr-8 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="">Semua</option>
                    <option value="pending"   @selected(request('status') === 'pending')>Menunggu</option>
                    <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                    <option value="rejected"  @selected(request('status') === 'rejected')>Ditolak</option>
                </select>
            </div>

            <button type="submit"
                    style="background:var(--bg-table-header); color:var(--text-secondary);"
                    class="rounded-lg px-4 py-1.5 text-sm transition hover:opacity-90">
                Filter
            </button>

            @if(request()->hasAny(['status']))
                <a href="{{ route('transfer-requests.index') }}"
                   class="py-1.5 text-sm" style="color:var(--text-muted);">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── Tabel ────────────────────────────────────────────────────────── --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:var(--bg-table-header); color:var(--text-secondary);">
                    <th class="px-4 py-3 text-left font-medium">Kode</th>
                    <th class="px-4 py-3 text-left font-medium">Lab Asal</th>
                    <th class="px-4 py-3"></th>
                    <th class="px-4 py-3 text-left font-medium">Lab Tujuan</th>
                    <th class="px-4 py-3 text-left font-medium">Diajukan oleh</th>
                    <th class="px-4 py-3 text-center font-medium">Item</th>
                    <th class="px-4 py-3 text-center font-medium">Status</th>
                    <th class="px-4 py-3 text-left font-medium">Tanggal</th>
                    <th class="px-4 py-3 text-center font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transferRequests as $req)
                    <tr style="border-bottom:1px solid var(--border-color);" class="transition-colors">
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm font-semibold text-blue-600">{{ $req->request_code }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span style="background:var(--bg-table-header); border:1px solid var(--border-color); color:var(--text-secondary);"
                                  class="rounded-full px-2 py-0.5 text-xs">
                                {{ $req->fromLab?->lab_name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-2 py-3" style="color:var(--text-muted);">→</td>
                        <td class="px-4 py-3">
                            <span style="background:var(--bg-table-header); border:1px solid var(--border-color); color:var(--text-secondary);"
                                  class="rounded-full px-2 py-0.5 text-xs">
                                {{ $req->toLab?->lab_name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3" style="color:var(--text-secondary);">{{ $req->requestedBy?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span style="background:var(--bg-table-header); color:var(--text-secondary);"
                                  class="rounded-full px-2 py-0.5 text-xs font-medium">
                                {{ $req->items_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php [$label, $color] = $req->getStatusBadge(); @endphp
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $color }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-3" style="color:var(--text-muted);">{{ $req->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" onclick="openTransferDetailModal({{ $req->id }})"
                                title="Lihat Detail"
                                style="background:none; border:none; cursor:pointer; padding:4px; color:#9ca3af;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-sm" style="color:var(--text-muted);">
                            Belum ada transfer request
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transferRequests->hasPages())
        <div class="mt-6">
            {{ $transferRequests->links() }}
        </div>
    @endif
</div>

{{-- MODAL CREATE TRANSFER REQUEST --}}
@if($canCreateRequest)
    <x-modal.index id="createTransferModal" title="Buat Transfer Request"
        :action="route('transfer-requests.store')" submitText="Ajukan Request"
        cancelText="Cancel" boxClass="transfer-create-modal" innerClass="transfer-create-inner">
        <div style="margin-bottom: 16px;">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Lab Asal <span class="text-red-500">*</span>
                    </label>
                    <select name="from_lab_id" id="tr_from_lab_id"
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                        onchange="handleTrFromLabChange()">
                        <option value="">-- Pilih Lab Asal --</option>
                        @foreach($userLabs as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->lab_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Lab Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select name="to_lab_id" id="tr_to_lab_id"
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                        <option value="">-- Pilih Lab Tujuan --</option>
                        @foreach($targetLabs as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->lab_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                Catatan <span style="color:var(--text-muted);">(opsional)</span>
            </label>
            <textarea name="notes" rows="3"
                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                placeholder="Catatan tambahan untuk SPV..."></textarea>
        </div>

        <div id="tr_modal_item_list">
            <div id="tr_no_lab" style="text-align:center; color:var(--text-muted); font-size:13px;">
                Pilih lab asal terlebih dahulu
            </div>
            <div id="tr_loading" style="display:none; text-align:center; color:var(--text-muted); font-size:13px;">
                Memuat daftar barang...
            </div>
            <div id="tr_no_assets" style="display:none; text-align:center; color:var(--text-muted); font-size:13px;">
                Tidak ada barang di lab ini
            </div>
            <div id="tr_items" style="display:none;"></div>
        </div>

        <div id="tr_add_btn" style="display:none; margin-top:12px;">
            <button type="button" onclick="addTrModalItem()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#111B4C] px-4 py-2 text-sm font-semibold text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Baris
            </button>
        </div>
    </x-modal.index>
@endif

{{-- MODAL DETAIL TRANSFER REQUEST --}}
<div id="transferDetailModal"
    style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div
        style="background:var(--bg-modal); border-radius:16px; width:100%; max-width:760px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15); border:1px solid var(--border-color);">
        <div
            style="display:flex; align-items:center; padding:24px 32px 12px 32px; gap:16px; border-bottom:1px solid var(--border-color);">
            <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); flex-shrink:0; margin:0;">
                Transfer Request Information
            </h3>
            <div style="flex:1;">
                <div style="height:6px; background:var(--border-color); border-radius:99px; overflow:hidden;">
                    <div id="transferModalProgress"
                        style="height:6px; background:#93c5fd; border-radius:99px; width:0%; transition:width 0.5s;">
                    </div>
                </div>
            </div>
            <button onclick="closeTransferDetailModal()"
                style="color:var(--text-muted); background:none; border:none; cursor:pointer; padding:4px; line-height:1;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div style="padding:16px 32px 24px 32px;">
            <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:28px;">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">Kode Request</label>
                        <input id="transfer_modal_request_code" type="text" readonly
                            style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div>
                        <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">Lab Asal</label>
                        <input id="transfer_modal_from_lab" type="text" readonly
                            style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div>
                        <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">Lab Tujuan</label>
                        <input id="transfer_modal_to_lab" type="text" readonly
                            style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:16px;">
                    <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Diajukan oleh</label>
                    <input id="transfer_modal_requested_by" type="text" readonly
                        style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">Barang yang Ditransfer</p>
                <table id="transfer_modal_items" style="width:100%; font-size:13px; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                    <thead>
                        <tr style="background:var(--bg-table-header);">
                            <th style="padding:8px 14px; text-align:left;">Asset Name</th>
                            <th style="padding:8px 14px; text-align:center;">Qty</th>
                            <th style="padding:8px 14px; text-align:center;">{{ $isSpv ? 'Qty Disetujui' : 'Status' }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        @if($isSpv)
            <div style="display:flex; justify-content:flex-end; gap:10px; padding:0 32px 24px 32px;">
                <button type="button" onclick="rejectAllTransfer()"
                    style="border:1px solid #dc2626; background:#dc2626; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Tolak Semua
                </button>
                <button type="button" onclick="approveAllTransfer()"
                    style="border:1px solid #16a34a; background:#16a34a; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Setujui Semua
                </button>
                <button type="button" onclick="saveTransferStatuses()"
                    style="border:1px solid #111B4C; background:#111B4C; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Simpan
                </button>
            </div>
        @endif
    </div>
</div>

@endsection

@push('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .transfer-create-modal {
            max-height: calc(100vh - 48px);
            min-height: auto;
        }
        .transfer-create-modal .panel-modal-form {
            min-height: 0;
        }
        .transfer-create-inner {
            max-height: calc(100vh - 210px);
            overflow-y: auto;
            padding-right: 20px;
        }
        .transfer-create-modal .panel-modal-footer {
            flex-shrink: 0;
        }
    </style>
@endpush

@push('scripts')
<script>
let trLabAssets = [];
let trItemIndex = 1;
let currentTransferRequestId = null;

window.openPanelModal = function(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

window.closePanelModal = function(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

window.closePanelModalOnBackdrop = function(event, id) {
    if (event.target.id === id) closePanelModal(id);
}

function handleTrFromLabChange() {
    const labId = document.getElementById('tr_from_lab_id').value;
    if (!labId) {
        document.getElementById('tr_no_lab').style.display = 'block';
        document.getElementById('tr_loading').style.display = 'none';
        document.getElementById('tr_no_assets').style.display = 'none';
        document.getElementById('tr_items').style.display = 'none';
        document.getElementById('tr_add_btn').style.display = 'none';
        return;
    }

    document.getElementById('tr_no_lab').style.display = 'none';
    document.getElementById('tr_loading').style.display = 'block';
    document.getElementById('tr_no_assets').style.display = 'none';
    document.getElementById('tr_items').style.display = 'none';
    document.getElementById('tr_add_btn').style.display = 'none';

    fetch(`/api/labs/${labId}/assets`)
        .then(response => response.json())
        .then(data => {
            trLabAssets = data;
            document.getElementById('tr_loading').style.display = 'none';
            if (data.length === 0) {
                document.getElementById('tr_no_assets').style.display = 'block';
            } else {
                document.getElementById('tr_items').innerHTML = '';
                trItemIndex = 1;
                addTrModalItem();
                document.getElementById('tr_items').style.display = 'block';
                document.getElementById('tr_add_btn').style.display = 'block';
            }
        })
        .catch(() => {
            document.getElementById('tr_loading').style.display = 'none';
            alert('Gagal memuat data aset.');
        });
}

function getTrModalItemRowHtml(index) {
    return `
        <div class="item-row"
            style="border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:8px; position:relative;">
            <button type="button" onclick="removeTrModalItem(this)"
                style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--text-muted);">×</button>
            <div style="margin-bottom:8px;">
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Barang</label>
                <select name="items[${index}][asset_id]" id="tr_asset_${index}"
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                    onchange="handleTrModalAssetChange(${index})" required>
                    <option value="">-- Pilih --</option>
                    ${trLabAssets.map(a => `<option value="${a.asset_id}">${a.name}</option>`).join('')}
                </select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Stok Lab Asal</label>
                    <input type="text" id="tr_stock_${index}" readonly
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-muted); background:var(--bg-input);">
                </div>
                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Qty</label>
                    <input type="number" name="items[${index}][quantity]" id="tr_qty_${index}"
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                        min="1" value="1" onchange="handleTrModalQtyChange(${index})" oninput="handleTrModalQtyChange(${index})" required>
                </div>
            </div>
            <div>
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Catatan (opsional)</label>
                <input type="text" name="items[${index}][notes]"
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                    placeholder="Catatan...">
            </div>
        </div>
    `;
}

function addTrModalItem() {
    const container = document.getElementById('tr_items');
    container.innerHTML += getTrModalItemRowHtml(trItemIndex++);
}

function removeTrModalItem(btn) {
    const list = document.getElementById('tr_items');
    if (list.querySelectorAll('.item-row').length === 1) return;
    btn.closest('.item-row').remove();
}

function getTrStock(assetId) {
    const asset = trLabAssets.find(a => a.asset_id == assetId);
    return asset ? asset.stock : null;
}

function handleTrModalAssetChange(index) {
    const assetId = document.getElementById(`tr_asset_${index}`).value;
    const stockEl = document.getElementById(`tr_stock_${index}`);
    const qtyEl = document.getElementById(`tr_qty_${index}`);

    if (!assetId) {
        stockEl.value = '';
        qtyEl.value = 1;
        return;
    }

    const stock = getTrStock(assetId);
    stockEl.value = stock ?? '';
    updateTrModalStockStyle(index, stock, parseInt(qtyEl.value));
}

function handleTrModalQtyChange(index) {
    const assetId = document.getElementById(`tr_asset_${index}`).value;
    if (!assetId) return;
    const stock = getTrStock(assetId);
    const qty = parseInt(document.getElementById(`tr_qty_${index}`).value);
    updateTrModalStockStyle(index, stock, qty);
}

function updateTrModalStockStyle(index, stock, qty) {
    const stockEl = document.getElementById(`tr_stock_${index}`);
    const qtyEl = document.getElementById(`tr_qty_${index}`);
    if (stock !== null && qty > stock) {
        stockEl.style.color = '#dc2626';
        qtyEl.style.background = '#fee2e2';
        qtyEl.style.borderColor = '#dc2626';
    } else {
        stockEl.style.color = 'var(--text-muted)';
        qtyEl.style.background = 'var(--bg-input)';
        qtyEl.style.borderColor = 'var(--border-color)';
    }
}

function openTransferDetailModal(requestId) {
    currentTransferRequestId = requestId;
    const modal = document.getElementById('transferDetailModal');
    modal.style.display = 'flex';
    document.getElementById('transferModalProgress').style.width = '30%';
    const loadingRow = '<tr><td colspan="3" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Memuat...</td></tr>';
    document.querySelector('#transfer_modal_items tbody').innerHTML = loadingRow;

    fetch(`/transfer-requests/${requestId}/detail`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('transferModalProgress').style.width = '100%';
            document.getElementById('transfer_modal_request_code').value = data.request_code;
            document.getElementById('transfer_modal_from_lab').value = data.from_lab;
            document.getElementById('transfer_modal_to_lab').value = data.to_lab;
            document.getElementById('transfer_modal_requested_by').value = data.requested_by;
            document.querySelector('#transfer_modal_items tbody').innerHTML = data.items.map(item => `
                <tr style="border-top:1px solid var(--border-color);">
                    <td style="padding:8px 14px;color:var(--text-primary);">${item.asset_name}</td>
                    <td style="padding:8px 14px;text-align:center;color:var(--text-primary);">${item.quantity}</td>
                    <td style="padding:8px 14px;text-align:center;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                            ${getTransferStatusBadge(item)}
                            ${@json($isSpv) ? `
                                <input type="number" data-tr-item-id="${item.id}"
                                    value="${item.quantity_approved ?? item.quantity}" min="0" max="${item.quantity}"
                                    style="min-width:80px;padding:4px 8px;font-size:12px;border:1px solid var(--border-color);border-radius:4px;background:var(--bg-input);color:var(--text-primary);text-align:center;">
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="3" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Tidak ada barang</td></tr>';
        })
        .catch(() => {
            document.getElementById('transferModalProgress').style.width = '100%';
            const error = '<tr><td colspan="3" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat data</td></tr>';
            document.querySelector('#transfer_modal_items tbody').innerHTML = error;
        });
}

function getTransferStatusBadge(item) {
    if (!item.status) {
        if (item.quantity_approved == 0) {
            return '<span style="background:#dc2626;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Rejected</span>';
        } else if (item.quantity_approved < item.quantity) {
            return '<span style="background:#2563eb;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Partial</span>';
        } else if (item.quantity_approved == item.quantity) {
            return '<span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Approved</span>';
        }
        return '<span style="background:#facc15;color:#713f12;padding:2px 8px;border-radius:4px;font-size:11px;">Pending</span>';
    }
    return '<span style="background:#facc15;color:#713f12;padding:2px 8px;border-radius:4px;font-size:11px;">Pending</span>';
}

function closeTransferDetailModal() {
    currentTransferRequestId = null;
    document.getElementById('transferDetailModal').style.display = 'none';
    document.getElementById('transferModalProgress').style.width = '0%';
}

window.saveTransferStatuses = async function() {
    if (!currentTransferRequestId) {
        alert('Buka detail request terlebih dahulu.');
        return;
    }

    const items = [];
    document.querySelectorAll('[data-tr-item-id]').forEach(input => {
        items.push({
            id: input.dataset.trItemId,
            quantity_approved: parseInt(input.value) || 0
        });
    });

    try {
        const response = await fetch(`/transfer-requests/${currentTransferRequestId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ items })
        });
        const data = await response.json();
        if (!data.success) {
            alert(data.message || 'Gagal menyimpan status.');
            return;
        }
        alert('Status berhasil disimpan!');
        closeTransferDetailModal();
        window.location.reload();
    } catch (e) {
        alert('Gagal menyimpan status.');
    }
}

window.approveAllTransfer = function() {
    if (!currentTransferRequestId) {
        alert('Buka detail request terlebih dahulu.');
        return;
    }
    document.querySelectorAll('[data-tr-item-id]').forEach(input => {
        input.value = input.getAttribute('max');
    });
}

window.rejectAllTransfer = function() {
    if (!currentTransferRequestId) {
        alert('Buka detail request terlebih dahulu.');
        return;
    }
    if (confirm('Apakah Anda yakin ingin menolak seluruh request ini?')) {
        fetch(`/transfer-requests/${currentTransferRequestId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ rejection_reason: 'Ditolak seluruhnya oleh SPV' })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Gagal menolak request.');
                return;
            }
            alert('Request berhasil ditolak!');
            closeTransferDetailModal();
            window.location.reload();
        })
        .catch(() => alert('Gagal menolak request.'));
    }
}

document.getElementById('transferDetailModal').addEventListener('click', function(event) {
    if (event.target === this) closeTransferDetailModal();
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeTransferDetailModal();
        document.querySelectorAll('.panel-modal-overlay:not(.hidden)').forEach(modal => {
            modal.classList.add('hidden');
        });
        document.body.style.overflow = '';
    }
});
</script>
@endpush
