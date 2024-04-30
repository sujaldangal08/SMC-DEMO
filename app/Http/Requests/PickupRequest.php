<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesRoles;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class PickupRequest extends FormRequest
{
    use ValidatesRoles;
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
        //Check if the request is a POST request
        $isPostRequest = $this->isMethod('post');

        $dateRule = $isPostRequest ? 'required|date' : 'nullable|date';
        $materialsCount = is_array($this->input('materials')) ? count($this->input('materials')) : 0;
        $n_bins = $this->has('n_bins') ? $this->input('n_bins') : 2;
        return [
            'route_id' => ['nullable', 'exists:routes,id'],
            'asset_id' => ['nullable', Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
            'driver_id' => ['nullable', $this->roleRule('driver')],
            'customer_id' => ['nullable', $this->roleRule('customer')],
            'pickup_date' => $dateRule,
            'status' => 'nullable|in:pending,active,inactive,done,unloading,full,schedule',
            'notes' => 'nullable',
            'materials' => 'nullable|array',
            'amount' => ['nullable', 'array', 'size:' . $materialsCount],
            'weighing_type' => ['nullable', 'array', 'in:bridge,pallet', 'size:' . $materialsCount],
            'n_bins' => 'nullable|integer',
            'tare_weight' => 'nullable|array',
            'image' => ['nullable', 'array', 'size:' . $n_bins],
            'coordinates' => 'nullable|array|size:2',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     */
    protected function failedValidation(Validator $validator): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 500);
    }
}
