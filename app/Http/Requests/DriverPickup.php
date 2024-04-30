<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class DriverPickup extends FormRequest
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
        $materialsCount = is_array($this->input('materials')) ? count($this->input('materials')) : 0;
        $n_bins = $this->has('n_bins') ? $this->input('n_bins') : 2;

        return [
            'status' => 'nullable|in:pending,active,inactive,done,unloading,schedule',
            'notes' => 'nullable',
            'materials' => 'nullable|array',
            'amount' => ['nullable', 'array', 'size:'.$materialsCount, 'numeric'],
            'weighing_type' => ['nullable', 'array', 'in:bridge,pallet', 'size:'.$materialsCount],
            'n_bins' => 'nullable|integer',
            'tare_weight' => ['nullable', 'array', 'numeric', 'size:'.$n_bins],
            'image' => ['nullable', 'array', 'size:'.$n_bins],
        ];
    }

    protected function failedValidation(Validator $validator): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 422);
    }
}
