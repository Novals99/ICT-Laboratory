@extends('panel.content')

@section('title', 'Admin Dashboard')

@section('content')
<div class="panel-page-card">

       {{-- header --}}
       <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div>
                     <h2 class="panel-page-title">
                            Asset Log
                     </h2>
              </div>

              <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                     {{-- search --}}
                     <x-button.search.modul-search :action="route('assetlog.index')" name="search"
                            :value="request('search')" placeholder="Search..." />

                     {{-- filter --}}
                     <x-button.filter :action="route('assetlog.index')">
                            {{-- keep search value --}}
                            @if (request('search'))
                                   <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            {{-- filter lab --}}
                            <div class="filter-section">
                                   <div class="filter-section-title">Lab Terkait</div>
                                   <div class="max-h-40 space-y-2 overflow-y-auto pr-1">
                                          @forelse ($laboratories as $lab)
                                                 <label class="filter-checkbox-row">
                                                        <input type="checkbox" name="lab_id[]" value="{{ $lab->id }}" {{ in_array($lab->id, (array) request('lab_id', [])) ? 'checked' : '' }} style="accent-color: #111B4C;">
                                                        <span>{{ $lab->lab_name }}</span>
                                                 </label>
                                          @empty
                                                 <p class="text-sm text-gray-400">Lab data not found.</p>
                                          @endforelse
                                   </div>
                            </div>

                            {{-- filter type --}}
                            <div class="filter-section">
                                   <div class="filter-section-title">Type Log</div>
                                   @php
                                          $types = [
                                                 'stock_in' => 'Stock In',
                                                 'stock_out' => 'Stock Out',
                                                 'transfer' => 'Transfer',
                                                 'adjustment' => 'Adjustment',
                                                 'damaged' => 'Damaged',
                                                 'lost' => 'Lost',
                                                 'repaired' => 'Repaired',
                                          ];
                                   @endphp
                                   @foreach ($types as $value => $label)
                                          <label class="filter-checkbox-row">
                                                 <input type="checkbox" name="type[]" value="{{ $value }}" {{ in_array($value, (array) request('type', [])) ? 'checked' : '' }}
                                                        style="accent-color: #111B4C;">
                                                 <span>{{ $label }}</span>
                                          </label>
                                   @endforeach
                            </div>

                            {{-- filter date --}}
                            <div class="filter-section">
                                   <div class="filter-section-title">Log Date</div>
                                   <div class="filter-date-group">
                                          <div class="filter-date-item">
                                                 <label class="filter-date-label">From date</label>
                                                 <input type="date" name="date_from" value="{{ request('date_from') }}"
                                                        class="filter-date-input">
                                          </div>
                                          <div class="filter-date-item">
                                                 <label class="filter-date-label">To date</label>
                                                 <input type="date" name="date_to" value="{{ request('date_to') }}"
                                                        class="filter-date-input">
                                          </div>
                                   </div>
                            </div>
                     </x-button.filter>

                     {{-- export --}}
                     <x-button.export
                            href="{{ route('assetlog.index', array_merge(request()->query(), ['export' => 'excel'])) }}">
                            Export
                     </x-button.export>
              </div>
       </div>

       {{-- active filter info --}}
       @if (request()->hasAny(['search', 'lab_id', 'type', 'date_from', 'date_to']))
              <div
                     class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-300">
                     Filter is active.

                     <a href="{{ route('assetlog.index') }}" class="ml-2 font-semibold underline">
                            Reset all filter
                     </a>
              </div>
       @endif

       {{-- table --}}
       <x-table.index>
              <thead>
                     <tr>
                            <x-table.th>No</x-table.th>
                            <x-table.th>Date</x-table.th>
                            <x-table.th>Asset</x-table.th>
                            <x-table.th>Type</x-table.th>
                            <x-table.th>Lab Terkait</x-table.th>
                            <x-table.th>Qty</x-table.th>
                            <x-table.th>Before</x-table.th>
                            <x-table.th>After</x-table.th>
                            <x-table.th>Source</x-table.th>
                            <x-table.th>Handled By</x-table.th>
                            <x-table.th>Notes</x-table.th>
                     </tr>
              </thead>

              <tbody>
                     @forelse ($logs as $log)
                            <tr class="panel-table-row">
                                   <x-table.td>
                                          {{ $logs->firstItem() + $loop->index }}
                                   </x-table.td>

                                   <x-table.td>
                                          {{ $log->created_at ? $log->created_at->format('d-m-Y H:i') : '-' }}
                                   </x-table.td>

                                   <x-table.td>
                                          <div class="font-semibold text-gray-800 dark:text-gray-100">
                                                 {{ $log->asset->asset_name ?? '-' }}
                                          </div>

                                          <div class="text-xs text-gray-400">
                                                 {{ $log->asset->asset_category ?? '-' }}
                                          </div>
                                   </x-table.td>

                                   <x-table.td>
                                          @php
                                                 $typeLabel = match ($log->type) {
                                                        'stock_in' => 'Stock In',
                                                        'stock_out' => 'Stock Out',
                                                        'transfer' => 'Transfer',
                                                        'adjustment' => 'Adjustment',
                                                        'damaged' => 'Damaged',
                                                        'lost' => 'Lost',
                                                        'repaired' => 'Repaired',
                                                        default => ucwords(str_replace('_', ' ', $log->type)),
                                                 };

                                                 $typeClass = match ($log->type) {
                                                        'stock_in' => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950/30 dark:text-green-300 dark:border-green-900',
                                                        'stock_out' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/30 dark:text-red-300 dark:border-red-900',
                                                        'transfer' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-300 dark:border-blue-900',
                                                        'adjustment' => 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-950/30 dark:text-yellow-300 dark:border-yellow-900',
                                                        'damaged' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/30 dark:text-orange-300 dark:border-orange-900',
                                                        'lost' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/30 dark:text-purple-300 dark:border-purple-900',
                                                        'repaired' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/30 dark:text-teal-300 dark:border-teal-900',
                                                        default => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                                 };
                                          @endphp

                                          <span
                                                 class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $typeClass }}">
                                                 {{ $typeLabel }}
                                          </span>
                                   </x-table.td>

                                   <x-table.td>
                                          @if ($log->type === 'transfer')
                                                 <div class="text-sm text-gray-700 dark:text-gray-200">
                                                        {{ $log->fromLab->lab_name ?? '-' }}
                                                 </div>

                                                 <div class="text-xs text-gray-400">
                                                        to
                                                 </div>

                                                 <div class="text-sm text-gray-700 dark:text-gray-200">
                                                        {{ $log->toLab->lab_name ?? '-' }}
                                                 </div>
                                          @elseif ($log->fromLab)
                                                 {{ $log->fromLab->lab_name }}
                                          @elseif ($log->toLab)
                                                 {{ $log->toLab->lab_name }}
                                          @else
                                                 <span class="text-gray-400">-</span>
                                          @endif
                                   </x-table.td>

                                   <x-table.td>
                                          {{ number_format($log->quantity ?? 0) }}
                                   </x-table.td>

                                   <x-table.td>
                                          <div class="text-xs leading-5 text-gray-600 dark:text-gray-300">
                                                 <div>Total: {{ number_format($log->before_total_asset ?? 0) }}</div>
                                                 <div>Good: {{ number_format($log->before_total_good ?? 0) }}</div>
                                                 <div>Damaged: {{ number_format($log->before_total_damaged ?? 0) }}</div>
                                                 <div>Loss: {{ number_format($log->before_total_loss ?? 0) }}</div>
                                          </div>
                                   </x-table.td>

                                   <x-table.td>
                                          <div class="text-xs leading-5 text-gray-600 dark:text-gray-300">
                                                 <div>Total: {{ number_format($log->after_total_asset ?? 0) }}</div>
                                                 <div>Good: {{ number_format($log->after_total_good ?? 0) }}</div>
                                                 <div>Damaged: {{ number_format($log->after_total_damaged ?? 0) }}</div>
                                                 <div>Loss: {{ number_format($log->after_total_loss ?? 0) }}</div>
                                          </div>
                                   </x-table.td>

                                   <x-table.td>
                                          {{ $log->source ?? '-' }}
                                   </x-table.td>

                                   <x-table.td>
                                          <div class="font-medium text-gray-700 dark:text-gray-200">
                                                 {{ $log->user->name ?? '-' }}
                                          </div>

                                          @if ($log->user?->role)
                                                 <div class="text-xs text-gray-400">
                                                        {{ ucwords($log->user->role) }}
                                                 </div>
                                          @endif
                                   </x-table.td>

                                   <x-table.td>
                                          <span title="{{ $log->notes }}">
                                                 {{ $log->notes ? Str::limit($log->notes, 45) : '-' }}
                                          </span>
                                   </x-table.td>
                            </tr>
                     @empty
                            <x-table.empty colspan="11" message="Belum ada data asset log." />
                     @endforelse
              </tbody>
       </x-table.index>

       {{-- pagination --}}
       <div class="mt-5">
              {{ $logs->links() }}
       </div>
</div>
@endsection

@push('scripts')
<script>
       function toggleAssetLogFilter(event) {
              event.stopPropagation();

              const dropdown = document.getElementById('assetLogFilterDropdown');

              if (!dropdown) return;

              dropdown.classList.toggle('hidden');
       }

       document.addEventListener('click', function (event) {
              const dropdown = document.getElementById('assetLogFilterDropdown');

              if (!dropdown) return;

              if (!dropdown.contains(event.target)) {
                     dropdown.classList.add('hidden');
              }
       });

       document.addEventListener('keydown', function (event) {
              if (event.key === 'Escape') {
                     const dropdown = document.getElementById('assetLogFilterDropdown');

                     if (dropdown) {
                            dropdown.classList.add('hidden');
                     }
              }
       });
</script>
@endpush
