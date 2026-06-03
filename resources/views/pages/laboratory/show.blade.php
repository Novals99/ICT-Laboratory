@extends('panel.content')

@section('title', $laboratory->lab_name)

@section('content')

<div class="db-wrap">

    {{-- header info lab --}}
    <div class="db-card" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="font-size:20px; font-weight:700; color:#111827; margin:0 0 4px;">{{ $laboratory->lab_name }}</h2>
            <p style="font-size:13px; color:#6b7280; margin:0;">
                Capacity: <strong>{{ $laboratory->capacity }} PC</strong> &nbsp;·&nbsp;
                Active: <strong style="color:#16a34a;">{{ $totalActive }}</strong> &nbsp;·&nbsp;
                Inactive: <strong style="color:#dc2626;">{{ $totalInactive }}</strong>
            </p>
        </div>
        <a href="{{ route('laboratory.index') }}"
           style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:8px 16px; font-size:13px; text-decoration:none; color:#374151; font-weight:500;">
            ← Back
        </a>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7; color:#166534; border-radius:8px; padding:10px 16px; font-size:13px;">
        {{ session('success') }}
    </div>
    @endif

    {{-- PC List --}}
    <div class="db-card db-table-card">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px;">
            <h3 style="font-size:15px; font-weight:700; color:#111827; margin:0;">PC Information</h3>
            <button onclick="openAddPcModal()"
                    style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-weight:600;">
                + Add PC
            </button>
        </div>

        <div class="table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>No PC</th>
                        <th>Type</th>
                        <th>Processor</th>
                        <th>RAM</th>
                        <th>SSD</th>
                        <th>Motherboard</th>
                        <th>VGA</th>
                        <th>Status</th>
                        <th>Action</th>
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
                        {{-- Button Status PC --}}
                        <td>
                            <form action="{{ route('pc.updateStatus', $pc->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                @php
                                    $badgeColor = $pc->status_pc === 'active' ? '#16a34a' : '#dc2626';
                                    // Jika sekarang active maka yang dikirim inactive, begitu sebaliknya
                                    $newStatus = $pc->status_pc === 'active' ? 'inactive' : 'active';
                                @endphp

                                <input type="hidden" name="status_pc" value="{{ $newStatus }}">

                                <button type="submit" style="background:{{ $badgeColor }}; color:#fff; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:600; border: none; cursor: pointer;" onclick="return confirm('Ubah status PC menjadi {{ $newStatus }}?')">
                                    {{ ucfirst($pc->status_pc) }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button onclick="openEditPcModal({{ $pc->id }}, {{ $loop->index }}, @json($pc))"
                                        class="action-btn action-edit" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <form method="POST"
                                      action="{{ route('pc.destroy', [$laboratory->id, $pc->id]) }}"
                                      onsubmit="return confirm('Hapus PC ini?')"
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
                        <td colspan="9" style="text-align:center; padding:32px; color:#9ca3af; font-size:13px;">
                            Belum ada PC di lab ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── MODAL ADD PC ── --}}
<div id="modal-add-pc" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:520px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb; position:sticky; top:0; background:#fff; z-index:1;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Add PC</h3>
            <button onclick="closeAddPcModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:20px;">&times;</button>
        </div>
        <form method="POST" action="{{ route('pc.store', $laboratory->id) }}" style="padding:24px;">
            @csrf
            @include('pages.laboratory._pc-form', ['pc' => null])
            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:24px;">
                <button type="button" onclick="closeAddPcModal()"
                        style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit"
                        style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL EDIT PC ── --}}
<div id="modal-edit-pc" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:520px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb; position:sticky; top:0; background:#fff; z-index:1;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;" id="edit-pc-title">Edit PC</h3>
            <button onclick="closeEditPcModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:20px;">&times;</button>
        </div>
        <form method="POST" id="editPcForm" style="padding:24px;">
            @csrf @method('PUT')
            @include('pages.laboratory._pc-form', ['pc' => null, 'editMode' => true])
            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:24px;">
                <button type="button" onclick="closeEditPcModal()"
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
    function openAddPcModal() {
        document.getElementById('modal-add-pc').style.display = 'flex';
    }
    function closeAddPcModal() {
        document.getElementById('modal-add-pc').style.display = 'none';
    }

    function openEditPcModal(pcId, index, pc) {
        const form = document.getElementById('editPcForm');
        form.action = `/laboratory/{{ $laboratory->id }}/pc/${pcId}`;
        document.getElementById('edit-pc-title').textContent = `Edit PC-${String(index).padStart(2,'0')}`;

        form.querySelector('[name=type_pc]').value     = pc.type_pc;
        form.querySelector('[name=status_pc]').value   = pc.status_pc;
        form.querySelector('[name=processor]').value   = pc.processor ?? '';
        form.querySelector('[name=ram]').value         = pc.ram ?? '';
        form.querySelector('[name=ssd]').value         = pc.ssd ?? '';
        form.querySelector('[name=motherboard]').value = pc.motherboard ?? '';
        form.querySelector('[name=vga]').value         = pc.vga ?? '';
        form.querySelector('[name=cpu_fan]').value     = pc.cpu_fan ?? '';
        form.querySelector('[name=powersupply]').value = pc.powersupply ?? '';

        document.getElementById('modal-edit-pc').style.display = 'flex';
    }
    function closeEditPcModal() {
        document.getElementById('modal-edit-pc').style.display = 'none';
    }

    document.getElementById('modal-add-pc').addEventListener('click', function(e) {
        if (e.target === this) closeAddPcModal();
    });
    document.getElementById('modal-edit-pc').addEventListener('click', function(e) {
        if (e.target === this) closeEditPcModal();
    });
</script>
@endpush
