<?php

namespace App\Http\Requests\API\Transaction;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'from_account_id' => [
                'required',
                'integer',
                'exists:accounts,id',
                function ($attribute, $value, $fail) {
                    $account = Account::find($value);
                    if ($account && $account->status !== 'active') {
                        $fail('The source account must be active.');
                    }
                },
            ],
            'to_account_id' => [
                'required',
                'integer',
                'exists:accounts,id',
                'different:from_account_id',
                function ($attribute, $value, $fail) {
                    $account = Account::find($value);
                    if ($account && $account->status !== 'active') {
                        $fail('The destination account must be active.');
                    }
                },
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    $fromAccountId = $this->input('from_account_id');
                    if ($fromAccountId) {
                        $account = Account::find($fromAccountId);
                        if ($account && $account->balance < $value) {
                            $fail('Insufficient funds in the source account.');
                        }
                    }
                },
            ],
        ];
    }
}