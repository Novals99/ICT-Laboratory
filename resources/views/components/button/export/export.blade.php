@props([
    'menuId'   => 'exportMenu',
    'pdfUrl'   => null,
    'excelUrl' => null,
    'csvUrl'   => null,
])

<div class="relative">
    <button
        type="button"
        onclick="toggleExportMenu('{{ $menuId }}')"
        {{ $attributes->merge(['class' => 'panel-btn-secondary']) }}
    >
        <span>Export</span>
    </button>

    <div id="{{ $menuId }}" class="asset-category-menu hidden">
        <div class="asset-category-menu-title">Export As</div>

        @if ($pdfUrl)
            <a href="{{ $pdfUrl }}" class="asset-category-menu-item">PDF</a>
        @endif

        @if ($excelUrl)
            <a href="{{ $excelUrl }}" class="asset-category-menu-item">Excel (.xlsx)</a>
        @endif

        @if ($csvUrl)
            <a href="{{ $csvUrl }}" class="asset-category-menu-item">CSV</a>
        @endif
    </div>
</div>


@push('scripts')
<script>
    function toggleExportMenu(menuId) {
    const menu = document.getElementById(menuId);
    menu.classList.toggle('hidden');

    // klik diluar popup bklout
    document.addEventListener('click', function closeMenu(e) {
        if (!menu.contains(e.target) && !e.target.closest('[onclick]')) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeMenu);
        }
    });
}
</script>
@endpush
