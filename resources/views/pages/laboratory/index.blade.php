@extends('panel.content')

@section('title', 'Laboratory')

@section('content')

<div class="db-wrap">
    <div class="db-card db-table-card">

        {{-- header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px;">
            <h2 class="db-card-title" style="margin:0">Laboratory List</h2>
            <div style="display:flex; gap:10px; align-items:center;">
                <input type="text" id="searchInput" placeholder="Search..."
                       oninput="filterTable()"
                       style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 14px; font-size:13px; outline:none; width:200px;">
                <button onclick="exportTable()"
                        style="border:1px solid #e5e7eb; background:#fff; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:500;">
                    Export
                </button>
                <button onclick="openCreateModal()"
                        style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:600; display:flex; align-items:center; gap:6px;">
                    + Add Lab
                </button>
            </div>
        </div>

        @if(session('success'))
        <div style="margin:0 24px 12px; background:#dcfce7; color:#166534; border-radius:8px; padding:10px 16px; font-size:13px;">
            {{ session('success') }}
        </div>
        @endif

        <div class="table-wrap">
            <table class="db-table" id="labTable">
                <thead>
                    <tr>
                        <th class="th-check">
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
                    <tr>
                        <td class="th-check">
                            <input type="checkbox" class="db-checkbox row-check">
                        </td>
                        <td style="font-weight:600;">{{ $lab->lab_name }}</td>
                        <td>{{ $lab->capacity }} PC</td>
                        <td>
                            @if($lab->users->isNotEmpty())
                                {{ $lab->users->first()->name }}
                            @else
                                <span style="color:#9ca3af;">-</span>
                            @endif
                        </td>
                        <td>{{ $lab->total_pc_active ?? 0 }}</td>
                        <td>{{ $lab->total_pc_inactive ?? 0 }}</td>
                        <td>
                            <div class="action-btns">
                                {{-- detail --}}
                                <a href="{{ route('laboratory.show', $lab->id) }}"
                                   class="action-btn" title="Detail"
                                   style="color:#6b7280;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <circle cx="11" cy="11" r="8"/>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                    </svg>
                                </a>
                                {{-- edit --}}
                                <button onclick="openEditModal({{ $lab->id }}, '{{ $lab->lab_name }}', {{ $lab->capacity }})"
                                        class="action-btn action-edit" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                {{-- delete --}}
                                <form method="POST" action="{{ route('laboratory.destroy', $lab->id) }}"
                                      onsubmit="return confirm('Hapus lab {{ $lab->lab_name }}?')"
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
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:32px; color:#9ca3af; font-size:13px;">
                            Belum ada laboratorium
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- pagination --}}
        @if($laboratories->hasPages())
        <div style="padding:16px 24px;">
            {{ $laboratories->links() }}
        </div>
        @endif

    </div>
</div>

{{-- ── MODAL CREATE ── --}}
<div id="modal-create" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:500px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Create Laboratory</h3>
            <button onclick="closeCreateModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:20px;">&times;</button>
        </div>

        <form method="POST" action="{{ route('laboratory.store') }}" id="createLabForm">
            @csrf

            {{-- step indicator --}}
            <div style="display:flex; padding:16px 24px 0; gap:8px;">
                <div id="step-bar-1" style="flex:1; height:4px; border-radius:2px; background:#111B4C;"></div>
                <div id="step-bar-2" style="flex:1; height:4px; border-radius:2px; background:#e5e7eb;"></div>
            </div>

            {{-- Step 1: General Info --}}
            <div id="step-1" style="padding:24px;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 20px; text-align:center;">General Information</h4>

                <div style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Laboratory Name:</label>
                    <input type="text" name="lab_name" placeholder="Enter here..."
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
                           required>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">PC Capacity:</label>
                    <input type="number" name="capacity" placeholder="Enter here..." min="1"
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
                           required id="capacityInput">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:24px;">
                    <button type="button" onclick="closeCreateModal()"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                    <button type="button" onclick="goToStep2()"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Next</button>
                </div>
            </div>

            {{-- Step 2: PC Info --}}
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

{{-- ── MODAL EDIT ── --}}
<div id="modal-edit" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:440px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Edit Laboratory</h3>
            <button onclick="closeEditModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:20px;">&times;</button>
        </div>
        <form method="POST" id="editLabForm" style="padding:24px;">
            @csrf @method('PUT')
            <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 20px; text-align:center;">General Information</h4>
            <div style="margin-bottom:16px;">
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Laboratory Name:</label>
                <input type="text" name="lab_name" id="edit_lab_name" placeholder="Enter here..."
                       style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
                       required>
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">PC Capacity:</label>
                <input type="number" name="capacity" id="edit_capacity" placeholder="Enter here..." min="1"
                       style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;"
                       required>
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
    // ── Search ──
    function filterTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#labTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
        });
    }

    // ── Checkbox ──
    function toggleAll(master) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
    }

    // ── Export (simple CSV) ──
    function exportTable() {
        alert('Export feature coming soon.');
    }

    // ── Create Modal ──
    function openCreateModal() {
        document.getElementById('modal-create').style.display = 'flex';
        document.getElementById('step-1').style.display = 'block';
        document.getElementById('step-2').style.display = 'none';
        document.getElementById('step-bar-1').style.background = '#111B4C';
        document.getElementById('step-bar-2').style.background = '#e5e7eb';
    }

    function closeCreateModal() {
        document.getElementById('modal-create').style.display = 'none';
        document.getElementById('createLabForm').reset();
        document.getElementById('pc-list').innerHTML = '';
    }

    function goToStep2() {
        const labName = document.querySelector('[name=lab_name]').value;
        const capacity = parseInt(document.getElementById('capacityInput').value);
        if (!labName || !capacity || capacity < 1) {
            alert('Isi nama lab dan kapasitas terlebih dahulu.');
            return;
        }
        document.getElementById('step-1').style.display = 'none';
        document.getElementById('step-2').style.display = 'block';
        document.getElementById('step-bar-2').style.background = '#98083D';
        buildPcList(capacity);
    }

    function goToStep1() {
        document.getElementById('step-1').style.display = 'block';
        document.getElementById('step-2').style.display = 'none';
        document.getElementById('step-bar-2').style.background = '#e5e7eb';
    }

    function buildPcList(capacity) {
        const container = document.getElementById('pc-list');
        container.innerHTML = '';
        for (let i = 0; i < capacity; i++) {
            container.innerHTML += `
            <details style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:10px; overflow:hidden;">
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
                            <input type="text" name="pcs[${i}][ram]" placeholder="e.g. 8GB DDR4"
                                   style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">SSD</label>
                            <input type="text" name="pcs[${i}][ssd]" placeholder="e.g. 256GB"
                                   style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Motherboard</label>
                            <input type="text" name="pcs[${i}][motherboard]" placeholder="e.g. MSI Pro Z790"
                                   style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">VGA</label>
                            <input type="text" name="pcs[${i}][vga]" placeholder="e.g. RTX 4070"
                                   style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">CPU Fan</label>
                            <input type="text" name="pcs[${i}][cpu_fan]" placeholder="e.g. Noctua NH-D15"
                                   style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Power Supply</label>
                            <input type="text" name="pcs[${i}][powersupply]" placeholder="e.g. Corsair 650W"
                                   style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
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

    // close modal on overlay click
    document.getElementById('modal-create').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });
    document.getElementById('modal-edit').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>
@endpush
