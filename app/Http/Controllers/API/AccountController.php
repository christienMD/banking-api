<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Account\StoreAccountRequest;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Transaction;
use App\Traits\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('throttle:60,1')->only(['index', 'show']);
        $this->middleware('throttle:10,1')->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of accounts for a customer.
     */
    public function index($customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        
        if (!$customer) {
            return $this->error('Customer not found', 404);
        }
        
        $accounts = $customer->accounts()->paginate(15);
        
        return $this->success($accounts, 'Accounts retrieved successfully');
    }

    /**
     * Store a newly created account.
     */
    public function store(StoreAccountRequest $request, $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        
        if (!$customer) {
            return $this->error('Customer not found', 404);
        }
        
        $validated = $request->validated();
        
        try {
            // Use a transaction to ensure data integrity
            $result = DB::transaction(function () use ($customer, $validated) {
                // Generate a unique account number
                $accountNumber = $this->generateAccountNumber();
                
                // Create the account with zero balance initially
                $account = $customer->accounts()->create([
                    'account_number' => $accountNumber,
                    'balance' => 0,
                    'type' => $validated['type'],
                    'status' => 'active',
                ]);
                
                // Create a deposit transaction for the initial amount
                $transaction = Transaction::create([
                    'to_account_id' => $account->id,
                    'amount' => $validated['initial_deposit'],
                    'type' => 'deposit',
                    'status' => 'completed',
                    'reference_number' => $this->generateReferenceNumber(),
                ]);
                
                // Update the account balance
                $account->balance = $validated['initial_deposit'];
                $account->save();
                
                return [
                    'account' => $account,
                    'transaction' => $transaction,
                ];
            });
            
            // Load the customer relationship
            $result['account']->load('customer');
            
            return $this->success($result, 'Account created successfully with initial deposit', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create account: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified account.
     */
    public function show($accountId): JsonResponse
    {
        $account = Account::find($accountId);
        
        if (!$account) {
            return $this->error('Account not found', 404);
        }
        
        // Load the customer relationship
        $account->load('customer');
        
        return $this->success($account, 'Account retrieved successfully');
    }

    /**
     * Get account balance.
     */
    public function getBalance($accountId): JsonResponse
    {
        $account = Account::find($accountId);
        
        if (!$account) {
            return $this->error('Account not found', 404);
        }
        
        return $this->success([
            'account_number' => $account->account_number,
            'balance' => $account->balance,
            'currency' => 'XAF', // Assuming USD as default currency
        ], 'Balance retrieved successfully');
    }

    /**
     * Generate a unique account number.
     */
    private function generateAccountNumber(): string
    {
        $prefix = 'ACC';
        $number = '';
        
        do {
            // Generate a random 10-digit number
            $number = $prefix . mt_rand(1000000000, 9999999999);
        } while (Account::where('account_number', $number)->exists());
        
        return $number;
    }

    /**
     * Generate a unique transaction reference number.
     */
    private function generateReferenceNumber(): string
    {
        $prefix = 'TXN';
        $randomString = Str::random(8);
        $timestamp = now()->format('YmdHis');
        
        return $prefix . $timestamp . $randomString;
    }
}