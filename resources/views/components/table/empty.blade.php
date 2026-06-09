@props([
    'colspan' => 1,
    'message' => 'Data belum tersedia.',
])

<tr>
    <td colspan="{{ $colspan }}" class="panel-table-empty">
        {{ $message }}
    </td>
</tr>

{{-- buat semisal ada yg datanya kosong --}}