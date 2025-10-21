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
        $data[] = ['REPORT ON THE PHYSICAL COUNT OF PROPERTY PLANT AND EQUIPMENT', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 2
        $data[] = $formattedDate ? ['As of ' . $formattedDate, '', '', '', '', '', '', '', '', '', '', '', '', ''] : ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 3
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Empty row (Row 4)
        $data[] = $entityName ? ['Entity Name: ' . $entityName, '', '', '', '', '', '', '', '', '', '', '', '', ''] : ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 5
        $data[] = $accountablePerson ? [$accountablePerson, '', '', '', '', '', '', '', '', '', '', '', '', ''] : ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 6
        $data[] = ['(Name of Accountable Officer)', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 7
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Empty row (Row 8)
        $data[] = $position ? [$position, '', '', '', '', '', '', '', '', '', '', '', '', ''] : ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 9
        $data[] = ['(Designation)', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 10
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Empty row (Row 11)
        $data[] = $office ? [$office, '', '', '', '', '', '', '', '', '', '', '', '', ''] : ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 12
        $data[] = ['(Station)', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 13
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Empty row (Row 14)
        $data[] = $fundCluster ? ['Fund Cluster : ' . $fundCluster, '', '', '', '', '', '', '', '', '', '', '', '', ''] : ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Row 15
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '']; // Empty row (Row 16)
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
        ]; // Row 17 - Table Header

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

                // Header styling for entity info
                $sheet->getStyle('A5:N5')->getFont()->setBold(true); // Entity Name
                $sheet->getStyle('A6:N6')->getFont()->setBold(true); // Accountable Person
                $sheet->getStyle('A9:N9')->getFont()->setBold(true); // Position
                $sheet->getStyle('A12:N12')->getFont()->setBold(true); // Office
                $sheet->getStyle('A15:N15')->getFont()->setBold(true); // Fund Cluster

                // Row 17: Table header - Bold, center, wrap text
                $sheet->getStyle('A17:N17')->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle('A17:N17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A17:N17')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A17:N17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A17:N17')->getAlignment()->setWrapText(true);

                // Set row heights
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->getRowDimension(17)->setRowHeight(40);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(20); // Classification
                $sheet->getColumnDimension('B')->setWidth(25); // Article/Item
                $sheet->getColumnDimension('C')->setWidth(30); // Description
                $sheet->getColumnDimension('D')->setWidth(18); // Property Number
                $sheet->getColumnDimension('E')->setWidth(12); // Unit of Measure
                $sheet->getColumnDimension('F')->setWidth(12); // Unit Value
                $sheet->getColumnDimension('G')->setWidth(12); // Acquisition Date
                $sheet->getColumnDimension('H')->setWidth(8); // Quantity per Property Card
                $sheet->getColumnDimension('I')->setWidth(8); // Quantity per Physical Count
                $sheet->getColumnDimension('J')->setWidth(8); // Shortage/Overage Quantity
                $sheet->getColumnDimension('K')->setWidth(8); // Shortage/Overage Value
                $sheet->getColumnDimension('L')->setWidth(15); // Person Responsible
                $sheet->getColumnDimension('M')->setWidth(15); // Responsibility Center
                $sheet->getColumnDimension('N')->setWidth(12); // Condition of Properties
            },

        ];
    }
}
