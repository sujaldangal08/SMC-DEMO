<?php

namespace App\Http\Requests;

use App\Traits\ValidatesRoles;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryRequest extends FormRequest
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
        // Common rules for both 'post' and 'put'/'patch' methods
        $rules = [
            'schedule_id' => 'sometimes|exists:schedules,id',
            'vehicle_id' => ['sometimes', Rule::exists('assets', 'id')->where('asset_type', 'vehicle')],
            'driver_id' => ['sometimes', $this->roleRule('driver')],
            'materials' => 'sometimes|array',
            'amount_loaded' => 'sometimes|array',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
            'trip_number' => 'sometimes|string',
            'attachment' => ['nullable', 'array'],
            'notes' => 'nullable|string',
        ];

        if ($this->isMethod('post')) {
            // Additional rules for 'post' method
            $rules['schedule_id'] = 'required|'.$rules['schedule_id'];
            $rules['materials'] = 'required|'.$rules['materials'];
            $rules['amount_loaded'] = 'required|'.$rules['amount_loaded'];
            $rules['status'] = 'required|'.$rules['status'];
            $rules['trip_number'] = 'required|'.$rules['trip_number'];
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 422);
    }
}
