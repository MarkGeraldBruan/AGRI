<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Supplies;
use App\Models\StockMovement;
use App\Exports\StockCardExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class StockCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Handle export request
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportAllStockCards();
        }

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

    /**
     * Export stock card to Excel
     */
    public function exportExcel($id)
    {
        $supply = Supplies::findOrFail($id);

        // Get the same paginated movements that are displayed on the page
        $movements = StockMovement::forSupply($id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Excel::download(new StockCardExport($id, $supply, $movements), 'stock_card_' . $supply->name . '.xlsx');
    }

    /**
     * Export all stock cards to Excel
     */
    public function exportAllStockCards(Request $request)
    {
        $query = Supplies::query();

        // Apply the same filters as the index method
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        if ($request->has('low_stock') && $request->low_stock == '1') {
            $query->lowStock();
        }

        // Apply the same sorting as the index method
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Export only what's currently visible on the page (progressive export like stock card)
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $supplies = $query->skip($offset)->take($perPage)->get();

        $data = [];

        // Header row for supply details
        $data[] = ['ID', 'Name', 'Description', 'Quantity', 'Unit Price', 'Total Value', 'Category', 'Supplier', 'Purchase Date', 'Minimum Stock', 'Notes'];

        foreach ($supplies as $supply) {
            $data[] = [
                $supply->id,
                $supply->name,
                $supply->description ?: 'N/A',
                $supply->quantity,
                '₱' . number_format($supply->unit_price, 2),
                '₱' . number_format($supply->quantity * $supply->unit_price, 2),
                $supply->category ?: 'N/A',
                $supply->supplier ?: 'N/A',
                $supply->purchase_date ? $supply->purchase_date->format('F d, Y') : 'N/A',
                $supply->minimum_stock,
                $supply->notes ?: 'N/A'
            ];
        }

        return Excel::download(new class($data) implements FromArray, WithEvents {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getColumnDimension('A')->setWidth(8);  // ID
                        $sheet->getColumnDimension('B')->setWidth(25); // Name
                        $sheet->getColumnDimension('C')->setWidth(30); // Description
                        $sheet->getColumnDimension('D')->setWidth(10); // Quantity
                        $sheet->getColumnDimension('E')->setWidth(12); // Unit Price
                        $sheet->getColumnDimension('F')->setWidth(12); // Total Value
                        $sheet->getColumnDimension('G')->setWidth(15); // Category
                        $sheet->getColumnDimension('H')->setWidth(20); // Supplier
                        $sheet->getColumnDimension('I')->setWidth(15); // Purchase Date
                        $sheet->getColumnDimension('J')->setWidth(12); // Minimum Stock
                        $sheet->getColumnDimension('K')->setWidth(30); // Notes
                    }
                ];
            }
        }, 'all_stock_cards.xlsx');
    }
}
