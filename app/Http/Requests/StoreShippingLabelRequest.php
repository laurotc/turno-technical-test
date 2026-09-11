<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShippingLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from_address' => ['required', 'array'],
            'from_address.name' => ['required', 'string', 'max:255'],
            'from_address.company' => ['nullable', 'string', 'max:255'],
            'from_address.street1' => ['required', 'string', 'max:255'],
            'from_address.street2' => ['nullable', 'string', 'max:255'],
            'from_address.city' => ['required', 'string', 'max:255'],
            'from_address.state' => ['required', 'string', 'size:2'],
            'from_address.zip' => ['required', 'string', 'max:10'],
            'from_address.country' => ['required', 'string', Rule::in(['US'])],
            'from_address.phone' => ['required', 'string', 'max:30'],
            'from_address.email' => ['nullable', 'email', 'max:255'],

            'to_address' => ['required', 'array'],
            'to_address.name' => ['required', 'string', 'max:255'],
            'to_address.company' => ['nullable', 'string', 'max:255'],
            'to_address.street1' => ['required', 'string', 'max:255'],
            'to_address.street2' => ['nullable', 'string', 'max:255'],
            'to_address.city' => ['required', 'string', 'max:255'],
            'to_address.state' => ['required', 'string', 'size:2'],
            'to_address.zip' => ['required', 'string', 'max:10'],
            'to_address.country' => ['required', 'string', Rule::in(['US'])],
            'to_address.phone' => ['required', 'string', 'max:30'],
            'to_address.email' => ['nullable', 'email', 'max:255'],

            'parcel' => ['required', 'array'],
            'parcel.length' => ['required', 'numeric', 'gt:0'],
            'parcel.width' => ['required', 'numeric', 'gt:0'],
            'parcel.height' => ['required', 'numeric', 'gt:0'],
            'parcel.weight' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
