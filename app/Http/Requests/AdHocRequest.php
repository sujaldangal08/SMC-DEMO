<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesRoles;
use Illuminate\Contracts\Validation\Validator;

class AdHocRequest extends FormRequest
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
        $materialsCount = is_array($this->input('materials')) ? count($this->input('materials')) : 0;

        return [
            'customer_id' => ['required', $this->roleRule('customer')],
            'staff_id' => [$this->roleRule('manager')],
            'materials' => ['required', 'array'],
            'rate' => ['required', 'array', 'size:' . $materialsCount],
            'amount' => ['required', 'array', 'size:' . $materialsCount],
            'notes' => 'sometimes|string',
            'customer_status' => 'sometimes|in:pending,approved,rejected,review',
            'staff_status' => 'sometimes|in:pending,approved,rejected,review',
            'branch_id' => 'required|exists:branches,id',
            'attachment.*' => ['required', 'mimes:pdf,jpg,jpeg,png'],
            'weighing_type' => ['required', 'array', 'in:bridge,pallet', 'size:' . $materialsCount],
        ];
    }

    /** 
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    protected function failedValidation(Validator $validator)
    {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors(),
        ], 422);
    }
}
