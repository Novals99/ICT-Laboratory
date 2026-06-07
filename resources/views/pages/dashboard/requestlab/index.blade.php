@extends('panel.content')

@section('title', 'Request Lab')

@section('content')

<div class="bg-white rounded-2xl p-6 shadow-sm">

```
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-semibold text-gray-800">
        Request List
    </h2>

    <div class="flex items-center gap-3">
        <input
            type="text"
            placeholder="Search..."
            class="w-56 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none">

        <button class="px-4 py-2 border border-gray-300 rounded-lg">
            Filter
        </button>

        <button class="px-4 py-2 border border-gray-300 rounded-lg">
            Export
        </button>
    </div>
</div>

<div class="overflow-x-auto">

    <table class="w-full">

        <thead>
            <tr class="bg-gray-100 text-gray-700">
                <th class="p-3 w-10">
                    <input type="checkbox">
                </th>
                <th class="p-3 text-left">ID Request</th>
                <th class="p-3 text-left">Name</th>
                <th class="p-3 text-center">Total Request</th>
                <th class="p-3 text-center">Date</th>
                <th class="p-3 text-center">Status</th>
                <th class="p-3 text-center">Action</th>
            </tr>
        </thead>

        <tbody>

            <tr class="border-b hover:bg-blue-50">
                <td class="p-3">
                    <input type="checkbox">
                </td>
                <td>2604260108</td>
                <td>Ali rajin mengaji</td>
                <td class="text-center">8</td>
                <td class="text-center">26-04-26</td>
                <td class="text-center">
                    <span class="bg-yellow-400 px-4 py-1 rounded-lg text-sm">
                        Pending
                    </span>
                </td>
                <td class="text-center">
                    ✏️ 🗑️
                </td>
            </tr>

            <tr class="border-b hover:bg-blue-50">
                <td class="p-3">
                    <input type="checkbox">
                </td>
                <td>2805260210</td>
                <td>Mr. beast</td>
                <td class="text-center">10</td>
                <td class="text-center">28-05-26</td>
                <td class="text-center">
                    <span class="bg-green-600 text-white px-4 py-1 rounded-lg text-sm">
                        Approved
                    </span>
                </td>
                <td class="text-center">
                    ✏️ 🗑️
                </td>
            </tr>

            <tr class="border-b bg-blue-50">
                <td class="p-3">
                    <input type="checkbox">
                </td>
                <td>2704260302</td>
                <td>Tipal raja teler</td>
                <td class="text-center">2</td>
                <td class="text-center">27-04-26</td>
                <td class="text-center">
                    <span class="bg-green-600 text-white px-4 py-1 rounded-lg text-sm">
                        Approved
                    </span>
                </td>
                <td class="text-center">
                    ✏️ 🗑️
                </td>
            </tr>

            <tr class="border-b">
                <td class="p-3"><input type="checkbox"></td>
                <td>2604260108</td>
                <td>ustad ali</td>
                <td class="text-center">8</td>
                <td class="text-center">26-04-26</td>
                <td class="text-center">
                    <span class="bg-yellow-400 px-4 py-1 rounded-lg text-sm">
                        Pending
                    </span>
                </td>
                <td class="text-center">✏️ 🗑️</td>
            </tr>

            <tr class="border-b">
                <td class="p-3"><input type="checkbox"></td>
                <td>2604260108</td>
                <td>berkah ali</td>
                <td class="text-center">8</td>
                <td class="text-center">26-04-26</td>
                <td class="text-center">
                    <span class="bg-red-600 text-white px-4 py-1 rounded-lg text-sm">
                        Rejected
                    </span>
                </td>
                <td class="text-center">✏️ 🗑️</td>
            </tr>

            <tr class="border-b">
                <td class="p-3"><input type="checkbox"></td>
                <td>2604260108</td>
                <td>coach nopal</td>
                <td class="text-center">8</td>
                <td class="text-center">26-04-26</td>
                <td class="text-center">
                    <span class="bg-yellow-400 px-4 py-1 rounded-lg text-sm">
                        Pending
                    </span>
                </td>
                <td class="text-center">✏️ 🗑️</td>
            </tr>

        </tbody>

    </table>

</div>

<div class="flex justify-end items-center gap-3 mt-6 text-sm">
    <span class="text-gray-400">Previous</span>
    <button class="w-7 h-7 rounded bg-gray-800 text-white">1</button>
    <button>2</button>
    <button>3</button>
    <span>...</span>
    <button>67</button>
    <button>68</button>
    <button>Next</button>
</div>
```

</div>

@endsection

@push('styles')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush
