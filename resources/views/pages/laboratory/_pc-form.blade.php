@php $isEdit = isset($editMode) && $editMode; @endphp

<div style="display:grid; gap:14px;">

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
            <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Type PC</label>
            <select name="type_pc" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:13px;" required>
                <option value="mahasiswa" {{ old('type_pc', $pc->type_pc ?? '') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="dosen" {{ old('type_pc', $pc->type_pc ?? '') == 'dosen' ? 'selected' : '' }}>Dosen</option>
            </select>
        </div>
        @if($isEdit)
        <div>
            <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Status</label>
            <select name="status_pc" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:13px;" required>
                <option value="active" {{ old('status_pc', $pc->status_pc ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status_pc', $pc->status_pc ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        @endif
    </div>

    @foreach([
        'processor'   => 'Processor',
        'ram'         => 'RAM',
        'ssd'         => 'SSD',
        'motherboard' => 'Motherboard',
        'vga'         => 'VGA',
        'cpu_fan'     => 'CPU Fan',
        'powersupply' => 'Power Supply',
    ] as $field => $label)
    <div>
        <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">{{ $label }}</label>
        <input type="text" name="{{ $field }}"
               value="{{ old($field, $pc->$field ?? '') }}"
               placeholder="Enter here..."
               style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
    </div>
    @endforeach

</div>
