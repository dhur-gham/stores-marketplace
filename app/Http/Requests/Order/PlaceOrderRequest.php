<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Address data is keyed by store_id
            // Each store can have city_id and address (for physical stores)
            // We'll validate dynamically in the service based on store type
            'address_data' => ['nullable', 'array'],
            'address_data.*.city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address_data.*.address' => ['nullable', 'string', 'min:5'],
            'payment_method' => ['nullable', 'string', 'in:cod'], // Online payment temporarily disabled, defaults to 'cod' in controller
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address_data.*.address.min' => 'The address must be at least :min characters.',
            'address_data.*.address.string' => 'The address must be a valid text.',
            'address_data.*.city_id.integer' => 'The city must be a valid selection.',
            'address_data.*.city_id.exists' => 'The selected city is invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'address_data.*.address' => 'address',
            'address_data.*.city_id' => 'city',
        ];
    }
}
