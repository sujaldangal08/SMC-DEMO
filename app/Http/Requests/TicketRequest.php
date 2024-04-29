<?php

namespace App\Http\Requests;

use App\Traits\ValidatesRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;

class TicketRequest extends FormRequest
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

        $isPostRequest = $this->isMethod('post');
        $customerIdCount = count($this->input('customer_id'));
        $weighingTypeIsPallet = $this->input('weighing_type') === 'pallet';

        $dataTimeRule = $isPostRequest ? 'required|date' : 'nullable|date';
        $arrayOfSizeRule = ['required', 'array', 'size:' . $customerIdCount];
        $weightRule = $weighingTypeIsPallet ? 'nullable' : $arrayOfSizeRule;

        // dd($isPostRequest);
        return [
            'rego_number' => 'required|string',
            'driver_id' => ['required', $this->roleRule('driver')],
            'customer_id' => ['required', 'array', $this->roleRule('customer')],
            'route_id' => ['nullable', 'exists:routes,id'],
            'material' => $arrayOfSizeRule,
            'weighing_type' => 'required|in:bridge,pallet',
            'initial_truck_weight' => $weighingTypeIsPallet ? 'nullable' : 'required|numeric',
            'next_truck_weight' => $weightRule,
            'full_bin_weight' => $weighingTypeIsPallet ? $weightRule : 'nullable',
            'tare_bin' => $arrayOfSizeRule,
            'ticket_type' => 'required|in:direct, schedule',
            'note' => $arrayOfSizeRule,
            'in_time' => $dataTimeRule,
            'out_time' => $dataTimeRule,
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 422);
    }
}
