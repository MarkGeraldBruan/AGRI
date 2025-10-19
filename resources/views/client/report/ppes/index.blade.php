<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPES - Inventory and Inspection Report of Unserviceable Property</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .ppes-content {
            padding: 20px;
            background: white;
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
        }

        .report-header h1 {
            color: #000;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #296218;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background-color: #1e4612;
            color: white;
        }

        .filters-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            align-items: end;
        }

        .filter-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 6px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #296218;
            color: white;
        }

        .btn-primary:hover {
            background: #1e4612;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .report-table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }

        .report-table th {
            background-color: #e8f5e9;
            color: #000;
            font-weight: 600;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        .export-btn {
            background-color: #27ae60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .export-btn.pdf {
            background-color: #e74c3c;
        }

        .export-btn.excel {
            background-color: #3498db;
        }

        .export-btn:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }

        .print-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #296218;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            cursor: pointer;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .print-button:hover {
            background: #1e4612;
            transform: scale(1.05);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        @media print {
            body {
                background: white;
            }

            .back-button, .filters-section, .print-button {
                display: none !important;
            }

            .ppes-content {
                padding: 0;
            }

            .report-table th,
            .report-table td {
                border: 1px solid #000 !important;
                padding: 4px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    @include('layouts.core.sidebar')
    <div class="details">
        @include('layouts.core.header')

        <div class="ppes-content">
            <a href="{{ route('client.reports.index') }}" class="back-button">
                <ion-icon name="arrow-back-outline"></ion-icon>
                Back to Reports
            </a>

            <div class="ppes-header">
                <h1>INVENTORY AND INSPECTION REPORT OF UNSERVICEABLE PROPERTY</h1>
                <h2>(ATI-RTC I, Sta. Barbara, Pangasinan)</h2>
            </div>
            <div class="report-info">
                <p><strong>As of {!! isset($header['as_of']) && trim($header['as_of']) !== '' ? e($header['as_of']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}</strong></p>

                <div class="header-grid" style="display:grid;grid-template-columns: repeat(4, 1fr);gap: 20px;text-align:center;">
                    <div>
                        <p style="margin:0; font-weight:600;">Entity Name:</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">
                            {!! isset($header['entity_name']) && trim($header['entity_name']) !== '' ? e($header['entity_name']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}
                        </p>
                    </div>

                    <div>
                        <p style="margin:0; font-weight:600;">Accountable Officer:</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">
                            {!! isset($header['accountable_person']) && trim($header['accountable_person']) !== '' ? e($header['accountable_person']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}
                        </p>
                        <p style="margin:4px 0 0 0; font-style:italic;">(Name)</p>
                    </div>

                    <div>
                        <p style="margin:0; font-weight:600;">Position:</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">
                            {!! isset($header['position']) && trim($header['position']) !== '' ? e($header['position']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}
                        </p>
                        <p style="margin:4px 0 0 0; font-style:italic;">(Designation)</p>
                    </div>

                    <div>
                        <p style="margin:0; font-weight:600;">Office:</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">
                            {!! isset($header['office']) && trim($header['office']) !== '' ? e($header['office']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}
                        </p>
                        <p style="margin:4px 0 0 0; font-style:italic;">(Station)</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">Fund Cluster: {!! isset($header['fund_cluster']) && trim($header['fund_cluster']) !== '' ? '<span style="border-bottom:1px solid #000; padding:0 18px;">' . e($header['fund_cluster']) . '</span>' : '<span style="border-bottom:1px solid #000;padding:0 18px;display:inline-block;">&nbsp;</span>' !!}</p>
                    </div>
                </div>

                {{-- Header input form --}}
                <div class="filters-section" style="margin-top:20px;">
                    <form method="get" class="filters-form">
                        {{-- preserve current filters as hidden inputs --}}
                        <input type="hidden" name="condition" value="{{ request('condition') }}">
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                        <input type="hidden" name="classification" value="{{ request('classification') }}">

                        <div class="filter-group">
                            <label>As of</label>
                            <input type="date" name="as_of" value="{{ request('as_of') ?? now()->format('Y-m-d') }}">
                        </div>
                        <div class="filter-group">
                            <label>Entity Name</label>
                            <input type="text" name="entity_name" value="{{ request('entity_name') ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label>Fund Cluster</label>
                            <input type="text" name="fund_cluster" value="{{ request('fund_cluster') ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label>Accountable Person</label>
                            <input type="text" name="accountable_person" value="{{ request('accountable_person') ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label>Position</label>
                            <input type="text" name="position" value="{{ request('position') ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label>Office</label>
                            <input type="text" name="office" value="{{ request('office') ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary">Apply Header</button>
                            <a href="{{ route('client.report.ppes') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>

            {{-- Floating action export buttons --}}
            @include('client.report._export_fab', [
                'excelUrl' => route('client.report.ppes.export.excel', request()->all()),
                'pdfUrl' => route('client.report.ppes.export.pdf', request()->all())
            ])

            <div class="report-table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th colspan="10">INVENTORY</th>
                            <th colspan="5">INSPECTION and DISPOSAL</th>
                            <th colspan="1">Appraised Value</th>
                            <th colspan="2">RECORD OF SALES</th>
                        </tr>
                        <tr>
                            <th>Date Acquired</th>
                            <th>Particulars/ Articles</th>
                            <th>Property No.</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Accumulated Depreciation</th>
                            <th>Accumulated Impairment Losses</th>
                            <th>Carrying Amount</th>
                            <th>Remarks</th>
                            <th colspan="5">DISPOSAL</th>
                            <th></th>
                            <th>OR No.</th>
                            <th>Amount</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>Sale</th>
                            <th>Transfer</th>
                            <th>Destruction</th>
                            <th>Others (Specify)</th>
                            <th>Total</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th>(1)</th>
                            <th>(2)</th>
                            <th>(3)</th>
                            <th>(4)</th>
                            <th>(5)</th>
                            <th>(6)</th>
                            <th>(7)</th>
                            <th>(8)</th>
                            <th>(9)</th>
                            <th>(10)</th>
                            <th>(11)</th>
                            <th>(12)</th>
                            <th>(13)</th>
                            <th>(14)</th>
                            <th>(15)</th>
                            <th>(16)</th>
                            <th>(17)</th>
                            <th>(18)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ppesItems as $item)
                        <tr>
                            <td>{{ $item->date_acquired }}</td>
                            <td>{{ $item->particulars_articles }}</td>
                            <td>{{ $item->property_no }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>₱ {{ number_format((float) ($item->unit_cost ?? 0), 2) }}</td>
                            <td>₱ {{ number_format((float) ($item->total_cost ?? 0), 2) }}</td>
                            <td>₱ {{ number_format((float) ($item->accumulated_depreciation ?? 0), 2) }}</td>
                            <td>₱ {{ number_format((float) ($item->accumulated_impairment_losses ?? 0), 2) }}</td>
                            <td>₱ {{ number_format((float) ($item->carrying_amount ?? 0), 2) }}</td>
                            <td>{{ $item->remarks }}</td>
                            <td>{{ $item->sale }}</td>
                            <td>{{ $item->transfer }}</td>
                            <td>{{ $item->destruction }}</td>
                            <td>{{ $item->others }}</td>
                            <td>{{ $item->total_disposal }}</td>
                            <td>₱ {{ number_format((float) ($item->appraised_value ?? 0), 2) }}</td>
                            <td>{{ $item->or_no }}</td>
                            <td>₱ {{ number_format((float) ($item->amount ?? 0), 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="18">No unserviceable equipment found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
