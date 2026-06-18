@extends('panel.content')
@section('title', 'Recycle Bin')

@section('content')
<div class="db-wrap">
    <div class="db-card bg-white dark:bg-gray-800">
        <div style="padding:24px;">
            <h2 style="font-size:20px; font-weight:bold; margin:0 0 16px; color:#111827;">Recycle Bin</h2>
            <p style="color:#6b7280; margin:0;">
                Laboratorium yang telah dihapus akan muncul di sini. Anda dapat memulihkan atau menghapus secara permanen.
            </p>
        </div>

        <div style="padding:24px; text-align:center; color:#9ca3af;">
            <p>Belum ada laboratorium di recycle bin.</p>
        </div>

        <div style="padding:16px 24px; border-top:1px solid #f3f4f6; display:flex; gap:12px;">
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
