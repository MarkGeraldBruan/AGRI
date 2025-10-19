<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Supplies;
use App\Exports\RsmiExport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class RsmiController extends Controller
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
            // Assuming department filter on category or supplier, since no direct department field
            $query->where('category', 'like', '%' . $request->department . '%')
                  ->orWhere('supplier', 'like', '%' . $request->department . '%');
        }
        if ($request->filled('status')) {
            if ($request->status === 'issued') {
                // All supplies are considered issued for RSMI
                $query->where('quantity', '>', 0);
            } elseif ($request->status === 'pending') {
                $query->where('quantity', '=', 0);
            }
        }

        $supplies = $query->orderBy('created_at', 'desc')->get();

        $rsmiItems = $supplies->map(function ($supply) {
            return (object) [
                'issue_no' => 'RSMI-' . now()->format('Y') . '-' . str_pad($supply->id, 4, '0', STR_PAD_LEFT),
                'responsibility_center' => $supply->category ?? '---',
                'stock_no' => '---',
                'item' => $supply->name,
                'unit' => $supply->unit,
                'quantity_issued' => $supply->quantity,
                'unit_cost' => $supply->unit_price,
                'amount' => $supply->unit_price * $supply->quantity,
            ];
        });

        $recapLeft = $rsmiItems->groupBy('stock_no')->map(function ($group) {
            return [
                'stock_no' => $group->first()->stock_no ?? '---',
                'quantity' => $group->sum('quantity_issued'),
            ];
        })->values();

        $recapRight = [
            'unit_cost' => $rsmiItems->sum('unit_cost'),
            'total_cost' => $rsmiItems->sum('amount'),
            'uacs_code' => '---', // Placeholder, can be updated if UACS code is available
        ];

        $asOfRaw = $request->query('as_of');
        $header = [
            'as_of' => $asOfRaw ? Carbon::parse($asOfRaw)->format('F Y') : now()->format('F Y'),
            'entity_name' => $request->query('entity_name', 'ATI-RTC I'),
            'fund_cluster' => $request->query('fund_cluster', ''),
            'accountable_person' => $request->query('accountable_person', ''),
            'position' => $request->query('position', ''),
            'office' => $request->query('office', ''),
            'assumption_date' => $request->query('assumption_date', ''),
            'serial_no' => $request->query('serial_no', now()->format('Y-m-d')),
            'date' => $request->query('date', now()->format('F d, Y')),
            'accountability_text' => 'For which ' . ($request->query('accountable_person', '________________')) . ', ' . ($request->query('position', '________________')) . ', ' . ($request->query('office', '________________')) . ' is accountable, having assumed such accountability on ' . ($request->query('assumption_date', '________________')) . '.',
        ];
        return view('client.report.rsmi.index', [
            'rsmiItems' => $rsmiItems,
            'recapLeft' => $recapLeft,
            'recapRight' => $recapRight,
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

        $rsmiItems = $supplies->map(function ($supply) {
            return (object) [
                'issue_no' => 'RSMI-' . now()->format('Y') . '-' . str_pad($supply->id, 4, '0', STR_PAD_LEFT),
                'responsibility_center' => $supply->category ?? '---',
                'stock_no' => '---',
                'item' => $supply->name,
                'unit' => $supply->unit,
                'quantity_issued' => $supply->quantity,
                'unit_cost' => $supply->unit_price,
                'amount' => $supply->unit_price * $supply->quantity,
            ];
        });

        $recapLeft = $rsmiItems->groupBy('stock_no')->map(function ($group) {
            return [
                'stock_no' => $group->first()->stock_no ?? '---',
                'quantity' => $group->sum('quantity_issued'),
            ];
        })->values();

        $recapRight = [
            'unit_cost' => $rsmiItems->sum('unit_cost'),
            'total_cost' => $rsmiItems->sum('amount'),
            'uacs_code' => '---', // Placeholder, can be updated if UACS code is available
        ];

        $asOfRaw = $request->query('as_of');
        $header = [
            'as_of' => $asOfRaw ? Carbon::parse($asOfRaw)->format('F Y') : now()->format('F Y'),
            'entity_name' => $request->query('entity_name', 'ATI-RTC I'),
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
            'rsmiItems' => $rsmiItems,
            'recapLeft' => $recapLeft,
            'recapRight' => $recapRight,
            'header' => $header,
        ];
        $pdf = Pdf::loadView('client.report.rsmi.pdf', $data);
        return $pdf->download('rsmi_report_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new RsmiExport($request), 'rsmi_report_' . now()->format('Y-m-d') . '.xlsx');
    }
}
