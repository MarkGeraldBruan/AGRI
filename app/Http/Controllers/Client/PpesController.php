<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PpesExport;

class PpesController extends Controller
{
    public function index(Request $request)
    {
        // Get equipment data for PPES report (focusing on unserviceable property)
        $query = Equipment::query();

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('acquisition_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('acquisition_date', '<=', $request->date_to);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('classification')) {
            $query->where('classification', 'like', '%' . $request->classification . '%');
        }

        // Get all equipment for PPES report
        $ppesItems = $query->orderBy('acquisition_date', 'desc')
            ->get()
            ->map(function ($equipment) {
                return (object) [
                    'date_acquired' => $equipment->acquisition_date ? $equipment->acquisition_date->format('m/d/Y') : '---',
                    'particulars_articles' => $equipment->article . ' - ' . $equipment->description,
                    'property_no' => $equipment->property_number ?: '---',
                    'qty' => 1, // Usually 1 for equipment items
                    'unit_cost' => $equipment->unit_value,
                    'total_cost' => $equipment->unit_value,
                    'accumulated_depreciation' => 0, // To be calculated based on business rules
                    'accumulated_impairment_losses' => 0, // To be calculated based on business rules
                    'carrying_amount' => $equipment->unit_value, // Total cost minus depreciation and impairment
                    'remarks' => $equipment->remarks ?: '---',
                    // Disposal columns
                    'sale' => '',
                    'transfer' => '',
                    'destruction' => '',
                    'others' => '',
                    'total_disposal' => '',
                    'appraised_value' => '',
                    // Record of Sales
                    'or_no' => '',
                    'amount' => '',
                ];
            });

        // Get all unique classifications for filter dropdown
        $classifications = Equipment::whereNotNull('classification')
            ->where('classification', '!=', '')
            ->distinct()
            ->pluck('classification')
            ->sort()
            ->values();

        // normalize 'as_of' to always be a human friendly formatted date (e.g. "October 18, 2025")
        $asOfRaw = $request->input('as_of');
        $header = [
            'as_of' => $asOfRaw ? \Carbon\Carbon::parse($asOfRaw)->format('F d, Y') : '',
            'entity_name' => $request->input('entity_name') ?: '',
            'fund_cluster' => $request->input('fund_cluster') ?: '',
            'accountable_person' => $request->input('accountable_person') ?: '',
            'position' => $request->input('position') ?: '',
            'office' => $request->input('office') ?: '',
            'assumption_date' => $request->input('assumption_date') ?: '',
        ];

        return view('client.report.ppes.index', [
            'ppesItems' => $ppesItems,
            'classifications' => $classifications,
            'filters' => $request->all(),
            'header' => $header,
        ]);
    }

    public function exportPDF(Request $request)
    {
        $query = Equipment::query();

        // Apply same filters
        if ($request->filled('date_from')) {
            $query->whereDate('acquisition_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('acquisition_date', '<=', $request->date_to);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('classification')) {
            $query->where('classification', 'like', '%' . $request->classification . '%');
        }

        $ppesItems = $query->orderBy('acquisition_date', 'desc')
            ->get()
            ->map(function ($equipment) {
                return (object) [
                    'date_acquired' => $equipment->acquisition_date ? $equipment->acquisition_date->format('m/d/Y') : '---',
                    'particulars_articles' => $equipment->article . ' - ' . $equipment->description,
                    'property_no' => $equipment->property_number ?: '---',
                    'qty' => 1,
                    'unit_cost' => $equipment->unit_value,
                    'total_cost' => $equipment->unit_value,
                    'accumulated_depreciation' => 0,
                    'accumulated_impairment_losses' => 0,
                    'carrying_amount' => $equipment->unit_value,
                    'remarks' => $equipment->remarks ?: '---',
                    'sale' => '',
                    'transfer' => '',
                    'destruction' => '',
                    'others' => '',
                    'total_disposal' => '',
                    'appraised_value' => '',
                    'or_no' => '',
                    'amount' => '',
                ];
            });

        $asOfRaw = $request->input('as_of');
        $header = [
            'as_of' => $asOfRaw ? \Carbon\Carbon::parse($asOfRaw)->format('F d, Y') : '',
            'entity_name' => $request->input('entity_name') ?: '',
            'fund_cluster' => $request->input('fund_cluster') ?: '',
            'accountable_person' => $request->input('accountable_person') ?: '',
            'position' => $request->input('position') ?: '',
            'office' => $request->input('office') ?: '',
            'assumption_date' => $request->input('assumption_date') ?: '',
        ];

        $pdf = Pdf::loadView('client.report.ppes.pdf', [
            'ppesItems' => $ppesItems,
            'filters' => $request->all(),
            'header' => $header,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('PPES_Report_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new PpesExport($request), 'PPES_Report_' . now()->format('Y-m-d') . '.xlsx');
    }
}
