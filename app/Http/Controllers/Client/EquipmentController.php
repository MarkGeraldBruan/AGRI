<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

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
     * Export equipment to Excel/CSV
     */
    public function export()
    {
        // Check permission
        if (!auth()->user()->hasPermission('read')) {
            return redirect()->route('client.equipment.view')
                ->with('error', 'You do not have permission to export equipment.');
        }

        $equipment = Equipment::all();
        
        $filename = "equipment_" . date('Y-m-d_His') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($equipment) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Article',
                'Classification',
                'Description',
                'Property Number',
                'Unit of Measurement',
                'Unit Value',
                'Condition',
                'Acquisition Date',
                'Responsibility Center',
                'Responsible Person',
                'Remarks'
            ]);

            // Add data
            foreach ($equipment as $item) {
                fputcsv($file, [
                    $item->article,
                    $item->classification,
                    $item->description,
                    $item->property_number,
                    $item->unit_of_measurement,
                    $item->unit_value,
                    $item->condition,
                    $item->acquisition_date ? $item->acquisition_date->format('Y-m-d') : '',
                    $item->location,
                    $item->responsible_person,
                    $item->remarks
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}