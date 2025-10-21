<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPC-PPE Report - PDF</title>
    <style>
        /* Ensure dompdf can load a Unicode font that contains the ₱ glyph.
           Place DejaVuSans.ttf in public/fonts/DejaVuSans.ttf */
        @font-face {
            font-family: 'DejaVu Sans Custom';
            src: url('file://{{ str_replace('\\','/', public_path('fonts/DejaVuSans.ttf')) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            /* Use embedded DejaVu Sans to guarantee peso glyph rendering */
            font-family: 'DejaVu Sans Custom', 'DejaVu Sans', 'Times New Roman', serif;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .report-header h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .report-header p {
            font-size: 11px;
            margin: 5px 0;
        }

        .entity-info {
            text-align: center;
            margin: 15px 0;
            font-size: 10px;
        }

        .accountability-info {
            text-align: center;
            margin: 15px 0;
            font-size: 10px;
            padding: 8px;
            border: 1px solid #000;
        }

        .equipment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 20px;
            border: 1px solid #000;
        }

        .equipment-table th {
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            background: #f0f0f0;
            font-size: 7px;
            text-transform: uppercase;
        }

        .equipment-table td {
            padding: 4px 2px;
            border: 1px solid #000;
            font-size: 8px;
        }

        .classification-header-row {
            background: #e0e0e0 !important;
        }

        .classification-header-row td {
            font-weight: bold;
            font-size: 9px;
            padding: 6px 2px;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .page-break {
            page-break-before: always;
        }

        .footer-info {
            margin-top: 20px;
            font-size: 9px;
            text-align: center;
        }

        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .signature-line {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-line p {
            margin: 5px 0;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT</h1>
        <p>As of {{ $header['as_of'] }}</p>
    </div>

    <div class="entity-info">
        <p><strong>Entity Name:</strong> {{ $header['entity_name'] }}</p>
        <p><strong>Fund Cluster:</strong> {{ $header['fund_cluster'] }}</p>
    </div>

    <div class="accountability-info">
        <p>For which {{ $header['accountable_person'] }}, {{ $header['position'] }}, {{ $header['office'] }} is accountable, having assumed such accountability on {{ isset($header['assumption_date']) && trim($header['assumption_date']) !== '' ? \Carbon\Carbon::parse($header['assumption_date'])->format('F d, Y') : '________________' }}.</p>
    </div>

    @if($groupedEquipment->count() > 0)
        @foreach($groupedEquipment as $classification => $equipmentItems)
            <table class="equipment-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 12%;">ARTICLE/ITEM</th>
                        <th rowspan="2" style="width: 15%;">DESCRIPTION</th>
                        <th rowspan="2" style="width: 10%;">PROPERTY NUMBER</th>
                        <th rowspan="2" style="width: 8%;">UNIT OF MEASURE</th>
                        <th rowspan="2" style="width: 8%;">UNIT VALUE</th>
                        <th rowspan="2" style="width: 8%;">Acquisition Date</th>
                        <th rowspan="2" style="width: 6%;">QUANTITY per PROPERTY CARD</th>
                        <th rowspan="2" style="width: 6%;">QUANTITY per PHYSICAL COUNT</th>
                        <th colspan="2" style="width: 10%;">SHORTAGE/OVERAGE</th>
                        <th colspan="3" style="width: 17%;">REMARKS</th>
                    </tr>
                    <tr>
                        <th style="width: 5%;">Quantity</th>
                        <th style="width: 5%;">Value</th>
                        <th style="width: 6%;">Person Responsible</th>
                        <th style="width: 6%;">Responsibility Center</th>
                        <th style="width: 5%;">Condition of Properties</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Classification Header -->
                    <tr class="classification-header-row">
                        <td colspan="13">
                            {{ strtoupper($classification ?: 'UNCLASSIFIED EQUIPMENT') }}
                        </td>
                    </tr>

                    @php
                        $groupedByArticle = $equipmentItems->groupBy('article');
                    @endphp

                    @foreach($groupedByArticle as $article => $items)
                        @foreach($items as $index => $equipment)
                            <tr>
                                @if($index === 0)
                                    <!-- Show article name only for first item -->
                                    <td rowspan="{{ $items->count() }}" style="vertical-align: middle; font-weight: bold;">
                                        {{ $article }}
                                    </td>
                                @endif

                                <td>{{ $equipment->description ?: '-' }}</td>
                                <td class="text-center">{{ $equipment->property_number }}</td>
                                <td class="text-center">{{ $equipment->unit_of_measurement }}</td>
                                <td class="text-right">{{ number_format($equipment->unit_value, 2) }}</td>
                                <td class="text-center">
                                    {{ $equipment->acquisition_date ? $equipment->acquisition_date->format('M-d') : '-' }}
                                </td>
                                                <td class="text-center">1</td>
                                                <td class="text-center">1</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                <td class="text-center">
                                    {{ $equipment->responsible_person ?: 'Unknown / Book of the Accountant' }}
                                </td>
                                <td class="text-center">
                                    {{ $equipment->location ?: '-' }}
                                </td>
                                <td class="text-center">{{ $equipment->condition }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach
    @endif

    <div class="footer-info">
        <p><strong>Certified Correct:</strong></p>
        <br><br>
        <p>___________________________________</p>
        <p>{{ $header['accountable_person'] }}</p>
        <p>{{ $header['position'] }}</p>
        <p>{{ $header['office'] }}</p>
    </div>
</body>
</html>
