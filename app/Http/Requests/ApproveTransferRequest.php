<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ApproveTransferRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()?->role === 'spv inventory';
    }

    public function rules(): array
    {
        return [
            'items'                     => ['required', 'array'],
            'items.*.id'                => ['required', 'integer', 'exists:transfer_request_items,id'],
            'items.*.quantity_approved' => ['required', 'integer', 'min:0'],
        ];
    }
}
