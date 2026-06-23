@extends('panel.content')
@section('title', auth()->user()->role === 'spv inventory' ? 'SPV Dashboard' : 'Staff Dashboard')

@section('content')

@php
$isSPV = auth()->user()->role === 'spv inventory';
$isStaffLab = !$isSPV && in_array($laboratory->id, $myLabIds);
$electronicAssets    = $allAssets->filter(fn($a) => $a->asset_category === 'electronic')->values();
$nonElectronicAssets = $allAssets->filter(fn($a) => $a->asset_category !== 'electronic')->values();
$existingElectric    = $laboratory->assets->filter(fn($a) => $a->asset_category === 'electronic')->values();
$existingNonElectric = $laboratory->assets->filter(fn($a) => $a->asset_category !== 'electronic')->values();
@endphp

<div class="db-wrap">

            {{-- ── HEADER ── --}}
    @include('pages.laboratory.header', [
        'laboratory'    => $laboratory,
        'totalActive'   => $totalActive,
        'totalInactive' => $totalInactive,
        'isSPV'         => $isSPV,
    ])

    @if(session('success'))
    <div style="background:#dcfce7; color:#166534; border-radius:8px; padding:10px 16px; font-size:13px;">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:#fee2e2; color:#991b1b; border-radius:8px; padding:10px 16px; font-size:13px;">
        {{ session('error') }}
    </div>
    @endif

    {{-- ══ SECTION 1: PC INFORMATION ══ --}}
    <div id="section-pc" class="db-card" style="padding:0; overflow:hidden;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 24px 14px; border-bottom:1px solid #f3f4f6;">
            <h3 style="font-size:15px; font-weight:700; color:#111827; margin:0;">PC Information</h3>
            @if($canEdit)
            <button onclick="openAddPcModal()"
                    style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:600;">
                + Add PC
            </button>
            @endif
        </div>

        <div style="overflow-x:auto;">
            <table class="db-table" style="min-width:{{ $canEdit ? '1200px' : '1100px' }};">
                <thead>
                    <tr>
                        <th>No PC</th>
                        <th>Type</th>
                        <th>Processor</th>
                        <th>RAM</th>
                        <th>SSD</th>
                        <th>Motherboard</th>
                        <th>VGA</th>
                        <th>CPU Fan</th>
                        <th>Power Supply</th>
                        <th>Status</th>
                        @if($canEdit)<th>Action</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($laboratory->pcs as $i => $pc)
                    <tr>
                        <td style="font-weight:600;">PC-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ ucfirst($pc->type_pc) }}</td>
                        <td>{{ $pc->processor ?? '-' }}</td>
                        <td>{{ $pc->ram ?? '-' }}</td>
                        <td>{{ $pc->ssd ?? '-' }}</td>
                        <td>{{ $pc->motherboard ?? '-' }}</td>
                        <td>{{ $pc->vga ?? '-' }}</td>
                        <td>{{ $pc->cpu_fan ?? '-' }}</td>
                        <td>{{ $pc->powersupply ?? '-' }}</td>
                        <td>
                            <span style="background:{{ $pc->status_pc === 'active' ? '#16a34a' : '#dc2626' }}; color:#fff; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:600;">
                                {{ ucfirst($pc->status_pc) }}
                            </span>
                        </td>
                        @if($canEdit)
                        <td>
                            <div class="action-btns">
                                <button type="button"
                                        onclick="openEditPcModal({{ $pc->id }})"
                                        class="action-btn action-edit" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                @if($isStaffLab)
                                <button type="button"
                                        onclick='openReturnModal("pc", {{ $pc->id }}, null, "PC-{{ str_pad($loop->index, 2, '0', STR_PAD_LEFT) }}")'
                                        class="action-btn action-delete" title="Retur PC">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canEdit ? 11 : 10 }}" style="text-align:center; padding:32px; color:#9ca3af; font-size:13px;">Belum ada PC</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 24px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f3f4f6;">
            <a href="{{ route('laboratory.index') }}"
               style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; text-decoration:none; color:#374151; font-weight:500; display:inline-flex; align-items:center; gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Back
            </a>
            <button type="button" onclick="showSection('asset')"
                    style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600; display:flex; align-items:center; gap:6px;">
                Next
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </button>
        </div>
    </div>

<style>
/* Definisikan warna default (Light Mode) */
:root {
    --bg-main: #ffffff;
    --bg-light: #f3f4f6;
    --bg-hover: #f9fafb;
    --text-bold: #111827;
    --text-normal: #374151;
    --text-muted: #9ca3af;
    --border-main: #d1d5db;
    --border-light: #e5e7eb;
    --bg-primary: #111B4C;
    --text-primary: #ffffff;

    /* Warna Status */
    --bg-danger: #fef2f2;
    --text-danger: #dc2626;
    --bg-warning: #fffbeb;
    --text-warning: #f59e0b;
    --bg-success: #dcfce7;
    --text-success: #16a34a;
}

/* Definisikan warna untuk Dark Mode
   PENTING: Sesuaikan '.dark' dengan class/trigger dark mode di project kamu (bisa jadi [data-theme="dark"] atau body.dark-mode) */
.dark, [data-theme="dark"] {
    --bg-main: #1f2937;
    --bg-light: #374151;
    --bg-hover: #4b5563;
    --text-bold: #f9fafb;
    --text-normal: #d1d5db;
    --text-muted: #9ca3af;
    --border-main: #4b5563;
    --border-light: #374151;
    --bg-primary: #3b82f6;
    --text-primary: #ffffff;

    --bg-danger: rgba(220, 38, 38, 0.2);
    --text-danger: #f87171;
    --bg-warning: rgba(245, 158, 11, 0.2);
    --text-warning: #fbbf24;
    --bg-success: rgba(22, 163, 74, 0.2);
    --text-success: #4ade80;
}
</style>

    {{-- ══ SECTION 2: ASSET INFORMATION ══ --}}
    <div id="section-asset" class="db-card" style="display:none; padding:0; overflow:hidden;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 24px 14px; border-bottom:1px solid var(--border-light);">
            <h3 style="font-size:15px; font-weight:700; color:var(--text-bold); margin:0;">Asset Information</h3>
            @if($isSPV)
            <button onclick="openAddAssetModal()"
                    style="background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:600;">
                + Add Asset
            </button>
            @endif
        </div>

        <div style="overflow-x:auto;">
            <table class="db-table" style="min-width:{{ $canEdit ? '950px' : '800px' }};">
                <thead>
                    <tr>
                        <th>Nama Aset</th>
                        <th>Kategori</th>
                        <th style="text-align:center;">Total Good</th>
                        <th style="text-align:center;">Total Rusak</th>
                        <th style="text-align:center;">Total Hilang</th>
                        <th style="text-align:center;">Total Aset</th>
                        @if($canEdit)<th style="text-align:center;">Action</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($laboratory->assets as $asset)
                    <tr>
                        <td style="font-weight:500;">{{ $asset->asset_name }}</td>
                        <td>{{ ucfirst($asset->asset_category) }}</td>

                        @if($canEdit)
                        {{-- Total Good --}}
                        <td style="text-align:center;">
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <form method="POST" action="{{ route('lab.assetlab.adjust', [$laboratory->id, $asset->id]) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="field" value="total_good_lab">
                                    <input type="hidden" name="action" value="decrement">
                                    <button type="submit" style="width:28px; height:28px; border:1px solid var(--border-main); border-radius:6px; background:var(--bg-main); color:var(--text-normal); cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; font-weight:600;">−</button>
                                </form>
                                <span style="min-width:36px; text-align:center; font-weight:700; font-size:14px; background:var(--bg-light); color:var(--text-bold); padding:4px 8px; border-radius:6px; display:inline-block;">{{ $asset->pivot->total_good_lab ?? 0 }}</span>
                                <form method="POST" action="{{ route('lab.assetlab.adjust', [$laboratory->id, $asset->id]) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="field" value="total_good_lab">
                                    <input type="hidden" name="action" value="increment">
                                    <button type="submit" style="width:28px; height:28px; border:1px solid var(--border-main); border-radius:6px; background:var(--bg-main); color:var(--text-normal); cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; font-weight:600;">+</button>
                                </form>
                            </div>
                        </td>
                        {{-- Total Rusak --}}
                        <td style="text-align:center;">
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <form method="POST" action="{{ route('lab.assetlab.adjust', [$laboratory->id, $asset->id]) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="field" value="total_damaged_lab">
                                    <input type="hidden" name="action" value="decrement">
                                    <button type="submit" style="width:28px; height:28px; border:1px solid var(--border-main); border-radius:6px; background:var(--bg-main); color:var(--text-normal); cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; font-weight:600;">−</button>
                                </form>
                                <span style="min-width:36px; text-align:center; font-weight:700; font-size:14px; color:var(--text-danger); background:var(--bg-danger); padding:4px 8px; border-radius:6px; display:inline-block;">{{ $asset->pivot->total_damaged_lab ?? 0 }}</span>
                                <form method="POST" action="{{ route('lab.assetlab.adjust', [$laboratory->id, $asset->id]) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="field" value="total_damaged_lab">
                                    <input type="hidden" name="action" value="increment">
                                    <button type="submit" style="width:28px; height:28px; border:1px solid var(--border-main); border-radius:6px; background:var(--bg-main); color:var(--text-normal); cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; font-weight:600;">+</button>
                                </form>
                            </div>
                        </td>
                        {{-- Total Hilang --}}
                        <td style="text-align:center;">
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <form method="POST" action="{{ route('lab.assetlab.adjust', [$laboratory->id, $asset->id]) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="field" value="total_loss_lab">
                                    <input type="hidden" name="action" value="decrement">
                                    <button type="submit" style="width:28px; height:28px; border:1px solid var(--border-main); border-radius:6px; background:var(--bg-main); color:var(--text-normal); cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; font-weight:600;">−</button>
                                </form>
                                <span style="min-width:36px; text-align:center; font-weight:700; font-size:14px; color:var(--text-warning); background:var(--bg-warning); padding:4px 8px; border-radius:6px; display:inline-block;">{{ $asset->pivot->total_loss_lab ?? 0 }}</span>
                                <form method="POST" action="{{ route('lab.assetlab.adjust', [$laboratory->id, $asset->id]) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="field" value="total_loss_lab">
                                    <input type="hidden" name="action" value="increment">
                                    <button type="submit" style="width:28px; height:28px; border:1px solid var(--border-main); border-radius:6px; background:var(--bg-main); color:var(--text-normal); cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; font-weight:600;">+</button>
                                </form>
                            </div>
                        </td>
                        @else
                        <td style="text-align:center; font-weight:700; font-size:14px; background:var(--bg-light); color:var(--text-bold); padding:4px 8px; border-radius:6px; display:inline-block;">{{ $asset->pivot->total_good_lab ?? 0 }}</td>
                        <td style="text-align:center; font-weight:700; font-size:14px; color:var(--text-danger); background:var(--bg-danger); padding:4px 8px; border-radius:6px; display:inline-block;">{{ $asset->pivot->total_damaged_lab ?? 0 }}</td>
                        <td style="text-align:center; font-weight:700; font-size:14px; color:var(--text-warning); background:var(--bg-warning); padding:4px 8px; border-radius:6px; display:inline-block;">{{ $asset->pivot->total_loss_lab ?? 0 }}</td>
                        @endif

                        <td style="text-align:center; font-weight:700; font-size:14px; color:var(--text-bold);">
                            {{ $asset->pivot->total_good_lab + $asset->pivot->total_damaged_lab + $asset->pivot->total_loss_lab }}
                        </td>

                        @if($canEdit)
                        <td style="text-align:center;">
                            <div class="action-btns" style="justify-content:center;">
                            @if(in_array($asset->asset_category, ['electronic', 'component-pc']))
                            <button type="button"
                                    onclick="openAssetSerialModal({{ $asset->id }}, '{{ addslashes($asset->asset_name) }}')"
                                    class="action-btn action-edit" title="{{ $isSPV ? 'Edit Serial' : 'Lihat Serial' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <path d="M7 9v6M10 9v6M13 9v6M17 9v6"/>
                                </svg>
                            </button>
                            @endif
                            @if($isStaffLab)
                            <button type="button"
                                    onclick='openReturnModal("asset", null, {{ $asset->id }}, "{{ addslashes($asset->asset_name) }}")'
                                    class="action-btn action-delete" title="Retur Aset">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                    <path d="M10 11v6M14 11v6"/>
                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                </svg>
                            </button>
                            @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canEdit ? 7 : 6 }}" style="text-align:center; padding:32px; color:var(--text-muted); font-size:13px;">Belum ada aset di lab ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 24px; border-top:1px solid var(--border-light);">
            <button type="button" onclick="showSection('pc')"
                    style="border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:500; display:inline-flex; align-items:center; gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Back
            </button>
        </div>
    </div>

</div>

{{-- ══ MODAL ADD PC ══ --}}
@if($canEdit)
<div id="modal-add-pc" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:var(--bg-main); border-radius:16px; width:100%; max-width:500px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--border-light); flex-shrink:0;">
            <h3 style="font-size:16px; font-weight:700; color:var(--text-bold); margin:0;">Add PC</h3>
            <button onclick="closeAddPcModal()" style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:22px;">&times;</button>
        </div>
        <form method="POST" action="{{ route('pc.store', $laboratory->id) }}" style="overflow-y:auto; flex:1; padding:24px; display:flex; flex-direction:column; gap:14px;">
            @csrf
            <div>
                <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Type</label>
                <select name="type_pc" style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:10px 14px; font-size:13px; outline:none;">
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="dosen">Dosen</option>
                </select>
            </div>

            {{-- (#8) Pemilih komponen + serial number per slot (termasuk RAM 2 opsional) --}}
            <x-partials.pc-component-picker :laboratory="$laboratory" />
            <div style="display:flex; justify-content:flex-end; gap:8px; padding-top:4px;">
                <button type="button" onclick="closeAddPcModal()"
                        style="border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit"
                        style="background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ MODAL EDIT PC (#10: per-PC + serial picker) ══ --}}
@foreach($laboratory->pcs as $i => $pc)
<div id="modal-edit-pc-{{ $pc->id }}" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:var(--bg-main); border-radius:16px; width:100%; max-width:500px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--border-light); flex-shrink:0;">
            <h3 style="font-size:16px; font-weight:700; color:var(--text-bold); margin:0;">Edit PC-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</h3>
            <button type="button" onclick="closeEditPcModal({{ $pc->id }})" style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:22px;">&times;</button>
        </div>
        <form method="POST" action="{{ route('pc.update', [$laboratory->id, $pc->id]) }}" style="overflow-y:auto; flex:1; padding:24px; display:flex; flex-direction:column; gap:14px;">
            @csrf @method('PUT')
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Type</label>
                    <select name="type_pc" style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:10px 14px; font-size:13px; outline:none;">
                        <option value="mahasiswa" {{ $pc->type_pc === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="dosen" {{ $pc->type_pc === 'dosen' ? 'selected' : '' }}>Dosen</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Status</label>
                    <select name="status_pc" style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:10px 14px; font-size:13px; outline:none;">
                        <option value="active" {{ $pc->status_pc === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $pc->status_pc === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            {{-- (#10) Pemilih komponen + serial number per slot --}}
            <x-partials.pc-component-picker :laboratory="$laboratory" :pc="$pc" />

            <div style="display:flex; justify-content:flex-end; gap:8px; padding-top:4px;">
                <button type="button" onclick="closeEditPcModal({{ $pc->id }})"
                        style="border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit"
                        style="background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Update</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- ══ MODAL ADD ASSET ══ --}}
<div id="modal-add-asset" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:var(--bg-main); border-radius:16px; width:100%; max-width:500px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--border-light);">
            <h3 style="font-size:16px; font-weight:700; color:var(--text-bold); margin:0;">Add Asset</h3>
            <button onclick="closeAddAssetModal()" style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:22px;">&times;</button>
        </div>
        <form method="POST" action="{{ route('laboratory.update', $laboratory->id) }}" style="padding:24px; display:flex; flex-direction:column; gap:14px;">
            @csrf @method('PUT')
            <input type="hidden" name="lab_name" value="{{ $laboratory->lab_name }}">
            <input type="hidden" name="capacity" value="{{ $laboratory->capacity }}">
            @foreach($laboratory->pcs as $i => $pc)
            <input type="hidden" name="pcs[{{ $i }}][id]" value="{{ $pc->id }}">
            <input type="hidden" name="pcs[{{ $i }}][type_pc]" value="{{ $pc->type_pc }}">
            <input type="hidden" name="pcs[{{ $i }}][status_pc]" value="{{ $pc->status_pc }}">
            <input type="hidden" name="pcs[{{ $i }}][processor]" value="{{ $pc->processor }}">
            <input type="hidden" name="pcs[{{ $i }}][ram]" value="{{ $pc->ram }}">
            <input type="hidden" name="pcs[{{ $i }}][ssd]" value="{{ $pc->ssd }}">
            <input type="hidden" name="pcs[{{ $i }}][motherboard]" value="{{ $pc->motherboard }}">
            <input type="hidden" name="pcs[{{ $i }}][vga]" value="{{ $pc->vga }}">
            <input type="hidden" name="pcs[{{ $i }}][cpu_fan]" value="{{ $pc->cpu_fan }}">
            <input type="hidden" name="pcs[{{ $i }}][powersupply]" value="{{ $pc->powersupply }}">
            @endforeach
            @foreach($laboratory->assets as $idx => $ast)
            <input type="hidden" name="lab_assets[ex{{ $idx }}][asset_id]" value="{{ $ast->id }}">
            <input type="hidden" name="lab_assets[ex{{ $idx }}][quantity]" value="{{ $ast->pivot->total_good_lab }}">
            @endforeach

            <div>
                <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Asset Name:</label>
                <select name="lab_assets[new0][asset_id]"
                        style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:10px 14px; font-size:13px; outline:none;">
                    <option value="">Choose asset...</option>
                    @foreach($allAssets as $a)
                    <option value="{{ $a->id }}">{{ $a->asset_name }} ({{ ucfirst($a->asset_category) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Quantity:</label>
                <input type="number" name="lab_assets[new0][quantity]" value="1" min="1"
                       style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeAddAssetModal()"
                        style="border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit"
                        style="background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Add</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ MODAL RETURN CONFIRMATION ══ --}}
<div id="modal-return" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:var(--bg-main); border-radius:16px; width:100%; max-width:500px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--border-light);">
            <h3 style="font-size:16px; font-weight:700; color:var(--text-bold); margin:0;">Konfirmasi Retur</h3>
            <button onclick="closeReturnModal()" style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:22px;">&times;</button>
        </div>
        <form method="POST" action="{{ route('return-requests.store-quick') }}" style="padding:24px; display:flex; flex-direction:column; gap:14px;">
            @csrf
            <input type="hidden" name="lab_id" value="{{ $laboratory->id }}">
            <input type="hidden" id="return-pc-id" name="pc_id">
            <input type="hidden" id="return-asset-id" name="asset_id">

            <p style="font-size:14px; color:var(--text-normal); margin:0;">
                Yakin untuk menghapus/retur <strong id="return-item-name"></strong> ke gudang?
            </p>

            <div id="return-asset-fields" style="display:none;">
                <div>
                    <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Kuantitas:</label>
                    <input type="number" name="quantity" id="return-quantity" value="1" min="1"
                           style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Kondisi:</label>
                    <select name="condition" id="return-condition"
                            style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:10px 14px; font-size:13px; outline:none;">
                        <option value="good">Baik</option>
                        <option value="damaged">Rusak</option>
                        <option value="lost">Hilang</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Catatan (opsional):</label>
                <textarea name="notes" id="return-notes" rows="3"
                          style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box; resize:vertical;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeReturnModal()"
                        style="border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
<button type="submit"
                        style="background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Kirim Pengajuan Retur</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ MODAL SERIAL ASET (#14) ══ --}}
<div id="modal-asset-serial" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:60; align-items:center; justify-content:center;">
    <div style="background:var(--bg-main); border-radius:16px; width:100%; max-width:460px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--border-light); flex-shrink:0;">
            <h3 style="font-size:16px; font-weight:700; color:var(--text-bold); margin:0;">
                Serial Number — <span id="asset-serial-title" style="font-weight:600;"></span>
            </h3>
            <button type="button" onclick="closeAssetSerialModal()" style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:22px;">&times;</button>
        </div>
        <div style="overflow-y:auto; flex:1; padding:24px;">
            <div id="asset-serial-list" style="display:flex; flex-direction:column; gap:8px;"></div>
            <p id="asset-serial-empty" style="display:none; text-align:center; color:var(--text-muted); font-size:13px; margin:8px 0 0;">
                Belum ada serial number untuk aset ini di lab.
            </p>
        </div>
        @if($isSPV)
        <div style="display:flex; justify-content:flex-end; gap:8px; padding:0 24px 24px; flex-shrink:0;">
            <button type="button" onclick="closeAssetSerialModal()"
                    style="border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
            <button type="button" onclick="saveAssetSerials()"
                    style="background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Simpan</button>
        </div>
        @endif
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// ── Data ──
const existingPcs = @json($laboratory->pcs->values());
const labId = {{ $laboratory->id }};
const pcComponents = @json($pcComponents);
const electronicOptions    = @json($electronicAssets->map(fn($a) => ['id'=>$a->id,'name'=>$a->asset_name])->values());
const nonElectronicOptions = @json($nonElectronicAssets->map(fn($a) => ['id'=>$a->id,'name'=>$a->asset_name])->values());
let assetCounter = {{ $laboratory->assets->count() + 100 }};
const pcFields = ['processor','ram','ssd','motherboard','vga','cpu_fan','powersupply'];

// ── Section Navigation ──
function showSection(s) {
    document.getElementById('section-pc').style.display    = s === 'pc'    ? 'block' : 'none';
    document.getElementById('section-asset').style.display = s === 'asset' ? 'block' : 'none';
}

// ── Auto-show section after redirect ──
@if(session('section') === 'asset')
    showSection('asset');
@elseif(session('section') === 'pc')
    showSection('pc');
@endif

@if($canEdit)
// ── Add PC Dropdowns ──
function filterAddDropdown(field) {
    showAddDropdown(field);
}
function showAddDropdown(field) {
    const query    = document.getElementById(`apc_${field}_search`).value.toLowerCase();
    const dropdown = document.getElementById(`apc_${field}_dropdown`);
    const filtered = pcComponents.filter(c => c.name.toLowerCase().includes(query));

    dropdown.innerHTML = '';

    const emptyOpt = document.createElement('div');
    emptyOpt.textContent = '— Kosongkan / Ketik Manual —';
    emptyOpt.style.cssText = 'padding:8px 12px; font-size:13px; cursor:pointer; color:var(--text-muted); border-bottom:1px solid var(--border-light);';
    emptyOpt.onmousedown = () => {
        const txt = document.getElementById(`apc_${field}_search`).value;
        selectAddComponent(field, txt, txt);
    };
    dropdown.appendChild(emptyOpt);

    if (filtered.length > 0) {
        filtered.forEach(comp => {
            const disabled = comp.stock < 1;
            const item = document.createElement('div');
            item.style.cssText = `padding:8px 12px; font-size:13px; cursor:${disabled?'not-allowed':'pointer'}; display:flex; justify-content:space-between; align-items:center;`;
            item.innerHTML = `
                <span style="color:${disabled?'var(--text-muted)':'var(--text-normal)'};">${comp.name}</span>
                <span style="font-size:11px; background:${disabled?'var(--bg-danger)':'var(--bg-success)'}; color:${disabled?'var(--text-danger)':'var(--text-success)'}; padding:2px 6px; border-radius:4px; font-weight:600;">
                    Stok: ${comp.stock}
                </span>`;
            if (!disabled) {
                item.onmousedown = () => selectAddComponent(field, comp.name, comp.name);
                item.onmouseover = () => item.style.background = 'var(--bg-hover)';
                item.onmouseout  = () => item.style.background = '';
            }
            dropdown.appendChild(item);
        });
    }

    dropdown.style.display = 'block';
}
function selectAddComponent(field, value, label) {
    document.getElementById(`apc_${field}_val`).value    = value;
    document.getElementById(`apc_${field}_search`).value = label;
    document.getElementById(`apc_${field}_dropdown`).style.display = 'none';
}

// Close dropdowns when clicking outside
document.addEventListener('click', e => {
    pcFields.forEach(f => {
        const search   = document.getElementById(`apc_${f}_search`);
        const dropdown = document.getElementById(`apc_${f}_dropdown`);
        if (search && dropdown && !search.contains(e.target) && !dropdown.contains(e.target)) {
            if (dropdown.style.display !== 'none') {
                document.getElementById(`apc_${f}_val`).value = search.value;
                dropdown.style.display = 'none';
            }
        }
    });
});

// ── Add PC ──
function openAddPcModal()  {
    const m = document.getElementById('modal-add-pc');
    m.style.display = 'flex';
    if (window.initPcComponentPickers) window.initPcComponentPickers(m);
}
function closeAddPcModal() { document.getElementById('modal-add-pc').style.display = 'none'; }
document.getElementById('modal-add-pc').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAddPcModal();
});

// ── Edit PC (#10: per-PC modal + serial picker) ──
function openEditPcModal(pcId) {
    const m = document.getElementById('modal-edit-pc-' + pcId);
    if (!m) return;
    m.style.display = 'flex';
    if (window.initPcComponentPickers) window.initPcComponentPickers(m);
}
function closeEditPcModal(pcId) {
    const m = document.getElementById('modal-edit-pc-' + pcId);
    if (m) m.style.display = 'none';
}

// ── Return Modal ──
function openReturnModal(type, pcId, assetId, itemName) {
    document.getElementById('return-item-name').textContent = itemName;
    document.getElementById('return-pc-id').value = pcId || '';
    document.getElementById('return-asset-id').value = assetId || '';
    document.getElementById('return-quantity').value = 1;
    document.getElementById('return-condition').value = 'good';
    document.getElementById('return-notes').value = '';

    const assetFields = document.getElementById('return-asset-fields');
    if (type === 'asset') {
        assetFields.style.display = 'block';
    } else {
        assetFields.style.display = 'none';
    }

    document.getElementById('modal-return').style.display = 'flex';
}
function closeReturnModal() { document.getElementById('modal-return').style.display = 'none'; }
document.getElementById('modal-return').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeReturnModal();
});

// ── Searchable dropdown for PC components ──
function filterDropdown(field) {
    showDropdown(field);
}
function showDropdown(field) {
    const query    = document.getElementById(`epc_${field}_search`).value.toLowerCase();
    const dropdown = document.getElementById(`epc_${field}_dropdown`);
    const filtered = pcComponents.filter(c => c.name.toLowerCase().includes(query));

    dropdown.innerHTML = '';

    const emptyOpt = document.createElement('div');
    emptyOpt.textContent = '— Kosongkan / Ketik Manual —';
    emptyOpt.style.cssText = 'padding:8px 12px; font-size:13px; cursor:pointer; color:var(--text-muted); border-bottom:1px solid var(--border-light);';
    emptyOpt.onmousedown = () => {
        const txt = document.getElementById(`epc_${field}_search`).value;
        selectComponent(field, txt, txt);
    };
    dropdown.appendChild(emptyOpt);

    if (filtered.length > 0) {
        filtered.forEach(comp => {
            const disabled = comp.stock < 1;
            const item = document.createElement('div');
            item.style.cssText = `padding:8px 12px; font-size:13px; cursor:${disabled?'not-allowed':'pointer'}; display:flex; justify-content:space-between; align-items:center;`;
            item.innerHTML = `
                <span style="color:${disabled?'var(--text-muted)':'var(--text-normal)'};">${comp.name}</span>
                <span style="font-size:11px; background:${disabled?'var(--bg-danger)':'var(--bg-success)'}; color:${disabled?'var(--text-danger)':'var(--text-success)'}; padding:2px 6px; border-radius:4px; font-weight:600;">
                    Stok: ${comp.stock}
                </span>`;
            if (!disabled) {
                item.onmousedown = () => selectComponent(field, comp.name, comp.name);
                item.onmouseover = () => item.style.background = 'var(--bg-hover)';
                item.onmouseout  = () => item.style.background = '';
            }
            dropdown.appendChild(item);
        });
    }

    dropdown.style.display = 'block';
}
function selectComponent(field, value, label) {
    document.getElementById(`epc_${field}_val`).value    = value;
    document.getElementById(`epc_${field}_search`).value = label;
    document.getElementById(`epc_${field}_dropdown`).style.display = 'none';
}
document.addEventListener('click', e => {
    pcFields.forEach(f => {
        const search   = document.getElementById(`epc_${f}_search`);
        const dropdown = document.getElementById(`epc_${f}_dropdown`);
        if (search && dropdown && !search.contains(e.target) && !dropdown.contains(e.target)) {
            if (dropdown.style.display !== 'none') {
                document.getElementById(`epc_${f}_val`).value = search.value;
                dropdown.style.display = 'none';
            }
        }
    });
});
// ── Add Asset Modal ──
function openAddAssetModal()  { document.getElementById('modal-add-asset').style.display = 'flex'; }
function closeAddAssetModal() { document.getElementById('modal-add-asset').style.display = 'none'; }
document.getElementById('modal-add-asset').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAddAssetModal();
});

// ── Serial Aset (#14) ──
const IS_SPV_SERIAL = @json($isSPV);
let currentAssetSerialId = null;

function openAssetSerialModal(assetId, name) {
    currentAssetSerialId = assetId;
    document.getElementById('asset-serial-title').textContent = name;
    document.getElementById('asset-serial-empty').style.display = 'none';
    const list = document.getElementById('asset-serial-list');
    list.innerHTML = '<p style="text-align:center;color:var(--text-muted);font-size:13px;">Memuat...</p>';
    document.getElementById('modal-asset-serial').style.display = 'flex';

    fetch(`/api/laboratory/${labId}/assets/${assetId}/serials`)
        .then(r => r.json())
        .then(d => {
            list.innerHTML = '';
            if (!(d.serials || []).length) {
                document.getElementById('asset-serial-empty').style.display = 'block';
                return;
            }
            d.serials.forEach((s, idx) => {
                const row = document.createElement('div');
                row.style.cssText = 'display:flex; gap:8px; align-items:center;';
                const num = document.createElement('span');
                num.textContent = (idx + 1) + '.';
                num.style.cssText = 'width:22px; color:var(--text-muted); font-size:13px; flex-shrink:0;';
                const inp = document.createElement('input');
                inp.type = 'text';
                inp.value = s.serial_number;
                inp.dataset.serialId = s.id;
                inp.readOnly = !IS_SPV_SERIAL || s.locked;
                inp.style.cssText = 'flex:1; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:8px 12px; font-size:13px; outline:none;';
                row.appendChild(num);
                row.appendChild(inp);
                if (s.locked) {
                    const badge = document.createElement('span');
                    badge.textContent = 'Terpasang';
                    badge.style.cssText = 'font-size:11px; background:var(--bg-success); color:var(--text-success); padding:2px 8px; border-radius:6px; flex-shrink:0;';
                    row.appendChild(badge);
                }
                list.appendChild(row);
            });
        })
        .catch(() => { list.innerHTML = '<p style="text-align:center;color:#f87171;font-size:13px;">Gagal memuat.</p>'; });
}

function closeAssetSerialModal() {
    document.getElementById('modal-asset-serial').style.display = 'none';
    currentAssetSerialId = null;
}

function saveAssetSerials() {
    if (!currentAssetSerialId) return;
    const inputs = document.querySelectorAll('#asset-serial-list input[data-serial-id]');
    const serials = Array.from(inputs)
        .filter(i => !i.readOnly)
        .map(i => ({ id: i.dataset.serialId, serial_number: i.value }));

    fetch(`/api/laboratory/${labId}/assets/${currentAssetSerialId}/serials/sync`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ serials })
    })
        .then(r => r.json())
        .then(d => { if (d.success) closeAssetSerialModal(); else alert('Gagal menyimpan.'); })
        .catch(() => alert('Gagal menyimpan.'));
}

document.getElementById('modal-asset-serial').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAssetSerialModal();
});
@endif
</script>
@endpush
