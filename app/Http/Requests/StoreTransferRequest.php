<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'from_lab_id'       => ['required', 'integer', 'exists:laboratories,id'],

            // to_lab_id tidak boleh sama dengan from_lab_id
            // Rule 'different' otomatis handle ini
            'to_lab_id'         => ['required', 'integer', 'exists:laboratories,id', 'different:from_lab_id'],

            'notes'             => ['nullable', 'string', 'max:1000'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.asset_id'  => ['required', 'integer', 'exists:assets,id'],
            'items.*.quantity'  => ['required', 'integer', 'min:1'],
            'items.*.notes'     => ['nullable', 'string', 'max:500'],
            'items.*.serial_number_ids' => ['nullable', 'array'],
            'items.*.serial_number_ids.*' => ['nullable', 'integer', 'exists:asset_serial_numbers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_lab_id.required'  => 'Lab asal wajib dipilih.',
            'to_lab_id.required'    => 'Lab tujuan wajib dipilih.',
            'to_lab_id.different'   => 'Lab tujuan tidak boleh sama dengan lab asal.',
            'items.required'        => 'Minimal harus ada 1 barang yang ditransfer.',
            'items.*.asset_id.required' => 'Barang wajib dipilih.',
            'items.*.quantity.min'      => 'Jumlah minimal 1.',
        ];
    }
}
