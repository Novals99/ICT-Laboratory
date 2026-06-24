@extends('panel.content')

@section('title', 'Return to Warehouse')

@php
    $role = auth()->user()->role;
    $isSpv = $role === 'spv inventory';
    $canCreateRequest = in_array($role, ['staff', 'pic']);
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
                    <option value="partial" @selected(request('status') === 'partial')>Partially Approved</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium" style="color:var(--text-muted);">Sort By Date</label>
                <select name="sort"
                    style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                    class="rounded-lg py-1.5 pl-2 pr-8 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest to Oldest</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest to Newest</option>
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

            @if(request()->hasAny(['status', 'sort', 'lab_id']))
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
                            <th style="padding:8px 14px; text-align:left;">Serial Number</th>
                            <th style="padding:8px 14px; text-align:center;">Qty Diajukan</th>
                            <th style="padding:8px 14px; text-align:center;">Qty Disetujui</th>
                            <th style="padding:8px 14px; text-align:center;">Condition</th>
                            <th style="padding:8px 14px; text-align:center;">Status / Action</th>
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
            @if(auth()->user()->role === 'staff')
                <div style="padding:10px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input); font-weight:600;">
                    {{ $userLabs->first()?->lab_name ?? '-' }}
                </div>
                <input type="hidden" name="lab_id" id="rr_modal_lab_id" value="{{ $userLabs->first()?->id }}">
            @else
                <select name="lab_id" id="rr_modal_lab_id"
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                    onchange="handleRRModalLabChange()">
                    <option value="">-- Choose Laboratory --</option>
                    @foreach($userLabs as $lab)
                        <option value="{{ $lab->id }}">{{ $lab->lab_name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div style="margin-bottom:16px;">
            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                Choose Category <span class="text-red-500">*</span>
            </label>
            <div style="display:flex; flex-wrap:wrap; gap:8px;" id="rr_category_buttons">
                <button type="button" data-value="electronic" class="rr-cat-btn" style="padding:8px 16px; border-radius:8px; border:1px solid var(--border-color); font-size:12px; background:var(--bg-input); color:var(--text-secondary); cursor:pointer; font-weight:600; transition:all 0.2s;">Electronic</button>
                <button type="button" data-value="component-pc" class="rr-cat-btn" style="padding:8px 16px; border-radius:8px; border:1px solid var(--border-color); font-size:12px; background:var(--bg-input); color:var(--text-secondary); cursor:pointer; font-weight:600; transition:all 0.2s;">PC Component</button>
                <button type="button" data-value="pc" class="rr-cat-btn" style="padding:8px 16px; border-radius:8px; border:1px solid var(--border-color); font-size:12px; background:var(--bg-input); color:var(--text-secondary); cursor:pointer; font-weight:600; transition:all 0.2s;">PC</button>
                <button type="button" data-value="non-electronic" class="rr-cat-btn" style="padding:8px 16px; border-radius:8px; border:1px solid var(--border-color); font-size:12px; background:var(--bg-input); color:var(--text-secondary); cursor:pointer; font-weight:600; transition:all 0.2s;">Non-Electronic</button>
            </div>
        </div>

        <div style="margin-bottom:16px;" id="rr_pc_row">
            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                Choose PC <span style="color:var(--text-muted);">(optional)</span>
            </label>
            <select id="rr_modal_pc_id" style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);" onchange="handleRRPcChange()">
                <option value="">-- All PCs / No PC --</option>
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
        .rr-cat-btn.is-active {
            background: #111B4C !important;
            color: #ffffff !important;
            border-color: #111B4C !important;
        }
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
    let rrLabPcs = [];
    let rrPcComponents = [];
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

    async function handleRRModalLabChange() {
        const labId = document.getElementById('rr_modal_lab_id').value;

        document.getElementById('rr_modal_no_lab').style.display = labId ? 'none' : 'block';
        document.getElementById('rr_modal_loading').style.display = labId ? 'block' : 'none';
        document.getElementById('rr_modal_no_assets').style.display = 'none';
        document.getElementById('rr_modal_items').style.display = 'none';
        document.getElementById('rr_modal_add_btn').style.display = 'none';
        
        if (!labId) return;

        try {
            // Fetch all assets for this lab (without category query, to cache them)
            const assetsRes = await fetch(`/api/labs/${labId}/assets`);
            rrLabAssets = await assetsRes.json();
            
            // Fetch PCs in this lab
            const pcsRes = await fetch(`/api/labs/${labId}/pcs`);
            rrLabPcs = await pcsRes.json();
            
            // Populate PC dropdown
            const pcSelect = document.getElementById('rr_modal_pc_id');
            pcSelect.innerHTML = '<option value="">-- All PCs / No PC --</option>' +
                rrLabPcs.map(pc => `<option value="${pc.id}">${pc.sku} (${ucFirst(pc.type_pc)})</option>`).join('');
            
            // Reset PC components
            rrPcComponents = [];
            
            // Reset active categories (remove active class from all buttons)
            document.querySelectorAll('.rr-cat-btn').forEach(btn => btn.classList.remove('is-active'));
            
            document.getElementById('rr_modal_loading').style.display = 'none';
            
            if (rrLabAssets.length === 0) {
                document.getElementById('rr_modal_no_assets').style.display = 'block';
                return;
            }
            
            document.getElementById('rr_modal_items').innerHTML = '';
            rrItemIndex = 1;
            addRRModalItem();
            document.getElementById('rr_modal_items').style.display = 'block';
            document.getElementById('rr_modal_add_btn').style.display = 'block';
        } catch (e) {
            alert('Failed to load laboratory data.');
            document.getElementById('rr_modal_loading').style.display = 'none';
        }
    }

    function ucFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    async function handleRRPcChange() {
        const pcId = document.getElementById('rr_modal_pc_id').value;
        if (!pcId) {
            rrPcComponents = [];
            updateRrAssetDropdowns();
            return;
        }
        
        try {
            const res = await fetch(`/api/pcs/${pcId}/components`);
            rrPcComponents = await res.json();
            updateRrAssetDropdowns();
        } catch (e) {
            alert('Failed to load PC components.');
        }
    }

    function updateRrAssetDropdowns() {
        const activeCategories = Array.from(document.querySelectorAll('.rr-cat-btn.is-active')).map(btn => btn.dataset.value);
        
        // Filter by active categories
        let filtered = rrLabAssets.filter(a => activeCategories.length === 0 || activeCategories.includes(a.category));
        
        // Filter by PC components if PC is chosen
        const pcId = document.getElementById('rr_modal_pc_id').value;
        if (pcId) {
            const componentAssetIds = rrPcComponents.map(c => c.asset_id);
            filtered = filtered.filter(a => componentAssetIds.includes(a.asset_id));
        }
        
        // Populate all asset selects in item rows
        const selects = document.querySelectorAll('[id^="rr_asset_"]');
        selects.forEach(select => {
            const currentValue = select.value;
            select.innerHTML = '<option value="">-- Select Asset --</option>' +
                filtered.map(a => `<option value="${a.asset_id}" data-category="${a.category}">${a.name}</option>`).join('');
            
            // Restore selected value if it's still in the filtered list
            if (currentValue && filtered.some(a => String(a.asset_id) === String(currentValue))) {
                select.value = currentValue;
            } else {
                select.value = '';
                // Trigger change event to reset serial numbers or quantities
                select.dispatchEvent(new Event('change'));
            }
        });
    }

    function getRRItemRowHtml(idx) {
        return `
            <div class="item-row" data-index="${idx}"
                style="border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:8px; position:relative;">
                <button type="button" onclick="removeRRModalItem(this)"
                    style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:16px;">&times;</button>
                
                <div style="margin-bottom:8px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Asset Name <span class="text-red-500">*</span></label>
                    <select name="items[${idx}][asset_id]" id="rr_asset_${idx}" required
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                        onchange="handleRRModalAssetChange(${idx})">
                        <option value="">-- Select Asset --</option>
                    </select>
                </div>

                <div id="rr_serial_container_${idx}" style="display:none; margin-bottom:8px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Serial Numbers</label>
                    <div id="rr_serial_list_${idx}" style="display:flex; flex-direction:column; gap:6px; margin-bottom:6px;">
                    </div>
                    <button type="button" onclick="addRRSerialSelect(${idx})"
                        class="rounded-lg bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-300 transition" style="border:none; cursor:pointer;">
                        + Add Serial Number
                    </button>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
                    <div>
                        <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="items[${idx}][quantity]" id="rr_qty_${idx}" min="1" value="1" required
                            style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div>
                        <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Condition <span class="text-red-500">*</span></label>
                        <select name="items[${idx}][condition]" id="rr_condition_${idx}"
                            style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                            <option value="good">Good</option>
                            <option value="damaged">Damaged</option>
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

    window.addRRSerialSelect = function(index, preselectedValue = null) {
        const list = document.getElementById(`rr_serial_list_${index}`);
        const assetSelect = document.getElementById(`rr_asset_${index}`);
        const assetId = assetSelect.value;
        const serials = (window.rrSerialData && window.rrSerialData[assetId]) ? window.rrSerialData[assetId] : [];
        
        if (serials.length === 0) return;
        
        const pcId = document.getElementById('rr_modal_pc_id').value;
        let filteredSerials = serials;
        if (pcId) {
            filteredSerials = serials.filter(s => String(s.pc_id) === String(pcId));
        }

        if (filteredSerials.length === 0) {
            alert('No serial numbers for this asset are installed on the selected PC.');
            return;
        }

        const rowId = Date.now() + Math.random().toString(36).substr(2, 5);
        
        const row = document.createElement('div');
        row.id = `rr_serial_row_${rowId}`;
        row.style.cssText = 'display:flex; gap:6px; align-items:center; margin-bottom:4px;';
        
        const select = document.createElement('select');
        select.name = `items[${index}][serial_number_ids][]`;
        select.className = `rr-serial-select-${index}`;
        select.style.cssText = 'flex:1; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);';
        select.required = true;
        select.innerHTML = '<option value="">-- Choose Serial Number --</option>' +
            filteredSerials.map(s => {
                const pcLabel = s.pc_sku ? ` - (PC: ${s.pc_sku})` : '';
                return `<option value="${s.id}">${s.serial_number}${pcLabel}</option>`;
            }).join('');
            
        if (preselectedValue) {
            select.value = preselectedValue;
        }
        
        select.addEventListener('change', () => {
            validateRRUniqueSerials(index);
        });
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'panel-btn-secondary';
        removeBtn.innerHTML = '&times;';
        removeBtn.style.padding = '0 12px';
        removeBtn.style.fontSize = '16px';
        removeBtn.style.border = '1px solid var(--border-color)';
        removeBtn.style.borderRadius = '8px';
        removeBtn.style.cursor = 'pointer';
        removeBtn.style.height = '38px';
        removeBtn.onclick = () => {
            row.remove();
            updateRRQtyFromSerials(index);
            validateRRUniqueSerials(index);
        };
        
        row.appendChild(select);
        row.appendChild(removeBtn);
        list.appendChild(row);
        
        updateRRQtyFromSerials(index);
        validateRRUniqueSerials(index);
    };

    window.updateRRQtyFromSerials = function(index) {
        const list = document.getElementById(`rr_serial_list_${index}`);
        const count = list.querySelectorAll('select').length;
        const qtyInput = document.getElementById(`rr_qty_${index}`);
        qtyInput.value = count;
    };

    window.validateRRUniqueSerials = function(index) {
        const list = document.getElementById(`rr_serial_list_${index}`);
        const selects = list.querySelectorAll('select');
        const selectedValues = Array.from(selects).map(s => s.value).filter(Boolean);
        
        selects.forEach(sel => {
            const currentVal = sel.value;
            Array.from(sel.options).forEach(opt => {
                if (opt.value && opt.value !== currentVal) {
                    opt.disabled = selectedValues.includes(opt.value);
                } else {
                    opt.disabled = false;
                }
            });
        });
    };

    function handleRRModalAssetChange(idx) {
        const labId = document.getElementById('rr_modal_lab_id').value;
        const assetSelect = document.getElementById(`rr_asset_${idx}`);
        const assetId = assetSelect.value;
        const qtyEl = document.getElementById(`rr_qty_${idx}`);
        const serialContainer = document.getElementById(`rr_serial_container_${idx}`);
        const serialList = document.getElementById(`rr_serial_list_${idx}`);

        serialContainer.style.display = 'none';
        if (serialList) serialList.innerHTML = '';
        qtyEl.value = 1;
        qtyEl.readOnly = false;

        if (!labId || !assetId) return;

        const asset = rrLabAssets.find(a => a.asset_id == assetId);
        if (!asset) return;

        const category = asset.category;
        const usesSerial = ['electronic', 'pc', 'non-electronic'].includes(category);

        if (usesSerial) {
            serialContainer.style.display = 'block';
            qtyEl.readOnly = true;

            fetch(`/api/laboratory/${labId}/assets/${assetId}/serials-with-pc`)
                .then(res => res.json())
                .then(data => {
                    window.rrSerialData = window.rrSerialData || {};
                    window.rrSerialData[assetId] = data.serials || [];

                    if (!data.serials || data.serials.length === 0) {
                        if (serialList) serialList.innerHTML = '<div style="color:#f87171; font-size:12px; padding:4px 0;">No serial numbers available in this lab</div>';
                        qtyEl.value = 0;
                        return;
                    }
                    
                    addRRSerialSelect(idx);
                })
                .catch(() => alert('Failed to load serial numbers.'));
        }
    }

    function addRRModalItem() {
        const container = document.getElementById('rr_modal_items');
        const idx = rrItemIndex++;
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = getRRItemRowHtml(idx);
        container.appendChild(tempDiv.firstElementChild);
        
        updateRrAssetDropdowns();
    }

    function removeRRModalItem(btn) {
        const list = document.getElementById('rr_modal_items');
        if (list.querySelectorAll('.item-row').length === 1) return;
        btn.closest('.item-row').remove();
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Setup category button toggle listeners
        document.querySelectorAll('.rr-cat-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.classList.toggle('is-active');
                updateRrAssetDropdowns();
            });
        });
        
        // If lab is pre-selected (hidden input for staff)
        const labIdInput = document.getElementById('rr_modal_lab_id');
        if (labIdInput && labIdInput.value) {
            handleRRModalLabChange();
        }
    });

    let rrItemStates = {};
    let rrItemsList = [];

    function openReturnDetailModal(requestId) {
        currentReturnRequestId = requestId;
        const modal = document.getElementById('returnDetailModal');
        modal.style.display = 'flex';
        document.getElementById('returnModalProgress').style.width = '30%';
        const loadingRow = '<tr><td colspan="5" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Loading...</td></tr>';
        document.querySelector('#return_modal_items tbody').innerHTML = loadingRow;

        rrItemStates = {};
        rrItemsList = [];

        fetch(`/return-requests/${requestId}/detail`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('returnModalProgress').style.width = '100%';
                document.getElementById('return_modal_request_code').value = data.request_code;
                document.getElementById('return_modal_lab').value = data.lab_name;
                document.getElementById('return_modal_requested_by').value = data.requested_by;
                
                rrItemsList = data.items;
                data.items.forEach(item => {
                    if (item.status === 'pending') {
                        rrItemStates[item.id] = 'pending';
                    }
                });
                
                renderReturnRows();
            })
            .catch(() => {
                document.getElementById('returnModalProgress').style.width = '100%';
                const error = '<tr><td colspan="5" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Failed to load data</td></tr>';
                document.querySelector('#return_modal_items tbody').innerHTML = error;
            });
    }

    function renderReturnRows() {
        const isSpv = @json($isSpv);
        const tbody = document.querySelector('#return_modal_items tbody');
        
        if (rrItemsList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">No items</td></tr>';
            return;
        }
        
        tbody.innerHTML = rrItemsList.map(item => {
            let actionHtml = '';
            if (item.status !== 'pending') {
                const isApproved = item.status === 'approved';
                const badgeBg = isApproved ? 'rgba(22, 163, 74, 0.2)' : 'rgba(220, 38, 38, 0.2)';
                const badgeText = isApproved ? '#4ade80' : '#f87171';
                const badgeLabel = isApproved ? 'Approved' : 'Rejected';
                actionHtml = `<span style="background:${badgeBg}; color:${badgeText}; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600;">${badgeLabel}</span>`;
            } else {
                if (isSpv) {
                    const curState = rrItemStates[item.id];
                    const appOpacity = curState === 'approved' ? '1.0' : (curState === 'pending' ? '0.4' : '0.15');
                    const rejOpacity = curState === 'rejected' ? '1.0' : (curState === 'pending' ? '0.4' : '0.15');
                    
                    actionHtml = `
                        <div style="display:flex; align-items:center; justify-content:center; gap:12px;">
                            <button type="button" onclick="setRrRowState(${item.id}, 'approved')"
                                style="background:none; border:none; cursor:pointer; color:#4ade80; padding:4px; opacity:${appOpacity}; transition:opacity 0.2s;" title="Approve">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </button>
                            <button type="button" onclick="setRrRowState(${item.id}, 'rejected')"
                                style="background:none; border:none; cursor:pointer; color:#f87171; padding:4px; opacity:${rejOpacity}; transition:opacity 0.2s;" title="Reject">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    `;
                } else {
                    actionHtml = `<span style="background:rgba(245, 158, 11, 0.2); color:#fbbf24; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600;">Pending</span>`;
                }
            }
            
            let qtyApprovedHtml = '';
            if (item.status !== 'pending') {
                qtyApprovedHtml = `<span style="font-weight:600; color:var(--text-primary);">${item.quantity_approved ?? 0}</span>`;
            } else {
                if (isSpv) {
                    const curState = rrItemStates[item.id];
                    const isRejected = curState === 'rejected';
                    qtyApprovedHtml = `
                        <input type="number" id="qty_approved_${item.id}" 
                               value="${rrItemStates[item.id + '_qty'] !== undefined ? rrItemStates[item.id + '_qty'] : (isRejected ? 0 : item.quantity)}" 
                               min="0" max="${item.quantity}" 
                               ${isRejected ? 'disabled' : ''}
                               oninput="rrItemStates[${item.id} + '_qty'] = this.value"
                               style="width:70px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:6px; padding:4px 8px; text-align:center;">
                    `;
                } else {
                    qtyApprovedHtml = `<span style="color:var(--text-muted);">-</span>`;
                }
            }
            
            return `
                <tr style="border-top:1px solid var(--border-color);">
                    <td style="padding:10px 14px;color:var(--text-primary);">${item.asset_name}</td>
                    <td style="padding:10px 14px;color:var(--text-secondary);font-family:monospace;">${item.serial_number ?? '-'}</td>
                    <td style="padding:10px 14px;text-align:center;color:var(--text-primary); font-weight:600;">${item.quantity}</td>
                    <td style="padding:10px 14px;text-align:center;">${qtyApprovedHtml}</td>
                    <td style="padding:10px 14px;text-align:center;color:var(--text-primary); font-weight:600;">${item.condition}</td>
                    <td style="padding:10px 14px;text-align:center;">${actionHtml}</td>
                </tr>
            `;
        }).join('');
    }

    function setRrRowState(itemId, state) {
        if (rrItemStates[itemId] !== undefined) {
            rrItemStates[itemId] = state;
            if (state === 'rejected') {
                rrItemStates[itemId + '_qty'] = 0;
            } else if (state === 'approved') {
                if (rrItemStates[itemId + '_qty'] === 0) {
                    const item = rrItemsList.find(i => i.id === itemId);
                    if (item) rrItemStates[itemId + '_qty'] = item.quantity;
                }
            }
            renderReturnRows();
        }
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

        const items = Object.keys(rrItemStates)
            .filter(key => !key.endsWith('_qty'))
            .map(id => {
                const qtyVal = rrItemStates[id + '_qty'];
                const itemObj = rrItemsList.find(i => String(i.id) === String(id));
                const defaultQty = itemObj ? itemObj.quantity : 0;
                return {
                    id: parseInt(id),
                    status: rrItemStates[id],
                    quantity_approved: qtyVal !== undefined ? parseInt(qtyVal) : (rrItemStates[id] === 'rejected' ? 0 : defaultQty)
                };
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
            alert('Status saved successfully!');
            closeReturnDetailModal();
            window.location.reload();
        } catch (e) {
            alert('Failed to save status.');
        }
    }

    window.approveAllReturn = function() {
        if (!currentReturnRequestId) {
            alert('Please open a request first.');
            return;
        }
        Object.keys(rrItemStates).forEach(id => {
            rrItemStates[id] = 'approved';
        });
        renderReturnRows();
    }

    window.rejectAllReturn = function() {
        if (!currentReturnRequestId) {
            alert('Please open a request first.');
            return;
        }
        Object.keys(rrItemStates).forEach(id => {
            rrItemStates[id] = 'rejected';
        });
        renderReturnRows();
    }

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
