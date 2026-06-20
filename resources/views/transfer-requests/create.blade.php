<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('transfer-requests.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Buat Transfer Request</h2>
                <p class="text-sm text-gray-500">Pindahkan barang dari lab Anda ke lab lain</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">

        @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
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
                    fetch(`/api/labs/${id}/assets`)
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
            }"
            x-init="if (fromLabId) onFromLabChange(fromLabId)">

        <form action="{{ route('transfer-requests.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">
                            Informasi Transfer
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Lab Asal <span class="text-red-500">*</span>
                            </label>
                            <select name="from_lab_id"
                                    class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('from_lab_id') border-red-400 @enderror"
                                    x-on:change="onFromLabChange($event.target.value)"
                                    :disabled="loading">
                                <option value="">-- Pilih Lab Asal --</option>
                                @foreach($userLabs as $lab)
                                <option value="{{ $lab->id }}" {{ old('from_lab_id') == $lab->id ? 'selected' : '' }}>
                                    {{ $lab->lab_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('from_lab_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Lab Tujuan <span class="text-red-500">*</span>
                            </label>
                            <select name="to_lab_id" x-model="toLabId"
                                    class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('to_lab_id') border-red-400 @enderror">
                                <option value="">-- Pilih Lab Tujuan --</option>
                                @foreach($targetLabs as $lab)
                                <option value="{{ $lab->id }}" {{ old('to_lab_id') == $lab->id ? 'selected' : '' }}>
                                    {{ $lab->lab_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('to_lab_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <textarea name="notes" rows="4"
                                      class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Alasan/konteks transfer...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-700">
                            Stok kedua lab <strong>belum berubah</strong> sampai SPV menyetujui request ini.
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">
                                Barang yang Ditransfer
                            </h3>
                            <button type="button"
                                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium disabled:opacity-40"
                                    x-on:click="addItem()"
                                    :disabled="!fromLabId || loading">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Baris
                            </button>
                        </div>

                        <div x-show="loading" class="py-10 text-center text-gray-400 text-sm">
                            <div class="inline-block animate-spin rounded-full h-5 w-5 border-2 border-blue-500 border-t-transparent mr-2"></div>
                            Memuat daftar barang...
                        </div>

                        <div x-show="!fromLabId && !loading" class="py-10 text-center text-gray-400 text-sm">
                            Pilih lab asal terlebih dahulu
                        </div>

                        <div x-show="fromLabId && !loading && availableAssets.length === 0"
                             class="py-10 text-center text-gray-400 text-sm">
                            Tidak ada barang dengan stok di lab ini
                        </div>

                        <div x-show="fromLabId && !loading && availableAssets.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Barang</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">Stok Lab Asal</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 w-24">Qty</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Catatan</th>
                                        <th class="px-4 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr :class="overStock(item) ? 'bg-red-50' : ''">
                                            <td class="px-4 py-2">
                                                <select :name="`items[${index}][asset_id]`"
                                                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                                        x-model="item.asset_id" required>
                                                    <option value="">-- Pilih --</option>
                                                    <template x-for="a in availableAssets" :key="a.asset_id">
                                                        <option :value="a.asset_id" x-text="a.name"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <span x-show="item.asset_id"
                                                      class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                                      :class="overStock(item) ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'">
                                                    <span x-text="getStock(item.asset_id)"></span>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number"
                                                       :name="`items[${index}][quantity]`"
                                                       class="w-full text-sm text-center border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                                       :class="overStock(item) ? 'border-red-400 bg-red-50' : ''"
                                                       x-model.number="item.quantity"
                                                       min="1"
                                                       :max="getStock(item.asset_id)"
                                                       required>
                                                <p x-show="overStock(item)" class="text-red-500 text-xs mt-0.5 text-center">
                                                    Melebihi stok!
                                                </p>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text"
                                                       :name="`items[${index}][notes]`"
                                                       class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                                       x-model="item.notes"
                                                       placeholder="Catatan...">
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <button type="button"
                                                        class="text-gray-300 hover:text-red-500 transition disabled:opacity-30"
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

                        <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <a href="{{ route('transfer-requests.index') }}"
                               class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <button type="submit"
                                    class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition disabled:opacity-40"
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
</x-app-layout>
