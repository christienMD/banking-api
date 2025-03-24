<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Transaction\StoreTransactionRequest;
use App\Models\Account;
use App\Models\Transaction;
use App\Traits\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    use ApiResponder;

    /**
     * Transfer money between accounts.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            // Use a transaction to ensure data integrity
            $result = DB::transaction(function () use ($validated) {
                $fromAccount = Account::findOrFail($validated['from_account_id']);
                $toAccount = Account::findOrFail($validated['to_account_id']);
                $amount = $validated['amount'];
                
                // Check sufficient funds (additional check in case validation was bypassed)
                if ($fromAccount->balance < $amount) {
                    throw new \Exception('Insufficient funds in the source account.');
                }
                
                // Create the transaction record
                $transaction = Transaction::create([
                    'from_account_id' => $fromAccount->id,
                    'to_account_id' => $toAccount->id,
                    'amount' => $amount,
                    'type' => 'transfer',
                    'status' => 'completed',
                    'reference_number' => $this->generateReferenceNumber(),
                ]);
                
                // Update account balances
                $fromAccount->balance -= $amount;
                $toAccount->balance += $amount;
                
                $fromAccount->save();
                $toAccount->save();
                
                // Load account relationships
                $transaction->load(['fromAccount', 'toAccount']);
                
                return $transaction;
            });
            
            return $this->success($result, 'Transfer completed successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Transfer failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get transaction history for an account.
     */
    public function index($accountId): JsonResponse
    {
        $account = Account::find($accountId);
        
        if (!$account) {
            return $this->error('Account not found', 404);
        }
        
        // Get both incoming and outgoing transactions
        $transactions = Transaction::where('from_account_id', $account->id)
            ->orWhere('to_account_id', $account->id)
            ->latest()
            ->paginate(15);
        
        return $this->success($transactions, 'Transaction history retrieved successfully');
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