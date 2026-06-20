@extends('panel.content')

@section('title', 'Buat Return Request')

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
        labId: '{{ old('lab_id', count($userLabs) === 1 ? $userLabs->first()->id : '') }}',
        availableAssets: [],
        loading: false,
        items: [{ asset_id: '', quantity: 1, condition: 'good', reason: '' }],
        onLabChange(id) {
            this.labId = id;
            this.availableAssets = [];
            this.items = [{ asset_id: '', quantity: 1, condition: 'good', reason: '' }];
            if (!id) return;
            this.loading = true;
            fetch('/api/labs/' + id + '/assets')
                .then(r => r.json())
                .then(data => { this.availableAssets = data; })
                .catch(() => alert('Gagal memuat data aset.'))
                .finally(() => { this.loading = false; });
        },
        addItem() {
            this.items.push({ asset_id: '', quantity: 1, condition: 'good', reason: '' });
        },
        removeItem(i) {
            if (this.items.length <= 1) return;
            this.items.splice(i, 1);
        },
        getStock(item) {
            const a = this.availableAssets.find(x => x.asset_id == item.asset_id);
            if (!a) return null;
            if (item.condition === 'damaged') return a.stock_damaged;
            if (item.condition === 'lost') return a.stock_loss;
            return a.stock_good;
        },
        overStock(item) {
            const s = this.getStock(item);
            return s !== null && item.quantity > s;
        },
    }" x-init="if (labId) onLabChange(labId)">
        <form action="{{ route('return-requests.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-xl p-5 space-y-4">
                        <h3 class="font-semibold text-sm uppercase tracking-wide" style="color:var(--text-secondary);">
                            Informasi Request
                        </h3>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color:var(--text-secondary);">
                                Laboratorium <span class="text-red-500">*</span>
                            </label>
                            <select name="lab_id"
                                    style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                    class="w-full rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                    x-on:change="onLabChange($event.target.value)"
                                    :disabled="loading">
                                <option value="">-- Pilih Lab --</option>
                                @foreach($userLabs as $lab)
                                    <option value="{{ $lab->id }}"
                                        {{ old('lab_id', count($userLabs) === 1 ? $userLabs->first()->id : '') == $lab->id ? 'selected' : '' }}>
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
                                      class="w-full rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                      placeholder="Catatan tambahan untuk SPV...">{{ old('notes') }}</textarea>
                        </div>
                        <div style="background:var(--bg-notes); border:1px solid var(--border-color);" class="rounded-lg p-3 text-xs" style="color:var(--text-secondary);">
                            Stok lab <strong>belum berubah</strong> sampai SPV menyetujui request ini.
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--border-color);">
                            <h3 class="font-semibold text-sm uppercase tracking-wide" style="color:var(--text-secondary);">
                                Barang yang Diretur
                            </h3>
                            <button type="button"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium transition hover:opacity-80 disabled:opacity-40"
                                    style="color:#111B4C"
                                    x-on:click="addItem()"
                                    :disabled="!labId || loading">
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
                        <div x-show="!labId && !loading" class="py-10 text-center text-sm" style="color:var(--text-muted);">
                            Pilih laboratorium terlebih dahulu
                        </div>
                        <div x-show="labId && !loading && availableAssets.length === 0" class="py-10 text-center text-sm" style="color:var(--text-muted);">
                            Tidak ada barang di lab ini
                        </div>
                        <div x-show="labId && !loading && availableAssets.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead style="background:var(--bg-table-header);">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium" style="color:var(--text-secondary);">Barang</th>
                                        <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Stok Lab</th>
                                        <th class="px-4 py-3 text-center font-medium" style="color:var(--text-secondary);">Qty</th>
                                        <th class="px-4 py-3 text-left font-medium" style="color:var(--text-secondary);">Kondisi</th>
                                        <th class="px-4 py-3 text-left font-medium" style="color:var(--text-secondary);">Alasan</th>
                                        <th class="px-4 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr :class="overStock(item) ? 'bg-red-50' : ''" style="border-bottom:1px solid var(--border-color);">
                                            <td class="px-4 py-3">
                                                <select :name="`items[${index}][asset_id]`"
                                                        style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                                        class="w-full rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
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
                                                    <span x-text="getStock(item)"></span>
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
                                                       :max="getStock(item)"
                                                       required>
                                                <p x-show="overStock(item)" class="text-red-500 text-xs mt-0.5 text-center">
                                                    Melebihi stok!
                                                </p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <select :name="`items[${index}][condition]`"
                                                        style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                                        class="text-sm rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400"
                                                        x-model="item.condition">
                                                    <option value="good">Baik</option>
                                                    <option value="damaged">Rusak</option>
                                                    <option value="lost">Hilang</option>
                                                </select>
                                                <p x-show="item.condition === 'damaged'" class="text-yellow-600 text-xs mt-0.5">
                                                    Tidak kembali ke stok baik
                                                </p>
                                                <p x-show="item.condition === 'lost'" class="text-red-500 text-xs mt-0.5">
                                                    Tidak kembali ke gudang
                                                </p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text"
                                                       :name="`items[${index}][reason]`"
                                                       style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                                                       class="w-full text-sm rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-gray-400"
                                                       x-model="item.reason"
                                                       placeholder="Alasan...">
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
                            <a href="{{ route('return-requests.index') }}"
                               class="text-sm px-4 py-2 rounded-lg border transition hover:opacity-80"
                               style="border-color:var(--border-color); color:var(--text-secondary);">
                                Batal
                            </a>
                            <button type="submit"
                                    class="text-sm text-white font-medium px-5 py-2 rounded-lg transition hover:opacity-80 disabled:opacity-40"
                                    style="background:#111B4C"
                                    :disabled="!labId || loading || items.some(i => !i.asset_id || i.quantity < 1 || overStock(i))">
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
