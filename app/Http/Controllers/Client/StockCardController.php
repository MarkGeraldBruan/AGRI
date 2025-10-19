<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Supplies;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Supplies::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Filter by low stock
        if ($request->has('low_stock') && $request->low_stock == '1') {
            $query->lowStock();
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $supplies = $query->paginate(15);
        
        // Get unique categories for filter dropdown
        $categories = Supplies::distinct()->pluck('category')->filter();

        return view('client.stockcard.index', compact('supplies', 'categories'));
    }

    /**
     * Show stock card transactions for a specific item
     */
    public function show($id)
    {
        $supply = Supplies::findOrFail($id);
        
        // Get paginated stock movements for this supply, ordered by date
        $movements = StockMovement::forSupply($id)
            ->orderBy('created_at', 'desc') // Consider showing newest first
            ->paginate(20); // Add pagination with 20 items per page

        return view('client.stockcard.show', compact('supply', 'movements'));
    }

    /**
     * Show stock in form
     */
    public function stockIn()
    {
        $supplies = Supplies::all();
        return view('client.stockcard.stock-in', compact('supplies'));
    }

    /**
     * Process stock in
     */
    public function processStockIn(Request $request)
    {
        $validated = $request->validate([
            'supply_id' => 'required|exists:supplies,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        $supply = Supplies::findOrFail($validated['supply_id']);
        $oldQuantity = $supply->quantity;
        $newQuantity = $oldQuantity + $validated['quantity'];
        
        // Update supply quantity
        $supply->increment('quantity', $validated['quantity']);

        // Create stock movement record
        StockMovement::create([
            'supply_id' => $supply->id,
            'type' => 'in',
            'quantity' => $validated['quantity'],
            'balance_after' => $newQuantity,
            'reference' => StockMovement::generateReference(),
            'notes' => $validated['notes'],
            'office_description' => 'Stock In - ' . ($validated['notes'] ?: 'Inventory Replenishment')
        ]);

        return redirect()->route('client.stockcard.index')->with('success', 'Stock added successfully!');
    }

    /**
     * Show stock out form
     */
    public function stockOut()
    {
        $supplies = Supplies::where('quantity', '>', 0)->get();
        return view('client.stockcard.stock-out', compact('supplies'));
    }

    /**
     * Process stock out
     */
    public function processStockOut(Request $request)
    {
        $validated = $request->validate([
            'supply_id' => 'required|exists:supplies,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        $supply = Supplies::findOrFail($validated['supply_id']);
        
        if ($supply->quantity < $validated['quantity']) {
            return redirect()->back()->withErrors(['quantity' => 'Not enough stock available.']);
        }

        $oldQuantity = $supply->quantity;
        $newQuantity = $oldQuantity - $validated['quantity'];
        
        // Update supply quantity
        $supply->decrement('quantity', $validated['quantity']);

        // Create stock movement record
        StockMovement::create([
            'supply_id' => $supply->id,
            'type' => 'out',
            'quantity' => $validated['quantity'],
            'balance_after' => $newQuantity,
            'reference' => StockMovement::generateReference(),
            'notes' => $validated['notes'],
            'office_description' => 'CDMS - ' . ($validated['notes'] ?: 'For office use')
        ]);

        return redirect()->route('client.stockcard.index')->with('success', 'Stock removed successfully!');
    }

    public function create()
    {
        // Not needed for stock card
    }

    public function store(Request $request)
    {
        // Not needed for stock card
    }

    public function edit($id)
    {
        // Not needed for stock card
    }

    public function update(Request $request, $id)
    {
        // Not needed for stock card
    }

    public function destroy($id)
    {
        // Not needed for stock card
    }
}