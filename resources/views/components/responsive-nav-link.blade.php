@props(['active' => false])

@php
    $classes = $active
        ? 'block w-full border-l-4 border-indigo-400 bg-indigo-50 py-2 pe-4 ps-3 text-start text-base font-medium text-indigo-700 transition duration-150 ease-in-out focus:outline-none dark:border-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300'
        : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-gray-600 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:outline-none dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
