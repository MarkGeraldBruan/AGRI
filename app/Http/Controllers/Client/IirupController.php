<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use App\Exports\IirupExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class IirupController extends Controller
{
    public function index(Request $request)
    {
        // Get equipment data for IIRUP report (focusing on unserviceable property)
        $query = Equipment::query();

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('acquisition_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('acquisition_date', '<=', $request->date_to);
        }
        if ($request->filled('classification')) {
            $query->where('classification', 'like', '%' . $request->classification . '%');
        }

        // Focus on unserviceable equipment for IIRUP report
        $ppesItems = $query->where('condition', 'Unserviceable')
            ->orderBy('acquisition_date', 'desc')
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
                    // Disposal columns
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

        // Get unique classifications for filter dropdown
        $classifications = Equipment::whereNotNull('classification')
            ->distinct()
            ->pluck('classification')
            ->sort();

        $header = [
            'as_of' => $request->query('as_of') ? \Carbon\Carbon::parse($request->as_of)->format('F d, Y') : now()->format('F d, Y'),
            'entity_name' => $request->query('entity_name', ''),
            'fund_cluster' => $request->query('fund_cluster', ''),
            'accountable_person' => $request->query('accountable_person', ''),
            'position' => $request->query('position', ''),
            'office' => $request->query('office', ''),
            'assumption_date' => $request->query('assumption_date', ''),
        ];

        return view('client.report.iirup.index', compact('ppesItems', 'header', 'classifications'));
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new IirupExport($request), 'iirup_report_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPDF(Request $request)
    {
        // Reuse logic similar to index
        $query = Equipment::query();

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('acquisition_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('acquisition_date', '<=', $request->date_to);
        }
        if ($request->filled('classification')) {
            $query->where('classification', 'like', '%' . $request->classification . '%');
        }

        $ppesItems = $query->where('condition', 'Unserviceable')
            ->orderBy('acquisition_date', 'desc')
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

        $header = [
            'as_of' => $request->query('as_of') ? \Carbon\Carbon::parse($request->as_of)->format('F d, Y') : now()->format('F d, Y'),
            'entity_name' => $request->query('entity_name', ''),
            'fund_cluster' => $request->query('fund_cluster', ''),
            'accountable_person' => $request->query('accountable_person', ''),
            'position' => $request->query('position', ''),
            'office' => $request->query('office', ''),
            'assumption_date' => $request->query('assumption_date', ''),
        ];

        $data = [
            'ppesItems' => $ppesItems,
            'header' => $header,
            'serial_no' => now()->format('Y-m-d'),
            'date' => now()->format('F j, Y'),
        ];

        $pdf = Pdf::loadView('client.report.iirup.pdf', $data);
        return $pdf->download('iirup_report_' . now()->format('Y-m-d') . '.pdf');
    }
}
