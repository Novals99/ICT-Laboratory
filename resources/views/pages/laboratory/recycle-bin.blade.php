@extends('panel.content')

@section('title', auth()->user()->role === 'spv inventory' ? 'SPV Dashboard' : 'Staff Dashboard')

@section('content')
    <div class="panel-page-card">

        {{-- Header --}}
        <div class="mb-5 flex flex-col gap-2">
            <h2 class="panel-page-title">
                Recycle Bin
            </h2>
            <p class="text-sm text-gray-500">
                Laboratories that have been deleted will appear here. You can restore them or permanently delete them (asset stock will be automatically returned to the inventory).
            </p>
        </div>

        {{-- Alert --}}
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 text-green-700 px-4 py-2 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-100 text-red-700 px-4 py-2 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Table --}}
        <x-table.index>
            <thead>
                <tr>
                    <x-table.th>Lab Name</x-table.th>
                    <x-table.th>Capacity</x-table.th>
                    <x-table.th>Deleted at</x-table.th>
                    <x-table.th align="center">Action</x-table.th>
                </tr>
            </thead>

            <tbody>
                @forelse($trashedLabs as $lab)
                    <tr class="panel-table-row">
                        <x-table.td class="font-semibold">
                            {{ $lab->lab_name }}
                        </x-table.td>

                        <x-table.td>
                            {{ $lab->capacity }} PC
                        </x-table.td>

                        <x-table.td>
                            {{ $lab->deleted_at?->format('d M Y H:i') }}
                        </x-table.td>

                        <x-table.td align="center">
                            <div class="flex items-center justify-center gap-2">

                                {{-- Restore --}}
                                <form method="POST" action="{{ route('laboratory.restore', $lab->id) }}">
                                    @csrf
                                        <x-table.action
                                            type="button"
                                            variant="restore"
                                            title="Restore"
                                            onclick="window.dispatchEvent(new CustomEvent('open-confirm', {
                                                detail: {
                                                    title: 'Restore Laboratory?',
                                                    message: 'Restore {{ $lab->lab_name }}?',
                                                    form: this.closest('form'),
                                                    type: 'restore'
                                                }
                                            }))"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                                <path d="M3 3v5h5"/>
                                            </svg>
                                        </x-table.action>
                                </form>

                                {{-- Delete Permanently --}}
                                <form method="POST" action="{{ route('laboratory.forceDestroy', $lab->id) }}">
                                    @csrf
                                    @method('DELETE')

                                        <x-table.action
                                            type="button"
                                            variant="delete"
                                            title="Delete Permanently"
                                            onclick="window.dispatchEvent(new CustomEvent('open-confirm', {
                                                detail: {
                                                    title: 'Delete Permanently?',
                                                    message: 'Permanently delete {{ $lab->lab_name }}?',
                                                    form: this.closest('form'),
                                                    type: 'delete'
                                                }
                                            }))"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                <path d="M10 11v6M14 11v6"/>
                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                            </svg>
                                        </x-table.action>
                                </form>

                            </div>
                        </x-table.td>
                    </tr>
                @empty
                    <x-table.empty colspan="4" message="No laboratories in the recycle bin." />
                @endforelse
            </tbody>
        </x-table.index>

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $trashedLabs->links() }}
        </div>

        {{-- Back Button --}}
        <div class="mt-4">
            <a href="{{ route('laboratory.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Back to Laboratory
            </a>
        </div>

    </div>
@endsection