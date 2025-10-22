<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Exports\PropertyCardExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PropertyCardController extends Controller
{
    /**
     * Display a listing of property cards
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

        return view('client.propertycard.index', compact('equipment'));
    }

    /**
     * Display the specified property card
     */
    public function show($id)
    {
        $equipment = Equipment::findOrFail($id);
        return view('client.propertycard.show', compact('equipment'));
    }

    /**
     * Export property card to Excel
     */
    public function exportExcel(Request $request)
    {
        // Get the same query as the index method
        $query = Equipment::query();

        // Apply the same filters as the index method
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        if ($request->has('condition') && $request->condition) {
            $query->byCondition($request->condition);
        }

        // Apply the same sorting as the index method
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // If condition filter is applied (Serviceable/Unserviceable), export all matching that condition
        // If no condition filter (All), export only what's currently visible on the page
        if ($request->has('condition') && $request->condition) {
            // Export all equipment matching the condition filter
            $equipment = $query->get();
        } else {
            // Export only current page when "All Conditions" is selected
            $perPage = 10;
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $equipment = $query->skip($offset)->take($perPage)->get();
        }

        $data = [];

        // Header row for equipment details
        $data[] = ['Property Number', 'Article', 'Classification', 'Description', 'Unit of Measurement', 'Unit Value', 'Condition', 'Acquisition Date', 'Location', 'Responsible Person', 'Remarks'];

        foreach ($equipment as $item) {
            $data[] = [
                $item->property_number,
                $item->article,
                $item->classification,
                $item->description,
                $item->unit_of_measurement,
                $item->unit_value,
                $item->condition,
                $item->acquisition_date ? $item->acquisition_date->format('F d, Y') : '',
                $item->location,
                $item->responsible_person,
                $item->remarks
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
