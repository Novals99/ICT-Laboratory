@php
    $staffUsers = $laboratory->users->where('role', 'staff')->values();
@endphp

{{-- ── HEADER CARD ── --}}
<div class="db-card bg-white dark:bg-gray-800" style="flex:1; min-width:0;">

    {{-- Baris 1: Nama Lab + Tombol Add Staff (SPV only) --}}
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:6px;">
        <h2 style="font-size:18px; font-weight:700; color:var(--text-bold); margin:0; line-height:1.3;">
            {{ $laboratory->lab_name }}
        </h2>

        @if($isSPV)
        <button
            type="button"
            id="btn-open-assign-staff"
            style="flex-shrink:0; background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:7px 14px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap; display:inline-flex; align-items:center; gap:5px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
            Add Staff
        </button>
        @endif
    </div>

    {{-- Baris 2: Stats --}}
    <p style="font-size:13px; color:var(--text-muted); margin:0 0 14px;">
        Capacity: <strong style="color:var(--text-bold);">{{ $laboratory->capacity }} PC</strong>
        &nbsp;·&nbsp;
        Active: <strong style="color:#16a34a;">{{ $totalActive }}</strong>
        &nbsp;·&nbsp;
        Inactive: <strong style="color:#dc2626;">{{ $totalInactive }}</strong>
    </p>

    {{-- Baris 3: Staff Cards --}}
    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
        @forelse($staffUsers as $staff)
        <div class="flex min-w-[150px] items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-slate-800">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-900 text-xs font-bold text-white dark:bg-indigo-600">
                {{ strtoupper(substr($staff->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="mb-px text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Staff</p>
                <p class="truncate whitespace-nowrap text-xs font-semibold text-gray-900 dark:text-gray-100">{{ $staff->name }}</p>
            </div>
            @if($isSPV)
            <form method="POST" action="{{ route('laboratory.staff.remove', [$laboratory->id, $staff->id]) }}" style="margin-left:auto; flex-shrink:0;">
                @csrf @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Hapus {{ addslashes($staff->name) }} dari lab ini?')"
                        style="background:none; border:none; cursor:pointer; color:var(--text-muted); padding:2px; display:flex; align-items:center;"
                        title="Hapus dari lab">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </form>
            @endif
        </div>
        @empty
        <div style="background:var(--bg-light); border:1px dashed var(--border-main); border-radius:8px; padding:8px 14px;">
            <p style="font-size:12px; color:var(--text-muted); margin:0; font-style:italic;">Belum ada staff yang ditugaskan</p>
        </div>
        @endforelse
    </div>
</div>

{{-- ══ MODAL ASSIGN STAFF (SPV only) ══ --}}
@if($isSPV)
@php
    $availableStaff = \App\Models\User::where('role', 'staff')
        ->whereNotIn('id', $staffUsers->pluck('id'))
        ->orderBy('name')
        ->get();
@endphp

{{-- Overlay modal --}}
<div id="modal-assign-staff"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:var(--bg-main); border-radius:16px; width:100%; max-width:460px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); display:flex; flex-direction:column;">

        {{-- Header modal --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--border-light);">
            <h3 style="font-size:16px; font-weight:700; color:var(--text-bold); margin:0;">Assign Staff ke Lab</h3>
            <button type="button" id="btn-close-assign-staff"
                    style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:22px; line-height:1;">&times;</button>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('laboratory.staff.assign', $laboratory->id) }}"
              style="padding:24px; display:flex; flex-direction:column; gap:14px;">
            @csrf

            <div id="staff-slot-container" style="display:flex; flex-direction:column; gap:10px;">
                {{-- Slot pertama --}}
                <div class="staff-slot" style="display:flex; align-items:flex-end; gap:8px;">
                    <div style="flex:1;">
                        <label style="font-size:12px; font-weight:500; color:var(--text-muted); display:block; margin-bottom:4px;">Staff</label>
                        <select name="user_ids[]" class="staff-select"
                                style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 12px; font-size:13px; outline:none;">
                            <option value="">-- Pilih staff --</option>
                            @foreach($availableStaff as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- spacer supaya sejajar dengan slot-slot berikutnya yang ada tombol hapus --}}
                    <div style="width:32px; flex-shrink:0;"></div>
                </div>
            </div>

            {{-- Tombol tambah slot --}}
            <button type="button" id="btn-add-staff-slot"
                    style="background:none; border:1px dashed var(--border-main); border-radius:8px; width:100%; padding:8px; font-size:12px; color:var(--text-muted); cursor:pointer; display:flex; align-items:center; justify-content:center; gap:5px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Staff Lain
            </button>

            {{-- Footer --}}
            <div style="display:flex; justify-content:flex-end; gap:8px; padding-top:4px;">
                <button type="button" id="btn-cancel-assign-staff"
                        style="border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">
                    Batal
                </button>
                <button type="submit"
                        style="background:var(--bg-primary); color:var(--text-primary); border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const allStaff = @json($availableStaff->map(fn($u) => ['id' => $u->id, 'name' => $u->name]));
    const overlay  = document.getElementById('modal-assign-staff');

    // ── Buka / tutup modal ──
    function openModal() {
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.getElementById('btn-open-assign-staff').addEventListener('click', openModal);
    document.getElementById('btn-close-assign-staff').addEventListener('click', closeModal);
    document.getElementById('btn-cancel-assign-staff').addEventListener('click', closeModal);

    // Klik backdrop tutup modal
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    // ── Slot management ──
    function getSelectedIds() {
        return [...document.querySelectorAll('.staff-select')]
            .map(s => s.value).filter(Boolean);
    }

    function rebuildOptions() {
        const selected = getSelectedIds();
        document.querySelectorAll('.staff-select').forEach(sel => {
            const cur = sel.value;
            sel.innerHTML = '<option value="">-- Pilih staff --</option>';
            allStaff.forEach(u => {
                if (!selected.includes(String(u.id)) || String(u.id) === cur) {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    if (String(u.id) === cur) opt.selected = true;
                    sel.appendChild(opt);
                }
            });
        });
        checkAddButton();
    }

    function checkAddButton() {
        const remaining = allStaff.filter(u => !getSelectedIds().includes(String(u.id)));
        document.getElementById('btn-add-staff-slot').style.display =
            remaining.length === 0 ? 'none' : 'flex';
    }

    // Listener slot pertama
    document.querySelector('.staff-select').addEventListener('change', rebuildOptions);

    // Tombol tambah slot
    document.getElementById('btn-add-staff-slot').addEventListener('click', function () {
        const container = document.getElementById('staff-slot-container');
        const selected  = getSelectedIds();

        let opts = '<option value="">-- Pilih staff --</option>';
        allStaff.forEach(u => {
            if (!selected.includes(String(u.id))) {
                opts += `<option value="${u.id}">${u.name}</option>`;
            }
        });

        const slot = document.createElement('div');
        slot.className = 'staff-slot';
        slot.style.cssText = 'display:flex; align-items:flex-end; gap:8px;';
        slot.innerHTML = `
            <div style="flex:1;">
                <label style="font-size:12px; font-weight:500; color:var(--text-muted); display:block; margin-bottom:4px;">Staff</label>
                <select name="user_ids[]" class="staff-select"
                        style="width:100%; border:1px solid var(--border-main); background:var(--bg-main); color:var(--text-normal); border-radius:8px; padding:9px 12px; font-size:13px; outline:none;">
                    ${opts}
                </select>
            </div>
            <div style="flex-shrink:0;">
                <button type="button"
                        style="background:none; border:1px solid var(--border-light); border-radius:6px; width:32px; height:38px; cursor:pointer; color:var(--text-muted); display:flex; align-items:center; justify-content:center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>`;

        // Listener hapus slot
        slot.querySelector('button').addEventListener('click', function () {
            slot.remove();
            rebuildOptions();
        });

        // Listener select baru
        slot.querySelector('select').addEventListener('change', rebuildOptions);

        container.appendChild(slot);
        checkAddButton();
    });
})();
</script>
@endif