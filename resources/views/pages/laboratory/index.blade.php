@extends('panel.content')

@section('title', 'Laboratory')

@section('content')

<div class="db-wrap">
    <div class="db-card" style="padding:0; overflow:hidden;">

        {{-- ── HEADER ── --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px; flex-wrap:wrap; gap:12px;">
            <h2 style="font-size:18px; font-weight:700; color:#111827; margin:0;">Laboratory List</h2>
            <div style="display:flex; gap:10px; align-items:center;">

                {{-- Search --}}
                <div style="position:relative;">
                    <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af;"
                         width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search..."
                           oninput="filterTable()"
                           style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 14px 8px 32px; font-size:13px; outline:none; width:220px; color:#374151;">
                </div>

                {{-- Filter --}}
                <button style="display:flex; align-items:center; gap:6px; border:1px solid #e5e7eb; background:#fff; border-radius:8px; padding:8px 14px; font-size:13px; cursor:pointer; color:#374151; font-weight:500;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Filter
                </button>

                {{-- Add Lab (SPV only) --}}
                @if(auth()->user()->role === 'spv inventory')
                <button onclick="openCreateModal()"
                        style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:600; display:flex; align-items:center; gap:6px;">
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

        {{-- ── TABLE ── --}}
        <div style="overflow-x:auto;">
            <table class="db-table" id="labTable">
                <thead>
                    <tr>
                        <th style="width:40px; padding-left:20px;">
                            <input type="checkbox" class="db-checkbox" id="checkAll" onclick="toggleAll(this)">
                        </th>
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
                        $isMyLab = in_array($lab->id, $myLabIds) || auth()->user()->role === 'spv inventory';
                        $adminUser = $lab->users->first();
                    @endphp
                    <tr>
                        <td style="padding-left:20px;">
                            <input type="checkbox" class="db-checkbox row-check">
                        </td>
                        <td style="font-weight:600; color:#111827;">{{ $lab->lab_name }}</td>
                        <td>{{ $lab->capacity }} PC</td>
                        <td>{{ $adminUser?->name ?? '-' }}</td>
                        <td>
                            <span style="color:#16a34a; font-weight:600;">{{ $lab->total_pc_active ?? 0 }}</span>
                        </td>
                        <td>
                            <span style="color:#dc2626; font-weight:600;">{{ $lab->total_pc_inactive ?? 0 }}</span>
                        </td>
                        <td>
                            <div class="action-btns">

                                {{-- View — semua bisa --}}
                                <a href="{{ route('laboratory.show', $lab->id) }}"
                                   class="action-btn" title="View" style="color:#6b7280;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                {{-- Edit & Delete — hanya lab sendiri atau SPV --}}
                                @if($isMyLab)
                                <button onclick="openEditModal({{ $lab->id }}, '{{ addslashes($lab->lab_name) }}', {{ $lab->capacity }})"
                                        class="action-btn action-edit" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>

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
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:40px; color:#9ca3af; font-size:13px;">
                            Belum ada laboratorium
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── PAGINATION ── --}}
        @if($laboratories->hasPages())
        <div style="padding:16px 24px; display:flex; justify-content:flex-end;">
            {{ $laboratories->links() }}
        </div>
        @endif

    </div>
</div>

{{-- ── MODAL CREATE (SPV only) ── --}}
@if(auth()->user()->role === 'spv inventory')
<div id="modal-create" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:500px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Create Laboratory</h3>
            <button onclick="closeCreateModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px; line-height:1;">&times;</button>
        </div>
        <form method="POST" action="{{ route('laboratory.store') }}" id="createLabForm">
            @csrf
            <div style="display:flex; padding:16px 24px 0; gap:8px;">
                <div id="step-bar-1" style="flex:1; height:4px; border-radius:2px; background:#111B4C;"></div>
                <div id="step-bar-2" style="flex:1; height:4px; border-radius:2px; background:#e5e7eb;"></div>
            </div>

            {{-- Step 1 --}}
            <div id="step-1" style="padding:24px;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 20px; text-align:center;">General Information</h4>
                <div style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Laboratory Name:</label>
                    <input type="text" name="lab_name" placeholder="Enter here..."
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">PC Capacity:</label>
                    <input type="number" name="capacity" id="capacityInput" placeholder="Enter here..." min="1"
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:24px;">
                    <button type="button" onclick="closeCreateModal()"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                    <button type="button" onclick="goToStep2()"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Next</button>
                </div>
            </div>

            {{-- Step 2 --}}
            <div id="step-2" style="display:none; padding:24px; max-height:60vh; overflow-y:auto;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 20px; text-align:center;">PC Information</h4>
                <div id="pc-list"></div>
                <div style="display:flex; justify-content:space-between; gap:8px; margin-top:24px;">
                    <button type="button" onclick="goToStep1()"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Back</button>
                    <button type="submit"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ── MODAL EDIT ── --}}
<div id="modal-edit" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:440px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Edit Laboratory</h3>
            <button onclick="closeEditModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px; line-height:1;">&times;</button>
        </div>
        <form method="POST" id="editLabForm" style="padding:24px;">
            @csrf @method('PUT')
            <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 20px; text-align:center;">General Information</h4>
            <div style="margin-bottom:16px;">
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Laboratory Name:</label>
                <input type="text" name="lab_name" id="edit_lab_name"
                       style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">PC Capacity:</label>
                <input type="number" name="capacity" id="edit_capacity" min="1"
                       style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:24px;">
                <button type="button" onclick="closeEditModal()"
                        style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit"
                        style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function filterTable() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#labTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    function toggleAll(master) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
    }

    // ── Create Modal ──
    function openCreateModal() {
        document.getElementById('modal-create').style.display = 'flex';
    }
    function closeCreateModal() {
        document.getElementById('modal-create').style.display = 'none';
        document.getElementById('createLabForm').reset();
        document.getElementById('pc-list').innerHTML = '';
        document.getElementById('step-1').style.display = 'block';
        document.getElementById('step-2').style.display = 'none';
        document.getElementById('step-bar-2').style.background = '#e5e7eb';
    }
    function goToStep2() {
        const name = document.querySelector('[name=lab_name]').value;
        const cap  = parseInt(document.getElementById('capacityInput').value);
        if (!name || !cap || cap < 1) { alert('Isi nama lab dan kapasitas terlebih dahulu.'); return; }
        document.getElementById('step-1').style.display = 'none';
        document.getElementById('step-2').style.display = 'block';
        document.getElementById('step-bar-2').style.background = '#98083D';
        buildPcList(cap);
    }
    function goToStep1() {
        document.getElementById('step-1').style.display = 'block';
        document.getElementById('step-2').style.display = 'none';
        document.getElementById('step-bar-2').style.background = '#e5e7eb';
    }
    function buildPcList(cap) {
        const c = document.getElementById('pc-list');
        c.innerHTML = '';
        for (let i = 0; i < cap; i++) {
            c.innerHTML += `
            <details style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:10px;">
                <summary style="padding:12px 16px; font-size:13px; font-weight:600; cursor:pointer; background:#f9fafb;">
                    PC-${String(i).padStart(2,'0')}
                </summary>
                <div style="padding:16px; display:grid; gap:10px;">
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
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">RAM</label>
                            <input type="text" name="pcs[${i}][ram]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">SSD</label>
                            <input type="text" name="pcs[${i}][ssd]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Motherboard</label>
                            <input type="text" name="pcs[${i}][motherboard]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">VGA</label>
                            <input type="text" name="pcs[${i}][vga]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">CPU Fan</label>
                            <input type="text" name="pcs[${i}][cpu_fan]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Power Supply</label>
                            <input type="text" name="pcs[${i}][powersupply]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                    </div>
                </div>
            </details>`;
        }
    }

    // ── Edit Modal ──
    function openEditModal(id, labName, capacity) {
        document.getElementById('edit_lab_name').value = labName;
        document.getElementById('edit_capacity').value = capacity;
        document.getElementById('editLabForm').action = `/laboratory/${id}`;
        document.getElementById('modal-edit').style.display = 'flex';
    }
    function closeEditModal() {
        document.getElementById('modal-edit').style.display = 'none';
    }

    // close on overlay click
    document.getElementById('modal-edit').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
    @if(auth()->user()->role === 'spv inventory')
    document.getElementById('modal-create').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });
    @endif
</script>
@endpush
