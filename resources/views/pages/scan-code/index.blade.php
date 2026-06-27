@extends('panel.content')

@section('title', 'Scan Code')

@push('styles')
<style>
    /* ── page wrapper ── */
    .sc-page {
        max-width: 860px;
        margin: 0 auto;
    }

    /* ── header ── */
    .sc-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 28px;
    }
    .sc-header-icon {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, #111B4C 0%, #2563eb 100%);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 16px rgba(17,27,76,.25);
    }
    .sc-header-icon svg { color: #fff; }
    .sc-header-title { font-size: 1.45rem; font-weight: 700; color: var(--text-primary); }
    .sc-header-sub   { font-size: .825rem; color: var(--text-muted); margin-top: 2px; }

    /* ── card ── */
    .sc-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }

    /* ── tabs ── */
    .sc-tabs {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-table-header);
    }
    .sc-tab {
        flex: 1;
        padding: 14px 0;
        text-align: center;
        font-size: .88rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        border: none;
        background: transparent;
        border-bottom: 3px solid transparent;
        transition: color .2s, border-color .2s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .sc-tab:hover { color: var(--text-primary); }
    .sc-tab.active {
        color: #111B4C;
        border-bottom-color: #111B4C;
        background: var(--bg-card);
    }
    .dark .sc-tab.active { color: #60a5fa; border-bottom-color: #60a5fa; }

    /* ── tab panels ── */
    .sc-panel { padding: 28px; display: none; }
    .sc-panel.active { display: block; }

    /* ── camera view ── */
    #reader {
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        background: #000;
        min-height: 240px;
    }
    /* Override html5-qrcode default styles */
    #reader video { border-radius: 12px; }
    #reader img { border-radius: 12px; }

    .sc-cam-controls {
        display: flex;
        gap: 10px;
        margin-top: 14px;
        flex-wrap: wrap;
    }
    .sc-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 20px;
        border-radius: 10px;
        font-size: .85rem; font-weight: 600;
        cursor: pointer; border: none; transition: all .18s;
    }
    .sc-btn-primary {
        background: linear-gradient(135deg, #111B4C, #2563eb);
        color: #fff;
        box-shadow: 0 3px 10px rgba(17,27,76,.25);
    }
    .sc-btn-primary:hover { opacity: .88; transform: translateY(-1px); }
    .sc-btn-danger {
        background: #fee2e2; color: #b91c1c;
        border: 1px solid #fca5a5;
    }
    .sc-btn-danger:hover { background: #fecaca; }
    .sc-btn-secondary {
        background: var(--bg-input);
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }
    .sc-btn-secondary:hover { border-color: #111B4C; color: #111B4C; }
    .dark .sc-btn-secondary:hover { border-color: #60a5fa; color: #60a5fa; }

    /* ── upload area ── */
    .sc-upload-area {
        border: 2px dashed var(--border-color);
        border-radius: 14px;
        padding: 48px 24px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        background: var(--bg-notes);
    }
    .sc-upload-area:hover, .sc-upload-area.dragover {
        border-color: #2563eb;
        background: rgba(37,99,235,.04);
    }
    .sc-upload-icon { margin: 0 auto 12px; color: var(--text-muted); }
    .sc-upload-title { font-size: .95rem; font-weight: 600; color: var(--text-primary); }
    .sc-upload-sub   { font-size: .8rem;  color: var(--text-muted); margin-top: 4px; }
    .sc-upload-preview {
        width: 100%; max-height: 280px; object-fit: contain;
        border-radius: 10px; margin-top: 16px;
        border: 1px solid var(--border-color);
        display: none;
    }

    /* ── manual input ── */
    .sc-manual-row {
        display: flex; gap: 8px; margin-top: 18px;
    }
    .sc-manual-input {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        font-size: .875rem;
        background: var(--bg-input);
        color: var(--text-primary);
        outline: none;
        transition: border-color .2s;
    }
    .sc-manual-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .sc-manual-label {
        font-size: .8rem; font-weight: 600; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: .04em;
        margin-bottom: 6px; margin-top: 20px; display: block;
    }

    /* ── divider ── */
    .sc-or {
        display: flex; align-items: center; gap: 12px;
        margin: 20px 0; color: var(--text-muted); font-size: .8rem;
    }
    .sc-or::before, .sc-or::after {
        content: ''; flex: 1; height: 1px; background: var(--border-color);
    }

    /* ── result panel ── */
    .sc-result {
        margin-top: 24px;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        animation: scFadeIn .3s ease;
        display: none;
    }
    .sc-result.show { display: block; }
    @keyframes scFadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* result: not found */
    .sc-result-notfound {
        padding: 24px;
        background: #fff7ed;
        display: flex; align-items: center; gap: 14px;
    }
    .dark .sc-result-notfound { background: rgba(234,88,12,.08); }
    .sc-result-notfound-icon { color: #ea580c; flex-shrink: 0; }
    .sc-result-notfound-title { font-weight: 700; color: #ea580c; }
    .sc-result-notfound-msg   { font-size: .83rem; color: var(--text-muted); margin-top: 2px; }

    /* result: found */
    .sc-result-header {
        padding: 16px 20px;
        background: linear-gradient(135deg, #111B4C 0%, #1d4ed8 100%);
        display: flex; align-items: center; gap: 14px;
    }
    .sc-result-badge {
        padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 700;
        letter-spacing: .05em; text-transform: uppercase;
    }
    .badge-sku    { background: rgba(255,255,255,.2); color: #fff; }
    .badge-serial { background: rgba(99,255,180,.2); color: #86efac; }
    .sc-result-asset-name { font-size: 1.1rem; font-weight: 700; color: #fff; }
    .sc-result-sku-val    { font-size: .8rem; color: rgba(255,255,255,.7); margin-top: 2px; }

    .sc-result-body { padding: 20px; background: var(--bg-card); }
    .sc-info-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr));
        gap: 12px;
    }
    .sc-info-item { }
    .sc-info-label { font-size: .72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; }
    .sc-info-value { font-size: .9rem; font-weight: 600; color: var(--text-primary); margin-top: 3px; }

    /* status pill */
    .sc-pill {
        display: inline-block;
        padding: 2px 10px; border-radius: 20px;
        font-size: .72rem; font-weight: 700; text-transform: capitalize;
    }
    .pill-available  { background: #dcfce7; color: #16a34a; }
    .pill-in-use     { background: #dbeafe; color: #1d4ed8; }
    .pill-damaged    { background: #fee2e2; color: #b91c1c; }
    .pill-lost       { background: #fef3c7; color: #b45309; }
    .dark .pill-available { background: rgba(22,163,74,.15); }
    .dark .pill-in-use    { background: rgba(29,78,216,.15); }
    .dark .pill-damaged   { background: rgba(185,28,28,.15); }
    .dark .pill-lost      { background: rgba(180,83,9,.15);  }

    .sc-divider { height: 1px; background: var(--border-color); margin: 16px 0; }

    .sc-serial-section { margin-top: 4px; }
    .sc-serial-title { font-size: .78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 10px; }

    /* ── scanning indicator ── */
    .sc-scanning-badge {
        display: none; align-items: center; gap: 8px;
        background: #f0fdf4; border: 1px solid #86efac;
        border-radius: 8px; padding: 8px 14px;
        font-size: .82rem; font-weight: 600; color: #16a34a;
        margin-top: 12px;
    }
    .sc-scanning-badge.show { display: flex; }
    .dark .sc-scanning-badge { background: rgba(22,163,74,.1); }
    .sc-scanning-dot {
        width: 8px; height: 8px; border-radius: 50%; background: #22c55e;
        animation: scPulse 1.2s ease-in-out infinite;
    }
    @keyframes scPulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%       { transform: scale(1.4); opacity: .6; }
    }
</style>
@endpush

@section('content')
<div class="sc-page">

    {{-- Header --}}
    <div class="sc-header">
        <div class="sc-header-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 5h2M3 9h2M3 13h2M3 17h2M3 21h2" />
                <rect x="7" y="3" width="4" height="18" rx="1" />
                <path d="M13 5h2M13 9h2M13 13h2M13 17h2M13 21h2" />
                <rect x="17" y="3" width="4" height="18" rx="1" />
            </svg>
        </div>
        <div>
            <div class="sc-header-title">Scan Code</div>
            <div class="sc-header-sub">Scan QR Code untuk melihat detail barang inventaris</div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="sc-card">

        {{-- Tabs --}}
        <div class="sc-tabs">
            <button class="sc-tab active" id="tabCamera" onclick="switchTab('camera')">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                    <circle cx="12" cy="13" r="4" />
                </svg>
                Kamera
            </button>
            <button class="sc-tab" id="tabUpload" onclick="switchTab('upload')">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="16 16 12 12 8 16" />
                    <line x1="12" y1="12" x2="12" y2="21" />
                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3" />
                </svg>
                Upload Gambar
            </button>
        </div>

        {{-- ── Camera Panel ── --}}
        <div class="sc-panel active" id="panelCamera">
            <div id="reader"></div>

            <div class="sc-scanning-badge" id="scanningBadge">
                <div class="sc-scanning-dot"></div>
                Kamera aktif — arahkan QR Code ke kamera
            </div>

            <div class="sc-cam-controls">
                <button class="sc-btn sc-btn-primary" id="btnStart" onclick="startCamera()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                        <circle cx="12" cy="13" r="4" />
                    </svg>
                    Aktifkan Kamera
                </button>
                <button class="sc-btn sc-btn-danger" id="btnStop" onclick="stopCamera()" style="display:none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                    </svg>
                    Stop Kamera
                </button>
                <button class="sc-btn sc-btn-secondary" id="btnTorch" onclick="toggleTorch()" style="display:none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                    <span id="lblTorch">Senter</span>
                </button>
            </div>

            <div class="sc-or">atau ketik manual</div>

            <label class="sc-manual-label" for="manualInputCamera">Masukkan Data QR Code / SKU / Serial Number</label>
            <div class="sc-manual-row">
                <input id="manualInputCamera" class="sc-manual-input" type="text"
                    placeholder="Contoh: ELC-0001 atau SN-ABC123"
                    onkeydown="if(event.key==='Enter') doLookup(this.value)">
                <button class="sc-btn sc-btn-primary" onclick="doLookup(document.getElementById('manualInputCamera').value)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    Cari
                </button>
            </div>

            {{-- Result Panel (Camera) --}}
            <div class="sc-result" id="resultCamera"></div>
        </div>

        {{-- ── Upload Panel ── --}}
        <div class="sc-panel" id="panelUpload">
            <div class="sc-upload-area" id="uploadArea"
                onclick="document.getElementById('fileInput').click()"
                ondragover="onDragOver(event)"
                ondragleave="onDragLeave(event)"
                ondrop="onDrop(event)">
                <svg class="sc-upload-icon" width="44" height="44" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="16 16 12 12 8 16" />
                    <line x1="12" y1="12" x2="12" y2="21" />
                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3" />
                </svg>
                <div class="sc-upload-title">Klik atau drag & drop gambar QR Code</div>
                <div class="sc-upload-sub">Mendukung JPG, PNG, GIF, WebP — Pastikan gambar QR Code terlihat jelas.</div>
                <img id="uploadPreview" class="sc-upload-preview" alt="Preview">
            </div>
            <input type="file" id="fileInput" accept="image/*" style="display:none" onchange="onFileSelected(event)">

            <div class="sc-cam-controls" style="margin-top: 14px;" id="uploadActions" style="display:none;">
                <button class="sc-btn sc-btn-primary" id="btnScanUpload" onclick="scanUploadedImage()" style="display:none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                    </svg>
                    Scan Gambar
                </button>
                <button class="sc-btn sc-btn-secondary" onclick="clearUpload()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v6M14 11v6" />
                    </svg>
                    Hapus
                </button>
            </div>

            <div class="sc-or">atau ketik manual</div>

            <label class="sc-manual-label" for="manualInputUpload">Masukkan Data QR Code / SKU / Serial Number</label>
            <div class="sc-manual-row">
                <input id="manualInputUpload" class="sc-manual-input" type="text"
                    placeholder="Contoh: ELC-0001 atau SN-ABC123"
                    onkeydown="if(event.key==='Enter') doLookup(this.value)">
                <button class="sc-btn sc-btn-primary" onclick="doLookup(document.getElementById('manualInputUpload').value)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    Cari
                </button>
            </div>

            {{-- Result Panel (Upload) --}}
            <div class="sc-result" id="resultUpload"></div>
        </div>

    </div>{{-- /sc-card --}}
</div>
@endsection

@push('scripts')
{{-- html5-qrcode library for camera, jsQR for robust image upload scanning --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
    /* ── state ── */
    let html5QrCode = null;
    let isCameraRunning = false;
    let isTorchOn = false;
    let currentTab = 'camera';
    let uploadedFile = null;

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const LOOKUP_URL = "{{ route('scan-code.lookup') }}";

    /* ════════════════════════════════════
       TAB SWITCHING
    ════════════════════════════════════ */
    function switchTab(tab) {
        currentTab = tab;

        // update tab buttons
        document.getElementById('tabCamera').classList.toggle('active', tab === 'camera');
        document.getElementById('tabUpload').classList.toggle('active', tab === 'upload');

        // show/hide panels
        document.getElementById('panelCamera').classList.toggle('active', tab === 'camera');
        document.getElementById('panelUpload').classList.toggle('active', tab === 'upload');

        // stop camera when switching away
        if (tab !== 'camera' && isCameraRunning) stopCamera();
    }

    /* ════════════════════════════════════
       CAMERA SCANNER
    ════════════════════════════════════ */
    async function startCamera() {
        if (isCameraRunning) return;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode('reader');
        }

        const config = {
            fps: 12,
            qrbox: { width: 260, height: 160 },
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.DATA_MATRIX,
            ],
        };

        try {
            await html5QrCode.start(
                { facingMode: 'environment' },
                config,
                onScanSuccess,
                onScanError
            );
            isCameraRunning = true;
            document.getElementById('btnStart').style.display = 'none';
            document.getElementById('btnStop').style.display  = 'inline-flex';
            document.getElementById('scanningBadge').classList.add('show');

            // Check torch/flashlight capability and show button if supported
            setTimeout(() => {
                let hasTorch = false;
                try {
                    const capabilities = html5QrCode.getRunningTrackCapabilities();
                    if (capabilities && capabilities.torch) {
                        hasTorch = true;
                    }
                } catch (e) {}

                if (!hasTorch) {
                    try {
                        const camCapabilities = html5QrCode.getRunningTrackCameraCapabilities();
                        if (camCapabilities && camCapabilities.torchFeature && camCapabilities.torchFeature().isSupported()) {
                            hasTorch = true;
                        }
                    } catch (e) {}
                }

                if (hasTorch) {
                    document.getElementById('btnTorch').style.display = 'inline-flex';
                }
            }, 500);
        } catch (err) {
            showAlert('Tidak dapat mengakses kamera: ' + err, 'error');
        }
    }

    async function stopCamera() {
        if (!isCameraRunning || !html5QrCode) return;
        try {
            await html5QrCode.stop();
        } catch(_) {}
        isCameraRunning = false;
        isTorchOn = false;
        document.getElementById('btnStart').style.display = 'inline-flex';
        document.getElementById('btnStop').style.display  = 'none';
        document.getElementById('btnTorch').style.display = 'none';
        document.getElementById('btnTorch').classList.remove('sc-btn-primary');
        document.getElementById('btnTorch').classList.add('sc-btn-secondary');
        document.getElementById('lblTorch').textContent = 'Senter';
        document.getElementById('scanningBadge').classList.remove('show');
    }

    async function toggleTorch() {
        if (!isCameraRunning || !html5QrCode) return;
        try {
            isTorchOn = !isTorchOn;
            await html5QrCode.applyVideoConstraints({
                advanced: [{ torch: isTorchOn }]
            });
            document.getElementById('lblTorch').textContent = isTorchOn ? 'Matikan Senter' : 'Senter';
            const btnTorch = document.getElementById('btnTorch');
            if (isTorchOn) {
                btnTorch.classList.add('sc-btn-primary');
                btnTorch.classList.remove('sc-btn-secondary');
            } else {
                btnTorch.classList.remove('sc-btn-primary');
                btnTorch.classList.add('sc-btn-secondary');
            }
        } catch (err) {
            console.error("Failed to toggle torch:", err);
            isTorchOn = !isTorchOn; // revert state
            alert("Senter tidak dapat diaktifkan pada perangkat/browser ini.");
        }
    }

    let lastScanned = '';
    let lastScannedTime = 0;

    function onScanSuccess(decodedText) {
        const now = Date.now();
        // debounce: ignore same code within 3 sec
        if (decodedText === lastScanned && now - lastScannedTime < 3000) return;
        lastScanned     = decodedText;
        lastScannedTime = now;

        // flash indicator
        document.getElementById('scanningBadge').style.background = 'rgba(37,99,235,.15)';
        setTimeout(() => document.getElementById('scanningBadge').style.background = '', 500);

        doLookup(decodedText);
    }

    function onScanError() { /* silent */ }

    /* ════════════════════════════════════
       UPLOAD SCANNER
    ════════════════════════════════════ */
    function onFileSelected(event) {
        const file = event.target.files[0];
        if (!file) return;
        uploadedFile = file;
        showPreview(file);
        document.getElementById('btnScanUpload').style.display = 'inline-flex';
        // auto-scan on file select
        scanUploadedFile(file);
    }

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('uploadPreview');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    function scanUploadedImage() {
        if (uploadedFile) scanUploadedFile(uploadedFile);
    }

    async function scanUploadedFile(file) {
        if (!file) return;

        showLoading('resultUpload', 'Memproses gambar...');

        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                // Draw to offscreen canvas
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d', { willReadFrequently: true });

                // Maximum width/height for performance while maintaining readability
                const MAX_DIMENSION = 1200;
                let width = img.width;
                let height = img.height;

                if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
                    const ratio = Math.min(MAX_DIMENSION / width, MAX_DIMENSION / height);
                    width = Math.round(width * ratio);
                    height = Math.round(height * ratio);
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                const imageData = ctx.getImageData(0, 0, width, height);
                // Use jsQR to decode the image data
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });

                if (code) {
                    doLookup(code.data);
                } else {
                    // Try to invert colors in case of weird QR
                    const codeInverted = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "invertFirst",
                    });
                    if (codeInverted) {
                        doLookup(codeInverted.data);
                    } else {
                        showResult('upload', null, 'QR Code tidak terdeteksi pada gambar. Coba gambar yang lebih jelas atau pastikan gambar berisi QR Code.');
                    }
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function clearUpload() {
        uploadedFile = null;
        document.getElementById('fileInput').value = '';
        document.getElementById('uploadPreview').style.display = 'none';
        document.getElementById('uploadPreview').src = '';
        document.getElementById('btnScanUpload').style.display = 'none';
        document.getElementById('resultUpload').classList.remove('show');
        document.getElementById('resultUpload').innerHTML = '';
    }

    /* drag & drop */
    function onDragOver(e)  { e.preventDefault(); document.getElementById('uploadArea').classList.add('dragover'); }
    function onDragLeave(e) { document.getElementById('uploadArea').classList.remove('dragover'); }
    function onDrop(e) {
        e.preventDefault();
        document.getElementById('uploadArea').classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            uploadedFile = file;
            showPreview(file);
            document.getElementById('btnScanUpload').style.display = 'inline-flex';
            scanUploadedFile(file);
        }
    }

    /* ════════════════════════════════════
       LOOKUP (AJAX)
    ════════════════════════════════════ */
    async function doLookup(barcode) {
        barcode = (barcode || '').trim();
        if (!barcode) return;

        const resultEl = currentTab === 'camera' ? 'resultCamera' : 'resultUpload';
        showLoading(resultEl, barcode);

        try {
            const resp = await fetch(LOOKUP_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ barcode }),
            });

            const data = await resp.json();
            showResult(currentTab === 'camera' ? 'camera' : 'upload', data, null);
        } catch(e) {
            showResult(currentTab === 'camera' ? 'camera' : 'upload', null, 'Terjadi kesalahan koneksi.');
        }
    }

    /* ════════════════════════════════════
       RENDER RESULT
    ════════════════════════════════════ */
    function showLoading(elId, barcode) {
        const el = document.getElementById('result' + (elId === 'resultCamera' ? 'Camera' : 'Upload'));
        el.innerHTML = `
            <div style="padding:20px; display:flex; align-items:center; gap:12px; background:var(--bg-card);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" style="animation:spin 1s linear infinite">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
                <span style="color:var(--text-muted); font-size:.875rem;">Mencari <strong style="color:var(--text-primary)">${escHtml(barcode)}</strong>...</span>
            </div>`;
        el.classList.add('show');
    }

    function showResult(tab, data, errMsg) {
        const elId = tab === 'camera' ? 'resultCamera' : 'resultUpload';
        const el = document.getElementById(elId);

        if (errMsg || !data) {
            el.innerHTML = notFoundHtml(errMsg || 'Tidak dapat memproses permintaan.', '');
            el.classList.add('show');
            return;
        }

        if (!data.found) {
            el.innerHTML = notFoundHtml(data.message || 'QR Code tidak sesuai.', '');
            el.classList.add('show');
            return;
        }

        // Found!
        const asset = data.asset;
        const serial = data.serial;
        const typeLabel = data.type === 'sku' ? 'Ditemukan via SKU' : 'Ditemukan via Serial Number';
        const badgeClass = data.type === 'sku' ? 'badge-sku' : 'badge-serial';

        const catLabel = {
            'electronic': 'Electronic',
            'non-electronic': 'Non-Electronic',
            'pc': 'PC',
            'component-pc': 'Component PC',
        }[asset.asset_category] ?? asset.asset_category;

        let serialSection = '';
        if (serial) {
            const conditionMap = { good: 'Baik', damaged: 'Rusak', lost: 'Hilang' };
            const statusMap    = { available: 'Available', 'in-use': 'In Use', damaged: 'Damaged', lost: 'Lost' };
            serialSection = `
                <div class="sc-divider"></div>
                <div class="sc-serial-section">
                    <div class="sc-serial-title">Detail Unit Serial</div>
                    <div class="sc-info-grid">
                        <div class="sc-info-item">
                            <div class="sc-info-label">Serial Number</div>
                            <div class="sc-info-value" style="font-family:monospace;">${escHtml(serial.serial_number)}</div>
                        </div>
                        <div class="sc-info-item">
                            <div class="sc-info-label">Kondisi</div>
                            <div class="sc-info-value">${escHtml(conditionMap[serial.condition] ?? serial.condition ?? '-')}</div>
                        </div>
                        <div class="sc-info-item">
                            <div class="sc-info-label">Status</div>
                            <div class="sc-info-value">
                                <span class="sc-pill ${pillClass(serial.status)}">${escHtml(statusMap[serial.status] ?? serial.status ?? '-')}</span>
                            </div>
                        </div>
                        ${serial.lab_name ? `
                        <div class="sc-info-item">
                            <div class="sc-info-label">Lokasi Lab</div>
                            <div class="sc-info-value">${escHtml(serial.lab_name)}</div>
                        </div>` : ''}
                        ${serial.slot ? `
                        <div class="sc-info-item">
                            <div class="sc-info-label">Slot PC</div>
                            <div class="sc-info-value">${escHtml(serial.slot)}</div>
                        </div>` : ''}
                        ${serial.notes ? `
                        <div class="sc-info-item" style="grid-column: 1/-1">
                            <div class="sc-info-label">Catatan</div>
                            <div class="sc-info-value">${escHtml(serial.notes)}</div>
                        </div>` : ''}
                    </div>
                </div>`;
        }

        el.innerHTML = `
            <div class="sc-result-header">
                <div style="flex:1;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span class="sc-result-badge ${badgeClass}">${escHtml(typeLabel)}</span>
                    </div>
                    <div class="sc-result-asset-name">${escHtml(asset.asset_name)}</div>
                    <div class="sc-result-sku-val">SKU: ${escHtml(asset.sku)}</div>
                </div>
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <div class="sc-result-body">
                <div class="sc-info-grid">
                    <div class="sc-info-item">
                        <div class="sc-info-label">Kategori</div>
                        <div class="sc-info-value">${escHtml(catLabel)}</div>
                    </div>
                    ${asset.component_type ? `
                    <div class="sc-info-item">
                        <div class="sc-info-label">Tipe Komponen</div>
                        <div class="sc-info-value">${escHtml(asset.component_type)}</div>
                    </div>` : ''}
                    ${!serial ? `
                    <div class="sc-info-item">
                        <div class="sc-info-label">Total Aset</div>
                        <div class="sc-info-value">${asset.total_asset ?? 0}</div>
                    </div>
                    <div class="sc-info-item">
                        <div class="sc-info-label">Kondisi Baik</div>
                        <div class="sc-info-value" style="color:#16a34a;">${asset.total_good ?? 0}</div>
                    </div>
                    <div class="sc-info-item">
                        <div class="sc-info-label">Rusak</div>
                        <div class="sc-info-value" style="color:#b91c1c;">${asset.total_damaged ?? 0}</div>
                    </div>
                    <div class="sc-info-item">
                        <div class="sc-info-label">Hilang</div>
                        <div class="sc-info-value" style="color:#b45309;">${asset.total_loss ?? 0}</div>
                    </div>` : ''}
                </div>
                ${serialSection}
            </div>`;
        el.classList.add('show');
    }

    function notFoundHtml(msg, sub) {
        return `
            <div class="sc-result-notfound">
                <svg class="sc-result-notfound-icon" width="36" height="36" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div>
                    <div class="sc-result-notfound-title">QR Code Tidak Sesuai</div>
                    <div class="sc-result-notfound-msg">${escHtml(msg)}</div>
                </div>
            </div>`;
    }

    /* ── helpers ── */
    function pillClass(status) {
        return { available: 'pill-available', 'in-use': 'pill-in-use', damaged: 'pill-damaged', lost: 'pill-lost' }[status] ?? 'pill-available';
    }
    function escHtml(str) {
        if (!str && str !== 0) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function showAlert(msg, type) {
        console.error(msg);
    }

    // spin animation for loading
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(style);

    // stop camera on page unload
    window.addEventListener('beforeunload', () => { if (isCameraRunning) stopCamera(); });
</script>
@endpush
