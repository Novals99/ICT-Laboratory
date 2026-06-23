<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreReturnRequest extends FormRequest
{
    /**
     * Hanya user yang sudah login yang boleh submit.
     * Superadmin tidak seharusnya buat return request, tapi tidak dilarang.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            // Lab harus ada di DB dan statusnya aktif
            'lab_id'            => ['required', 'integer', 'exists:laboratories,id'],

            'notes'             => ['nullable', 'string', 'max:1000'],

            // Minimal 1 item per request
            'items'             => ['required', 'array', 'min:1'],

            // Validasi per item menggunakan dot notation (items.*)
            'items.*.asset_id'  => ['required', 'integer', 'exists:assets,id'],
            'items.*.quantity'  => ['required', 'integer', 'min:1'],
            'items.*.condition' => ['required', 'in:good,damaged,lost'],
            'items.*.reason'    => ['nullable', 'string', 'max:500'],
            'items.*.serial_number_ids' => ['nullable', 'array'],
            'items.*.serial_number_ids.*' => ['nullable', 'integer', 'exists:asset_serial_numbers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'lab_id.required'           => 'Laboratorium wajib dipilih.',
            'lab_id.exists'             => 'Laboratorium tidak ditemukan.',
            'items.required'            => 'Minimal harus ada 1 barang yang diretur.',
            'items.min'                 => 'Minimal harus ada 1 barang yang diretur.',
            'items.*.asset_id.required' => 'Barang wajib dipilih.',
            'items.*.asset_id.exists'   => 'Barang tidak ditemukan di database.',
            'items.*.quantity.required' => 'Jumlah wajib diisi.',
            'items.*.quantity.min'      => 'Jumlah minimal 1.',
            'items.*.condition.required'=> 'Kondisi barang wajib dipilih.',
            'items.*.condition.in'      => 'Kondisi tidak valid. Pilih: Baik, Rusak, atau Hilang.',
        ];
    }
}
