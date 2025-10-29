<?php

namespace App\Exports;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class PpesExport implements FromArray, WithEvents, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $query = Equipment::query();

        // Apply same filters
        if ($this->request->filled('date_from')) {
            $query->whereDate('acquisition_date', '>=', $this->request->date_from);
        }
        if ($this->request->filled('date_to')) {
            $query->whereDate('acquisition_date', '<=', $this->request->date_to);
        }
        if ($this->request->filled('condition')) {
            $query->where('condition', $this->request->condition);
        }
        if ($this->request->filled('classification')) {
            $query->where('classification', 'like', '%' . $this->request->classification . '%');
        }

        $equipment = $query->orderBy('acquisition_date', 'desc')
            ->get();

        $ppesItems = $equipment->map(function ($equipment) {
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

        // Prepare data for Excel
        $data = [];

        // Build header values from request
        $entityName = $this->request->query('entity_name') ?: '';
        $accountablePerson = $this->request->query('accountable_person') ?: '';
        $position = $this->request->query('position') ?: '';
        $office = $this->request->query('office') ?: '';
        $fundCluster = $this->request->query('fund_cluster') ?: '';
        $asOfDate = $this->request->query('as_of');
        $formattedDate = $asOfDate ? \Carbon\Carbon::parse($asOfDate)->format('F d, Y') : '';
        $assumptionDate = $this->request->query('assumption_date') ?: '';

        $data[] = ['REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = ['(ATI-RTC I)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = [$formattedDate ? 'As of ' . $formattedDate : '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = [''];
        // Header grid layout matching screen view exactly (4 columns)
        $data[] = ['', 'Entity Name:', $entityName, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '(Name)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', 'Accountable Officer:', $accountablePerson, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '(Name)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', 'Position:', $position, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '(Designation)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', 'Office:', $office, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '(Station)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', 'Fund Cluster:', $fundCluster, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = [''];

        // Accountability text
        $accountabilityText = 'For which ' . ($accountablePerson ?: '___') . ', ' . ($position ?: '___') . ', ' . ($office ?: '___') . ' is accountable, having assumed such accountability on ' . ($assumptionDate ? \Carbon\Carbon::parse($assumptionDate)->format('F d, Y') : '__________') . '.';
        $data[] = [$accountabilityText, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $data[] = [''];

        // Add table headers
        $data[] = ['INVENTORY', '', '', '', '', '', '', '', '', 'INSPECTION and DISPOSAL', '', '', '', '', 'Appraised Value', 'RECORD OF SALES', ''];
        $data[] = ['Date Acquired', 'Particulars/ Articles', 'Property No.', 'Qty', 'Unit Cost', 'Total Cost', 'Accumulated Depreciation', 'Accumulated Impairment Losses', 'Carrying Amount', 'Remarks', 'DISPOSAL', '', '', '', '', 'OR No.', 'Amount', ''];
        $data[] = ['', '', '', '', '', '', '', '', '', '', 'Sale', 'Transfer', 'Destruction', 'Others (Specify)', 'Total', '', '', ''];
        $data[] = ['(1)', '(2)', '(3)', '(4)', '(5)', '(6)', '(7)', '(8)', '(9)', '(10)', '(11)', '(12)', '(13)', '(14)', '(15)', '(16)', '(17)', '(18)'];

        // Add empty row after headers
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Add table data
        foreach ($ppesItems as $item) {
                $data[] = [
                    $item->date_acquired,
                    $item->particulars_articles,
                    $item->property_no,
                    $item->qty,
                    number_format((float) ($item->unit_cost ?? 0), 2),
                    number_format((float) ($item->total_cost ?? 0), 2),
                    number_format((float) ($item->accumulated_depreciation ?? 0), 2),
                    number_format((float) ($item->accumulated_impairment_losses ?? 0), 2),
                    number_format((float) ($item->carrying_amount ?? 0), 2),
                    $item->remarks,
                    $item->sale,
                    $item->transfer,
                    $item->destruction,
                    $item->others,
                    $item->total_disposal,
                    number_format((float) ($item->appraised_value ?? 0), 2),
                    $item->or_no,
                    number_format((float) ($item->amount ?? 0), 2),
                    '',
                ];
        }

        // Add footer
        $data[] = [''];
        $data[] = ['PANGASINAN'];

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge title rows
                $sheet->mergeCells('A1:R1'); // Title spans all columns

                // Merge header info area - updated for new grid layout (4 columns)
                $sheet->mergeCells('C5:R5'); // Entity Name value spans multiple columns
                $sheet->mergeCells('C6:R6'); // (Name) label spans multiple columns
                $sheet->mergeCells('C7:R7'); // Accountable Officer value spans multiple columns
                $sheet->mergeCells('C8:R8'); // (Name) label spans multiple columns
                $sheet->mergeCells('C9:R9'); // Position value spans multiple columns
                $sheet->mergeCells('C10:R10'); // (Designation) label spans multiple columns
                $sheet->mergeCells('C11:R11'); // Office value spans multiple columns
                $sheet->mergeCells('C12:R12'); // (Station) label spans multiple columns
                $sheet->mergeCells('C13:R13'); // Fund Cluster value spans multiple columns

                // Style title
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);

                // Style header labels
                $sheet->getStyle('B5:B13')->getFont()->setBold(true); // All header labels (Entity Name, Accountable Officer, Position, Office, Fund Cluster)

                // Add borders around header fields to create box effect (4 columns)
                $sheet->getStyle('A5:R6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM); // Entity Name box
                $sheet->getStyle('A7:R8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM); // Accountable Officer box
                $sheet->getStyle('A9:R10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM); // Position box
                $sheet->getStyle('A11:R12')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM); // Office box
                $sheet->getStyle('A13:R13')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM); // Fund Cluster box

                // Apply borders to table header area starting row ~16 (depends on header lines)
                $headerStart = 16; // approximate row where table headers begin
                $sheet->getStyle("A{$headerStart}:R{$headerStart}")->getFont()->setBold(true);

                // Apply thin borders to all used cells
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Fill header columns background
                $sheet->getStyle("A{$headerStart}:R{$headerStart}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');

                // Right-align numeric columns (E,F,G,H,I and P,R)
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                $sheet->getStyle('G')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                $sheet->getStyle('H')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                $sheet->getStyle('I')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                $sheet->getStyle('P')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                $sheet->getStyle('R')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
            }
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }

    public function title(): string
    {
        return 'PPES Report';
    }
}
