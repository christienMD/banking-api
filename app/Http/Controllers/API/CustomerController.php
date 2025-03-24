<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Customer\StoreCustomerRequest;
use App\Http\Requests\API\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Traits\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('throttle:60,1')->only(['index', 'show']);
        $this->middleware('throttle:10,1')->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();
        
        // Apply filters if provided
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        
        // Apply pagination (default 15 per page)
        $perPage = $request->per_page ?? 12;
        $customers = $query->paginate($perPage);
        
        return $this->success($customers, 'Customers retrieved successfully');
    }
    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return $this->success($customer, 'Customer created successfully', 201);
    }

    /**
     * Display the specified customer.
     */
    public function show($id): JsonResponse
    {
        // Find the customer by ID
        $customer = Customer::find($id);
        
        if (!$customer) {
            return $this->error('Customer not found', 404);
        }
        
        // Load accounts relationship
        $customer->load('accounts');
        
        return $this->success($customer, 'Customer retrieved successfully');
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, $id): JsonResponse
    {
        // Find the customer by ID
        $customer = Customer::find($id);
        
        if (!$customer) {
            return $this->error('Customer not found', 404);
        }
        
        // Update the customer with validated data
        $customer->update($request->validated());
        
        // Refresh the model to get updated data
        $customer->refresh();
        
        return $this->success($customer, 'Customer updated successfully');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        // Check if customer has accounts before deleting
        if ($customer->accounts()->count() > 0) {
            return $this->error('Cannot delete customer with existing accounts', 409);
        }

        $customer->delete();

        return $this->success(null, 'Customer deleted successfully');
    }
}