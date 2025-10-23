<?php

namespace App\Exports;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RpcPpeExport implements FromArray, WithEvents
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $query = Equipment::orderBy('classification')
            ->orderBy('article')
            ->orderBy('property_number');

        // Apply filters if provided
        if ($this->request->filled('classification')) {
            $query->where('classification', $this->request->classification);
        }

        if ($this->request->filled('condition')) {
            $query->where('condition', $this->request->condition);
        }

        if ($this->request->filled('date_from')) {
            $query->whereDate('acquisition_date', '>=', $this->request->date_from);
        }

        if ($this->request->filled('date_to')) {
            $query->whereDate('acquisition_date', '<=', $this->request->date_to);
        }

        $equipment = $query->get();

        // Build header values from request
        $entityName = $this->request->query('entity_name') ?: '';
        $accountablePerson = $this->request->query('accountable_person') ?: '';
        $position = $this->request->query('position') ?: '';
        $office = $this->request->query('office') ?: '';
        $fundCluster = $this->request->query('fund_cluster') ?: '';
        $asOfDate = $this->request->query('as_of');
        $formattedDate = $asOfDate ? \Carbon\Carbon::parse($asOfDate)->format('F d, Y') : '';

        $data = [];

        // Header rows
        $data[] = ['Annex A', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 1
        $data[] = ['REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 2
        $data[] = $formattedDate ? ['As of ' . $formattedDate, '', '', '', '', '', '', '', '', '', '', '', '', ''] : ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 3
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Empty row (Row 4)
        // Header grid layout matching screen view exactly
        $data[] = ['Entity Name:', $entityName ?: 'Agricultural Training Institute-RTC I', '', '', 'Accountable Officer:', $accountablePerson ?: 'Franklin A. Salcedo', '', '', 'Position:', $position ?: 'Supply and Property Officer', '', '', 'Office:', $office ?: 'ATI-RTC I']; // Row 5
        $data[] = ['', '', '', '', '', '(Name)', '', '', '', '(Designation)', '', '', '', '(Station)']; // Row 6
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', 'Fund Cluster:', $fundCluster ?: '01']; // Row 7
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Empty row (Row 8)
        $data[] = [
            'Classification',
            'Article/Item',
            'Description',
            'Property Number',
            'Unit of Measure',
            'Unit Value',
            'Acquisition Date',
            'Quantity per Property Card',
            'Quantity per Physical Count',
            'Shortage/Overage Quantity',
            'Shortage/Overage Value',
            'Person Responsible',
            'Responsibility Center',
            'Condition of Properties'
        ]; // Row 9 - Table Header

        // Table rows
        foreach ($equipment as $item) {
            $data[] = [
                $item->classification ?: 'UNCLASSIFIED EQUIPMENT',
                $item->article,
                $item->description ?: '-',
                $item->property_number,
                $item->unit_of_measurement,
                number_format((float) $item->unit_value, 2),
                $item->acquisition_date ? $item->acquisition_date->format('M-d-Y') : '-',
                1, // Quantity per Property Card
                1, // Quantity per Physical Count
                '-', // Shortage/Overage Quantity
                '-', // Shortage/Overage Value
                $item->responsible_person ?: 'Unknown / Book of the Accountant',
                $item->location ?: '-',
                $item->condition
            ];
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Row 1: Annex A - Right aligned
                $sheet->mergeCells('A1:N1');
                $sheet->getStyle('A1')->getFont()->setItalic(true)->setSize(12);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Row 2: Title - Center and bold
                $sheet->mergeCells('A2:N2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Row 3: As of date - Center and bold
                $sheet->mergeCells('A3:N3');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Header styling for entity info - updated for new grid layout
                $sheet->mergeCells('B5:D5'); // Entity Name value
                $sheet->mergeCells('F5:H5'); // Accountable Officer value
                $sheet->mergeCells('J5:L5'); // Position value
                $sheet->mergeCells('N5:N5'); // Office value
                $sheet->mergeCells('B6:D6'); // (Name)
                $sheet->mergeCells('F6:H6'); // (Name) label
                $sheet->mergeCells('J6:L6'); // (Designation)
                $sheet->mergeCells('N6:N6'); // (Station)
                $sheet->mergeCells('M7:N7'); // Fund Cluster value

                // Add borders around header fields to create box effect
                $sheet->getStyle('A5:D7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN); // Entity Name box
                $sheet->getStyle('E5:H7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN); // Accountable Officer box
                $sheet->getStyle('I5:L7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN); // Position box
                $sheet->getStyle('M5:N7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN); // Office box

                // Style header labels
                $sheet->getStyle('A5:A7')->getFont()->setBold(true); // Entity Name, Accountable Officer, Fund Cluster labels
                $sheet->getStyle('E5:K7')->getFont()->setBold(true); // Other labels
                $sheet->getStyle('M7')->getFont()->setBold(true); // Fund Cluster label

                // Row 9: Table header - Bold, center, wrap text
                $sheet->getStyle('A9:N9')->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle('A9:N9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A9:N9')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A9:N9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A9:N9')->getAlignment()->setWrapText(true);

                // Set row heights
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->getRowDimension(9)->setRowHeight(40);

                // Set optimal column widths for better readability
                $sheet->getColumnDimension('A')->setWidth(20); // Classification
                $sheet->getColumnDimension('B')->setWidth(30); // Article/Item
                $sheet->getColumnDimension('C')->setWidth(40); // Description
                $sheet->getColumnDimension('D')->setWidth(22); // Property Number
                $sheet->getColumnDimension('E')->setWidth(16); // Unit of Measure
                $sheet->getColumnDimension('F')->setWidth(15); // Unit Value
                $sheet->getColumnDimension('G')->setWidth(16); // Acquisition Date
                $sheet->getColumnDimension('H')->setWidth(12); // Quantity per Property Card
                $sheet->getColumnDimension('I')->setWidth(12); // Quantity per Physical Count
                $sheet->getColumnDimension('J')->setWidth(12); // Shortage/Overage Quantity
                $sheet->getColumnDimension('K')->setWidth(12); // Shortage/Overage Value
                $sheet->getColumnDimension('L')->setWidth(20); // Person Responsible
                $sheet->getColumnDimension('M')->setWidth(20); // Responsibility Center
                $sheet->getColumnDimension('N')->setWidth(16); // Condition of Properties
            },

        ];
    }
}
