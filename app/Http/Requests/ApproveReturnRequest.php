<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ApproveReturnRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya SPV yang boleh submit approval (cek kolom `role`)
        return Auth::user()?->role === 'spv inventory';
    }

    public function rules(): array
    {
        return [
            'items'                      => ['required', 'array'],
            'items.*.id'                 => ['required', 'integer', 'exists:return_request_items,id'],

            // quantity_approved bisa 0 (artinya item ini tidak disetujui / partial reject)
            // Tidak boleh negatif
            'items.*.quantity_approved'  => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.quantity_approved.min' => 'Jumlah yang disetujui tidak boleh negatif.',
        ];
    }
}
