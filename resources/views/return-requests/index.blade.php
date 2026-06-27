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
                Notes <span style="color:var(--text-muted);">(optional)</span>
            </label>
            <textarea name="notes" rows="3"
                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                placeholder="Additional notes for the supervisor..."></textarea>
        </div>

        <div id="rr_modal_item_list">
            <div id="rr_modal_no_lab" style="text-align:center; color:var(--text-muted); font-size:13px; margin-bottom:12px;">
                Please select a laboratory first
            </div>
            <div id="rr_modal_loading" style="display:none; text-align:center; color:var(--text-muted); font-size:13px; margin-bottom:12px;">
                Loading assets...
            </div>
            <div id="rr_modal_no_assets" style="display:none; text-align:center; color:var(--text-muted); font-size:13px; margin-bottom:12px;">
                No assets available in this laboratory
            </div>
            <div id="rr_cards_container" style="display:none; flex-direction:column; gap:16px;"></div>
        </div>

        <div id="rr_add_btn" style="display:none; margin-top:12px;">
            <button type="button" id="btn_add_rr_card" onclick="addRRCategoryCard()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#111B4C] px-4 py-2 text-sm font-semibold text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Category
            </button>
        </div>
    </x-modal.index>

    {{-- QR SCANNER MODAL --}}
    <div id="qrScanModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:var(--bg-main, #fff); border-radius:16px; width:100%; max-width:400px; padding:20px; box-shadow:0 20px 60px rgba(0,0,0,0.25); border:1px solid var(--border-color, #e5e7eb); display:flex; flex-direction:column; gap:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:15px; font-weight:700; color:var(--text-bold, #111827); margin:0;">Scan QR Code</h3>
                <button type="button" onclick="closeQrScanModal()" style="background:none; border:none; cursor:pointer; color:var(--text-muted, #9ca3af); font-size:22px;">&times;</button>
            </div>
            
            <div style="display:flex; border-bottom:1px solid var(--border-color, #e5e7eb); margin-bottom:8px;">
                <button type="button" id="scanTabCam" onclick="switchScanTab('camera')" style="flex:1; padding:8px 0; border:none; background:transparent; font-size:12px; font-weight:600; cursor:pointer; border-bottom:2px solid #111B4C; color:#111B4C;">Kamera</button>
                <button type="button" id="scanTabFile" onclick="switchScanTab('file')" style="flex:1; padding:8px 0; border:none; background:transparent; font-size:12px; font-weight:600; cursor:pointer; border-bottom:2px solid transparent; color:var(--text-muted, #9ca3af);">Upload File</button>
            </div>

            <div id="scanCamPanel" style="display:block;">
                <div id="qr_scanner_reader" style="width:100%; border-radius:12px; overflow:hidden; background:#000; min-height:220px;"></div>
                <div style="margin-top:12px; text-align:center;">
                    <button type="button" id="btnStartScanCam" onclick="startScanCamera()" style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">Aktifkan Kamera</button>
                    <button type="button" id="btnStopScanCam" onclick="stopScanCamera()" style="background:#dc2626; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; display:none;">Stop Kamera</button>
                </div>
            </div>
            
            <div id="scanFilePanel" style="display:none;">
                <div onclick="document.getElementById('qr_file_input').click()" style="border:2px dashed var(--border-main, #d1d5db); border-radius:12px; padding:30px 16px; text-align:center; cursor:pointer; background:var(--bg-light, #f3f4f6);">
                    <svg style="margin:0 auto 8px; color:var(--text-muted);" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span style="font-size:13px; font-weight:600; color:var(--text-normal, #374151); display:block;">Pilih / Drop Foto QR Code</span>
                    <span style="font-size:11px; color:var(--text-muted); display:block; margin-top:4px;">JPG, PNG, WebP</span>
                </div>
                <input type="file" id="qr_file_input" accept="image/*" style="display:none;" onchange="handleQrFileSelected(event)">
            </div>
        </div>
    </div>
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
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
    let rrUserLabs = @json($userLabs->map(fn($l) => ['id' => $l->id, 'name' => $l->lab_name]));
    let rrLabAssets = [];
    let rrLabPcs = [];
    let rrPcComponents = [];
    let rrItemIndex = 1;
    let cardIndex = 0;
    let itemRowIndex = 0;
    let currentReturnRequestId = null;

    // QR scanner state
    let activeQrSelect = null;
    let scanHtml5QrCode = null;
    let isScanCameraRunning = false;

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

    // QR scanner helpers
    window.openQrScannerFor = function(selectEl) {
        activeQrSelect = selectEl;
        document.getElementById('qrScanModal').style.display = 'flex';
        switchScanTab('camera');
    };

    window.closeQrScanModal = function() {
        stopScanCamera();
        document.getElementById('qrScanModal').style.display = 'none';
        activeQrSelect = null;
    };

    window.switchScanTab = function(tab) {
        document.getElementById('scanTabCam').style.borderBottomColor = tab === 'camera' ? '#111B4C' : 'transparent';
        document.getElementById('scanTabCam').style.color = tab === 'camera' ? '#111B4C' : 'var(--text-muted)';
        document.getElementById('scanTabFile').style.borderBottomColor = tab === 'file' ? '#111B4C' : 'transparent';
        document.getElementById('scanTabFile').style.color = tab === 'file' ? '#111B4C' : 'var(--text-muted)';
        
        document.getElementById('scanCamPanel').style.display = tab === 'camera' ? 'block' : 'none';
        document.getElementById('scanFilePanel').style.display = tab === 'file' ? 'block' : 'none';
        
        if (tab !== 'camera') {
            stopScanCamera();
        }
    };

    window.startScanCamera = async function() {
        if (isScanCameraRunning) return;
        if (!scanHtml5QrCode) {
            scanHtml5QrCode = new Html5Qrcode('qr_scanner_reader');
        }
        
        const config = {
            fps: 12,
            qrbox: { width: 220, height: 220 },
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39
            ]
        };
        
        try {
            await scanHtml5QrCode.start(
                { facingMode: 'environment' },
                config,
                onQrScanSuccess,
                () => {}
            );
            isScanCameraRunning = true;
            document.getElementById('btnStartScanCam').style.display = 'none';
            document.getElementById('btnStopScanCam').style.display = 'inline-flex';
        } catch (err) {
            alert('Gagal mengakses kamera: ' + err);
        }
    };

    window.stopScanCamera = async function() {
        if (!isScanCameraRunning || !scanHtml5QrCode) return;
        try {
            await scanHtml5QrCode.stop();
        } catch (_) {}
        isScanCameraRunning = false;
        document.getElementById('btnStartScanCam').style.display = 'inline-flex';
        document.getElementById('btnStopScanCam').style.display = 'none';
    };

    function onQrScanSuccess(decodedText) {
        if (!activeQrSelect) return;
        
        const options = Array.from(activeQrSelect.options);
        const matched = options.find(o => o.text.trim().toLowerCase().startsWith(decodedText.trim().toLowerCase()));
        
        if (matched) {
            activeQrSelect.value = matched.value;
            activeQrSelect.dispatchEvent(new Event('change'));
            closeQrScanModal();
        } else {
            alert(`Kode "${decodedText}" tidak sesuai atau tidak tersedia untuk item ini di lab.`);
        }
    }

    window.handleQrFileSelected = function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(evt) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                
                if (code) {
                    onQrScanSuccess(code.data);
                } else {
                    alert('QR Code tidak terdeteksi pada gambar. Silakan coba gambar lain yang lebih jelas.');
                }
            };
            img.src = evt.target.result;
        };
        reader.readAsDataURL(file);
    };

    async function handleRRModalLabChange() {
        const labId = document.getElementById('rr_modal_lab_id').value;

        document.getElementById('rr_modal_no_lab').style.display = labId ? 'none' : 'block';
        document.getElementById('rr_modal_loading').style.display = labId ? 'block' : 'none';
        document.getElementById('rr_modal_no_assets').style.display = 'none';
        document.getElementById('rr_cards_container').style.display = 'none';
        document.getElementById('rr_add_btn').style.display = 'none';
        
        if (!labId) return;

        try {
            const assetsRes = await fetch(`/api/labs/${labId}/assets`);
            rrLabAssets = await assetsRes.json();
            
            const pcsRes = await fetch(`/api/labs/${labId}/pcs`);
            rrLabPcs = await pcsRes.json();
            
            document.getElementById('rr_modal_loading').style.display = 'none';
            
            if (rrLabAssets.length === 0) {
                document.getElementById('rr_modal_no_assets').style.display = 'block';
                return;
            }
            
            document.getElementById('rr_cards_container').innerHTML = '';
            cardIndex = 0;
            itemRowIndex = 0;
            addRRCategoryCard();
            
            document.getElementById('rr_cards_container').style.display = 'flex';
            document.getElementById('rr_add_btn').style.display = 'inline-flex';
        } catch (e) {
            alert('Failed to load laboratory data.');
            document.getElementById('rr_modal_loading').style.display = 'none';
        }
    }

    function ucFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    window.addRRCategoryCard = function() {
        const container = document.getElementById('rr_cards_container');
        const idx = cardIndex++;
        
        const card = document.createElement('div');
        card.className = 'tr-category-card rounded-xl p-4'; // keep same class for submit traversal
        card.style.cssText = 'background:var(--bg-input); border:1px solid var(--border-color); display:flex; flex-direction:column; gap:12px;';
        card.dataset.cardIndex = idx;
        
        card.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <div style="display:flex; gap:12px; align-items:center; flex:1;">
                    <div style="width:200px;">
                        <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Category <span class="text-red-500">*</span></label>
                        <select class="js-card-category" required onchange="handleCardCategoryChange(${idx})"
                            style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-card);">
                            <option value="">-- Select Category --</option>
                            <option value="electronic">Electronic</option>
                            <option value="component-pc">PC Component</option>
                            <option value="pc">PC</option>
                            <option value="non-electronic">Non-Electronic</option>
                        </select>
                    </div>
                    <div class="js-card-pc-container" style="display:none; width:200px;">
                        <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">PC <span class="text-red-500">*</span></label>
                        <select class="js-card-pc" onchange="handleCardPcChange(${idx})"
                            style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-card);">
                            <option value="">-- All PCs / No PC --</option>
                        </select>
                    </div>
                </div>
                <button type="button" onclick="removeCategoryCard(this)"
                    style="background:none; border:none; cursor:pointer; color:#dc2626; font-size:13px; font-weight:600; margin-top:16px;">Remove Card</button>
            </div>
            
            <div class="js-card-items-container" style="display:flex; flex-direction:column; gap:8px;"></div>
        `;
        container.appendChild(card);
    };

    window.removeCategoryCard = function(btn) {
        const container = document.getElementById('rr_cards_container');
        if (container.querySelectorAll('.tr-category-card').length === 1) {
            alert('At least one category card is required.');
            return;
        }
        btn.closest('.tr-category-card').remove();
    };

    window.handleCardCategoryChange = function(cardIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        if (!card) return;
        
        const catSelect = card.querySelector('.js-card-category');
        const category = catSelect.value;
        
        if (category) {
            const otherSelects = Array.from(document.querySelectorAll('.js-card-category')).filter(sel => sel !== catSelect);
            const hasDuplicate = otherSelects.some(sel => sel.value === category);
            if (hasDuplicate) {
                alert('Kategori ini sudah dipilih pada card lain.');
                catSelect.value = '';
                handleCardCategoryChange(cardIdx);
                return;
            }
        }
        
        const pcContainer = card.querySelector('.js-card-pc-container');
        const pcSelect = card.querySelector('.js-card-pc');
        const itemsContainer = card.querySelector('.js-card-items-container');
        
        itemsContainer.innerHTML = '';
        
        if (!category) {
            pcContainer.style.display = 'none';
            pcSelect.value = '';
            return;
        }
        
        if (category === 'component-pc') {
            pcContainer.style.display = 'block';
            pcSelect.innerHTML = '<option value="">-- All PCs / No PC --</option>' +
                rrLabPcs.map(pc => `<option value="${pc.id}">${pc.pc_name || pc.sku || 'PC'} (${ucFirst(pc.type_pc)})</option>`).join('');
        } else {
            pcContainer.style.display = 'none';
            pcSelect.value = '';
        }
        
        addCardItem(cardIdx);
    };

    window.handleCardPcChange = async function(cardIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        if (!card) return;
        
        const pcSelect = card.querySelector('.js-card-pc');
        const pcId = pcSelect.value;
        
        if (pcId) {
            try {
                const res = await fetch(`/api/pcs/${pcId}/components`);
                card.dataset.pcComponents = JSON.stringify(await res.json());
            } catch (e) {
                alert('Failed to load PC components.');
                card.dataset.pcComponents = '[]';
            }
        } else {
            card.removeAttribute('data-pc-components');
        }
        
        // Update PC component rows
        const rows = card.querySelectorAll('.item-row');
        rows.forEach(row => {
            const itemIdx = row.dataset.itemIdx;
            populateComponentTypes(cardIdx, itemIdx);
            handleComponentTypeChange(cardIdx, itemIdx);
        });
    };

    function updateCardAssetDropdowns(cardIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        if (!card) return;
        
        const category = card.querySelector('.js-card-category').value;
        const pcComponentsStr = card.dataset.pcComponents;
        const pcComponents = pcComponentsStr ? JSON.parse(pcComponentsStr) : null;
        
        let filtered = rrLabAssets.filter(a => a.category === category);
        
        if (pcComponents) {
            const componentAssetIds = pcComponents.map(c => c.asset_id);
            filtered = filtered.filter(a => componentAssetIds.includes(a.asset_id));
        }
        
        const selects = card.querySelectorAll('.js-asset-select');
        selects.forEach(select => {
            const currentValue = select.value;
            select.innerHTML = '<option value="">-- Choose Asset --</option>' +
                filtered.map(a => `<option value="${a.asset_id}">${a.name}</option>`).join('');
            
            if (currentValue && filtered.some(a => String(a.asset_id) === String(currentValue))) {
                select.value = currentValue;
            } else {
                select.value = '';
                select.dispatchEvent(new Event('change'));
            }
        });
    }

    window.addCardItem = function(cardIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        if (!card) return;
        
        const container = card.querySelector('.js-card-items-container');
        const idx = itemRowIndex++;
        
        const category = card.querySelector('.js-card-category').value;
        const isPcComp = category === 'component-pc';
        
        const row = document.createElement('div');
        row.className = 'item-row';
        row.dataset.itemIdx = idx;
        row.style.cssText = 'border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:8px; position:relative; background:var(--bg-card);';
        
        let componentTypeHtml = '';
        if (isPcComp) {
            componentTypeHtml = `
                <div style="margin-bottom:8px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Component Type <span class="text-red-500">*</span></label>
                    <select class="js-component-type-select" required onchange="handleComponentTypeChange(${cardIdx}, ${idx})"
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                        <option value="">-- Choose Component Type --</option>
                    </select>
                </div>
            `;
        }
        
        row.innerHTML = `
            <button type="button" onclick="removeCardItem(this)"
                style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:16px;">&times;</button>
            
            ${componentTypeHtml}

            <div style="margin-bottom:8px;">
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Asset Name <span class="text-red-500">*</span></label>
                <select class="js-asset-select" required onchange="handleCardAssetChange(${cardIdx}, ${idx})"
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    <option value="">-- Choose Asset --</option>
                </select>
            </div>

            <div class="js-serial-container" style="display:none; margin-bottom:8px;">
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Kode Inventaris</label>
                <div class="js-serial-list" style="display:flex; flex-direction:column; gap:6px; margin-bottom:6px;"></div>
                <button type="button" onclick="addCardSerialSelect(${cardIdx}, ${idx})"
                    class="rounded-lg bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-300 transition" style="border:none; cursor:pointer;">
                    + Add Kode Inventaris
                </button>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-bottom:8px;">
                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Available Stock</label>
                    <input type="text" class="js-stock-input" readonly
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-muted); background:var(--bg-input);">
                </div>
                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Quantity</label>
                    <input type="number" class="js-qty-input"
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                        min="1" value="1" onchange="handleCardQtyChange(${cardIdx}, ${idx})" oninput="handleCardQtyChange(${cardIdx}, ${idx})" required>
                </div>
                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Condition <span class="text-red-500">*</span></label>
                    <select class="js-condition-select" required onchange="handleCardConditionChange(${cardIdx}, ${idx})"
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                        <option value="good">Good</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:8px;">
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Reason (optional)</label>
                <input type="text" class="js-reason-input"
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);"
                    placeholder="Enter reason...">
            </div>

            <div style="display:flex; justify-content:flex-end;">
                <button type="button" onclick="addCardItem(${cardIdx})"
                    class="rounded-lg bg-[#111B4C] px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90 transition" style="border:none; cursor:pointer;">
                    + Add Asset
                </button>
            </div>
        `;
        
        // Hide add asset buttons on previous rows in this card
        container.querySelectorAll('.item-row button[onclick^="addCardItem"]').forEach(btn => {
            btn.parentElement.style.display = 'none';
        });
        
        container.appendChild(row);
        
        if (isPcComp) {
            populateComponentTypes(cardIdx, idx);
            handleComponentTypeChange(cardIdx, idx);
        } else {
            updateCardAssetDropdowns(cardIdx);
        }
    };

    window.removeCardItem = function(btn) {
        const container = btn.closest('.js-card-items-container');
        const card = btn.closest('.tr-category-card');
        const cardIdx = card.dataset.cardIndex;
        
        if (container.querySelectorAll('.item-row').length === 1) {
            alert('At least one asset row is required per card.');
            return;
        }
        
        btn.closest('.item-row').remove();
        
        const rows = container.querySelectorAll('.item-row');
        const lastRow = rows[rows.length - 1];
        if (lastRow) {
            const lastBtn = lastRow.querySelector('button[onclick^="addCardItem"]');
            if (lastBtn) lastBtn.parentElement.style.display = 'flex';
        }
        
        validateCardUniqueAssets(cardIdx);
        validateCardUniqueComponentTypes(cardIdx);
    };

    window.populateComponentTypes = function(cardIdx, itemIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        const row = card.querySelector(`.item-row[data-item-idx="${itemIdx}"]`);
        if (!row) return;
        const compTypeSelect = row.querySelector('.js-component-type-select');
        if (!compTypeSelect) return;
        
        const pcComponentsStr = card.dataset.pcComponents;
        const pcComponents = pcComponentsStr ? JSON.parse(pcComponentsStr) : null;
        
        let types = [];
        const slotLabels = {
            processor: 'Processor',
            ram: 'RAM',
            ram2: 'RAM (Slot 2)',
            ssd: 'SSD',
            hdd: 'HDD',
            motherboard: 'Motherboard',
            vga: 'VGA',
            cpu_fan: 'CPU Fan',
            powersupply: 'Power Supply'
        };
        
        if (pcComponents && pcComponents.length > 0) {
            types = pcComponents.map(c => ({ value: c.slot, label: slotLabels[c.slot] || ucFirst(c.slot) }));
        } else {
            types = [
                { value: 'processor', label: 'Processor' },
                { value: 'ram', label: 'RAM' },
                { value: 'ssd', label: 'SSD' },
                { value: 'hdd', label: 'HDD' },
                { value: 'motherboard', label: 'Motherboard' },
                { value: 'vga', label: 'VGA' },
                { value: 'cpu_fan', label: 'CPU Fan' },
                { value: 'powersupply', label: 'Power Supply' }
            ];
        }
        
        compTypeSelect.innerHTML = '<option value="">-- Choose Component Type --</option>' +
            types.map(t => `<option value="${t.value}">${t.label}</option>`).join('');
    };

    window.handleComponentTypeChange = function(cardIdx, itemIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        const row = card.querySelector(`.item-row[data-item-idx="${itemIdx}"]`);
        if (!row) return;
        
        const compTypeSelect = row.querySelector('.js-component-type-select');
        const componentType = compTypeSelect.value;
        
        const assetSelect = row.querySelector('.js-asset-select');
        assetSelect.innerHTML = '<option value="">-- Choose Asset --</option>';
        
        const qtyInput = row.querySelector('.js-qty-input');
        qtyInput.value = 1;
        qtyInput.readOnly = false;
        row.querySelector('.js-serial-container').style.display = 'none';
        row.querySelector('.js-serial-list').innerHTML = '';
        
        if (!componentType) return;
        
        const pcComponentsStr = card.dataset.pcComponents;
        const pcComponents = pcComponentsStr ? JSON.parse(pcComponentsStr) : null;
        
        let filteredAssets = [];
        
        if (pcComponents && pcComponents.length > 0) {
            const mappedType = (componentType === 'ram2') ? 'ram' : componentType;
            const comp = pcComponents.find(c => c.slot === componentType);
            if (comp) {
                const asset = rrLabAssets.find(a => a.asset_id == comp.asset_id);
                if (asset) {
                    filteredAssets.push(asset);
                } else {
                    filteredAssets.push({
                        asset_id: comp.asset_id,
                        name: comp.name,
                        category: comp.category,
                        component_type: mappedType,
                        stock: 1
                    });
                }
            }
        } else {
            filteredAssets = rrLabAssets.filter(a => a.category === 'component-pc' && a.component_type === componentType);
        }
        
        assetSelect.innerHTML = '<option value="">-- Choose Asset --</option>' +
            filteredAssets.map(a => `<option value="${a.asset_id}">${a.name}</option>`).join('');
            
        if (filteredAssets.length === 1) {
            assetSelect.value = filteredAssets[0].asset_id;
            handleCardAssetChange(cardIdx, itemIdx);
        }
        
        validateCardUniqueAssets(cardIdx);
        validateCardUniqueComponentTypes(cardIdx);
    };

    window.validateCardUniqueAssets = function(cardIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        if (!card) return;
        const selects = card.querySelectorAll('.js-asset-select');
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

    window.validateCardUniqueComponentTypes = function(cardIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        if (!card) return;
        const selects = card.querySelectorAll('.js-component-type-select');
        if (selects.length === 0) return;
        
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

    window.handleCardAssetChange = function(cardIdx, itemIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        const row = card.querySelector(`.item-row[data-item-idx="${itemIdx}"]`);
        if (!row) return;
        
        const assetSelect = row.querySelector('.js-asset-select');
        const assetId = assetSelect.value;
        const stockInput = row.querySelector('.js-stock-input');
        const qtyInput = row.querySelector('.js-qty-input');
        const serialContainer = row.querySelector('.js-serial-container');
        const serialList = row.querySelector('.js-serial-list');
        
        serialContainer.style.display = 'none';
        serialList.innerHTML = '';
        stockInput.value = '';
        qtyInput.value = 1;
        qtyInput.readOnly = false;
        
        if (!assetId) {
            validateCardUniqueAssets(cardIdx);
            return;
        }
        
        const asset = rrLabAssets.find(a => a.asset_id == assetId);
        if (!asset) return;
        
        const category = asset.category;
        const usesSerial = ['electronic', 'pc', 'non-electronic'].includes(category);
        
        const condSelect = row.querySelector('.js-condition-select');
        const condition = condSelect.value;
        
        if (usesSerial) {
            serialContainer.style.display = 'block';
            qtyInput.readOnly = true;
            
            const labId = document.getElementById('rr_modal_lab_id').value;
            fetch(`/api/laboratory/${labId}/assets/${assetId}/serials-with-pc`)
                .then(res => res.json())
                .then(data => {
                    window.rrSerialData = window.rrSerialData || {};
                    window.rrSerialData[assetId] = data.serials || [];
                    
                    let filteredSerials = (data.serials || []).filter(s => s.condition === condition);
                    
                    const pcSelect = card.querySelector('.js-card-pc');
                    const pcId = pcSelect ? pcSelect.value : '';
                    if (pcId) {
                        filteredSerials = filteredSerials.filter(s => String(s.pc_id) === String(pcId));
                    }
                    
                    stockInput.value = filteredSerials.length;
                    
                    if (filteredSerials.length === 0) {
                        serialList.innerHTML = `<div style="color:#f87171; font-size:12px; padding:4px 0;">No ${condition} serial numbers available in this lab</div>`;
                        qtyInput.value = 0;
                        return;
                    }
                    
                    // If category is PC Component and PC is selected, we automatically find and preselect the serial_id
                    if (category === 'component-pc' && pcId) {
                        const compTypeSelect = row.querySelector('.js-component-type-select');
                        const componentType = compTypeSelect ? compTypeSelect.value : '';
                        const pcComponentsStr = card.dataset.pcComponents;
                        const pcComponents = pcComponentsStr ? JSON.parse(pcComponentsStr) : null;
                        if (pcComponents && componentType) {
                            const comp = pcComponents.find(c => c.slot === componentType);
                            if (comp && comp.serial_id) {
                                const matchingSerial = filteredSerials.find(s => s.id == comp.serial_id);
                                if (matchingSerial) {
                                    addCardSerialSelect(cardIdx, itemIdx, comp.serial_id);
                                    return;
                                }
                            }
                        }
                    }
                    addCardSerialSelect(cardIdx, itemIdx);
                })
                .catch(() => alert('Failed to load serial numbers.'));
        } else {
            const stock = getAvailableStockForAssetAndCondition(asset, condition);
            stockInput.value = stock;
            updateCardQtyStyle(row, stock, parseInt(qtyInput.value));
        }
        
        validateCardUniqueAssets(cardIdx);
    };

    window.addCardSerialSelect = function(cardIdx, itemIdx, preselectedValue = null) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        const row = card.querySelector(`.item-row[data-item-idx="${itemIdx}"]`);
        if (!row) return;
        
        const list = row.querySelector('.js-serial-list');
        const assetSelect = row.querySelector('.js-asset-select');
        const assetId = assetSelect.value;
        const serials = (window.rrSerialData && window.rrSerialData[assetId]) ? window.rrSerialData[assetId] : [];
        
        if (serials.length === 0) return;
        
        const condSelect = row.querySelector('.js-condition-select');
        const condition = condSelect.value;
        
        const pcSelect = card.querySelector('.js-card-pc');
        const pcId = pcSelect ? pcSelect.value : '';
        let filteredSerials = serials.filter(s => s.condition === condition);
        if (pcId) {
            filteredSerials = filteredSerials.filter(s => String(s.pc_id) === String(pcId));
        }

        if (filteredSerials.length === 0) {
            alert('No serial numbers for this asset match the condition and PC constraints.');
            return;
        }
        
        const rowId = Date.now() + Math.random().toString(36).substr(2, 5);
        const subRow = document.createElement('div');
        subRow.id = `rr_serial_row_${rowId}`;
        subRow.style.cssText = 'display:flex; gap:6px; align-items:center; margin-bottom:4px;';
        
        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'flex:1; display:flex; gap:6px; align-items:center;';
        
        const select = document.createElement('select');
        select.className = 'js-serial-picker-select';
        select.style.cssText = 'flex:1; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);';
        select.required = true;
        select.innerHTML = '<option value="">-- Choose Kode Inventaris --</option>' +
            filteredSerials.map(s => {
                const pcLabel = s.pc_sku ? ` - (PC: ${s.pc_sku})` : '';
                return `<option value="${s.id}">${s.serial_number}${pcLabel}</option>`;
            }).join('');
            
        if (preselectedValue) {
            select.value = preselectedValue;
        }
        
        select.addEventListener('change', () => {
            validateCardUniqueSerials(row);
        });
        
        wrapper.appendChild(select);
        
        const scanBtn = document.createElement('button');
        scanBtn.type = 'button';
        scanBtn.style.cssText = 'background:none; border:none; cursor:pointer; color:#111B4C; padding:4px; display:inline-flex; align-items:center; justify-content:center;';
        scanBtn.title = 'Scan QR Code';
        scanBtn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                <path d="M3 7V5a2 2 0 0 1 2-2h2m10 0h2a2 2 0 0 1 2 2v2m0 10v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2" />
                <rect x="7" y="7" width="10" height="10" rx="1" />
            </svg>`;
        scanBtn.onclick = () => openQrScannerFor(select);
        wrapper.appendChild(scanBtn);
        
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
            subRow.remove();
            updateCardQtyFromSerials(row);
            validateCardUniqueSerials(row);
        };
        
        subRow.appendChild(wrapper);
        subRow.appendChild(removeBtn);
        list.appendChild(subRow);
        
        updateCardQtyFromSerials(row);
        validateCardUniqueSerials(row);
    };

    window.updateCardQtyFromSerials = function(row) {
        const list = row.querySelector('.js-serial-list');
        const count = list.querySelectorAll('select').length;
        const qtyInput = row.querySelector('.js-qty-input');
        qtyInput.value = count;
    };

    window.validateCardUniqueSerials = function(row) {
        const list = row.querySelector('.js-serial-list');
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

    window.getAvailableStockForAssetAndCondition = function(asset, condition) {
        if (!asset) return 0;
        if (condition === 'damaged') {
            return asset.stock_damaged ?? 0;
        } else if (condition === 'lost') {
            return asset.stock_loss ?? 0;
        } else {
            return asset.stock_good ?? 0;
        }
    };

    window.handleCardConditionChange = function(cardIdx, itemIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        const row = card.querySelector(`.item-row[data-item-idx="${itemIdx}"]`);
        if (!row) return;
        
        const assetSelect = row.querySelector('.js-asset-select');
        const assetId = assetSelect.value;
        if (!assetId) return;
        
        const asset = rrLabAssets.find(a => a.asset_id == assetId);
        if (!asset) return;
        
        const category = asset.category;
        const usesSerial = ['electronic', 'pc', 'non-electronic'].includes(category);
        
        const condSelect = row.querySelector('.js-condition-select');
        const condition = condSelect.value;
        
        const qtyInput = row.querySelector('.js-qty-input');
        const stockInput = row.querySelector('.js-stock-input');
        
        if (usesSerial) {
            const serialList = row.querySelector('.js-serial-list');
            serialList.innerHTML = '';
            qtyInput.value = 0;
            
            const serials = (window.rrSerialData && window.rrSerialData[assetId]) ? window.rrSerialData[assetId] : [];
            const pcSelect = card.querySelector('.js-card-pc');
            const pcId = pcSelect ? pcSelect.value : '';
            
            let filteredSerials = serials.filter(s => s.condition === condition);
            if (pcId) {
                filteredSerials = filteredSerials.filter(s => String(s.pc_id) === String(pcId));
            }
            
            stockInput.value = filteredSerials.length;
            
            if (filteredSerials.length === 0) {
                serialList.innerHTML = `<div style="color:#f87171; font-size:12px; padding:4px 0;">No ${condition} serial numbers available in this lab</div>`;
                return;
            }
            addCardSerialSelect(cardIdx, itemIdx);
        } else {
            const stock = getAvailableStockForAssetAndCondition(asset, condition);
            stockInput.value = stock;
            updateCardQtyStyle(row, stock, parseInt(qtyInput.value));
        }
    };

    window.handleCardQtyChange = function(cardIdx, itemIdx) {
        const card = document.querySelector(`.tr-category-card[data-card-index="${cardIdx}"]`);
        const row = card.querySelector(`.item-row[data-item-idx="${itemIdx}"]`);
        if (!row) return;
        const assetSelect = row.querySelector('.js-asset-select');
        const assetId = assetSelect.value;
        if (!assetId) return;
        const asset = rrLabAssets.find(a => a.asset_id == assetId);
        if (!asset) return;
        
        const condition = row.querySelector('.js-condition-select').value;
        const stock = getAvailableStockForAssetAndCondition(asset, condition);
        const qty = parseInt(row.querySelector('.js-qty-input').value);
        updateCardQtyStyle(row, stock, qty);
    };

    function updateCardQtyStyle(row, stock, qty) {
        const stockInput = row.querySelector('.js-stock-input');
        const qtyInput = row.querySelector('.js-qty-input');
        if (stock !== null && qty > stock) {
            stockInput.style.color = '#dc2626';
            qtyInput.style.background = '#fee2e2';
            qtyInput.style.borderColor = '#dc2626';
        } else {
            stockInput.style.color = 'var(--text-muted)';
            qtyInput.style.background = 'var(--bg-input)';
            qtyInput.style.borderColor = 'var(--border-color)';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const labIdInput = document.getElementById('rr_modal_lab_id');
        if (labIdInput && labIdInput.value) {
            handleRRModalLabChange();
        }
        
        const createForm = document.querySelector('#addReturnModal form');
        if (createForm) {
            createForm.addEventListener('submit', function(e) {
                let index = 0;
                const cards = document.querySelectorAll('.tr-category-card');
                cards.forEach(card => {
                    const rows = card.querySelectorAll('.item-row');
                    rows.forEach(row => {
                        const assetSel = row.querySelector('.js-asset-select');
                        const qtyInput = row.querySelector('.js-qty-input');
                        const conditionSelect = row.querySelector('.js-condition-select');
                        const reasonInput = row.querySelector('.js-reason-input');
                        
                        if (assetSel && assetSel.value) {
                            assetSel.name = `items[${index}][asset_id]`;
                            qtyInput.name = `items[${index}][quantity]`;
                            conditionSelect.name = `items[${index}][condition]`;
                            reasonInput.name = `items[${index}][reason]`;
                            
                            const serialSelects = row.querySelectorAll('.js-serial-picker-select');
                            serialSelects.forEach(sel => {
                                sel.name = `items[${index}][serial_number_ids][]`;
                            });
                            index++;
                        }
                    });
                });
                
                if (index === 0) {
                    e.preventDefault();
                    alert('Please select at least one asset to return.');
                }
            });
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
