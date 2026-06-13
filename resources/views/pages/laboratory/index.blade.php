@extends('panel.content')
@section('title', 'Laboratory')

@section('content')

@php
$isSPV = auth()->user()->role === 'spv inventory';
$electronicAssets    = $allAssets->filter(fn($a) => $a->asset_category === 'electronic')->values();
$nonElectronicAssets = $allAssets->filter(fn($a) => $a->asset_category !== 'electronic')->values();
@endphp

<div class="db-wrap">
    <div class="db-card db-table-card">

        {{-- header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px;">
            <h2 class="db-card-title" style="margin:0">Laboratory List</h2>
            <div style="display:flex; gap:10px; align-items:center;">
                <div style="position:relative;">
                    <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af;"
                         width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search..." oninput="filterTable()"
                           style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 14px 8px 32px; font-size:13px; outline:none; width:200px;">
                </div>
                <button style="display:flex; align-items:center; gap:6px; border:1px solid #e5e7eb; background:#fff; border-radius:8px; padding:8px 14px; font-size:13px; cursor:pointer; color:#374151;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Filter
                </button>
                @if($isSPV)
                <button onclick="openCreateModal()"
                        style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:600;">
                    + Add Lab
                </button>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div style="margin:0 24px 12px; background:#dcfce7; color:#166534; border-radius:8px; padding:10px 16px; font-size:13px;">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div style="margin:0 24px 12px; background:#fee2e2; color:#991b1b; border-radius:8px; padding:10px 16px; font-size:13px;">
            {{ session('error') }}
        </div>
        @endif

        @if($isSPV)
        <div style="display:flex; align-items:center; gap:12px; padding:0 24px 12px;">
            <button type="button" onclick="submitBulkDelete()"
                    style="background:#dc2626; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:600;">
                🗑 Hapus Terpilih
            </button>
        </div>
        @endif

        <div class="table-wrap">
            <table class="db-table" id="labTable">
                <thead>
                    <tr>
                        @if($isSPV)
                        <th class="th-check" style="width:40px;">
                            <input type="checkbox" class="db-checkbox" id="checkAll" onchange="toggleAll(this)">
                        </th>
                        @endif
                        <th>Name</th>
                        <th>PC Capacity</th>
                        <th>Admin</th>
                        <th>Active</th>
                        <th>Inactive</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laboratories as $lab)
                    @php
                        $isMyLab   = in_array($lab->id, $myLabIds);
                        $adminUser = $lab->users->firstWhere('role', 'admin');
                    @endphp
                    <tr>
                        @if($isSPV)
                        <td class="th-check">
                            <input type="checkbox" name="ids[]" value="{{ $lab->id }}" class="db-checkbox row-check" form="bulkDeleteForm">
                        </td>
                        @endif
                        <td style="font-weight:600;">{{ $lab->lab_name }}</td>
                        <td>{{ $lab->capacity }} PC</td>
                        <td>{{ $adminUser?->name ?? '-' }}</td>
                        <td><span style="color:#16a34a; font-weight:600;">{{ $lab->total_pc_active ?? 0 }}</span></td>
                        <td><span style="color:#dc2626; font-weight:600;">{{ $lab->total_pc_inactive ?? 0 }}</span></td>
                        <td>
                            <div class="action-btns">
                                @if($isSPV || $isMyLab)
                                    <a href="{{ route('laboratory.show', $lab->id) }}"
                                       class="action-btn action-edit" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('laboratory.destroy', $lab->id) }}"
                                          onsubmit="return confirm('Hapus lab {{ addslashes($lab->lab_name) }}?')"
                                          style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn action-delete" title="Hapus">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                <path d="M10 11v6M14 11v6"/>
                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('laboratory.show', $lab->id) }}"
                                       class="action-btn" title="View" style="color:#6b7280;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isSPV ? 7 : 6 }}" style="text-align:center; padding:40px; color:#9ca3af; font-size:13px;">
                            Belum ada laboratorium
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($laboratories->hasPages())
        <div style="padding:16px 24px;">
            {{ $laboratories->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Form bulk delete tersembunyi --}}
@if($isSPV)
<form id="bulkDeleteForm" method="POST" action="{{ route('laboratory.bulkDestroy') }}" style="display:none;">
    @csrf @method('DELETE')
</form>
@endif

{{-- ══ MODAL CREATE (SPV only) ══ --}}
@if($isSPV)
<div id="modal-create" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:560px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15);">

        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Create Laboratory</h3>
            <button onclick="closeCreateModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px;">&times;</button>
        </div>

        <div style="display:flex; padding:16px 24px 0; gap:6px;">
            <div id="clab-bar-1" style="flex:1; height:4px; border-radius:2px; background:#111B4C;"></div>
            <div id="clab-bar-2" style="flex:1; height:4px; border-radius:2px; background:#e5e7eb;"></div>
            <div id="clab-bar-3" style="flex:1; height:4px; border-radius:2px; background:#e5e7eb;"></div>
        </div>

        <form method="POST" action="{{ route('laboratory.store') }}" id="createLabForm">
            @csrf

            {{-- Step 1: General --}}
            <div id="clab-step-1" style="padding:24px;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 20px; text-align:center;">General Information</h4>
                <div style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Laboratory Name:</label>
                    <input type="text" name="lab_name" id="clab_name" placeholder="Enter here..."
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">PC Capacity:</label>
                    <input type="number" name="capacity" id="clab_capacity" placeholder="Enter here..." min="1"
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:24px;">
                    <button type="button" onclick="closeCreateModal()"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                    <button type="button" onclick="clabGoStep(2)"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Next</button>
                </div>
            </div>

            {{-- Step 2: PC --}}
            <div id="clab-step-2" style="display:none; padding:24px; max-height:55vh; overflow-y:auto;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 16px; text-align:center;">PC Information</h4>
                <div id="clab-pc-list"></div>
                <div style="display:flex; justify-content:space-between; gap:8px; margin-top:24px;">
                    <button type="button" onclick="clabGoStep(1)"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Back</button>
                    <button type="button" onclick="clabGoStep(3)"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Next</button>
                </div>
            </div>

            {{-- Step 3: Asset --}}
            <div id="clab-step-3" style="display:none; padding:24px; max-height:55vh; overflow-y:auto;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 16px; text-align:center;">Asset Information</h4>

                <div style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:12px; overflow:hidden;">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                        <span style="font-size:13px; font-weight:600; color:#374151;">Electronic Category</span>
                        <button type="button" onclick="addCreateAssetRow('electronic')"
                                style="background:#111B4C; color:#fff; border:none; border-radius:6px; padding:5px 12px; font-size:13px; cursor:pointer;">+</button>
                    </div>
                    <div id="clab-electronic-rows" style="padding:12px; display:flex; flex-direction:column; gap:10px;"></div>
                </div>

                <div style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                        <span style="font-size:13px; font-weight:600; color:#374151;">Non-Electronic Category</span>
                        <button type="button" onclick="addCreateAssetRow('non-electronic')"
                                style="background:#111B4C; color:#fff; border:none; border-radius:6px; padding:5px 12px; font-size:13px; cursor:pointer;">+</button>
                    </div>
                    <div id="clab-non-electronic-rows" style="padding:12px; display:flex; flex-direction:column; gap:10px;"></div>
                </div>

                <div style="display:flex; justify-content:space-between; gap:8px; margin-top:24px;">
                    <button type="button" onclick="clabGoStep(2)"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Back</button>
                    <button type="submit"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
const electronicOptions   = @json($electronicAssets->map(fn($a) => ['id'=>$a->id,'name'=>$a->asset_name])->values());
const nonElecOptions      = @json($nonElectronicAssets->map(fn($a) => ['id'=>$a->id,'name'=>$a->asset_name])->values());
let   createAssetCounter  = 0;

function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#labTable tbody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
function toggleAll(m) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = m.checked);
}

function submitBulkDelete() {
    const checked = document.querySelectorAll('.row-check:checked');
    if (checked.length === 0) {
        alert('Pilih minimal satu lab.');
        return;
    }
    if (!confirm('Hapus ' + checked.length + ' lab yang dipilih? Semua asset dan PC akan dikembalikan ke stok.')) {
        return;
    }
    
    const form = document.getElementById('bulkDeleteForm');
    // Hapus input lama
    form.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
    // Tambah input baru
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });
    
    form.submit();
}

@if($isSPV)
// Create Modal
function openCreateModal() {
    document.getElementById('modal-create').style.display = 'flex';
    clabGoStep(1);
}
function closeCreateModal() {
    document.getElementById('modal-create').style.display = 'none';
    document.getElementById('createLabForm').reset();
    document.getElementById('clab-pc-list').innerHTML = '';
    document.getElementById('clab-electronic-rows').innerHTML = '';
    document.getElementById('clab-non-electronic-rows').innerHTML = '';
    createAssetCounter = 0;
}

function clabGoStep(step) {
    [1,2,3].forEach(s => {
        document.getElementById(`clab-step-${s}`).style.display = s === step ? 'block' : 'none';
        document.getElementById(`clab-bar-${s}`).style.background =
            s < step ? '#98083D' : s === step ? '#111B4C' : '#e5e7eb';
    });
    if (step === 2) {
        const cap = parseInt(document.getElementById('clab_capacity').value);
        if (!cap || cap < 1) { alert('Isi kapasitas terlebih dahulu.'); clabGoStep(1); return; }
        buildCreatePcList(cap);
    }
}

function buildCreatePcList(cap) {
    const c = document.getElementById('clab-pc-list');
    c.innerHTML = '';
    for (let i = 0; i < cap; i++) {
        c.innerHTML += `
        <details style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:10px;">
            <summary style="padding:10px 14px; font-size:13px; font-weight:600; cursor:pointer; background:#f9fafb;">
                PC-${String(i).padStart(2,'0')}
            </summary>
            <div style="padding:14px; display:grid; gap:10px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Type</label>
                        <select name="pcs[${i}][type_pc]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px;">
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="dosen">Dosen</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Processor</label>
                        <input type="text" name="pcs[${i}][processor]" placeholder="e.g. i3-8100"
                               style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                    </div>
                </div>
                ${['ram','ssd','motherboard','vga','cpu_fan','powersupply'].map(f => `
                <div>
                    <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">${f.replace('_',' ').replace(/\\b\\w/g,c=>c.toUpperCase())}</label>
                    <input type="text" name="pcs[${i}][${f}]"
                           style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                </div>`).join('')}
            </div>
        </details>`;
    }
}

function addCreateAssetRow(type) {
    const idx       = createAssetCounter++;
    const container = document.getElementById(type === 'electronic' ? 'clab-electronic-rows' : 'clab-non-electronic-rows');
    const options   = type === 'electronic' ? electronicOptions : nonElecOptions;
    const div = document.createElement('div');
    div.className = 'asset-row';
    div.style.cssText = 'border:1px solid #e5e7eb; border-radius:8px; padding:12px; position:relative;';
    div.innerHTML = `
        <button type="button" onclick="this.closest('.asset-row').remove()"
                style="position:absolute; top:6px; right:8px; background:none; border:none; cursor:pointer; color:#9ca3af; font-size:16px;">×</button>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div>
                <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Asset Name:</label>
                <select name="lab_assets[${idx}][asset_id]"
                        style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                    <option value="">Choose asset...</option>
                    ${options.map(o => `<option value="${o.id}">${o.name}</option>`).join('')}
                </select>
            </div>
            <div>
                <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Quantity:</label>
                <input type="number" name="lab_assets[${idx}][quantity]" value="0" min="0"
                       style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
            </div>
        </div>`;
    container.appendChild(div);
}

document.getElementById('modal-create').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeCreateModal();
});
@endif
</script>
@endpush