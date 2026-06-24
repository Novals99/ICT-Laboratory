@props([
    'laboratory',          // wajib: lab tempat komponen diambil (untuk endpoint stok)
    'pc' => null,          // mode edit: PC yang sedang diedit (untuk preselect + exclude)
])

@php
    // Slot komponen → component_type asset. RAM 2 nullable & berbagi pool 'ram'.
    $slots = [
        'pc'          => ['label' => 'PC Kode Inventaris', 'type' => 'pc',         'required' => false],
        'processor'   => ['label' => 'Processor',          'type' => 'processor',   'required' => false],
        'ram'         => ['label' => 'RAM',                'type' => 'ram',         'required' => false],
        'ram2'        => ['label' => 'RAM 2 (opsional)',   'type' => 'ram',         'required' => false],
        'ssd'         => ['label' => 'SSD',                'type' => 'ssd',         'required' => false],
        'hdd'         => ['label' => 'HDD',                'type' => 'hdd',         'required' => false],
        'motherboard' => ['label' => 'Motherboard',        'type' => 'motherboard', 'required' => false],
        'vga'         => ['label' => 'VGA',                'type' => 'vga',         'required' => false],
        'cpu_fan'     => ['label' => 'CPU Fan',            'type' => 'cpu_fan',     'required' => false],
        'powersupply' => ['label' => 'Power Supply',       'type' => 'powersupply', 'required' => false],
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
            </label>            @if ($slot === 'pc')
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    {{-- Pilih asset komponen --}}
                    <select class="pc-comp-asset"
                            style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:13px;">
                        <option value="">— Pilih komponen —</option>
                    </select>

                    {{-- Pilih serial number unit (INI yang dikirim ke server) untuk PC Box --}}
                    <select name="pc_serial_id" class="pc-comp-serial"
                            data-current="{{ $pc?->pc_serial_id }}"
                            style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:13px;">
                        <option value="">— Kode Inventaris —</option>
                    </select>
                </div>
            @else
                <div style="display:block;">
                    {{-- Dropdown komponen + spesifikasi --}}
                    <select name="{{ $slot }}" class="pc-comp-asset"
                            data-current="{{ $pc?->{$slot} }}"
                            style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:13px;">
                        <option value="">— Pilih komponen —</option>
                    </select>
                </div>
            @endif
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
                        const slot = row.dataset.slot;
                        const type = row.dataset.type;
                        const assetSel = row.querySelector('.pc-comp-asset');
                        const assets = data[type] || [];

                        if (slot === 'pc') {
                            // Isi dropdown asset.
                            assets.forEach(a => {
                                const opt = document.createElement('option');
                                opt.value = a.asset_id;
                                opt.textContent = a.asset_name;
                                assetSel.appendChild(opt);
                            });

                            const serialSel = row.querySelector('.pc-comp-serial');
                            const current = serialSel.dataset.current;

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
                        } else {
                            // Isi dropdown asset dengan kombinasi name + spec.
                            assets.forEach(a => {
                                const opt = document.createElement('option');
                                const val = a.asset_name + (a.specification ? ' - ' + a.specification : '');
                                opt.value = val;
                                opt.textContent = val;
                                assetSel.appendChild(opt);
                            });

                            const current = assetSel.dataset.current;
                            if (current) {
                                let matchedOpt = [...assetSel.options].find(opt => opt.value === current);
                                if (!matchedOpt) {
                                    matchedOpt = [...assetSel.options].find(opt => opt.value.includes(current) || current.includes(opt.value));
                                }
                                if (matchedOpt) {
                                    assetSel.value = matchedOpt.value;
                                }
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
                    serialSel.innerHTML = '<option value="">— Kode Inventaris —</option>';
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

                document.addEventListener('DOMContentLoaded', () => {
                    // Hanya init picker yang TERLIHAT saat load (mis. dipakai inline).
                    // Picker di dalam modal (hidden) di-init saat modal dibuka via initPcComponentPickers().
                    document.querySelectorAll('.pc-comp-picker').forEach(p => {
                        if (p.offsetParent !== null && !p.dataset.pickerReady) {
                            p.dataset.pickerReady = '1';
                            initPicker(p);
                        }
                    });
                });
                // Modal ditampilkan via JS → panggil ini saat modal dibuka.
                window.initPcComponentPickers = initAll;
            })();
        </script>
    @endpush
@endonce
