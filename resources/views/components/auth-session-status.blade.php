@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 text-sm font-medium text-green-600 dark:text-green-400']) }}>
        {{ $status }}
    </div>
@endif
