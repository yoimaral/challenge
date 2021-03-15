<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $isPSE = false;
        if (request()->get('payment_method_id') === '2') {
            $isPSE = true;
        }
        return [
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['required', 'numeric', 'min:5000', 'max:1000000'],
            'description' => ['required', 'min:5', 'max:1000'],
            'bank_code' => [
                $isPSE ? 'gt:0' : 'nullable',
            ],
        ];
    }
}
