@extends('panel.content')
@section('title', $laboratory->lab_name)

@section('content')

@php
$electronicAssets    = $allAssets->filter(fn($a) => $a->asset_category === 'electronic')->values();
$nonElectronicAssets = $allAssets->filter(fn($a) => $a->asset_category !== 'electronic')->values();
$existingElectric    = $laboratory->assets->filter(fn($a) => $a->asset_category === 'electronic')->values();
$existingNonElectric = $laboratory->assets->filter(fn($a) => $a->asset_category !== 'electronic')->values();
@endphp

<div class="db-wrap">

    {{-- ── HEADER ── --}}
    <div class="db-card" style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <div style="flex:1; min-width:0;">
            <h2 style="font-size:20px; font-weight:700; color:#111827; margin:0 0 6px;">{{ $laboratory->lab_name }}</h2>
            <p style="font-size:13px; color:#6b7280; margin:0 0 14px;">
                Capacity: <strong>{{ $laboratory->capacity }} PC</strong> &nbsp;·&nbsp;
                Active: <strong style="color:#16a34a;">{{ $totalActive }}</strong> &nbsp;·&nbsp;
                Inactive: <strong style="color:#dc2626;">{{ $totalInactive }}</strong>
            </p>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:8px;">
                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px 14px;">
                    <p style="font-size:11px; color:#9ca3af; font-weight:600; margin:0 0 3px; text-transform:uppercase; letter-spacing:.5px;">PIC</p>
                    <p style="font-size:13px; font-weight:600; color:#374151; margin:0;">{{ $pic?->name ?? '-' }}</p>
                </div>
                @forelse($admins as $admin)
                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px 14px;">
                    <p style="font-size:11px; color:#9ca3af; font-weight:600; margin:0 0 3px; text-transform:uppercase; letter-spacing:.5px;">Admin</p>
                    <p style="font-size:13px; font-weight:600; color:#374151; margin:0;">{{ $admin->name }}</p>
                </div>
                @empty
                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px 14px;">
                    <p style="font-size:11px; color:#9ca3af; font-weight:600; margin:0 0 3px; text-transform:uppercase; letter-spacing:.5px;">Admin</p>
                    <p style="font-size:13px; color:#9ca3af; margin:0;">-</p>
                </div>
                @endforelse
                @forelse($assistants as $asst)
                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px 14px;">
                    <p style="font-size:11px; color:#9ca3af; font-weight:600; margin:0 0 3px; text-transform:uppercase; letter-spacing:.5px;">Assistant</p>
                    <p style="font-size:13px; font-weight:600; color:#374151; margin:0;">{{ $asst->name }}</p>
                </div>
                @empty
                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px 14px;">
                    <p style="font-size:11px; color:#9ca3af; font-weight:600; margin:0 0 3px; text-transform:uppercase; letter-spacing:.5px;">Assistant</p>
                    <p style="font-size:13px; color:#9ca3af; margin:0;">-</p>
                </div>
                @endforelse
            </div>
        </div>
        <div style="display:flex; gap:8px; flex-shrink:0;">
            @if($canEdit)
            <button onclick="openEditLabModal()"
                    style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:500; color:#374151;">
                Edit Lab
            </button>
            @endif
            <a href="{{ route('laboratory.index') }}"
               style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:8px 16px; font-size:13px; text-decoration:none; color:#374151; font-weight:500;">
                ← Back
            </a>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7; color:#166534; border-radius:8px; padding:10px 16px; font-size:13px;">
        {{ session('success') }}
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
                                        onclick='openEditPcModal({{ $pc->id }},{{ $loop->index }},"{{ addslashes($pc->type_pc) }}","{{ addslashes($pc->status_pc) }}","{{ addslashes($pc->processor??'') }}","{{ addslashes($pc->ram??'') }}","{{ addslashes($pc->ssd??'') }}","{{ addslashes($pc->motherboard??'') }}","{{ addslashes($pc->vga??'') }}","{{ addslashes($pc->cpu_fan??'') }}","{{ addslashes($pc->powersupply??'') }}")'
                                        class="action-btn action-edit" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('pc.destroy', [$laboratory->id, $pc->id]) }}"
                                      onsubmit="return confirm('Hapus PC ini?')" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn action-delete" title="Hapus">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6M14 11v6"/>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </form>
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

    {{-- ══ SECTION 2: ASSET INFORMATION ══ --}}
    <div id="section-asset" class="db-card" style="display:none; padding:0; overflow:hidden;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 24px 14px; border-bottom:1px solid #f3f4f6;">
            <h3 style="font-size:15px; font-weight:700; color:#111827; margin:0;">Asset Information</h3>
            @if($canEdit)
            <button onclick="openAddAssetModal()"
                    style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:600;">
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
                                    <button type="submit" style="width:24px; height:24px; border:1px solid #d1d5db; border-radius:4px; background:#fff; cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center;">−</button>
                                </form>
                                <span style="min-width:28px; text-align:center; font-weight:600; font-size:13px;">{{ $asset->pivot->total_good_lab }}</span>
                                <form method="POST" action="{{ route('lab.assetlab.adjust', [$laboratory->id, $asset->id]) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="field" value="total_good_lab">
                                    <input type="hidden" name="action" value="increment">
                                    <button type="submit" style="width:24px; height:24px; border:1px solid #d1d5db; border-radius:4px; background:#fff; cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center;">+</button>
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
                                    <button type="submit" style="width:24px; height:24px; border:1px solid #d1d5db; border-radius:4px; background:#fff; cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center;">−</button>
                                </form>
                                <span style="min-width:28px; text-align:center; font-weight:600; font-size:13px; color:#dc2626;">{{ $asset->pivot->total_damaged_lab }}</span>
                                <form method="POST" action="{{ route('lab.assetlab.adjust', [$laboratory->id, $asset->id]) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="field" value="total_damaged_lab">
                                    <input type="hidden" name="action" value="increment">
                                    <button type="submit" style="width:24px; height:24px; border:1px solid #d1d5db; border-radius:4px; background:#fff; cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center;">+</button>
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
                                    <button type="submit" style="width:24px; height:24px; border:1px solid #d1d5db; border-radius:4px; background:#fff; cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center;">−</button>
                                </form>
                                <span style="min-width:28px; text-align:center; font-weight:600; font-size:13px; color:#f59e0b;">{{ $asset->pivot->total_loss_lab }}</span>
                                <form method="POST" action="{{ route('lab.assetlab.adjust', [$laboratory->id, $asset->id]) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="field" value="total_loss_lab">
                                    <input type="hidden" name="action" value="increment">
                                    <button type="submit" style="width:24px; height:24px; border:1px solid #d1d5db; border-radius:4px; background:#fff; cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center;">+</button>
                                </form>
                            </div>
                        </td>
                        @else
                        <td style="text-align:center; font-weight:600; font-size:13px;">{{ $asset->pivot->total_good_lab }}</td>
                        <td style="text-align:center; font-weight:600; font-size:13px; color:#dc2626;">{{ $asset->pivot->total_damaged_lab }}</td>
                        <td style="text-align:center; font-weight:600; font-size:13px; color:#f59e0b;">{{ $asset->pivot->total_loss_lab }}</td>
                        @endif

                        <td style="text-align:center; font-weight:700; font-size:14px; color:#111827;">
                            {{ $asset->pivot->total_asset_lab }}
                        </td>

                        @if($canEdit)
                        <td style="text-align:center;">
                            <form method="POST" action="{{ route('lab.assetlab.remove', [$laboratory->id, $asset->id]) }}"
                                  onsubmit="return confirm('Hapus aset ini dari lab?')" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-delete" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canEdit ? 7 : 6 }}" style="text-align:center; padding:32px; color:#9ca3af; font-size:13px;">Belum ada aset di lab ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 24px; border-top:1px solid #f3f4f6;">
            <button type="button" onclick="showSection('pc')"
                    style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:500; display:inline-flex; align-items:center; gap:6px;">
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
    <div style="background:#fff; border-radius:16px; width:100%; max-width:500px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Add PC</h3>
            <button onclick="closeAddPcModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px;">&times;</button>
        </div>
        <form method="POST" action="{{ route('pc.store', $laboratory->id) }}" style="overflow-y:auto; flex:1; padding:24px; display:flex; flex-direction:column; gap:14px;">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Type</label>
                    <select name="type_pc" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none;">
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Processor</label>
                    <input type="text" name="processor" placeholder="e.g. i3-8100"
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
                </div>
            </div>
            @foreach(['ram'=>'RAM','ssd'=>'SSD','motherboard'=>'Motherboard','vga'=>'VGA','cpu_fan'=>'CPU Fan','powersupply'=>'Power Supply'] as $f => $l)
            <div>
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">{{ $l }}</label>
                <input type="text" name="{{ $f }}" placeholder="Enter here..."
                       style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
            </div>
            @endforeach
            <div style="display:flex; justify-content:flex-end; gap:8px; padding-top:4px;">
                <button type="button" onclick="closeAddPcModal()"
                        style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit"
                        style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ MODAL EDIT PC ══ --}}
<div id="modal-edit-pc" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:500px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
            <h3 id="edit-pc-title" style="font-size:16px; font-weight:700; color:#111827; margin:0;">Edit PC</h3>
            <button onclick="closeEditPcModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px;">&times;</button>
        </div>
        <form method="POST" id="editPcForm" style="overflow-y:auto; flex:1; padding:24px; display:flex; flex-direction:column; gap:14px;">
            @csrf @method('PUT')
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Type</label>
                    <select name="type_pc" id="epc_type_pc" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none;">
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Status</label>
                    <select name="status_pc" id="epc_status_pc" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            @php $pcFields = ['processor'=>'Processor','ram'=>'RAM','ssd'=>'SSD','motherboard'=>'Motherboard','vga'=>'VGA','cpu_fan'=>'CPU Fan','powersupply'=>'Power Supply']; @endphp

            @foreach($pcFields as $f => $l)
            <div style="position:relative;">
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">{{ $l }}</label>
                <input type="hidden" name="{{ $f }}" id="epc_{{ $f }}_val">
                <input type="text" id="epc_{{ $f }}_search"
                       placeholder="Search component or type manually..."
                       autocomplete="off"
                       oninput="filterDropdown('{{ $f }}')"
                       onfocus="showDropdown('{{ $f }}')"
                       style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
                <div id="epc_{{ $f }}_dropdown"
                     style="display:none; position:absolute; z-index:200; background:#fff; border:1px solid #d1d5db; border-radius:8px; width:100%; max-height:160px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,.1); top:calc(100% + 2px); left:0;">
                </div>
            </div>
            @endforeach

            <div style="display:flex; justify-content:flex-end; gap:8px; padding-top:4px;">
                <button type="button" onclick="closeEditPcModal()"
                        style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit"
                        style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ MODAL ADD ASSET ══ --}}
<div id="modal-add-asset" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:500px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Add Asset</h3>
            <button onclick="closeAddAssetModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px;">&times;</button>
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
            <input type="hidden" name="lab_assets[ex{{ $idx }}][quantity]" value="{{ $ast->pivot->total_asset_lab }}">
            @endforeach

            <div>
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Asset Name:</label>
                <select name="lab_assets[new0][asset_id]"
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none;">
                    <option value="">Choose asset...</option>
                    @foreach($allAssets as $a)
                    <option value="{{ $a->id }}">{{ $a->asset_name }} ({{ ucfirst($a->asset_category) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Quantity:</label>
                <input type="number" name="lab_assets[new0][quantity]" value="1" min="1"
                       style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeAddAssetModal()"
                        style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit"
                        style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Add</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ MODAL EDIT LAB (3 steps) ══ --}}
<div id="modal-edit-lab" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:560px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Edit Laboratory</h3>
            <button onclick="closeEditLabModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px;">&times;</button>
        </div>
        <div style="display:flex; padding:16px 24px 0; gap:6px;">
            <div id="elab-bar-1" style="flex:1; height:4px; border-radius:2px; background:#111B4C;"></div>
            <div id="elab-bar-2" style="flex:1; height:4px; border-radius:2px; background:#e5e7eb;"></div>
            <div id="elab-bar-3" style="flex:1; height:4px; border-radius:2px; background:#e5e7eb;"></div>
        </div>
        <form method="POST" action="{{ route('laboratory.update', $laboratory->id) }}" id="editLabForm">
            @csrf @method('PUT')

            <div id="elab-step-1" style="padding:24px;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 20px; text-align:center;">General Information</h4>
                <div style="margin-bottom:14px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Laboratory Name:</label>
                    <input type="text" name="lab_name" id="elab_name" value="{{ $laboratory->lab_name }}"
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">PC Capacity:</label>
                    <input type="number" name="capacity" id="elab_capacity" value="{{ $laboratory->capacity }}" min="1"
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px;">
                    <button type="button" onclick="closeEditLabModal()"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                    <button type="button" onclick="elabGoStep(2)"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Next</button>
                </div>
            </div>

            <div id="elab-step-2" style="display:none; padding:24px; max-height:55vh; overflow-y:auto;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 16px; text-align:center;">PC Information</h4>
                <div id="elab-pc-list"></div>
                <div style="display:flex; justify-content:space-between; gap:8px; margin-top:20px;">
                    <button type="button" onclick="elabGoStep(1)"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Back</button>
                    <button type="button" onclick="elabGoStep(3)"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Next</button>
                </div>
            </div>

            <div id="elab-step-3" style="display:none; padding:24px; max-height:55vh; overflow-y:auto;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 16px; text-align:center;">Asset Information</h4>
                <div style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:12px; overflow:hidden;">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                        <span style="font-size:13px; font-weight:600; color:#374151;">Electronic Category</span>
                        <button type="button" onclick="addAssetRow('electronic')"
                                style="background:#111B4C; color:#fff; border:none; border-radius:6px; padding:5px 12px; font-size:13px; cursor:pointer;">+</button>
                    </div>
                    <div id="elab-electronic-rows" style="padding:12px; display:flex; flex-direction:column; gap:10px;">
                        @foreach($existingElectric as $idx => $asset)
                        <div class="asset-row" style="border:1px solid #e5e7eb; border-radius:8px; padding:12px; position:relative;">
                            <button type="button" onclick="removeAssetRow(this)"
                                    style="position:absolute; top:6px; right:8px; background:none; border:none; cursor:pointer; color:#9ca3af; font-size:16px;">×</button>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <div>
                                    <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Asset Name:</label>
                                    <select name="lab_assets[e{{ $idx }}][asset_id]"
                                            style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                                        <option value="">Choose asset...</option>
                                        @foreach($electronicAssets as $a)
                                        <option value="{{ $a->id }}" {{ $a->id == $asset->id ? 'selected' : '' }}>{{ $a->asset_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Quantity:</label>
                                    <input type="number" name="lab_assets[e{{ $idx }}][quantity]"
                                           value="{{ $asset->pivot->total_asset_lab }}" min="0"
                                           style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                        <span style="font-size:13px; font-weight:600; color:#374151;">Non-Electronic Category</span>
                        <button type="button" onclick="addAssetRow('non-electronic')"
                                style="background:#111B4C; color:#fff; border:none; border-radius:6px; padding:5px 12px; font-size:13px; cursor:pointer;">+</button>
                    </div>
                    <div id="elab-non-electronic-rows" style="padding:12px; display:flex; flex-direction:column; gap:10px;">
                        @foreach($existingNonElectric as $idx => $asset)
                        <div class="asset-row" style="border:1px solid #e5e7eb; border-radius:8px; padding:12px; position:relative;">
                            <button type="button" onclick="removeAssetRow(this)"
                                    style="position:absolute; top:6px; right:8px; background:none; border:none; cursor:pointer; color:#9ca3af; font-size:16px;">×</button>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <div>
                                    <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Asset Name:</label>
                                    <select name="lab_assets[ne{{ $idx }}][asset_id]"
                                            style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                                        <option value="">Choose asset...</option>
                                        @foreach($nonElectronicAssets as $a)
                                        <option value="{{ $a->id }}" {{ $a->id == $asset->id ? 'selected' : '' }}>{{ $a->asset_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Quantity:</label>
                                    <input type="number" name="lab_assets[ne{{ $idx }}][quantity]"
                                           value="{{ $asset->pivot->total_asset_lab }}" min="0"
                                           style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; gap:8px; margin-top:20px;">
                    <button type="button" onclick="elabGoStep(2)"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Back</button>
                    <button type="submit"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Update</button>
                </div>
            </div>
        </form>
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

@if($canEdit)
// ── Add PC ──
function openAddPcModal()  { document.getElementById('modal-add-pc').style.display = 'flex'; }
function closeAddPcModal() { document.getElementById('modal-add-pc').style.display = 'none'; }
document.getElementById('modal-add-pc').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAddPcModal();
});

// ── Edit PC ──
function openEditPcModal(pcId, index, type, status, processor, ram, ssd, motherboard, vga, cpuFan, ps) {
    document.getElementById('edit-pc-title').textContent = `Edit PC-${String(index).padStart(2,'0')}`;
    document.getElementById('editPcForm').action = `/laboratory/${labId}/pc/${pcId}`;
    document.getElementById('epc_type_pc').value   = type;
    document.getElementById('epc_status_pc').value = status;

    const values = { processor, ram, ssd, motherboard, vga, cpu_fan: cpuFan, powersupply: ps };
    pcFields.forEach(f => {
        const val = values[f] || '';
        document.getElementById(`epc_${f}_val`).value    = val;
        document.getElementById(`epc_${f}_search`).value = val;
        document.getElementById(`epc_${f}_dropdown`).style.display = 'none';
    });

    document.getElementById('modal-edit-pc').style.display = 'flex';
}
function closeEditPcModal() { document.getElementById('modal-edit-pc').style.display = 'none'; }
document.getElementById('modal-edit-pc').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeEditPcModal();
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

    // Empty option
    const emptyOpt = document.createElement('div');
    emptyOpt.textContent = '— Kosongkan / Ketik Manual —';
    emptyOpt.style.cssText = 'padding:8px 12px; font-size:13px; cursor:pointer; color:#9ca3af; border-bottom:1px solid #f3f4f6;';
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
                <span style="color:${disabled?'#9ca3af':'#374151'};">${comp.name}</span>
                <span style="font-size:11px; background:${disabled?'#fee2e2':'#dcfce7'}; color:${disabled?'#dc2626':'#16a34a'}; padding:2px 6px; border-radius:4px; font-weight:600;">
                    Stok: ${comp.stock}
                </span>`;
            if (!disabled) {
                item.onmousedown = () => selectComponent(field, comp.name, comp.name);
                item.onmouseover = () => item.style.background = '#f9fafb';
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
            // Sync hidden input with search text before closing
            if (dropdown.style.display !== 'none') {
                document.getElementById(`epc_${f}_val`).value = search.value;
                dropdown.style.display = 'none';
            }
        }
    });
});

// ── Add Asset ──
function openAddAssetModal()  { document.getElementById('modal-add-asset').style.display = 'flex'; }
function closeAddAssetModal() { document.getElementById('modal-add-asset').style.display = 'none'; }
document.getElementById('modal-add-asset').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAddAssetModal();
});

// ── Edit Lab ──
function openEditLabModal() { document.getElementById('modal-edit-lab').style.display='flex'; elabGoStep(1); }
function closeEditLabModal(){ document.getElementById('modal-edit-lab').style.display='none'; }
function elabGoStep(step) {
    [1,2,3].forEach(s => {
        document.getElementById(`elab-step-${s}`).style.display = s===step ? 'block' : 'none';
        document.getElementById(`elab-bar-${s}`).style.background = s<step ? '#98083D' : s===step ? '#111B4C' : '#e5e7eb';
    });
    if (step===2) {
        const cap = parseInt(document.getElementById('elab_capacity').value);
        if (!cap||cap<1) { alert('Isi kapasitas terlebih dahulu.'); elabGoStep(1); return; }
        buildElabPcList(cap);
    }
}
function buildElabPcList(cap) {
    const c = document.getElementById('elab-pc-list');
    c.innerHTML = '';
    for (let i=0; i<cap; i++) {
        const pc = existingPcs[i] || null;
        c.innerHTML += `
        <details style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:10px;" ${i===0?'open':''}>
            <summary style="padding:10px 14px; font-size:13px; font-weight:600; cursor:pointer; background:#f9fafb; display:flex; justify-content:space-between;">
                PC-${String(i).padStart(2,'0')}
                ${!pc?'<span style="font-size:11px;background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:4px;">New</span>':''}
            </summary>
            <div style="padding:14px; display:grid; gap:10px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Type</label>
                        <select name="pcs[${i}][type_pc]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px;">
                            <option value="mahasiswa" ${pc?.type_pc==='mahasiswa'?'selected':''}>Mahasiswa</option>
                            <option value="dosen" ${pc?.type_pc==='dosen'?'selected':''}>Dosen</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Status</label>
                        <select name="pcs[${i}][status_pc]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px;">
                            <option value="active" ${!pc||pc.status_pc==='active'?'selected':''}>Active</option>
                            <option value="inactive" ${pc?.status_pc==='inactive'?'selected':''}>Inactive</option>
                        </select>
                    </div>
                </div>
                ${['processor','ram','ssd','motherboard','vga','cpu_fan','powersupply'].map(f=>`
                <div>
                    <label style="font-size:12px;color:#6b7280;display:block;margin-bottom:4px;">${f.replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase())}</label>
                    <input type="text" name="pcs[${i}][${f}]" value="${pc?.[f]??''}"
                           style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:13px;box-sizing:border-box;">
                </div>`).join('')}
                ${pc?`<input type="hidden" name="pcs[${i}][id]" value="${pc.id}">`:''}
            </div>
        </details>`;
    }
}
function addAssetRow(type) {
    const idx = assetCounter++;
    const container = document.getElementById(type==='electronic' ? 'elab-electronic-rows' : 'elab-non-electronic-rows');
    const options   = type==='electronic' ? electronicOptions : nonElectronicOptions;
    const div = document.createElement('div');
    div.className='asset-row';
    div.style.cssText='border:1px solid #e5e7eb;border-radius:8px;padding:12px;position:relative;';
    div.innerHTML=`
        <button type="button" onclick="removeAssetRow(this)"
                style="position:absolute;top:6px;right:8px;background:none;border:none;cursor:pointer;color:#9ca3af;font-size:16px;">×</button>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <label style="font-size:12px;color:#6b7280;display:block;margin-bottom:4px;">Asset Name:</label>
                <select name="lab_assets[${idx}][asset_id]"
                        style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:13px;box-sizing:border-box;">
                    <option value="">Choose asset...</option>
                    ${options.map(o=>`<option value="${o.id}">${o.name}</option>`).join('')}
                </select>
            </div>
            <div>
                <label style="font-size:12px;color:#6b7280;display:block;margin-bottom:4px;">Quantity:</label>
                <input type="number" name="lab_assets[${idx}][quantity]" value="0" min="0"
                       style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 10px;font-size:13px;box-sizing:border-box;">
            </div>
        </div>`;
    container.appendChild(div);
}
function removeAssetRow(btn) { btn.closest('.asset-row').remove(); }
document.getElementById('modal-edit-lab').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeEditLabModal();
});
@endif
</script>
@endpush