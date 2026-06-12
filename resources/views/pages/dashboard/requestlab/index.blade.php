@extends('panel.content')

@section('title', 'Request Lab')

@section('content')

    <div class="bg-white rounded-2xl p-6 shadow-sm">

        {{-- Top Bar --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-semibold text-gray-800">
                Request List
            </h2>

            <div class="flex items-center gap-3">
                {{-- Search --}}
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                        </svg>
                    </span>
                    <input type="text" placeholder="Search..."
                        class="w-56 pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                </div>

                {{-- Filter --}}
                <button
                    class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 8h10M11 12h2M9 16h6" />
                    </svg>
                    Filter
                </button>

                {{-- Export --}}
                <a href="{{ route('requestlab.export.pdf') }}"
                    class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M12 12V4m0 8l-3-3m3 3 3-3" />
                    </svg>

                    Export
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-600">
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300 cursor-pointer">
                        </th>
                        <th class="px-4 py-3 text-left font-medium">ID Request</th>
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-center font-medium">Total Request</th>
                        <th class="px-4 py-3 text-center font-medium">Date</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-center font-medium">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($requests as $request)
                        <tr class="border-b border-gray-100 hover:bg-slate-50 transition-colors">

                            {{-- Checkbox --}}
                            <td class="px-4 py-3">
                                <input type="checkbox" class="w-4 h-4 rounded border-gray-300 cursor-pointer">
                            </td>

                            {{-- ID Request --}}
                            <td class="px-4 py-3 text-gray-500">
                                REQ-{{ str_pad($request->id, 3, '0', STR_PAD_LEFT) }}
                            </td>

                            {{-- Name --}}
                            <td class="px-4 py-3 text-gray-800 font-medium">
                                {{ $request->user->name ?? '-' }}
                            </td>

                            {{-- Total Request --}}
                            <td class="px-4 py-3 text-center text-gray-700">
                                {{ $request->request_items->sum('total_request') }}
                            </td>

                            {{-- Date --}}
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ $request->request_date ? \Carbon\Carbon::parse($request->request_date)->format('d-m-y') : '-' }}
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-3 text-center">
                                @if ($request->request_status === 'pending')
                                    <span
                                        class="inline-block px-4 py-1 bg-yellow-400 text-yellow-900 text-xs font-semibold rounded-md">
                                        Pending
                                    </span>
                                @elseif ($request->request_status === 'approved')
                                    <span
                                        class="inline-block px-4 py-1 bg-green-600 text-white text-xs font-semibold rounded-md">
                                        Approved
                                    </span>
                                @elseif ($request->request_status === 'Partially Approved')
                                    <span
                                        class="inline-block px-4 py-1 bg-blue-600 text-white text-xs font-semibold rounded-md">
                                        Partially Approved
                                    </span>
                                @elseif ($request->request_status === 'rejected')
                                    <span
                                        class="inline-block px-4 py-1 bg-red-600 text-white text-xs font-semibold rounded-md">
                                        Rejected
                                    </span>
                                @else
                                    <span
                                        class="inline-block px-4 py-1 bg-gray-200 text-gray-600 text-xs font-semibold rounded-md">
                                        {{ $request->request_status }}  
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                <div style="display:flex; align-items:center; justify-content:center; gap:12px;">

                                    {{-- Tombol Edit → buka modal --}}
                                    <button type="button" onclick="openModal({{ $request->id }})" title="Lihat Detail"
                                        style="background:none; border:none; cursor:pointer; padding:4px; color:#9ca3af; display:flex; align-items:center;"
                                        onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#9ca3af'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    {{-- Tombol Delete --}}
                                    <form action="{{ route('requestlab.destroy', $request->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data ini?')"
                                        style="display:inline; margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            style="background:none; border:none; cursor:pointer; padding:4px; color:#9ca3af; display:flex; align-items:center;"
                                            onmouseover="this.style.color='#ef4444'"
                                            onmouseout="this.style.color='#9ca3af'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                Tidak ada data request.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-end items-center gap-1 mt-6 text-sm">
            @if ($requests->onFirstPage())
                <span class="flex items-center gap-1 px-2 py-1 text-gray-300 cursor-not-allowed">← Previous</span>
            @else
                <a href="{{ $requests->previousPageUrl() }}"
                    class="flex items-center gap-1 px-2 py-1 text-gray-400 hover:text-gray-700">← Previous</a>
            @endif

            @foreach ($requests->getUrlRange(1, $requests->lastPage()) as $page => $url)
                @if ($page === 1 || $page === $requests->lastPage() || abs($page - $requests->currentPage()) <= 1)
                    @if ($page == $requests->currentPage())
                        <span
                            class="w-8 h-8 flex items-center justify-center rounded bg-gray-800 text-white font-medium">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="w-8 h-8 flex items-center justify-center rounded text-gray-600 hover:bg-gray-100">{{ $page }}</a>
                    @endif
                @elseif ($page === 2 || $page === $requests->lastPage() - 1)
                    <span class="px-1 text-gray-400">...</span>
                @endif
            @endforeach

            @if ($requests->hasMorePages())
                <a href="{{ $requests->nextPageUrl() }}"
                    class="flex items-center gap-1 px-2 py-1 text-gray-400 hover:text-gray-700">Next →</a>
            @else
                <span class="flex items-center gap-1 px-2 py-1 text-gray-300 cursor-not-allowed">Next →</span>
            @endif
        </div>

    </div>

    {{-- ===================== MODAL ===================== --}}
    {{-- Pakai teleport ke body agar tidak terpengaruh layout sidebar --}}
    <div id="requestModal"
        style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">

        <div
            style="background:#fff; border-radius:16px; width:100%; max-width:760px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">

            {{-- Header --}}
            <div style="display:flex; align-items:center; padding:24px 32px 12px 32px; gap:16px;">
                <h3 style="font-size:18px; font-weight:600; color:#1f2937; flex-shrink:0; margin:0;">
                    Request Information
                </h3>
                <div style="flex:1;">
                    <div style="height:6px; background:#e5e7eb; border-radius:99px; overflow:hidden;">
                        <div id="modalProgress"
                            style="height:6px; background:#93c5fd; border-radius:99px; width:0%; transition:width 0.5s;">
                        </div>
                    </div>
                </div>
                <button onclick="closeModal()"
                    style="color:#9ca3af; background:none; border:none; cursor:pointer; padding:4px; line-height:1;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div style="padding:16px 32px 24px 32px;">

                {{-- Info Fields --}}
                <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:28px;">

                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:130px; text-align:right; font-size:13px; color:#6b7280; flex-shrink:0;">ID
                            Request:</label>
                        <input id="modal_request_id" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#1f2937; outline:none; background:#fff;">
                    </div>

                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:130px; text-align:right; font-size:13px; color:#6b7280; flex-shrink:0;">Admin's
                            Name:</label>
                        <input id="modal_user_name" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#1f2937; outline:none; background:#fff;">
                    </div>

                    <div style="display:flex; align-items:center; gap:16px;">
                        <label
                            style="width:130px; text-align:right; font-size:13px; color:#6b7280; flex-shrink:0;">Total:</label>
                        <input id="modal_total" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#1f2937; outline:none; background:#fff;">
                    </div>

                </div>

                {{-- Two Column Tables --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

                    {{-- Electronic --}}
                    <div>
                        <p style="font-size:13px; color:#9ca3af; margin-bottom:8px;">Electronic Category</p>
                        <table
                            style="width:100%; font-size:13px; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                            <thead>
                                <tr style="background:#f3f4f6;">
                                    <th
                                        style="padding:8px 14px; text-align:left; font-weight:500; color:#374151; border-bottom:1px solid #e5e7eb;">
                                        Asset Name</th>
                                    <th
                                        style="padding:8px 14px; text-align:left; font-weight:500; color:#374151; border-bottom:1px solid #e5e7eb;">
                                        Qty</th>
                                </tr>
                            </thead>
                            <tbody id="modal_electronic">
                                <tr>
                                    <td colspan="2"
                                        style="padding:12px; text-align:center; color:#9ca3af; font-size:12px;">Memuat...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Non-Electronic --}}
                    <div>
                        <p style="font-size:13px; color:#9ca3af; margin-bottom:8px;">Non-Electronic Category</p>
                        <table
                            style="width:100%; font-size:13px; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                            <thead>
                                <tr style="background:#f3f4f6;">
                                    <th
                                        style="padding:8px 14px; text-align:left; font-weight:500; color:#374151; border-bottom:1px solid #e5e7eb;">
                                        Asset Name</th>
                                    <th
                                        style="padding:8px 14px; text-align:left; font-weight:500; color:#374151; border-bottom:1px solid #e5e7eb;">
                                        Qty</th>
                                </tr>
                            </thead>
                            <tbody id="modal_nonelectronic">
                                <tr>
                                    <td colspan="2"
                                        style="padding:12px; text-align:center; color:#9ca3af; font-size:12px;">Memuat...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                {{-- Notes --}}
                <div id="modal_notes_wrap" style="display:none; margin-bottom:12px;">
                    <p style="font-size:13px; color:#9ca3af; margin-bottom:4px;">Notes:</p>
                    <p id="modal_notes"
                        style="font-size:13px; color:#4b5563; background:#f9fafb; border-radius:8px; padding:10px 14px;">
                    </p>
                </div>

            </div>

            {{-- Footer --}}
            <div style="display:flex; justify-content:flex-end; gap:10px; padding:0 32px 24px 32px;">

                <form id="form_reject" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="Rejected">
                    <button type="submit"
                        style="padding:9px 24px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#4b5563; background:#fff; cursor:pointer;">
                        Reject
                    </button>
                </form>

                <form id="form_partial" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="Partially Approved">

                    <button type="submit"
                        style="padding:9px 24px; border:none; border-radius:8px; font-size:13px; font-weight:500; color:#fff; background:#2563eb; cursor:pointer;">
                        Partially Approved
                    </button>
                </form>

                <form id="form_approve" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="Approved">
                    <button type="submit"
                        style="padding:9px 24px; border:none; border-radius:8px; font-size:13px; font-weight:500; color:#fff; background:#111827; cursor:pointer;">
                        Approved
                    </button>
                </form>

            </div>

        </div>
    </div>
    {{-- ===================== END MODAL ===================== --}}

@endsection

@push('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush

@push('scripts')
    <script>
        function openModal(requestId) {
            const modal = document.getElementById('requestModal');

            // Reset
            document.getElementById('modal_request_id').value = '';
            document.getElementById('modal_user_name').value = '';
            document.getElementById('modal_total').value = '';
            document.getElementById('modal_electronic').innerHTML =
                '<tr><td colspan="2" style="padding:12px;text-align:center;color:#9ca3af;font-size:12px;">Memuat...</td></tr>';
            document.getElementById('modal_nonelectronic').innerHTML =
                '<tr><td colspan="2" style="padding:12px;text-align:center;color:#9ca3af;font-size:12px;">Memuat...</td></tr>';
            document.getElementById('modal_notes_wrap').style.display = 'none';
            document.getElementById('modalProgress').style.width = '30%';

            // Set form action
            document.getElementById('form_reject').action = `/requestlab/${requestId}/status`;
            document.getElementById('form_approve').action = `/requestlab/${requestId}/status`;

            // Tampilkan modal — pakai flex agar center
            modal.style.display = 'flex';

            // Pindahkan modal ke body supaya tidak terpengaruh layout sidebar
            document.body.appendChild(modal);

            // Fetch JSON
            fetch(`/requestlab/${requestId}/detail`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalProgress').style.width = '100%';

                    document.getElementById('modal_request_id').value = data.request_id ?? '-';
                    document.getElementById('modal_user_name').value = data.user_name ?? '-';
                    document.getElementById('modal_total').value = data.total_request ?? '-';

                    if (data.notes) {
                        document.getElementById('modal_notes').textContent = data.notes;
                        document.getElementById('modal_notes_wrap').style.display = 'block';
                    }

                    const rowHtml = (items) => (items ?? []).length ?
                        items.map(a => `
                    <tr style="border-top:1px solid #f3f4f6;">
                        <td style="padding:8px 14px;color:#374151;">${a.asset_name}</td>
                        <td style="padding:8px 14px;color:#374151;">${a.quantity}</td>
                    </tr>`).join('') :
                        '<tr><td colspan="2" style="padding:12px;text-align:center;color:#9ca3af;font-size:12px;">Tidak ada data</td></tr>';

                    document.getElementById('modal_electronic').innerHTML = rowHtml(data.electronic);
                    document.getElementById('modal_nonelectronic').innerHTML = rowHtml(data.non_electronic);
                })
                .catch(() => {
                    document.getElementById('modalProgress').style.width = '100%';
                    const errHtml =
                        '<tr><td colspan="2" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat data</td></tr>';
                    document.getElementById('modal_electronic').innerHTML = errHtml;
                    document.getElementById('modal_nonelectronic').innerHTML = errHtml;
                });
        }

        function closeModal() {
            const modal = document.getElementById('requestModal');
            modal.style.display = 'none';
            document.getElementById('modalProgress').style.width = '0%';
        }

        // Klik backdrop → tutup
        document.getElementById('requestModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Tombol Escape → tutup
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
@endpush
