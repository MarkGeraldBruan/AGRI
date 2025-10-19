<?php

namespace App\Exports;

use App\Models\Supplies;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RpciExport implements FromArray, WithEvents
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $query = Supplies::query();

        // Apply same filters as controller
        if ($this->request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }
        if ($this->request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }
        if ($this->request->filled('department')) {
            $query->where('category', 'like', '%' . $this->request->department . '%')
                  ->orWhere('supplier', 'like', '%' . $this->request->department . '%');
        }
        if ($this->request->filled('status')) {
            if ($this->request->status === 'issued') {
                $query->where('quantity', '>', 0);
            } elseif ($this->request->status === 'pending') {
                $query->where('quantity', '=', 0);
            }
        }

        $supplies = $query->orderBy('created_at', 'desc')->get();

        $rpciItems = $supplies->map(function ($supply) {
            return (object) [
                'issue_no' => 'Rpci-' . now()->format('Y') . '-' . str_pad($supply->id, 4, '0', STR_PAD_LEFT),
                'responsibility_center' => $supply->category ?? '---',
                'stock_no' => '---',
                'item' => $supply->name,
                'unit' => $supply->unit,
                'quantity_issued' => $supply->quantity,
                'unit_cost' => $supply->unit_price,
                'amount' => $supply->unit_price * $supply->quantity,
            ];
        });

        // Build header values from request
        $entityName = $this->request->query('entity_name', 'Agricultural Training Institute-RTC I');
        $accountablePerson = $this->request->query('accountable_person', 'Franklin A. Salcedo');
        $position = $this->request->query('position', 'Supply and Property Officer');
        $office = $this->request->query('office', 'ATI-RTC I');
        $fundCluster = $this->request->query('fund_cluster', '01');
        $asOfDate = $this->request->query('as_of');
        if ($asOfDate) {
            $formattedDate = \Carbon\Carbon::parse($asOfDate)->format('F d, Y');
        } else {
            $formattedDate = now()->format('F d, Y');
        }
        $asOfMonth = $this->request->query('as_of') ? \Carbon\Carbon::parse($this->request->query('as_of'))->format('F Y') : now()->format('F Y');

        $data = [];

        // Header rows
        $data[] = ['REPORT OF SUPPLIES AND MATERIALS ISSUED', '', '', '', '', '', '', '']; // Title
        $data[] = ['For the Month of ' . $asOfMonth, '', '', '', '', '', '', ''];
        $data[] = ['Fund Cluster: ' . $fundCluster, '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '']; // Empty row
        $data[] = ['Entity Name: ' . $entityName, '', '', '', '', '', '', ''];
        $data[] = [$accountablePerson, '', '', '', '', '', '', ''];
        $data[] = ['(Name of Accountable Officer)', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '']; // Empty row
        $data[] = [$position, '', '', '', '', '', '', ''];
        $data[] = ['(Designation)', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '']; // Empty row
        $data[] = [$office, '', '', '', '', '', '', ''];
        $data[] = ['(Station)', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '']; // Empty row
        $data[] = [
            'RIS No.',
            'Responsibility Center Code',
            'Stock No.',
            'Item',
            'Unit',
            'Quantity Issued',
            'Unit Cost',
            'Amount',
        ];

        // Table rows
        foreach ($rpciItems as $item) {
            $data[] = [
                $item->issue_no,
                $item->responsibility_center,
                $item->stock_no,
                $item->item,
                $item->unit,
                // ensure quantity is numeric
                (float) $item->quantity_issued,
                // ensure numeric unit cost and amount
                (float) $item->unit_cost,
                (float) $item->amount,
            ];
        }

        // Recapitulation separators and blocks
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['Recapitulation:', '', '', '', '', '', '', ''];
        $data[] = ['Stock No.', 'Quantity', '', '', '', '', '', ''];

        $recapLeft = $rpciItems->groupBy('stock_no')->map(function ($group) {
            return [
                'stock_no' => $group->first()->stock_no ?? '---',
                'quantity' => $group->sum('quantity_issued'),
            ];
        })->values();

        foreach ($recapLeft as $r) {
            $data[] = [$r['stock_no'], (float) $r['quantity'], '', '', '', '', '', ''];
        }

        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['Recapitulation:', '', '', '', '', '', '', ''];
        $data[] = ['Unit Cost', 'Total Cost', 'UACS Object Code', '', '', '', '', ''];

        $recapRight = [
            'unit_cost' => $rpciItems->sum('unit_cost'),
            'total_cost' => $rpciItems->sum('amount'),
            'uacs_code' => '---',
        ];

        $data[] = [
            (float) $recapRight['unit_cost'],
            (float) $recapRight['total_cost'],
            $recapRight['uacs_code'],
            '', '', '', '', ''
        ];

        // Signature / prepared by block (leave blanks that will be filled in PDF or Excel)
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['Prepared by:', '', '', '', '', '', 'Checked by:', ''];
        $data[] = [$accountablePerson, '', '', '', '', '', '', $position];
        $data[] = [$office, '', '', '', '', '', '', 'Assumption Date: ' . ($this->request->query('assumption_date', ''))];

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Appendix right
                $sheet->mergeCells('H1:H1');
                $sheet->getStyle('H1')->getFont()->setItalic(true)->setSize(14);
                $sheet->getStyle('H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Title
                $sheet->mergeCells('A3:H3');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Header styling for entity info
                $sheet->getStyle('A5:H5')->getFont()->setBold(true); // Entity Name
                $sheet->getStyle('A6:H6')->getFont()->setBold(true); // Accountable Person
                $sheet->getStyle('A9:H9')->getFont()->setBold(true); // Position
                $sheet->getStyle('A12:H12')->getFont()->setBold(true); // Office
                $sheet->getStyle('A15:H15')->getFont()->setBold(true); // Fund Cluster

                // Table header style (row 17)
                $sheet->getStyle('A17:H17')->getFont()->setBold(true);
                $sheet->getStyle('A17:H17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A17:H17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                // Extra: ensure Unit Cost cell is centered and bold
                $sheet->getStyle('G17')->getFont()->setBold(true);
                $sheet->getStyle('G17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(40);
                $sheet->getColumnDimension('E')->setWidth(8);
                $sheet->getColumnDimension('F')->setWidth(14);
                $sheet->getColumnDimension('G')->setWidth(14);
                $sheet->getColumnDimension('H')->setWidth(16);

                // Apply number format for currency columns (G and H) and center alignment for quantities
                $highestRow = $sheet->getHighestRow();
                // Data rows start at row 18 after header row 17
                $sheet->getStyle("G18:G{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("H18:H{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("F18:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set borders for the data range (from header row 17 to last row)
                $sheet->getStyle("A17:H{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        ];
    }
}
