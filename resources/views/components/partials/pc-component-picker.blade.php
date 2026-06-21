@props([
    'laboratory',          // wajib: lab tempat komponen diambil (untuk endpoint stok)
    'pc' => null,          // mode edit: PC yang sedang diedit (untuk preselect + exclude)
])

@php
    // Slot komponen → component_type asset. RAM 2 nullable & berbagi pool 'ram'.
    $slots = [
        'processor'   => ['label' => 'Processor',    'type' => 'processor',   'required' => false],
        'ram'         => ['label' => 'RAM',          'type' => 'ram',         'required' => false],
        'ram2'        => ['label' => 'RAM 2 (opsional)', 'type' => 'ram',     'required' => false],
        'ssd'         => ['label' => 'SSD',          'type' => 'ssd',         'required' => false],
        'motherboard' => ['label' => 'Motherboard',  'type' => 'motherboard', 'required' => false],
        'vga'         => ['label' => 'VGA',          'type' => 'vga',         'required' => false],
        'cpu_fan'     => ['label' => 'CPU Fan',      'type' => 'cpu_fan',     'required' => false],
        'powersupply' => ['label' => 'Power Supply', 'type' => 'powersupply', 'required' => false],
    ];

    $excludePc = $pc?->id;
@endphp

<div class="pc-comp-picker"
     data-lab-id="{{ $laboratory->id }}"
     @if($excludePc) data-exclude-pc="{{ $excludePc }}" @endif
     style="display:grid; gap:14px;">

    @foreach ($slots as $slot => $cfg)
        <div class="pc-comp-row" data-slot="{{ $slot }}" data-type="{{ $cfg['type'] }}">
            <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">
                {{ $cfg['label'] }}
            </label>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                {{-- Pilih asset komponen (difilter component_type; tidak dikirim ke server) --}}
                <select class="pc-comp-asset"
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:13px;">
                    <option value="">— Pilih komponen —</option>
                </select>

                {{-- Pilih serial number unit (INI yang dikirim ke server) --}}
                <select name="{{ $slot }}_serial_id" class="pc-comp-serial"
                        data-current="{{ $pc?->{$slot . '_serial_id'} }}"
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:13px;">
                    <option value="">— Serial Number —</option>
                </select>
            </div>
        </div>
    @endforeach
</div>

@once
    @push('scripts')
        <script>
            // (#8/#9/#10) Pemilih komponen PC berbasis serial number.
            //  - tiap slot menampilkan asset sesuai component_type
            //  - dropdown serial mengikuti asset yang dipilih (hanya yang available)
            //  - 1 serial number tidak bisa dipilih di dua slot
            (function () {
                async function initPicker(picker) {
                    const labId = picker.dataset.labId;
                    const excludePc = picker.dataset.excludePc;
                    const url = `/api/laboratory/${labId}/pc-components` + (excludePc ? `?exclude_pc=${excludePc}` : '');

                    let data = {};
                    try {
                        const res = await fetch(url);
                        data = await res.json();
                    } catch (e) {
                        return;
                    }

                    const rows = picker.querySelectorAll('.pc-comp-row');

                    rows.forEach(row => {
                        const type = row.dataset.type;
                        const assetSel = row.querySelector('.pc-comp-asset');
                        const serialSel = row.querySelector('.pc-comp-serial');
                        const current = serialSel.dataset.current;
                        const assets = data[type] || [];

                        // Isi dropdown asset.
                        assets.forEach(a => {
                            const opt = document.createElement('option');
                            opt.value = a.asset_id;
                            opt.textContent = a.asset_name;
                            assetSel.appendChild(opt);
                        });

                        // Saat asset dipilih → isi serial-nya.
                        assetSel.addEventListener('change', () => {
                            fillSerials(serialSel, assets, assetSel.value, null);
                            refreshDisabledSerials(picker);
                        });

                        // Preselect (mode edit): cari asset yang memuat serial current.
                        if (current) {
                            const owner = assets.find(a => a.serials.some(s => String(s.id) === String(current)));
                            if (owner) {
                                assetSel.value = owner.asset_id;
                                fillSerials(serialSel, assets, owner.asset_id, current);
                            }
                        }
                    });

                    // Jika satu serial dipilih, disable di slot lain.
                    picker.querySelectorAll('.pc-comp-serial').forEach(sel => {
                        sel.addEventListener('change', () => refreshDisabledSerials(picker));
                    });
                    refreshDisabledSerials(picker);
                }

                function fillSerials(serialSel, assets, assetId, selectId) {
                    serialSel.innerHTML = '<option value="">— Serial Number —</option>';
                    const asset = assets.find(a => String(a.asset_id) === String(assetId));
                    if (!asset) return;
                    asset.serials.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.serial_number;
                        if (selectId && String(s.id) === String(selectId)) opt.selected = true;
                        serialSel.appendChild(opt);
                    });
                }

                function refreshDisabledSerials(picker) {
                    const serialSelects = [...picker.querySelectorAll('.pc-comp-serial')];
                    const chosen = serialSelects.map(s => s.value).filter(Boolean);
                    serialSelects.forEach(sel => {
                        [...sel.options].forEach(opt => {
                            if (!opt.value) return;
                            opt.disabled = chosen.includes(opt.value) && sel.value !== opt.value;
                        });
                    });
                }

                function initAll(scope) {
                    (scope || document).querySelectorAll('.pc-comp-picker').forEach(p => {
                        if (!p.dataset.pickerReady) {
                            p.dataset.pickerReady = '1';
                            initPicker(p);
                        }
                    });
                }

                document.addEventListener('DOMContentLoaded', () => initAll());
                // Modal sering ditampilkan via JS — sediakan hook manual.
                window.initPcComponentPickers = initAll;
            })();
        </script>
    @endpush
@endonce