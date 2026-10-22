<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    //Get all customers
    public function index(): JsonResponse
    {
        $customers = Customer::all();

        return response()->json([
            'customers' => $customers,
        ]);
    }

    //Get a single customer
    public function customer(Customer $customer): JsonResponse
    {
        return response()->json([
            'customer' => $customer,
        ]);
    }
    
    //Create new customer
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
        ]);

        $existingCustomer = Customer::where('email', $data['email'])->first();
        if ($existingCustomer) {
            return response()->json(['message' => 'Customer with this email already exists'], 409);
        }

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return response()->json([
            'message' => 'Customer created successfully',
            'customer' => $customer,
        ], 201);
    }

    //Update customer
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('customers', 'email')->ignore($customer->id),
            ],
            'phone' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
        ]);

        $customer->update($data);

        return response()->json([
            'message' => 'Customer updated successfully',
            'customer' => $customer,
        ]);
    }

    //Delete customer
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully',
        ]);
    }

}
