@extends('panel.content')
@section('title', auth()->user()->role === 'spv inventory' ? 'SPV Dashboard' : 'Staff Dashboard')

@section('content')
<div class="db-wrap">
    <div class="db-card bg-white dark:bg-gray-800" style="padding:0; overflow:hidden;">

        <div style="padding:24px; border-bottom:1px solid #f3f4f6;">
            <h2 style="font-size:20px; font-weight:bold; margin:0 0 6px; color:#111827;">Recycle Bin</h2>
            <p style="color:#6b7280; margin:0; font-size:13px;">
                Laboratorium yang dihapus muncul di sini. Anda bisa memulihkan, atau menghapus permanen
                (stok asset otomatis dikembalikan ke inventory).
            </p>
        </div>

        @if(session('success'))
        <div style="margin:16px 24px 0; background:#dcfce7; color:#166534; border-radius:8px; padding:10px 16px; font-size:13px;">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div style="margin:16px 24px 0; background:#fee2e2; color:#991b1b; border-radius:8px; padding:10px 16px; font-size:13px;">
            {{ session('error') }}
        </div>
        @endif

        <div style="overflow-x:auto; margin-top:8px;">
            <table style="width:100%; border-collapse:collapse; font-size:13.5px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Lab Name</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Capacity</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Dihapus Pada</th>
                        <th style="padding:12px 16px; text-align:center; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trashedLabs as $lab)
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:13px 16px; font-weight:600; color:#111827;">{{ $lab->lab_name }}</td>
                        <td style="padding:13px 16px; color:#374151;">{{ $lab->capacity }} PC</td>
                        <td style="padding:13px 16px; color:#374151;">{{ $lab->deleted_at?->format('d M Y H:i') }}</td>
                        <td style="padding:13px 16px; text-align:center;">
                            <div style="display:inline-flex; gap:8px;">
                                <form method="POST" action="{{ route('laboratory.restore', $lab->id) }}"
                                      onsubmit="return confirm('Pulihkan lab {{ addslashes($lab->lab_name) }}?')">
                                    @csrf
                                    <button type="submit" style="border:1px solid #16a34a; background:#fff; color:#16a34a; border-radius:6px; padding:6px 14px; font-size:12px; font-weight:600; cursor:pointer;">
                                        Restore
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('laboratory.forceDestroy', $lab->id) }}"
                                      onsubmit="return confirm('Hapus permanen lab {{ addslashes($lab->lab_name) }}? Tindakan ini tidak bisa dibatalkan. Stok asset akan dikembalikan ke inventory.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="border:1px solid #dc2626; background:#fff; color:#dc2626; border-radius:6px; padding:6px 14px; font-size:12px; font-weight:600; cursor:pointer;">
                                        Delete Permanently
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:32px; color:#9ca3af; font-size:13px;">
                            Belum ada laboratorium di recycle bin.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trashedLabs->hasPages())
        <div style="padding:16px 24px; border-top:1px solid #f3f4f6;">
            {{ $trashedLabs->links() }}
        </div>
        @endif

        <div style="padding:16px 24px; border-top:1px solid #f3f4f6;">
            <a href="{{ route('laboratory.index') }}"
               style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; text-decoration:none; color:#374151; font-weight:500; display:inline-flex; align-items:center; gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Back to Laboratory
            </a>
        </div>
    </div>
</div>
@endsection
