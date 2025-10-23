<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EquipmentController extends Controller
{
    /**
     * Display a listing of equipment
     */
    public function index(Request $request)
    {
        $query = Equipment::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by condition
        if ($request->has('condition') && $request->condition) {
            $query->byCondition($request->condition);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $equipment = $query->paginate(10);

        // Get unique values for filters
        $conditions = ['Serviceable', 'Unserviceable'];

        return view('client.equipment.index', compact('equipment', 'conditions'));
    }

    /**
     * Show the form for creating new equipment
     */
    public function create()
    {
        // Check permission
        if (!auth()->user()->hasPermission('create')) {
            return redirect()->route('client.equipment.index')
                ->with('error', 'You do not have permission to create equipment.');
        }

        return view('client.equipment.create');
    }

    /**
     * Store newly created equipment
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('create')) {
            return redirect()->route('client.equipment.index')
                ->with('error', 'You do not have permission to create equipment.');
        }

        $validated = $request->validate([
            'property_number' => 'required|string|max:255|unique:equipment,property_number',
            'article' => 'required|string|max:255',
            'classification' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit_of_measurement' => 'required|string|max:50',
            'unit_value' => 'required|numeric|min:0',
            'condition' => 'required|in:Serviceable,Unserviceable',
            'acquisition_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'responsible_person' => 'nullable|string|max:255',
            'remarks' => 'nullable|string'
        ]);

        Equipment::create($validated);

        return redirect()->route('client.equipment.index')
            ->with('success', 'Equipment added successfully!');
    }

    /**
     * Display the specified equipment
     */
    public function show($id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('read')) {
            return redirect()->route('client.equipment.index')
                ->with('error', 'You do not have permission to view equipment.');
        }

        $equipment = Equipment::findOrFail($id);
        return view('client.equipment.view
        ', compact('equipment'));
    }

    /**
     * Show the form for editing equipment
     */
    public function edit($id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('update')) {
            return redirect()->route('client.equipment.index')
                ->with('error', 'You do not have permission to edit equipment.');
        }

        $equipment = Equipment::findOrFail($id);

        return view('client.equipment.edit', compact('equipment'));
    }

    /**
     * Update the specified equipment
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('update')) {
            return redirect()->route('client.equipment.index')
                ->with('error', 'You do not have permission to update equipment.');
        }

        $equipment = Equipment::findOrFail($id);

        $validated = $request->validate([
            'property_number' => 'required|string|max:255|unique:equipment,property_number,' . $id,
            'article' => 'required|string|max:255',
            'classification' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit_of_measurement' => 'required|string|max:50',
            'unit_value' => 'required|numeric|min:0',
            'condition' => 'required|in:Serviceable,Unserviceable',
            'acquisition_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'responsible_person' => 'nullable|string|max:255',
            'remarks' => 'nullable|string'
        ]);

        $equipment->update($validated);

        return redirect()->route('client.equipment.index')
            ->with('success', 'Equipment updated successfully!');
    }

    /**
     * Remove the specified equipment
     */
    public function destroy($id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('delete')) {
            return redirect()->route('client.equipment.index')
                ->with('error', 'You do not have permission to delete equipment.');
        }

        $equipment = Equipment::findOrFail($id);
        $equipment->delete();

        return redirect()->route('client.equipment.index')
            ->with('success', 'Equipment deleted successfully!');
    }

    /**
     * Get unique classifications for autocomplete
     */
    public function getClassifications()
    {
        $classifications = Equipment::whereNotNull('classification')
            ->distinct()
            ->pluck('classification')
            ->filter()
            ->values();

        return response()->json($classifications);
    }

    /**
     * Export equipment to Excel
     */
    public function export(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('read')) {
            return redirect()->route('client.equipment.index')
                ->with('error', 'You do not have permission to export equipment.');
        }

        // Get the same query as the index method
        $query = Equipment::query();

        // Apply the same filters as the index method
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        if ($request->has('condition') && !empty($request->condition)) {
            $query->byCondition($request->condition);
        }

        // Apply the same sorting as the index method
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Export only current page data (progressive export)
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $equipment = $query->skip($offset)->take($perPage)->get();

        $data = [];

        // Header row for equipment details
        $data[] = ['Property Number', 'Article', 'Classification', 'Description', 'Unit of Measurement', 'Unit Value', 'Condition', 'Acquisition Date', 'Location', 'Responsible Person', 'Remarks'];

        // Add equipment data
        foreach ($equipment as $item) {
            $data[] = [
                $item->property_number,
                $item->article,
                $item->classification ?: 'N/A',
                $item->description ?: 'N/A',
                $item->unit_of_measurement,
                $item->unit_value,
                $item->condition,
                $item->acquisition_date ? $item->acquisition_date->format('F d, Y') : 'N/A',
                $item->location ?: 'N/A',
                $item->responsible_person ?: 'N/A',
                $item->remarks ?: 'N/A'
            ];
        }

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithEvents {
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
                    \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getColumnDimension('A')->setWidth(15); // Property Number
                        $sheet->getColumnDimension('B')->setWidth(20); // Article
                        $sheet->getColumnDimension('C')->setWidth(15); // Classification
                        $sheet->getColumnDimension('D')->setWidth(30); // Description
                        $sheet->getColumnDimension('E')->setWidth(15); // Unit of Measurement
                        $sheet->getColumnDimension('F')->setWidth(12); // Unit Value
                        $sheet->getColumnDimension('G')->setWidth(12); // Condition
                        $sheet->getColumnDimension('H')->setWidth(15); // Acquisition Date
                        $sheet->getColumnDimension('I')->setWidth(20); // Location
                        $sheet->getColumnDimension('J')->setWidth(20); // Responsible Person
                        $sheet->getColumnDimension('K')->setWidth(30); // Remarks
                    }
                ];
            }
        }, 'property_cards.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
