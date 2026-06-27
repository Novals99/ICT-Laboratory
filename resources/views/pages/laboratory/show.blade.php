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
        <table class="db-table" style="min-width:400px; width:100%;">
            <thead>
                <tr>
                    <th>No PC</th>
                    <th>PC Unit / Serial</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laboratory->pcs as $i => $pc)
                <tr>
                    <td style="font-weight:600;">PC-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        @if($pc->pcSerial)
                            <div style="font-weight:600; color:var(--text-bold);">{{ $pc->pcSerial->asset->asset_name }}</div>
                            <div style="font-size:11px; color:var(--text-muted); font-family:monospace;">S/N: {{ $pc->pcSerial->serial_number }}</div>
                        @else
                            <span style="color:var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td>{{ ucfirst($pc->type_pc) }}</td>
                    <td>
                        <span style="background:{{ $pc->status_pc === 'active' ? '#16a34a' : '#dc2626' }}; color:#fff; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:600;">
                            {{ ucfirst($pc->status_pc) }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <div class="action-btns" style="justify-content:center;">
                            <x-table.action
                                variant="view"
                                title="Lihat Detail PC"
                                onclick="openViewPcModal({{ $pc->id }})"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </x-table.action>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:32px; color:#9ca3af; font-size:13px;">Belum ada PC</td>
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
    <div id="section-asset" class="db-card" style="display:none; padding:0;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 24px 14px; border-bottom:1px solid var(--border-light); flex-wrap: wrap; gap: 12px;">
            <h3 style="font-size:15px; font-weight:700; color:var(--text-bold); margin:0;">Asset Information</h3>
            <div style="display:flex; align-items:center; gap:8px;">
                <x-button.filter activeCount="{{
                    (request()->filled('category') ? 1 : 0) +
                    (request()->filled('sort') && request('sort') !== 'desc' ? 1 : 0)
                }}">
                    <input type="hidden" name="section" value="asset">
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="filter-section">
                        <div class="filter-section-title">Category</div>
                        @foreach ([
                            '' => 'All',
                            'component-pc' => 'PC Component',
                            'pc' => 'PC',
                            'non-electronic' => 'Non Electronic',
                            'electronic' => 'Electronic'
                        ] as $val => $label)
                            <label class="filter-checkbox-row" style="cursor: pointer; display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <input type="radio" name="category" value="{{ $val }}"
                                    {{ request('category', '') === $val ? 'checked' : '' }}
                                    style="accent-color: #111B4C; cursor: pointer;">
                                <span style="font-size: 13px; color: var(--text-secondary);">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="filter-section">
                        <div class="filter-section-title">Sort By Date</div>
                        <label class="filter-checkbox-row" style="cursor: pointer; display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <input type="radio" name="sort" value="desc" {{ request('sort', 'desc') === 'desc' ? 'checked' : '' }} style="accent-color: #111B4C; cursor: pointer;">
                            <span style="font-size: 13px; color: var(--text-secondary);">Newest to Oldest</span>
                        </label>
                        <label class="filter-checkbox-row" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="sort" value="asc" {{ request('sort') === 'asc' ? 'checked' : '' }} style="accent-color: #111B4C; cursor: pointer;">
                            <span style="font-size: 13px; color: var(--text-secondary);">Oldest to Newest</span>
                        </label>
                    </div>
                </x-button.filter>

                @if($isSPV)
                <button onclick="openAddAssetModal()"
                        style="background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:600;">
                    + Add Asset
                </button>
                @endif
            </div>
        </div>

        <div style="overflow-x:auto; border-radius: 0 0 14px 14px;">
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
                    @forelse($filteredAssets as $asset)
                    <tr>
                        <td style="font-weight:500;">{{ $asset->asset_name }}{{ $asset->specification ? ' - ' . $asset->specification : '' }}</td>
                        <td>{{ ucfirst($asset->asset_category) }}</td>

                        @if($canEdit && $asset->asset_category === 'component-pc')
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
                        <td style="text-align:center;"><span style="min-width:36px; text-align:center; font-weight:700; font-size:14px; background:var(--bg-light); color:var(--text-bold); padding:4px 8px; border-radius:6px; display:inline-block;">{{ $asset->pivot->total_good_lab ?? 0 }}</span></td>
                        <td style="text-align:center;"><span style="min-width:36px; text-align:center; font-weight:700; font-size:14px; color:var(--text-danger); background:var(--bg-danger); padding:4px 8px; border-radius:6px; display:inline-block;">{{ $asset->pivot->total_damaged_lab ?? 0 }}</span></td>
                        <td style="text-align:center;"><span style="min-width:36px; text-align:center; font-weight:700; font-size:14px; color:var(--text-warning); background:var(--bg-warning); padding:4px 8px; border-radius:6px; display:inline-block;">{{ $asset->pivot->total_loss_lab ?? 0 }}</span></td>
                        @endif

                        <td style="text-align:center; font-weight:700; font-size:14px; color:var(--text-bold);">
                            {{ $asset->pivot->total_good_lab + $asset->pivot->total_damaged_lab + $asset->pivot->total_loss_lab }}
                        </td>

                        @if($canEdit)
                        <td style="text-align:center;">
                            <div class="action-btns" style="justify-content:center;">
                            @if($asset->asset_category !== 'component-pc')
                            <button type="button"
                                    onclick="openAssetSerialModal({{ $asset->id }}, '{{ addslashes($asset->asset_name) }}', '{{ $asset->asset_category }}')"
                                    class="action-btn action-edit" title="{{ $isSPV ? 'Edit Kode Inventaris' : 'Lihat Kode Inventaris' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <path d="M7 9v6M10 9v6M13 9v6M17 9v6"/>
                                </svg>
                            </button>
                            @endif
                            @if($isStaffLab)
                            <button type="button"
                                    onclick='openReturnModal("asset", null, {{ $asset->id }}, "{{ addslashes($asset->asset_name . ($asset->specification ? ' - ' . $asset->specification : '')) }}")'
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


{{-- ══ MODAL VIEW PC ══ --}}
@foreach($laboratory->pcs as $i => $pc)
<div id="modal-view-pc-{{ $pc->id }}" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:var(--bg-main); border-radius:16px; width:100%; max-width:480px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); display:flex; flex-direction:column;">

        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--border-light); flex-shrink:0;">
            <div>
                <h3 style="font-size:16px; font-weight:700; color:var(--text-bold); margin:0;">
                    PC-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                </h3>
                <span style="font-size:12px; color:var(--text-muted);">{{ ucfirst($pc->type_pc) }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="background:{{ $pc->status_pc === 'active' ? '#16a34a' : '#dc2626' }}; color:#fff; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:600;">
                    {{ ucfirst($pc->status_pc) }}
                </span>
                <button type="button" onclick="closeViewPcModal({{ $pc->id }})" style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:22px; line-height:1;">&times;</button>
            </div>
        </div>

        {{-- Spec List --}}
        <div style="padding:20px 24px; display:flex; flex-direction:column; gap:0;">

            @php
            $specs = [
                ['label' => 'PC Unit',       'value' => $pc->pcSerial?->asset?->asset_name ?? '-'],
                ['label' => 'Serial Number',  'value' => $pc->pcSerial?->serial_number ?? '-'],
                ['label' => 'Processor',      'value' => $pc->processor ?? '-'],
                ['label' => 'RAM',            'value' => $pc->ram ?? '-'],
                ['label' => 'SSD',            'value' => $pc->ssd ?? '-'],
                ['label' => 'HDD',            'value' => $pc->hdd ?? '-'],
                ['label' => 'Motherboard',    'value' => $pc->motherboard ?? '-'],
                ['label' => 'VGA',            'value' => $pc->vga ?? '-'],
                ['label' => 'CPU Fan',        'value' => $pc->cpu_fan ?? '-'],
                ['label' => 'Power Supply',   'value' => $pc->powersupply ?? '-'],
            ];
            @endphp

            @foreach($specs as $idx => $spec)
            <div style="display:flex; align-items:center; padding:10px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--border-light);' : '' }}">
                <span style="width:140px; flex-shrink:0; font-size:13px; font-weight:500; color:var(--text-muted);">{{ $spec['label'] }}</span>
                <span style="font-size:13px; font-weight:600; color:{{ $spec['value'] === '-' ? 'var(--text-muted)' : 'var(--text-bold)' }};">
                    {{ $spec['value'] }}
                </span>
            </div>
            @endforeach

        </div>

{{-- Footer Actions --}}
{{-- Footer Actions --}}
<div style="display:flex; align-items:center; justify-content:space-between; padding:16px 24px; border-top:1px solid var(--border-light); flex-shrink:0;">

    {{-- Kiri: Retur --}}
    <div>
        @if($isStaffLab)
        <button
            type="button"
            class="btn-retur-pc panel-table-action panel-action-delete"
            data-pc-id="{{ $pc->id }}"
            data-pc-label="PC-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
            title="Retur PC"
            style="display:inline-flex; align-items:center; gap:6px;"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                <path d="M10 11v6M14 11v6"/>
                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
            Retur PC
        </button>
        @endif
    </div>

    {{-- Kanan: Toggle Status + Tutup + Edit --}}
    <div style="display:flex; gap:8px; align-items:center;">

        {{-- Toggle Status (hanya canEdit) --}}
        @if($canEdit)
        <form method="POST" action="{{ route('pc.toggle-status', [$laboratory->id, $pc->id]) }}">
            @csrf
            @method('PATCH')
            <button type="submit"
                    style="border:1px solid {{ $pc->status_pc === 'active' ? '#dc2626' : '#16a34a' }}; background:{{ $pc->status_pc === 'active' ? '#fef2f2' : '#dcfce7' }}; color:{{ $pc->status_pc === 'active' ? '#dc2626' : '#16a34a' }}; border-radius:8px; padding:9px 16px; font-size:13px; cursor:pointer; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                @if($pc->status_pc === 'active')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    Set Inactive
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    Set Active
                @endif
            </button>
        </form>
        @endif

        <button type="button"
                data-close-view="{{ $pc->id }}"
                style="border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">
            Tutup
        </button>

        @if($canEdit)
        <button type="button"
                data-open-edit="{{ $pc->id }}"
                style="background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Edit
        </button>
        @endif
    </div>
</div>
@endforeach



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
                <select name="lab_assets[new0][asset_id]" id="add-asset-select"
                        style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:10px 14px; font-size:13px; outline:none;" required>
                    <option value="">Choose asset...</option>
                    @foreach($allAssets as $a)
                    <option value="{{ $a->id }}" data-category="{{ $a->asset_category }}">{{ $a->asset_name }}{{ $a->specification ? ' - ' . $a->specification : '' }} ({{ ucfirst($a->asset_category) }})</option>
                    @endforeach
                </select>
            </div>
            
            <div id="add-asset-serials-container" style="display:none;">
                <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Select Kode Inventaris from SPV:</label>
                <div id="add-asset-serials-list" style="border:1px solid var(--border-main); border-radius:8px; padding:10px; max-height:150px; overflow-y:auto; display:flex; flex-direction:column; gap:6px; background:var(--bg-main);">
                    <!-- Checkboxes will be populated here -->
                </div>
            </div>

            <div>
                <label style="font-size:13px; font-weight:500; color:var(--text-normal); display:block; margin-bottom:6px;">Quantity:</label>
                <input type="number" name="lab_assets[new0][quantity]" id="add-asset-qty" value="1" min="1"
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
    <div style="background:var(--bg-main); border-radius:16px; width:100%; max-width:880px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--border-light); flex-shrink:0;">
            <h3 style="font-size:16px; font-weight:700; color:var(--text-bold); margin:0;">
                Kode Inventaris — <span id="asset-serial-title" style="font-weight:600;"></span>
            </h3>
            <button type="button" onclick="closeAssetSerialModal()" style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:22px;">&times;</button>
        </div>
        <div style="overflow-y:auto; flex:1; padding:24px;">
            <table class="db-table" style="width:100%; border-collapse:collapse;" id="asset-serial-table">
                <thead>
                    <tr style="background:var(--bg-light); border-bottom:2px solid var(--border-light);">
                        <th style="padding:10px; text-align:center; font-size:12px; font-weight:600; width:40px; color:var(--text-bold);">No</th>
                        <th style="padding:10px; text-align:left; font-size:12px; font-weight:600; color:var(--text-bold);">Kode Inventaris</th>
                        <th style="padding:10px; text-align:center; font-size:12px; font-weight:600; width:180px; color:var(--text-bold);">Scan QR / Kamera</th>
                        <th style="padding:10px; text-align:center; font-size:12px; font-weight:600; width:90px; color:var(--text-bold);">Status</th>
                        <th style="padding:10px; text-align:center; font-size:12px; font-weight:600; width:90px; color:var(--text-bold);">Kondisi</th>
                        <th style="padding:10px; text-align:center; font-size:12px; font-weight:600; width:180px; color:var(--text-bold);">Pilih Kondisi</th>
                        <th class="pc-only-col" style="padding:10px; text-align:left; font-size:12px; font-weight:600; width:130px; color:var(--text-bold);">PC Terpasang</th>
                    </tr>
                </thead>
                <tbody id="asset-serial-table-body">
                    <!-- Dynamic serial rows will be loaded here -->
                </tbody>
            </table>
            <p id="asset-serial-empty" style="display:none; text-align:center; color:var(--text-muted); font-size:13px; margin:8px 0 0;">
                Belum ada kode inventaris untuk aset ini di lab.
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
const pcFields = ['processor','ram','ssd','hdd','motherboard','vga','cpu_fan','powersupply'];

// ── Section Navigation ──
function showSection(s) {
    document.getElementById('section-pc').style.display    = s === 'pc'    ? 'block' : 'none';
    document.getElementById('section-asset').style.display = s === 'asset' ? 'block' : 'none';
}

// ── Auto-show section after redirect ──
@if(session('section') === 'asset' || request('section') === 'asset')
    showSection('asset');
@elseif(session('section') === 'pc' || request('section') === 'pc')
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
function openAddAssetModal()  { 
    document.getElementById('modal-add-asset').style.display = 'flex'; 
    document.getElementById('add-asset-select').value = '';
    document.getElementById('add-asset-serials-container').style.display = 'none';
    document.getElementById('add-asset-serials-list').innerHTML = '';
    const qtyInp = document.getElementById('add-asset-qty');
    qtyInp.value = 1;
    qtyInp.readOnly = false;
    qtyInp.min = 1;
}
function closeAddAssetModal() { document.getElementById('modal-add-asset').style.display = 'none'; }
document.getElementById('modal-add-asset').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAddAssetModal();
});

document.getElementById('add-asset-select').addEventListener('change', function() {
    const assetId = this.value;
    const selectedOption = this.options[this.selectedIndex];
    const category = selectedOption ? selectedOption.getAttribute('data-category') : '';
    
    const container = document.getElementById('add-asset-serials-container');
    const list = document.getElementById('add-asset-serials-list');
    const qtyInp = document.getElementById('add-asset-qty');
    
    list.innerHTML = '';
    
    if (!assetId) {
        container.style.display = 'none';
        qtyInp.value = 1;
        qtyInp.readOnly = false;
        qtyInp.min = 1;
        return;
    }
    
    const usesSerial = ['electronic', 'pc', 'non-electronic', 'component-pc'].includes(category);
    
    if (usesSerial) {
        container.style.display = 'block';
        qtyInp.value = 0;
        qtyInp.readOnly = true;
        qtyInp.min = 0;
        
        list.innerHTML = '<p style="font-size:12px;color:var(--text-muted);padding:4px 0;">Memuat nomor seri...</p>';
        
        fetch(`/api/assets/${assetId}/available-spv-serials`)
            .then(r => r.json())
            .then(data => {
                list.innerHTML = '';
                if (!data.serials || data.serials.length === 0) {
                    list.innerHTML = '<p style="font-size:12px;color:var(--text-muted);padding:4px 0;">Tidak ada nomor seri tersedia di gudang SPV.</p>';
                    return;
                }
                
                data.serials.forEach(s => {
                    const lbl = document.createElement('label');
                    lbl.style.cssText = 'display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-normal); cursor:pointer; margin:2px 0;';
                    
                    const chk = document.createElement('input');
                    chk.type = 'checkbox';
                    chk.name = 'lab_assets[new0][serial_ids][]';
                    chk.value = s.id;
                    chk.className = 'add-asset-serial-checkbox';
                    chk.style.cssText = 'accent-color: var(--bg-primary);';
                    chk.addEventListener('change', function() {
                        const checkedCount = list.querySelectorAll('.add-asset-serial-checkbox:checked').length;
                        qtyInp.value = checkedCount;
                    });
                    
                    const span = document.createElement('span');
                    span.textContent = `${s.serial_number} (${s.condition})`;
                    
                    lbl.appendChild(chk);
                    lbl.appendChild(span);
                    list.appendChild(lbl);
                });
            })
            .catch(() => {
                list.innerHTML = '<p style="font-size:12px;color:#f87171;padding:4px 0;">Gagal memuat nomor seri.</p>';
            });
    } else {
        container.style.display = 'none';
        qtyInp.value = 1;
        qtyInp.readOnly = false;
        qtyInp.min = 1;
    }
});

// ── Serial Aset (#14) ──
const IS_SPV_SERIAL = @json($isSPV);
let currentAssetSerialId = null;

function openAssetSerialModal(assetId, name, category) {
    currentAssetSerialId = assetId;
    document.getElementById('asset-serial-title').textContent = name;
    document.getElementById('asset-serial-empty').style.display = 'none';
    const tbody = document.getElementById('asset-serial-table-body');
    const table = document.getElementById('asset-serial-table');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);font-size:13px;padding:16px;">Memuat...</td></tr>';
    table.style.display = 'table';
    document.getElementById('modal-asset-serial').style.display = 'flex';

    // Show/hide PC Terpasang column
    const isPc = (category === 'pc');
    document.querySelectorAll('.pc-only-col').forEach(el => {
        el.style.display = isPc ? '' : 'none';
    });

    fetch(`/api/laboratory/${labId}/assets/${assetId}/serials-with-pc`)
        .then(r => r.json())
        .then(d => {
            tbody.innerHTML = '';
            if (!(d.serials || []).length) {
                table.style.display = 'none';
                document.getElementById('asset-serial-empty').style.display = 'block';
                return;
            }
            d.serials.forEach((s, idx) => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid var(--border-light)';
                tr.dataset.id = s.id;
                
                // No
                const tdNo = document.createElement('td');
                tdNo.textContent = idx + 1;
                tdNo.style.cssText = 'padding:10px; text-align:center; color:var(--text-muted); font-size:13px;';
                tr.appendChild(tdNo);

                // Parse Prefix and QR Code from separate columns or fallback
                let prefixVal = s.prefix || '';
                let qrVal = s.qr_code || s.serial_number || '';
                if (s.serial_number && !s.prefix && !s.qr_code) {
                    const lastDash = s.serial_number.lastIndexOf('-');
                    if (lastDash !== -1) {
                        prefixVal = s.serial_number.substring(0, lastDash);
                        qrVal = s.serial_number.substring(lastDash + 1);
                    }
                }
                
                // Kode Inventaris / Prefix Input
                const tdPrefix = document.createElement('td');
                tdPrefix.style.padding = '10px';
                const prefixInp = document.createElement('input');
                prefixInp.type = 'text';
                prefixInp.value = prefixVal;
                prefixInp.placeholder = 'Prefix...';
                prefixInp.className = 'js-serial-prefix';
                prefixInp.readOnly = !IS_SPV_SERIAL;
                prefixInp.style.cssText = 'width:100%; border:1px solid var(--border-main); background:' + (prefixInp.readOnly ? 'var(--bg-light)' : 'var(--bg-main)') + '; color:var(--text-normal); border-radius:6px; padding:6px 10px; font-size:13px; outline:none; box-sizing:border-box;';
                tdPrefix.appendChild(prefixInp);
                tr.appendChild(tdPrefix);

                // Scan QR / Kamera
                const tdQr = document.createElement('td');
                tdQr.style.padding = '10px';
                const qrContainer = document.createElement('div');
                qrContainer.style.cssText = 'display:flex; gap:4px; align-items:center;';
                
                const qrInp = document.createElement('input');
                qrInp.type = 'text';
                qrInp.value = qrVal;
                qrInp.placeholder = 'Scan QR...';
                qrInp.className = 'js-serial-qr';
                qrInp.readOnly = !IS_SPV_SERIAL;
                qrInp.style.cssText = 'flex:1; width:80px; min-width:0; border:1px solid var(--border-main); background:' + (qrInp.readOnly ? 'var(--bg-light)' : 'var(--bg-main)') + '; color:var(--text-normal); border-radius:6px; padding:6px 10px; font-size:13px; outline:none; box-sizing:border-box;';
                qrContainer.appendChild(qrInp);

                if (IS_SPV_SERIAL) {
                    const camBtn = document.createElement('button');
                    camBtn.type = 'button';
                    camBtn.innerHTML = '📷';
                    camBtn.style.cssText = 'background:var(--bg-light); border:1px solid var(--border-main); border-radius:6px; padding:6px 8px; cursor:pointer; font-size:13px;';
                    camBtn.onclick = () => startQrScannerForInput(qrInp);
                    qrContainer.appendChild(camBtn);
                }

                tdQr.appendChild(qrContainer);
                tr.appendChild(tdQr);

                // Status Badge
                const tdStatus = document.createElement('td');
                tdStatus.style.cssText = 'padding:10px; text-align:center;';
                const statusBadge = document.createElement('span');
                const isUsed = s.status === 'in_use';
                const statusBg = isUsed ? '#dbeafe' : '#f3f4f6';
                const statusText = isUsed ? '#1d4ed8' : '#4b5563';
                statusBadge.textContent = isUsed ? 'Terpasang' : 'Tersedia';
                statusBadge.style.cssText = `font-size:11px; font-weight:600; padding:3px 8px; border-radius:6px; background:${statusBg}; color:${statusText}; display:inline-block;`;
                tdStatus.appendChild(statusBadge);
                tr.appendChild(tdStatus);
                
                // Condition Badge
                const tdCond = document.createElement('td');
                tdCond.style.cssText = 'padding:10px; text-align:center;';
                const condBadge = document.createElement('span');
                condBadge.className = 'js-cond-badge';
                
                const updateCondBadge = (cond) => {
                    const labels = { good: 'Baik', damaged: 'Rusak', lost: 'Hilang' };
                    let bg = '#dcfce7', text = '#15803d';
                    if (cond === 'damaged') { bg = '#fee2e2'; text = '#b91c1c'; }
                    else if (cond === 'lost') { bg = '#f3f4f6'; text = '#4b5563'; }
                    
                    condBadge.textContent = labels[cond] || cond;
                    condBadge.style.cssText = `font-size:11px; font-weight:600; padding:3px 8px; border-radius:6px; background:${bg}; color:${text}; display:inline-block;`;
                };
                
                updateCondBadge(s.condition || 'good');
                tdCond.appendChild(condBadge);
                tr.appendChild(tdCond);

                // Pilih Kondisi buttons
                const tdPilihCond = document.createElement('td');
                tdPilihCond.style.cssText = 'padding:10px; text-align:center;';
                const btnGroup = document.createElement('div');
                btnGroup.style.cssText = 'display:flex; gap:4px; justify-content:center;';

                const conditions = [
                    { key: 'good', label: 'Baik' },
                    { key: 'damaged', label: 'Rusak' },
                    { key: 'lost', label: 'Hilang' }
                ];

                conditions.forEach(condOpt => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = condOpt.label;
                    btn.dataset.cond = condOpt.key;
                    btn.className = `cond-btn cond-${condOpt.key}` + (s.condition === condOpt.key ? ' active' : '');
                    btn.disabled = !IS_SPV_SERIAL;
                    
                    if (IS_SPV_SERIAL) {
                        btn.onclick = () => {
                            btnGroup.querySelectorAll('.cond-btn').forEach(b => b.classList.remove('active'));
                            btn.classList.add('active');
                            updateCondBadge(condOpt.key);
                        };
                    }
                    btnGroup.appendChild(btn);
                });

                tdPilihCond.appendChild(btnGroup);
                tr.appendChild(tdPilihCond);
                
                // PC Terpasang
                const tdPc = document.createElement('td');
                tdPc.className = 'pc-only-col';
                tdPc.style.cssText = 'padding:10px; font-size:13px; font-weight:500; color:var(--text-normal); text-align:left;';
                tdPc.textContent = s.pc_sku || '-';
                tdPc.style.display = isPc ? '' : 'none';
                tr.appendChild(tdPc);
                
                tbody.appendChild(tr);
            });
        })
        .catch(() => { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#f87171;font-size:13px;padding:16px;">Gagal memuat.</td></tr>'; });
}

function closeAssetSerialModal() {
    document.getElementById('modal-asset-serial').style.display = 'none';
    currentAssetSerialId = null;
}

function saveAssetSerials() {
    if (!currentAssetSerialId) return;
    const rows = document.querySelectorAll('#asset-serial-table-body tr[data-id]');
    const serials = Array.from(rows).map(tr => {
        const id = tr.dataset.id;
        const prefixInput = tr.querySelector('.js-serial-prefix');
        const qrInput = tr.querySelector('.js-serial-qr');
        
        const p = prefixInput ? prefixInput.value.trim() : '';
        const q = qrInput ? qrInput.value.trim() : '';
        const serial_number = q ? q : (p || '');
        
        const activeCondBtn = tr.querySelector('.cond-btn.active');
        const condition = activeCondBtn ? activeCondBtn.dataset.cond : 'good';
        
        return { id, prefix: p, qr_code: q, serial_number, condition };
    });

    fetch(`/api/laboratory/${labId}/assets/${currentAssetSerialId}/serials/sync`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ serials })
    })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                closeAssetSerialModal();
                window.location.reload();
            } else {
                alert('Gagal menyimpan.');
            }
        })
        .catch(() => alert('Gagal menyimpan.'));
}

document.getElementById('modal-asset-serial').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAssetSerialModal();
});

/* ─── QR Code Scanner ─── */
let modalHtml5QrCode = null;
let activeQrInput = null;

window.startQrScannerForInput = async function(inputEl) {
    activeQrInput = inputEl;
    const overlay = document.getElementById('modal-qr-scanner-overlay');
    if (!overlay) return;

    overlay.style.display = 'flex';

    try {
        if (typeof Html5Qrcode === 'undefined') {
            document.getElementById('modal-qr-reader').innerHTML = 
                '<div style="color:#fff; display:flex; align-items:center; justify-content:center; height:280px; font-size:13px;">Loading camera library...</div>';
            
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = "https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js";
                script.onload = resolve;
                script.onerror = () => reject(new Error("Gagal memuat library scanner."));
                document.head.appendChild(script);
            });
            
            document.getElementById('modal-qr-reader').innerHTML = '';
        }

        modalHtml5QrCode = new Html5Qrcode("modal-qr-reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        modalHtml5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                if (activeQrInput) {
                    activeQrInput.value = decodedText;
                    activeQrInput.dispatchEvent(new Event('input', { bubbles: true }));
                    activeQrInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                stopModalQrScanner();
            },
            (errorMessage) => {
                // ignore errors
            }
        ).catch(err => {
            console.error("Unable to start scanner", err);
            document.getElementById('modal-qr-reader').innerHTML = 
                `<div style="color:#f87171; display:flex; align-items:center; justify-content:center; height:280px; font-size:13px; text-align:center; padding:16px;">Error: ${err.message || err}</div>`;
        });
    } catch (err) {
        console.error("Scanner library load error", err);
        document.getElementById('modal-qr-reader').innerHTML = 
            `<div style="color:#f87171; display:flex; align-items:center; justify-content:center; height:280px; font-size:13px; text-align:center; padding:16px;">Gagal memuat library kamera. Pastikan koneksi internet aktif.</div>`;
    }
};

window.stopModalQrScanner = function() {
    const overlay = document.getElementById('modal-qr-scanner-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
    if (modalHtml5QrCode) {
        modalHtml5QrCode.stop().then(() => {
            modalHtml5QrCode = null;
            activeQrInput = null;
        }).catch(err => {
            console.error("Failed to stop scanner", err);
            modalHtml5QrCode = null;
            activeQrInput = null;
        });
    } else {
        activeQrInput = null;
    }
};

// ── View PC Modal ──
function openViewPcModal(pcId) {
    const m = document.getElementById('modal-view-pc-' + pcId);
    if (!m) return;
    m.style.display = 'flex';
}
function closeViewPcModal(pcId) {
    const m = document.getElementById('modal-view-pc-' + pcId);
    if (m) m.style.display = 'none';
}

// Close modal view PC on backdrop click
document.querySelectorAll('[id^="modal-view-pc-"]').forEach(m => {
    m.addEventListener('click', e => {
        if (e.target === e.currentTarget) {
            const pcId = m.id.replace('modal-view-pc-', '');
            closeViewPcModal(pcId);
        }
    });
});
@endif
</script>

{{-- QR Scanner Overlay --}}
<div id="modal-qr-scanner-overlay" onclick="if(event.target===this) stopModalQrScanner()" style="display:none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 99999;">
    <div style="background: var(--bg-card, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 16px; width: 90%; max-width: 480px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); display: flex; flex-direction: column; gap: 16px;" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary, #111b4c);">Scan QR Code</div>
            <button type="button" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted, #9ca3af);" onclick="stopModalQrScanner()">&times;</button>
        </div>
        <div id="modal-qr-reader" style="width: 100%; border-radius: 12px; overflow: hidden; background: #000; min-height: 280px;"></div>
        <p style="font-size: .8rem; color: var(--text-muted); text-align: center; margin: 0;">
            Arahkan QR Code ke kamera untuk memindai otomatis.
        </p>
    </div>
</div>

<style>
.cond-btn {
    border: 1px solid var(--border-main);
    background: var(--bg-main);
    color: var(--text-normal);
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
}
.cond-btn.cond-good.active {
    background: #22c55e;
    border-color: #22c55e;
    color: #fff;
}
.cond-btn.cond-damaged.active {
    background: #ef4444;
    border-color: #ef4444;
    color: #fff;
}
.cond-btn.cond-lost.active {
    background: #6b7280;
    border-color: #6b7280;
    color: #fff;
}
</style>
@endpush
