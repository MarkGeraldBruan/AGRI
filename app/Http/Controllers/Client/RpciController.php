<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Supplies;
use App\Exports\RpciExport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class RpciController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplies::query();

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('department')) {
            $query->where('category', 'like', '%' . $request->department . '%')
                  ->orWhere('supplier', 'like', '%' . $request->department . '%');
        }
        if ($request->filled('status')) {
            if ($request->status === 'issued') {
                $query->where('quantity', '>', 0);
            } elseif ($request->status === 'pending') {
                $query->where('quantity', '=', 0);
            }
        }

        $supplies = $query->orderBy('created_at', 'desc')->get();

        $rpciItems = $supplies->map(function ($supply) {
            return (object) [
                'name' => '---', // Placeholder for article
                'description' => $supply->name,
                'stock_number' => '---', // Placeholder for stock number
                'unit_of_measure' => $supply->unit,
                'unit_value' => $supply->unit_price,
                'balance_per_card' => $supply->quantity,
                'on_hand_per_count' => $supply->quantity, // Assuming same as balance for now
                'shortage_overage_quantity' => 0, // Placeholder
                'shortage_overage_value' => 0, // Placeholder
                'remarks' => '---', // Placeholder
            ];
        });

        $asOfRaw = $request->query('as_of');
        $header = [
            'as_of' => $asOfRaw ? Carbon::parse($asOfRaw)->format('F d, Y') : now()->format('F d, Y'),
            'entity_name' => $request->query('entity_name', ''),
            'fund_cluster' => $request->query('fund_cluster', ''),
            'accountable_person' => $request->query('accountable_person', ''),
            'position' => $request->query('position', ''),
            'office' => $request->query('office', ''),
            'assumption_date' => $request->query('assumption_date', ''),
            'serial_no' => $request->query('serial_no', now()->format('Y-m-d')),
            'date' => $request->query('date', now()->format('F d, Y')),
            'accountability_text' => 'For which ' . ($request->query('accountable_person', '________________')) . ', ' . ($request->query('position', '________________')) . ', ' . ($request->query('office', '________________')) . ' is accountable, having assumed such accountability on ' . ($request->query('assumption_date', '________________')) . '.',
        ];

        return view('client.report.rpci.index', [
            'rpciItems' => $rpciItems,
            'header' => $header,
        ]);
    }

    public function exportPDF(Request $request)
    {
        $query = Supplies::query();

        // Apply same filters as index
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('department')) {
            $query->where('category', 'like', '%' . $request->department . '%')
                  ->orWhere('supplier', 'like', '%' . $request->department . '%');
        }
        if ($request->filled('status')) {
            if ($request->status === 'issued') {
                $query->where('quantity', '>', 0);
            } elseif ($request->status === 'pending') {
                $query->where('quantity', '=', 0);
            }
        }

        $supplies = $query->orderBy('created_at', 'desc')->get();

        $rpciItems = $supplies->map(function ($supply) {
            return (object) [
                'name' => '---',
                'description' => $supply->name,
                'stock_number' => '---',
                'unit_of_measure' => $supply->unit,
                'unit_value' => $supply->unit_price,
                'balance_per_card' => $supply->quantity,
                'on_hand_per_count' => $supply->quantity,
                'shortage_overage_quantity' => 0,
                'shortage_overage_value' => 0,
                'remarks' => '---',
            ];
        });

        $asOfRaw = $request->query('as_of');
        $header = [
            'as_of' => $asOfRaw ? Carbon::parse($asOfRaw)->format('F d, Y') : now()->format('F d, Y'),
            'entity_name' => $request->query('entity_name', ''),
            'fund_cluster' => $request->query('fund_cluster', ''),
            'accountable_person' => $request->query('accountable_person', ''),
            'position' => $request->query('position', ''),
            'office' => $request->query('office', ''),
            'assumption_date' => $request->query('assumption_date', ''),
            'serial_no' => $request->query('serial_no', now()->format('Y-m-d')),
            'date' => $request->query('date', now()->format('F d, Y')),
            'accountability_text' => 'For which ' . ($request->query('accountable_person', '________________')) . ', ' . ($request->query('position', '________________')) . ', ' . ($request->query('office', '________________')) . ' is accountable, having assumed such accountability on ' . ($request->query('assumption_date', '________________')) . '.',
        ];

        $data = [
            'rpciItems' => $rpciItems,
            'header' => $header,
        ];

        $pdf = Pdf::loadView('client.report.rpci.pdf', $data);
        return $pdf->download('rpci_report_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new RpciExport($request), 'rpci_report_' . now()->format('Y-m-d') . '.xlsx');
    }
}
