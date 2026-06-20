@extends('panel.content')

@section('title', 'Buat Transfer Request')

@section('content')
<div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-2xl p-6 shadow-sm">
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div x-data="{
        fromLabId: '{{ old('from_lab_id') }}',
        toLabId: '{{ old('to_lab_id') }}',
        availableAssets: [],
        loading: false,
        items: [{ asset_id: '', quantity: 1, notes: '' }],
        onFromLabChange(id) {
            this.fromLabId = id;
            this.availableAssets = [];
            this.items = [{ asset_id: '', quantity: 1, notes: '' }];
            if (!id) return;
            this.loading = true;
            fetch('/api/labs/' + id + '/assets')
                .then(r => r.json())
                .then(data => { this.availableAssets = data; })
                .catch(() => alert('Gagal memuat data aset.'))
                .finally(() => { this.loading = false; });
        },
        addItem() {
            this.items.push({ asset_id: '', quantity: 1, notes: '' });
        },
        removeItem(i) {
            if (this.items.length <= 1) return;
            this.items.splice(i, 1);
        },
        getStock(assetId) {
            const a = this.availableAssets.find(x => x.asset_id == assetId);
            return a ? a.stock : null;
        },
        overStock(item) {
            const s = this.getStock(item.asset_id);
            return s !== null && item.quantity > s;
        }
    }" x-init="if (fromLabId) onFromLabChange(fromLabId)">
        <form action="{{ route('transfer-requests.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-xl p-5 space-y-4">
                        <h3 class="font-semibold text-sm uppercase tracking-wide" style="color:var(--text-secondary);">
                            Informasi Transfer
                        </h3>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color:var(--text-secondary);">
                                Lab Asal <span class="text-red-500">*</span>
                            </label>
                            <select name="from_lab_id"
                                    style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                    class="w-full text-sm rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400"
                                    x-on:change="onFromLabChange($event.target.value)"
                                    :disabled="loading">
                                <option value="">-- Pilih Lab Asal --</option>
                                @foreach($userLabs as $lab)
                                    <option value="{{ $lab->id }}" {{ old('from_lab_id') == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->lab_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color:var(--text-secondary);">
                                Lab Tujuan <span class="text-red-500">*</span>
                            </label>
                            <select name="to_lab_id" x-model="toLabId"
                                    style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                    class="w-full text-sm rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400">
                                <option value="">-- Pilih Lab Tujuan --</option>
                                @foreach($targetLabs as $lab)
                                    <option value="{{ $lab->id }}" {{ old('to_lab_id') == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->lab_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color:var(--text-secondary);">
                                Catatan <span style="color:var(--text-muted); font-weight: normal;">(opsional)</span>
                            </label>
                            <textarea name="notes" rows="4"
                                      style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                      class="w-full text-sm rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400"
                                      placeholder="Alasan/konteks transfer...">{{ old('notes') }}</textarea>
                        </div>
                        <div style="background:var(--bg-notes); border:1px solid var(--border-color);" class="rounded-lg p-3 text-xs" style="color:var(--text-secondary);">
                            Stok kedua lab <strong>belum berubah</strong> sampai SPV menyetujui request ini.
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--border-color);">
                            <h3 class="font-semibold text-sm uppercase tracking-wide" style="color:var(--text-secondary);">
                                Barang yang Ditransfer
                            </h3>
                            <button type="button"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium transition hover:opacity-80 disabled:opacity-40"
                                    style="color:#111B4C"
                                    x-on:click="addItem()"
                                    :disabled="!fromLabId || loading">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Baris
                            </button>
                        </div>
                        <div x-show="loading" class="py-10 text-center text-sm" style="color:var(--text-muted);">
                            <div class="inline-block animate-spin rounded-full h-5 w-5 border-2 border-gray-400 border-t-transparent mr-2"></div>
                            Memuat daftar barang...
                        </div>
                        <div x-show="!fromLabId && !loading" class="py-10 text-center text-sm" style="color:var(--text-muted);">
                            Pilih lab asal terlebih dahulu
                        </div>
                        <div x-show="fromLabId && !loading && availableAssets.length === 0" class="py-10 text-center text-sm" style="color:var(--text-muted);">
                            Tidak ada barang dengan stok di lab ini
                        </div>
                        <div x-show="fromLabId && !loading && availableAssets.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead style="background:var(--bg-table-header);">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium" style="color:var(--text-secondary);">Barang</th>
                                        <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Stok Lab Asal</th>
                                        <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Qty</th>
                                        <th class="px-4 py-3 text-left font-medium" style="color:var(--text-secondary);">Catatan</th>
                                        <th class="px-4 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr :class="overStock(item) ? 'bg-red-50' : ''" style="border-bottom:1px solid var(--border-color);">
                                            <td class="px-4 py-3">
                                                <select :name="`items[${index}][asset_id]`"
                                                        style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                                        class="w-full text-sm rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400"
                                                        x-model="item.asset_id" required>
                                                    <option value="">-- Pilih --</option>
                                                    <template x-for="a in availableAssets" :key="a.asset_id">
                                                        <option :value="a.asset_id" x-text="a.name"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span x-show="item.asset_id"
                                                      class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                                      :class="overStock(item) ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'">
                                                    <span x-text="getStock(item.asset_id)"></span>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number"
                                                       :name="`items[${index}][quantity]`"
                                                       style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                                       class="w-full text-sm text-center rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400"
                                                       :class="overStock(item) ? 'border-red-400 bg-red-50' : ''"
                                                       x-model.number="item.quantity"
                                                       min="1"
                                                       :max="getStock(item.asset_id)"
                                                       required>
                                                <p x-show="overStock(item)" class="text-red-500 text-xs mt-0.5 text-center">
                                                    Melebihi stok!
                                                </p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text"
                                                       :name="`items[${index}][notes]`"
                                                       style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                                       class="w-full text-sm rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400"
                                                       x-model="item.notes"
                                                       placeholder="Catatan...">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button"
                                                        class="text-gray-400 hover:text-red-500 transition disabled:opacity-30"
                                                        x-on:click="removeItem(index)"
                                                        :disabled="items.length <= 1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-5 py-4 border-t flex justify-end gap-3" style="border-color:var(--border-color);">
                            <a href="{{ route('transfer-requests.index') }}"
                               class="text-sm px-4 py-2 rounded-lg border transition hover:opacity-80"
                               style="border-color:var(--border-color); color:var(--text-secondary);">
                                Batal
                            </a>
                            <button type="submit"
                                    class="text-sm text-white font-medium px-5 py-2 rounded-lg transition hover:opacity-80 disabled:opacity-40"
                                    style="background:#111B4C"
                                    :disabled="!fromLabId || !toLabId || loading || items.some(i => !i.asset_id || i.quantity < 1 || overStock(i))">
                                Ajukan Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
