@extends('panel.content')

@section('title', 'Return to Warehouse')

@php
    $role = auth()->user()->role;
    $isSpv = $role === 'spv inventory';
    $canCreateRequest = $role === 'staff';
@endphp

@section('content')
<div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-2xl p-6 shadow-sm">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-3xl font-semibold" style="color:var(--text-primary);">
                Return to Warehouse
            </h2>
            <p class="mt-1 text-sm" style="color:var(--text-muted);">
                Request to return assets from laboratories to the main warehouse
            </p>
        </div>

        @if ($canCreateRequest)
            <x-button.add type="button" onclick="openPanelModal('addReturnModal')">
                Create Return Request
            </x-button.add>
        @endif
    </div>

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

    <div style="background:var(--bg-input); border:1px solid var(--border-color);" class="mb-4 rounded-xl p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium" style="color:var(--text-muted);">Status</label>
                <select name="status"
                    style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                    class="rounded-lg py-1.5 pl-2 pr-8 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>

            @if($isSpv)
                <div>
                    <label class="mb-1 block text-xs font-medium" style="color:var(--text-muted);">Laboratory</label>
                    <select name="lab_id"
                        style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                        class="rounded-lg py-1.5 pl-2 pr-8 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="">All Labs</option>
                        @foreach($labs as $lab)
                            <option value="{{ $lab->id }}" @selected(request('lab_id') == $lab->id)>
                                {{ $lab->lab_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="submit"
                style="background:var(--bg-table-header); color:var(--text-secondary);"
                class="rounded-lg px-4 py-1.5 text-sm transition hover:opacity-90">
                Filter
            </button>

            @if(request()->hasAny(['status', 'lab_id']))
                <a href="{{ route('return-requests.index') }}"
                    class="py-1.5 text-sm" style="color:var(--text-muted);">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:var(--bg-table-header); color:var(--text-secondary);">
                    <th class="px-4 py-3 text-left font-medium">Request Code</th>
                    <th class="px-4 py-3 text-left font-medium">Lab</th>
                    <th class="px-4 py-3 text-left font-medium">Requested by</th>
                    <th class="px-4 py-3 text-center font-medium">Items</th>
                    <th class="px-4 py-3 text-center font-medium">Status</th>
                    <th class="px-4 py-3 text-left font-medium">Date</th>
                    <th class="px-4 py-3 text-center font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returnRequests as $req)
                    <tr style="border-bottom:1px solid var(--border-color);" class="transition-colors">
                        <td class="px-4 py-3" style="color:var(--text-secondary);">
                            {{ $req->request_code }}
                        </td>
                        <td class="px-4 py-3" style="color:var(--text-secondary);">
                            {{ $req->laboratory?->lab_name ?? '-' }}
                        </td>
                        <td class="px-4 py-3" style="color:var(--text-secondary);">
                            {{ $req->requestedBy?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($req->pc_id)
                                <span style="background:var(--bg-table-header); color:var(--text-secondary);"
                                    class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    PC
                                </span>
                            @else
                                <span style="background:var(--bg-table-header); color:var(--text-secondary);"
                                    class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ $req->items->count() }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php [$label, $color] = $req->getStatusBadge(); @endphp
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $color }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-3" style="color:var(--text-muted);">
                            {{ $req->created_at->format('d-m-y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" onclick="openReturnDetailModal({{ $req->id }})"
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
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            No return requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($returnRequests->hasPages())
        <div class="mt-6">
            {{ $returnRequests->links() }}
        </div>
    @endif
</div>

<div id="returnDetailModal"
    style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div
        style="background:var(--bg-modal); border-radius:16px; width:100%; max-width:760px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15); border:1px solid var(--border-color);">
        <div
            style="display:flex; align-items:center; padding:24px 32px 12px 32px; gap:16px; border-bottom:1px solid var(--border-color);">
            <h3 style="font-size:18px; font-weight:600; color:var(--text-primary); flex-shrink:0; margin:0;">
                Return Request Information
            </h3>
            <div style="flex:1;">
                <div style="height:6px; background:var(--border-color); border-radius:99px; overflow:hidden;">
                    <div id="returnModalProgress"
                        style="height:6px; background:#93c5fd; border-radius:99px; width:0%; transition:width 0.5s;">
                    </div>
                </div>
            </div>
            <button onclick="closeReturnDetailModal()"
                style="color:var(--text-muted); background:none; border:none; cursor:pointer; padding:4px; line-height:1;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div style="padding:16px 32px 24px 32px;">
            <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:28px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Request Code:</label>
                    <input id="return_modal_request_code" type="text" readonly
                        style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                </div>
                <div style="display:flex; align-items:center; gap:16px;">
                    <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Lab:</label>
                    <input id="return_modal_lab" type="text" readonly
                        style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                </div>
                <div style="display:flex; align-items:center; gap:16px;">
                    <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Requested by:</label>
                    <input id="return_modal_requested_by" type="text" readonly
                        style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">Items to Return</p>
                <table id="return_modal_items" style="width:100%; font-size:13px; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                    <thead>
                        <tr style="background:var(--bg-table-header);">
                            <th style="padding:8px 14px; text-align:left;">Asset Name</th>
                            <th style="padding:8px 14px; text-align:center;">Qty</th>
                            <th style="padding:8px 14px; text-align:center;">Condition</th>
                            <th style="padding:8px 14px; text-align:center;">{{ $isSpv ? 'Approved Qty' : 'Status' }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        @if ($isSpv)
            <div style="display:flex; justify-content:flex-end; gap:10px; padding:0 32px 24px 32px;">
                <button type="button" onclick="rejectAllReturn()"
                    style="border:1px solid #dc2626; background:#dc2626; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Reject All
                </button>
                <button type="button" onclick="approveAllReturn()"
                    style="border:1px solid #16a34a; background:#16a34a; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Approve All
                </button>
                <button type="button" onclick="saveReturnStatuses()"
                    style="border:1px solid #111B4C; background:#111B4C; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Save
                </button>
            </div>
        @endif
    </div>
</div>

@if ($canCreateRequest)
    <x-modal.index id="addReturnModal" title="Create Return Request"
        :action="route('return-requests.store')" submitText="Submit Request"
        cancelText="Cancel" boxClass="return-create-modal" innerClass="return-create-inner">
        <div style="margin-bottom:16px;">
            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                Laboratory <span class="text-red-500">*</span>
            </label>
            <select name="lab_id" id="rr_modal_lab_id"
                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                onchange="handleRRModalLabChange()">
                <option value="">-- Choose Laboratory --</option>
                @foreach($userLabs as $lab)
                    <option value="{{ $lab->id }}">{{ $lab->lab_name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                Notes <span style="color:var(--text-muted);">(optional)</span>
            </label>
            <textarea name="notes" rows="3"
                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                placeholder="Additional notes for the supervisor..."></textarea>
        </div>

        <div id="rr_modal_item_list">
            <div id="rr_modal_no_lab" style="text-align:center; color:var(--text-muted); font-size:13px;">
                Please select a laboratory first
            </div>
            <div id="rr_modal_loading" style="display:none; text-align:center; color:var(--text-muted); font-size:13px;">
                Loading assets...
            </div>
            <div id="rr_modal_no_assets" style="display:none; text-align:center; color:var(--text-muted); font-size:13px;">
                No assets available in this laboratory
            </div>
            <div id="rr_modal_items" style="display:none;"></div>
        </div>

        <div id="rr_modal_add_btn" style="display:none; margin-top:12px;">
            <button type="button" onclick="addRRModalItem()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#111B4C] px-4 py-2 text-sm font-semibold text-white">
                + Add Item
            </button>
        </div>
    </x-modal.index>
@endif
@endsection

@push('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .return-create-modal {
            max-height: calc(100vh - 48px);
            min-height: auto;
        }

        .return-create-modal .panel-modal-form {
            min-height: 0;
        }

        .return-create-inner {
            max-height: calc(100vh - 210px);
            overflow-y: auto;
            padding-right: 20px;
        }

        .return-create-modal .panel-modal-footer {
            flex-shrink: 0;
        }
    </style>
@endpush

@push('scripts')
<script>
    let rrUserLabs = @json($userLabs->map(fn($l) => ['id' => $l->id, 'name' => $l->lab_name]));
    let rrLabAssets = [];
    let rrItemIndex = 1;
    let currentReturnRequestId = null;

    window.openPanelModal = function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closePanelModal = function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.closePanelModalOnBackdrop = function(event, id) {
        if (event.target.id === id) closePanelModal(id);
    };

    function handleRRModalLabChange() {
        const labId = document.getElementById('rr_modal_lab_id').value;

        document.getElementById('rr_modal_no_lab').style.display = labId ? 'none' : 'block';
        document.getElementById('rr_modal_loading').style.display = labId ? 'block' : 'none';
        document.getElementById('rr_modal_no_assets').style.display = 'none';
        document.getElementById('rr_modal_items').style.display = 'none';
        document.getElementById('rr_modal_add_btn').style.display = 'none';

        if (!labId) return;

        fetch(`/api/labs/${labId}/assets`)
            .then(res => res.json())
            .then(data => {
                rrLabAssets = data;
                document.getElementById('rr_modal_loading').style.display = 'none';
                if (data.length === 0) {
                    document.getElementById('rr_modal_no_assets').style.display = 'block';
                } else {
                    document.getElementById('rr_modal_items').innerHTML = '';
                    rrItemIndex = 1;
                    addRRModalItem();
                    document.getElementById('rr_modal_items').style.display = 'block';
                    document.getElementById('rr_modal_add_btn').style.display = 'block';
                }
            })
            .catch(() => {
                document.getElementById('rr_modal_loading').style.display = 'none';
                alert('Failed to load asset data');
            });
    }

    function getRRItemRowHtml(idx) {
        return `
            <div class="item-row"
                style="border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:8px; position:relative;">
                <button type="button" onclick="removeRRModalItem(this)"
                    style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--text-muted);">x</button>
                <div style="margin-bottom:8px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Asset Name:</label>
                    <select name="items[${idx}][asset_id]" required
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                        <option value="">-- Select Asset --</option>
                        ${rrLabAssets.map(a => `<option value="${a.asset_id}">${a.name}</option>`).join('')}
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
                    <div>
                        <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Quantity:</label>
                        <input type="number" name="items[${idx}][quantity]" min="1" value="1" required
                            style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div>
                        <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Condition:</label>
                        <select name="items[${idx}][condition]"
                            style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                            <option value="good">Good</option>
                            <option value="damaged">Damaged</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Reason (optional):</label>
                    <input type="text" name="items[${idx}][reason]"
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                        placeholder="Reason for return...">
                </div>
            </div>
        `;
    }

    function addRRModalItem() {
        const container = document.getElementById('rr_modal_items');
        container.innerHTML += getRRItemRowHtml(rrItemIndex++);
    }

    function removeRRModalItem(btn) {
        const list = document.getElementById('rr_modal_items');
        if (list.querySelectorAll('.item-row').length === 1) return;
        btn.closest('.item-row').remove();
    }

    function openReturnDetailModal(requestId) {
        currentReturnRequestId = requestId;
        const modal = document.getElementById('returnDetailModal');
        modal.style.display = 'flex';
        document.getElementById('returnModalProgress').style.width = '30%';
        const loadingRow = '<tr><td colspan="4" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Loading...</td></tr>';
        document.querySelector('#return_modal_items tbody').innerHTML = loadingRow;
        fetch(`/return-requests/${requestId}/detail`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('returnModalProgress').style.width = '100%';
                document.getElementById('return_modal_request_code').value = data.request_code;
                document.getElementById('return_modal_lab').value = data.lab_name;
                document.getElementById('return_modal_requested_by').value = data.requested_by;
                document.querySelector('#return_modal_items tbody').innerHTML = data.items.map(item => `
                    <tr style="border-top:1px solid var(--border-color);">
                        <td style="padding:8px 14px;color:var(--text-primary);">${item.asset_name}</td>
                        <td style="padding:8px 14px;text-align:center;color:var(--text-primary);">${item.quantity}</td>
                        <td style="padding:8px 14px;text-align:center;color:var(--text-primary);">${item.condition}</td>
                        <td style="padding:8px 14px;text-align:center;">
                            <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                                ${getReturnStatusBadge(item)}
                                ${@json($isSpv) ? `
                                    <input type="number" data-return-item-id="${item.id}"
                                        value="${item.quantity_approved ?? item.quantity}" min="0" max="${item.quantity}"
                                        style="min-width:80px;padding:4px 8px;font-size:12px;border:1px solid var(--border-color);border-radius:4px;background:var(--bg-input);color:var(--text-primary);text-align:center;">
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="4" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">No items found</td></tr>';
            })
            .catch(() => {
                document.getElementById('returnModalProgress').style.width = '100%';
                const error = '<tr><td colspan="4" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Failed to load asset data</td></tr>';
                document.querySelector('#return_modal_items tbody').innerHTML = error;
            });
    }

    function getReturnStatusBadge(item) {
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

    function closeReturnDetailModal() {
        currentReturnRequestId = null;
        document.getElementById('returnDetailModal').style.display = 'none';
        document.getElementById('returnModalProgress').style.width = '0%';
    }

    window.saveReturnStatuses = async function() {
        if (!currentReturnRequestId) {
            alert('Please open a request first.');
            return;
        }

        const items = [];
        document.querySelectorAll('[data-return-item-id]').forEach(input => {
            items.push({
                id: input.dataset.returnItemId,
                quantity_approved: parseInt(input.value) || 0
            });
        });

        try {
            const response = await fetch(`/return-requests/${currentReturnRequestId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ items })
            });
            const data = await response.json();
            if (!data.success) {
                alert(data.message || 'Failed to save status.');
                return;
            }
            alert('Status successfully saved!');
            closeReturnDetailModal();
            window.location.reload();
        } catch (e) {
            alert('Failed to save status.');
        }
    };

    window.approveAllReturn = function() {
        if (!currentReturnRequestId) {
            alert('Please open a request first.');
            return;
        }
        document.querySelectorAll('[data-return-item-id]').forEach(input => {
            input.value = input.getAttribute('max');
        });
    };

    window.rejectAllReturn = function() {
        if (!currentReturnRequestId) {
            alert('Please open a request first.');
            return;
        }
        if (confirm('Are you sure you want to reject the entire request?')) {
            fetch(`/return-requests/${currentReturnRequestId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ rejection_reason: 'Rejected entirely by SPV' })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Failed to reject request.');
                    return;
                }
                alert('Request successfully rejected!');
                closeReturnDetailModal();
                window.location.reload();
            })
            .catch(() => alert('Failed to reject request.'));
        }
    };

    document.getElementById('returnDetailModal').addEventListener('click', function(event) {
        if (event.target === this) closeReturnDetailModal();
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeReturnDetailModal();
            document.querySelectorAll('.panel-modal-overlay:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
            });
            document.body.style.overflow = '';
        }
    });
</script>
@endpush
