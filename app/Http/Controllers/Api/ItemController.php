<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    //Get all items
    public function index(): JsonResponse
    {
        $items = Item::with('category')->get();

        return response()->json([
            'items' => $items,
        ]);
    }

    //Get a single item
    public function item(Item $item): JsonResponse
    {
        $item->load('category');
        return response()->json([
            'item' => $item,
        ]);
    }

    //Create new item
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $item = Item::create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'quantity' => $data['quantity'] ?? 0,
        ]);

        return response()->json([
            'message' => 'Item created successfully',
            'item' => $item->load('category'),
        ], 201);
    }

    //edit item
    public function update(Request $request, Item $item): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'sometimes|required|integer|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $item->update($data);

        return response()->json([
            'message' => 'Item updated successfully',
            'item' => $item->load('category'),
        ]);
    }

    //Delete an item
    public function destroy(Item $item): JsonResponse{
        
        $item->delete();

        return response()->json([
            'message' => 'Item deleted successfully',
        ]);
    }
}
